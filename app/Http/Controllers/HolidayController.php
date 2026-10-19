<?php

namespace App\Http\Controllers;

use App\Models\Holiday;
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
            ->orderBy('start_date')
            ->get();
        
        $weekendSettings = WeekendSetting::all();
        
        return view('holidays.index', compact('holidays', 'weekendSettings', 'year'));
    }

    public function create()
    {
        return view('holidays.create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'year' => 'required|integer|min:2020|max:2100',
            'is_recurring' => 'boolean',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $holiday = Holiday::create($request->all());

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'create',
            'model_type' => Holiday::class,
            'model_id' => $holiday->id,
            'new_values' => $request->all(),
            'ip_address' => $request->ip(),
        ]);

        return redirect()->route('holidays.index')->with('success', 'Holiday created successfully');
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
            'end_date' => 'required|date|after_or_equal:start_date',
            'year' => 'required|integer|min:2020|max:2100',
            'is_recurring' => 'boolean',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $oldValues = $holiday->toArray();
        
        $holiday->update($request->all());

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'update',
            'model_type' => Holiday::class,
            'model_id' => $holiday->id,
            'old_values' => $oldValues,
            'new_values' => $request->all(),
            'ip_address' => $request->ip(),
        ]);

        return redirect()->route('holidays.index')->with('success', 'Holiday updated successfully');
    }

    public function destroy(Request $request, Holiday $holiday)
    {
        // Check if holiday is used in any approved leave
        $approvedLeaves = \App\Models\LeaveRequest::where('status', 'approved')
            ->where(function($query) use ($holiday) {
                $query->where(function($q) use ($holiday) {
                    $q->where('start_date', '<=', $holiday->end_date)
                      ->where('end_date', '>=', $holiday->start_date);
                });
            })
            ->count();

        if ($approvedLeaves > 0) {
            return back()->with('error', 'Cannot delete this holiday as it has already been used in ' . $approvedLeaves . ' approved leave request(s). Please edit instead.');
        }

        $holiday->delete();

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'delete',
            'model_type' => Holiday::class,
            'model_id' => $holiday->id,
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', 'Holiday deleted successfully');
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