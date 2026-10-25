<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\Holiday;
use App\Models\WeekendSetting;
use Carbon\Carbon;

class LeaveCalculationService
{
    /**
     * Annual leave entitlement (from Excel spec)
     */
    private const ANNUAL_ENTITLEMENT = 21; // days
    private const MONTHLY_ENTITLEMENT = 1.75; // days (21 / 12)

    /**
     * Get complete leave analysis for an employee
     * Matches Excel "Analyzer" sheet exactly
     *
     * @param Employee $employee
     * @return array
     *
     * Response structure:
     * [
     *     'hiring_date' => '2023-01-15',
     *     'years' => [
     *         2023 => [
     *             'year' => 2023,
     *             'months_from_hiring' => 11,
     *             'days_entitled' => 19.25,
     *             'days_used' => 10.50,
     *             'ticket_count' => 2,
     *             'exit_reentry_count' => 1,
     *             'balance' => 8.75,
     *         ],
     *         ...
     *     ]
     * ]
     */
    public function analyze(Employee $employee): array
    {
        $joiningDate = $employee->joining_date;
        $joiningYear = $joiningDate->year;
        $currentYear = Carbon::now()->year;

        $yearlyData = [];

        // Generate data for each year from joining to current
        for ($year = $joiningYear; $year <= $currentYear; $year++) {
            $yearlyData[$year] = $this->calculateYearData($employee, $year);
        }

        return [
            'hiring_date' => $joiningDate->format('Y-m-d'),
            'years' => $yearlyData,
        ];
    }

    /**
     * Calculate data for a specific year
     * Excel columns: Months From Hiring, Days Entitled, Days Used, Ticket, Exit/Re-entry, Balance
     */
    public function calculateYearData(Employee $employee, int $year): array
    {
        // 1. Months from hiring (months worked in this year)
        $monthsFromHiring = $this->calculateMonthsAtYearEnd($employee, $year);

        // 2. Days entitled (prorated: 21/12 * months)
        $daysEntitled = $this->calculateDaysEntitled($employee, $year);

        // 3. Days used (sum of approved leave days in this year)
        $daysUsed = $this->calculateDaysUsed($employee, $year);

        // 4. Ticket count (flight tickets)
        $ticketCount = $this->calculateTicketCount($employee, $year);

        // 5. Exit/Re-entry count
        $exitReentryCount = $this->calculateExitReentryCount($employee, $year);

        // 6. Balance (can be negative) - with carry-forward from previous year
        $joiningYear = Carbon::parse($employee->joining_date)->year;
        if ($year < $joiningYear) {
            // Before hiring year - employee didn't exist yet
            return [
                'year' => $year,
                'months_from_hiring' => 0,
                'days_entitled' => 0,
                'days_used' => 0,
                'ticket_count' => 0,
                'exit_reentry_count' => 0,
                'balance' => 0,
            ];
        }

        $previousYearData = $this->calculateYearData($employee, $year - 1);
        $previousBalance = $previousYearData['balance'] ?? 0;
        $balance = $previousBalance + $daysEntitled - $daysUsed;

        return [
            'year' => $year,
            'months_from_hiring' => $monthsFromHiring,
            'days_entitled' => round($daysEntitled, 2),
            'days_used' => round($daysUsed, 2),
            'ticket_count' => $ticketCount,
            'exit_reentry_count' => $exitReentryCount,
            'balance' => round($balance, 2),
        ];
    }


