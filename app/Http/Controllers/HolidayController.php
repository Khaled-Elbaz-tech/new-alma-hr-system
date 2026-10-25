<?php

namespace App\Http\Controllers;

use App\Models\Holiday;
use App\Models\LeaveRequest;
use App\Services\LeaveCalculationService;
use App\Models\WeekendSetting;
use App\Models\ActivityLog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class HolidayController extends Controller
{
    public function index(Request $request)
    {
        $year = $request->year ?? Carbon::now()->year;

        $holidays = Holiday::where('year', $year)
            ->when($request->filled('search'), function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%');
            })
            ->when($request->filled('is_recurring') && in_array($request->is_recurring, ['0', '1'], true), function ($q) use ($request) {
                $q->where('is_recurring', (bool) $request->is_recurring);
            })
            ->orderBy('start_date')
            ->get();

        $weekendSettings = WeekendSetting::all();

        return view('holidays.index', compact('holidays', 'weekendSettings', 'year'));
    }

    public function create()
    {
        return view('holidays.create');
    }

    /**
     * L2 — duplicate rule. The Holiday model has a RANGE (start_date →
     * end_date), not a single `date` column, so "duplicate on the same date"
     * is enforced as "any overlapping range": a new/edited holiday whose
     * range shares at least one day with an existing holiday is rejected
     * with a clear error. This subsumes the exact-same-date case. Holidays
     * are company-wide (no region column), so no region scoping applies.
     * On update the holiday itself is excluded from the check.
     */
    protected function overlappingHolidayExists($startDate, $endDate, $exceptId = null)
    {
        // An empty end_date means a single day (see effectiveEndDate).
        $endDate = $endDate ?: $startDate;
        return Holiday::where('start_date', '<=', $endDate)
            ->where(function ($q) use ($startDate) {
                // A null end_date is a single day: it overlaps only when
                // its start_date falls inside the range (not merely
                // before the range end).
                $q->where('end_date', '>=', $startDate)
                  ->orWhere(function ($sq) use ($startDate) {
                      $sq->whereNull('end_date')->where('start_date', '>=', $startDate);
                  });
            })
            ->when($exceptId, function ($q) use ($exceptId) {
                $q->where('id', '!=', $exceptId);
            })
            ->exists();
    }

    /**
     * L2 — year derivation. The create/edit forms DO submit a separate
     * `year` select, but it is now IGNORED: the stored year is always
     * derived server-side from start_date's year (Carbon::parse(...)->year).
     * A submitted year that disagrees with the dates can no longer file a
     * holiday under the wrong year in the index filter (which queries
     * where('year', $year)). The form field is left in place (no form
     * change in this controller-only fix) so old cached forms still submit
     * cleanly; its value is simply overwritten. A range spanning New Year
     * is filed under its start year only — same as the existing index
     * design, which has a single year column.
     */
    protected function holidayData(Request $request)
    {
        return [
            'name' => $request->name,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date ?: $request->start_date,
            'year' => Carbon::parse($request->start_date)->year,
            'is_recurring' => $request->boolean('is_recurring'),
            'description' => $request->description,
        ];
    }

    /**
     * Effective end of a holiday: an empty end_date means a single-day
     * holiday (end = start). Stored rows are normalised to this in
     * holidayData(), but legacy rows may still carry a null end_date,
     * so the recompute and the breakdown both apply this rule defensively.
     */
    protected function effectiveEndDate(Holiday $holiday): Carbon
    {
        return $holiday->end_date
            ? Carbon::parse($holiday->end_date)->startOfDay()
            : Carbon::parse($holiday->start_date)->startOfDay();
    }

    /**
     * Leave-clarity (Task 3): recompute stored days for every NON-cancelled
     * leave request overlapping [$rangeStart, $rangeEnd] against the
     * CURRENT weekends + holidays, and update only rows whose days
     * actually changed. Never touches cancelled requests, never changes
     * dates, types or statuses — only total_days / calendar_days.
     * Returns ['changed' => int, 'days_delta' => float] where days_delta
     * = sum(old_days - new_days): positive means days were RETURNED to
     * employees (holiday added), negative means days are now COUNTED
     * again (holiday removed/moved away).
     */
    protected function recalculateRequestsForRange(string $rangeStart, string $rangeEnd, Request $request, string $holidayName): array
    {
        $service = app(LeaveCalculationService::class);
        $changed = 0;
        $daysDelta = 0.0;

        $requests = LeaveRequest::where('status', '!=', 'cancelled')
            ->where('start_date', '<=', $rangeEnd)
            ->where('end_date', '>=', $rangeStart)
            ->get();

        foreach ($requests as $leave) {
            $newWorking = $service->calculateWorkingDays(
                Carbon::parse($leave->start_date),
                Carbon::parse($leave->end_date),
                $leave->employee_id
            );
            $newCalendar = Carbon::parse($leave->start_date)->diffInDays(Carbon::parse($leave->end_date)) + 1;
            if ((float) $leave->total_days === (float) $newWorking && (int) $leave->calendar_days === $newCalendar) {
                continue;
            }
            $oldDays = $leave->total_days;
            $leave->update(['total_days' => $newWorking, 'calendar_days' => $newCalendar]);
            $changed++;
            $daysDelta += (float) $oldDays - (float) $newWorking;
            $employeeName = $leave->employee->user->name ?? 'Unknown';
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'update',
                'model_type' => LeaveRequest::class,
                'model_id' => $leave->id,
                'description' => "Leave days recalculated after holiday change '{$holidayName}' (employee: {$employeeName}, {$oldDays} days -> {$newWorking} days)",
                'ip_address' => $request->ip(),
            ]);
        }

        return ['changed' => $changed, 'days_delta' => round($daysDelta, 2)];
    }

    protected function recalculationFlash(array $result, string $verb): string
    {
        $n = $result['changed'];
        $delta = $result['days_delta'];
        if ($n === 0) {
            return 'No leave requests needed recalculation.';
        }
        $daysText = abs($delta) == 1 ? 'day' : 'days';
        if ($delta >= 0) {
            return "{$n} request(s) recalculated, {$delta} {$daysText} returned.";
        }
        return "{$n} request(s) recalculated, " . abs($delta) . " {$daysText} now counted again.";
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            // 'year' deliberately NOT required: it is derived from start_date
            // (see holidayData()). A submitted year is accepted but ignored.
            'year' => 'nullable|integer',
            'is_recurring' => 'boolean',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        if ($this->overlappingHolidayExists($request->start_date, $request->end_date)) {
            return back()->withErrors([
                'start_date' => 'A holiday already exists covering one or more of these dates. Please edit the existing holiday instead of creating a duplicate.',
            ])->withInput();
        }

        $data = $this->holidayData($request);
        $holiday = Holiday::create($data);

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'create',
            'model_type' => Holiday::class,
            'model_id' => $holiday->id,
            'new_values' => $data,
            'ip_address' => $request->ip(),
        ]);

        $recalc = $this->recalculateRequestsForRange($holiday->start_date->format('Y-m-d'), $this->effectiveEndDate($holiday)->format('Y-m-d'), $request, $holiday->name);

        return redirect()->route('holidays.index')->with('success', 'Holiday created successfully. ' . $this->recalculationFlash($recalc, 'created'));
    }

    public function edit(Holiday $holiday)
    {
        return view('holidays.edit', compact('holiday'));
    }

    public function update(Request $request, Holiday $holiday)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            // 'year' deliberately NOT required: derived from start_date.
            'year' => 'nullable|integer',
            'is_recurring' => 'boolean',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        if ($this->overlappingHolidayExists($request->start_date, $request->end_date, $holiday->id)) {
            return back()->withErrors([
                'start_date' => 'A holiday already exists covering one or more of these dates. Please edit the existing holiday instead of creating a duplicate.',
            ])->withInput();
        }

        $oldValues = $holiday->toArray();
        $oldStart = Carbon::parse($holiday->start_date)->format('Y-m-d');
        $oldEnd = $this->effectiveEndDate($holiday)->format('Y-m-d');

        $data = $this->holidayData($request);
        $holiday->update($data);
        $holiday->refresh();

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'update',
            'model_type' => Holiday::class,
            'model_id' => $holiday->id,
            'old_values' => $oldValues,
            'new_values' => $data,
            'ip_address' => $request->ip(),
        ]);

        // Union of old + new ranges: moving a holiday returns days on
        // the old dates AND removes them on the new dates.
        $newStart = Carbon::parse($holiday->start_date)->format('Y-m-d');
        $newEnd = $this->effectiveEndDate($holiday)->format('Y-m-d');
        $recalc = $this->recalculateRequestsForRange(
            min($oldStart, $newStart), max($oldEnd, $newEnd), $request, $holiday->name
        );

        return redirect()->route('holidays.index')->with('success', 'Holiday updated successfully. ' . $this->recalculationFlash($recalc, 'updated'));
    }

    public function destroy(Request $request, Holiday $holiday)
    {
        // Leave-clarity (Task 3) BEHAVIOUR CHANGE: deleting a holiday no
        // longer refuses when approved leaves used it. Instead the holiday
        // is deleted and every overlapping non-cancelled request is
        // recalculated, so those days count as leave again. The old
        // block is removed deliberately — a holiday day must not count
        // as leave while it exists, and must count again once removed.
        $rangeStart = Carbon::parse($holiday->start_date)->format('Y-m-d');
        $rangeEnd = $this->effectiveEndDate($holiday)->format('Y-m-d');
        $holidayName = $holiday->name;

        $holiday->delete();

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'delete',
            'model_type' => Holiday::class,
            'model_id' => $holiday->id,
            'ip_address' => $request->ip(),
        ]);

        $recalc = $this->recalculateRequestsForRange($rangeStart, $rangeEnd, $request, $holidayName);

        return back()->with('success', 'Holiday deleted successfully. ' . $this->recalculationFlash($recalc, 'deleted'));
    }

    // Weekend settings
    public function updateWeekendSettings(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'weekends' => 'required|array',
            'weekends.*' => 'string|in:sunday,monday,tuesday,wednesday,thursday,friday,saturday',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        // Reset all to non-weekend
        WeekendSetting::query()->update(['is_weekend' => false]);

        // Set selected days as weekend
        foreach ($request->weekends as $day) {
            WeekendSetting::where('day', $day)->update(['is_weekend' => true]);
        }

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'update_weekend_settings',
            'new_values' => ['weekends' => $request->weekends],
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', 'Weekend settings updated successfully');
    }
}
