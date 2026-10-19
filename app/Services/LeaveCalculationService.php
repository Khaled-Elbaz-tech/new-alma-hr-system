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
     * Calculate working days between two dates, excluding weekends and holidays.
     * Uses WeekendSetting config for weekend days (defaults to Friday+Saturday).
     */
    public function calculateWorkingDays(Carbon $startDate, Carbon $endDate, int $employeeId = null): int
    {
        // Get configured weekend days from settings
        $weekendDays = WeekendSetting::getWeekendDays();
        $dayMap = [
            'sunday' => 0, 'monday' => 1, 'tuesday' => 2,
            'wednesday' => 3, 'thursday' => 4, 'friday' => 5, 'saturday' => 6
        ];
        $weekendNums = [];
        foreach ($weekendDays as $day) {
            $dayLower = strtolower($day);
            if (isset($dayMap[$dayLower])) {
                $weekendNums[] = $dayMap[$dayLower];
            }
        }

        // Default to Friday+Saturday if no settings found
        if (empty($weekendNums)) {
            $weekendNums = [5, 6]; // Friday, Saturday
        }

        // Get holidays within date range
        $holidayDates = collect();
        $holidays = Holiday::where(function ($q) use ($startDate, $endDate) {
            $q->whereBetween('start_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
              ->orWhereBetween('end_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
              ->orWhere(function ($sq) use ($startDate, $endDate) {
                  $sq->where('start_date', '<=', $startDate->format('Y-m-d'))
                     ->where('end_date', '>=', $endDate->format('Y-m-d'));
              });
        })->get();

        foreach ($holidays as $holiday) {
            $hStart = Carbon::parse($holiday->start_date)->max($startDate);
            $hEnd = Carbon::parse($holiday->end_date)->min($endDate);
            for ($d = $hStart; $d->lte($hEnd); $d->addDay()) {
                $holidayDates->push($d->format('Y-m-d'));
            }
        }

        // Count working days (excluding weekends and holidays)
        $workingDays = 0;
        for ($d = $startDate->copy(); $d->lte($endDate); $d->addDay()) {
            if (in_array($d->dayOfWeek, $weekendNums)) {
                continue;
            }
            if ($holidayDates->contains($d->format('Y-m-d'))) {
                continue;
            }
            $workingDays++;
        }

        return $workingDays;
    }

    /**
     * Calculate calendar days between two dates (inclusive).
     */
    public function calculateCalendarDays(Carbon $startDate, Carbon $endDate): int
    {
        return $startDate->diffInDays($endDate) + 1;
    }
}