    /**
     * Visible annual balance under the company leave-visibility policy.
     *
     * RULE (user-confirmed): an employee's CURRENT-year annual entitlement
     * is never visible and never counted during that year. It "opens"
     * automatically on 1 January of the next year (pure date logic — no
     * migration, no rollover job). Visible balance = entitlements of all
     * years BEFORE the current year, minus approved annual leave used in
     * ALL years (current-year requests deduct from the carried balance).
     * Negative results are allowed and shown as-is, never floored at zero.
     * Scope: ANNUAL leave only (code 'annual'), like the rest of this engine.
     *
     * Returns rows from the joining year through the current year:
     *   rows[] = ['year', 'entitled' (null = hidden, current year only),
     *             'used', 'remaining' (running visible balance),
     *             'is_current', 'opens_on']
     * plus visible_entitled / visible_used / visible_balance totals.
     */
    public function visibleBalanceData(Employee $employee): array
    {
        $currentYear = Carbon::now()->year;
        $joiningYear = Carbon::parse($employee->joining_date)->year;

        $rows = [];
        $visibleEntitled = 0.0;
        $visibleUsed = 0.0;
        $running = 0.0;

        for ($year = $joiningYear; $year <= $currentYear; $year++) {
            $data = $this->calculateYearData($employee, $year);
            $isCurrent = ($year === $currentYear);
            // Current year: entitlement hidden (counted as 0 until 1 January).
            $entitled = $isCurrent ? null : (float) ($data['days_entitled'] ?? 0);
            $used = (float) ($data['days_used'] ?? 0);

            if (!$isCurrent) {
                $visibleEntitled += $entitled;
            }
            $visibleUsed += $used;
            $running += ($entitled ?? 0) - $used;

            $rows[] = [
                'year' => $year,
                'entitled' => $entitled,
                'used' => round($used, 2),
                'remaining' => round($running, 2),
                'is_current' => $isCurrent,
                'opens_on' => $isCurrent ? ('1 January ' . ($currentYear + 1)) : null,
            ];
        }

        return [
            'rows' => $rows,
            'visible_entitled' => round($visibleEntitled, 2),
            'visible_used' => round($visibleUsed, 2),
            'visible_balance' => round($visibleEntitled - $visibleUsed, 2),
            'current_year' => $currentYear,
            'opens_on' => '1 January ' . ($currentYear + 1),
        ];
    }

    /**
     * Calculate months from hiring at end of a specific year
     * Excel: "Months From Hiring (Year)"
     *
     * Calculates the number of months the employee worked IN this year,
     * not the total months from hiring to end of year.
     *
     * Example: Hiring 2025-01-15, Year 2026 → months worked in 2026 = up to 12
     *          (from Jan 2026 to current month if current year, or to Dec)
     */
    public function calculateMonthsAtYearEnd(Employee $employee, int $year): int
    {
        $yearStart = Carbon::create($year, 1, 1)->startOfDay();
        $yearEnd = Carbon::create($year, 12, 31)->endOfDay();
        $joiningDate = $employee->joining_date;

        // If employee joined after this year ended, 0 months
        if ($joiningDate->year > $year) {
            return 0;
        }

        // Work start is the later of year start or joining date
        $workStart = $joiningDate->lt($yearStart) ? $yearStart : $joiningDate;

        // Effective end is min(year end, now) for current year
        $effectiveEnd = min($yearEnd, Carbon::now());

        if ($workStart->gt($effectiveEnd)) {
            return 0;
        }

        // Calculate months worked in this year
        $months = ($effectiveEnd->year - $workStart->year) * 12 +
                  ($effectiveEnd->month - $workStart->month) + 1;

        return max(0, min(12, $months));
    }

    /**
     * Calculate entitled days for a year
     * Excel: "Days Entitled (Year)" = 21 days × (Months From Hiring / 12)
     * Or: 1.75 days per month × months worked
     */
    private function calculateDaysEntitled(Employee $employee, int $year): float
    {
        $months = $this->calculateMonthsAtYearEnd($employee, $year);
        $entitled = round(self::MONTHLY_ENTITLEMENT * $months, 2);
        return min($entitled, $employee->annual_leave_entitlement ?? 21);
    }

    /**
     * Calculate total approved ANNUAL leave days for a year
     * Only counts leaves with type "Annual Leave" - does NOT include other leave types
     * Excel: Days Used (Year) = sum of annual leave days only
     */
    private function calculateDaysUsed(Employee $employee, int $year): float
    {
        $total = LeaveRequest::where('employee_id', $employee->id)
            ->where('status', 'approved')
            ->whereYear('start_date', $year)
            ->whereHas('leaveType', function ($q) {
                $q->where('code', 'annual'); // ONLY annual leave affects balance
            })
            ->sum('total_days');

        return (float) $total;
    }

    /**
     * Count flight tickets for approved ANNUAL leaves in a year
     * Excel: Ticket (Year) - only for annual leave
     */
    private function calculateTicketCount(Employee $employee, int $year): int
    {
        return LeaveRequest::where('employee_id', $employee->id)
            ->where('status', 'approved')
            ->where('flight_ticket_required', true)
            ->whereYear('start_date', $year)
            ->whereHas('leaveType', function ($q) {
                $q->where('code', 'annual');
            })
            ->count();
    }

    /**
     * Count exit/re-entry for approved ANNUAL leaves in a year
     * Excel: Exit/Re-entry (Year) - only for annual leave
     */
    private function calculateExitReentryCount(Employee $employee, int $year): int
    {
        return LeaveRequest::where('employee_id', $employee->id)
            ->where('status', 'approved')
            ->where('exit_reentry_required', true)
            ->whereYear('start_date', $year)
            ->whereHas('leaveType', function ($q) {
                $q->where('code', 'annual');
            })
            ->count();
    }

    /**
     * Initialize/update leave balance for all employees for a given year
     * DEPRECATED: Balances are now calculated dynamically on-the-fly.
     * This method is kept for backward compatibility but does nothing.
     */
    public function initializeBalancesForYear(int $year): void
    {
        // No-op: balances are calculated dynamically from Hire Date + Leave Requests
    }

    /**
     * Effective end date of a holiday. A holiday with an empty end_date is a
     * single-day holiday (end = start). This matters because
     * Carbon::parse(null) returns "now", which would otherwise over-exclude
     * every day up to today. Used by the breakdown and by the holiday
     * recalculation in HolidayController (via the same rule there).
     */
    public function holidayEndDate(Holiday $holiday): Carbon
    {
        return $holiday->end_date
            ? Carbon::parse($holiday->end_date)->startOfDay()
            : Carbon::parse($holiday->start_date)->startOfDay();
    }

    /**
     * Full breakdown of a leave range, so the number on the screen can be
     * explained instead of just stated:
     *   calendar_days = working_days + weekend_days + holiday_days
     * A holiday that falls on a weekend counts as a WEEKEND day, not a
     * holiday day (same precedence as the old counter: weekends first),
     * so the three parts always sum exactly to the calendar span.
     * weekend_day_names = the configured weekend weekday names
     * (e.g. Friday, Saturday). holidays = one entry per excluded
     * non-weekend holiday DATE in range: ['date' => 'Y-m-d', 'name' => ...].
     * $employeeId is accepted for signature parity with
     * calculateWorkingDays; the calculation itself is company-wide
     * (weekends + holidays are not per-employee).
     */
    public function calculateWorkingDaysBreakdown(Carbon $startDate, Carbon $endDate, int $employeeId = null): array
    {
        $start = $startDate->copy()->startOfDay();
        $end = $endDate->copy()->startOfDay();

        $dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        $dayMap = [
            'sunday' => 0, 'monday' => 1, 'tuesday' => 2,
            'wednesday' => 3, 'thursday' => 4, 'friday' => 5, 'saturday' => 6,
        ];
        $weekendNums = [];
        foreach (WeekendSetting::getWeekendDays() as $day) {
            $lower = strtolower($day);
            if (isset($dayMap[$lower])) {
                $weekendNums[] = $dayMap[$lower];
            }
        }
        if (empty($weekendNums)) {
            $weekendNums = [5, 6]; // Friday, Saturday
        }
        $weekendDayNames = array_values(array_unique(array_map(fn ($n) => $dayNames[$n], $weekendNums)));

        if ($end->lt($start)) {
            return [
                'calendar_days' => 0, 'weekend_days' => 0, 'weekend_day_names' => $weekendDayNames,
                'holiday_days' => 0, 'holidays' => [], 'working_days' => 0,
            ];
        }

        // Holidays overlapping the range. end_date may be null (single day);
        // the start_date BETWEEN clause catches those, and the effective
        // end below keeps the per-day expansion to that single day.
        $holidays = Holiday::where(function ($q) use ($start, $end) {
            $q->whereBetween('start_date', [$start->format('Y-m-d'), $end->format('Y-m-d')])
              ->orWhereBetween('end_date', [$start->format('Y-m-d'), $end->format('Y-m-d')])
              ->orWhere(function ($sq) use ($start, $end) {
                  $sq->where('start_date', '<=', $start->format('Y-m-d'))
                     ->where('end_date', '>=', $end->format('Y-m-d'));
              });
        })->get();

        $holidayByDate = [];
        foreach ($holidays as $holiday) {
            $hStart = Carbon::parse($holiday->start_date)->max($start);
            $hEnd = $this->holidayEndDate($holiday)->min($end);
            for ($d = $hStart->copy(); $d->lte($hEnd); $d->addDay()) {
                $key = $d->format('Y-m-d');
                if (!isset($holidayByDate[$key])) {
                    $holidayByDate[$key] = $holiday->name;
                }
            }
        }

        $calendarDays = 0;
        $weekendDays = 0;
        $holidayDays = 0;
        $holidayDates = [];
        $workingDays = 0;
        for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
            $calendarDays++;
            $key = $d->format('Y-m-d');
            if (in_array($d->dayOfWeek, $weekendNums)) {
                $weekendDays++;
            } elseif (isset($holidayByDate[$key])) {
                $holidayDays++;
                $holidayDates[] = ['date' => $key, 'name' => $holidayByDate[$key]];
            } else {
                $workingDays++;
            }
        }

        return [
            'calendar_days' => $calendarDays,
            'weekend_days' => $weekendDays,
            'weekend_day_names' => $weekendDayNames,
            'holiday_days' => $holidayDays,
            'holidays' => $holidayDates,
            'working_days' => $workingDays,
        ];
    }

    /**
     * Calculate working days between two dates, excluding weekends and holidays.
     * Uses WeekendSetting config for weekend days (defaults to Friday+Saturday).
     * Delegates to calculateWorkingDaysBreakdown so the stored total and the
     * displayed explanation can never disagree.
     */
    public function calculateWorkingDays(Carbon $startDate, Carbon $endDate, int $employeeId = null): int
    {
        return $this->calculateWorkingDaysBreakdown($startDate, $endDate, $employeeId)['working_days'];
    }

    /**
     * Approved ANNUAL requests of one employee, grouped by start_date year.
     * This is exactly the population behind calculateDaysUsed()'s Used
     * figure (approved + code 'annual' + start year), so a year's list
     * always sums to that year's Used total. Used by the expandable
     * per-year breakdowns on the balance pages.
     *
     * @return array<int, \Illuminate\Support\Collection>
     */
    public function approvedAnnualRequestsByYear(Employee $employee): array
    {
        $grouped = [];
        $requests = LeaveRequest::where('employee_id', $employee->id)
            ->where('status', 'approved')
            ->whereHas('leaveType', function ($q) {
                $q->where('code', 'annual');
            })
            ->with('leaveType')
            ->orderBy('start_date')
            ->get();
        foreach ($requests as $request) {
            $grouped[Carbon::parse($request->start_date)->year][] = $request;
        }
        return $grouped;
    }

    /**
     * Calculate calendar days between two dates (inclusive).
     */
    public function calculateCalendarDays(Carbon $startDate, Carbon $endDate): int
    {
        return $startDate->diffInDays($endDate) + 1;
    }
}
