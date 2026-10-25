<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use App\Models\Department;
use App\Models\LeaveType;
use App\Models\Employee;
use App\Models\Holiday;
use App\Models\ActivityLog;
use App\Models\LeaveYear;
use App\Services\LeaveCalculationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class LeaveController extends Controller
{
    /**
     * Display a listing of leave requests (admin/HR view)
     */
    public function index(Request $request)
    {
        $query = LeaveRequest::with(['employee.user', 'leaveType'])
            ->orderBy('created_at', 'desc');

        // WF-12 (merged base): HR (super_admin and hr_manager) see ALL leave
        // requests, including historical ones. No employee scoping here — this
        // page is the HR overview; employees use leaves.my for their own
        // requests. (The filters version's hr_manager self-scoping block was
        // dropped in the merge; the filters themselves are kept below.)

        // Search by employee name, employee number or reason
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('employee.user', function ($q2) use ($search) {
                    $q2->where('name', 'like', "%{$search}%");
                })->orWhereHas('employee', function ($q2) use ($search) {
                    $q2->where('employee_number', 'like', "%{$search}%");
                })->orWhere('reason', 'like', "%{$search}%");
            });
        }

        // Status filter (whitelisted values only)
        if ($request->filled('status') && in_array($request->status, ['pending', 'hr_approved', 'manager_approved', 'approved', 'rejected', 'cancelled'], true)) {
            $query->where('status', $request->status);
        }

        // Leave type filter
        if ($request->filled('leave_type_id') && is_numeric($request->leave_type_id)) {
            $query->where('leave_type_id', (int) $request->leave_type_id);
        }

        // Department filter
        if ($request->filled('department_id') && is_numeric($request->department_id)) {
            $query->whereHas('employee', function ($q) use ($request) {
                $q->where('department_id', (int) $request->department_id);
            });
        }

        // Entry type filter: regular requests vs historical entries
        // (recorded via the Historical Leaves screen or CSV import).
        if ($request->filled('entry_type') && in_array($request->entry_type, ['regular', 'historical'], true)) {
            $query->where('is_historical', $request->entry_type === 'historical');
        }

        // Leave-date range (overlaps the requested window)
        if ($request->filled('from_date') && strtotime($request->from_date)) {
            $query->whereDate('end_date', '>=', date('Y-m-d', strtotime($request->from_date)));
        }
        if ($request->filled('to_date') && strtotime($request->to_date)) {
            $query->whereDate('start_date', '<=', date('Y-m-d', strtotime($request->to_date)));
        }

        $leaveRequests = $query->paginate(20)->withQueryString();

        // Leave-clarity: live breakdown per request on this page, computed
        // from the CURRENT weekend settings + holidays (not the frozen
        // stored total). The modal compares the two and flags any drift.
        $calculationService = app(LeaveCalculationService::class);
        $breakdowns = [];
        foreach ($leaveRequests as $leave) {
            $breakdowns[$leave->id] = $calculationService->calculateWorkingDaysBreakdown(
                Carbon::parse($leave->start_date),
                Carbon::parse($leave->end_date)
            );
        }

        // Get statistics counts
        $stats = [
            'total' => LeaveRequest::count(),
            'pending' => LeaveRequest::where('status', 'pending')->count(),
            'hr_approved' => LeaveRequest::where('status', 'hr_approved')->count(),
            'approved' => LeaveRequest::where('status', 'approved')->count(),
            'rejected' => LeaveRequest::where('status', 'rejected')->count(),
        ];

        // Get all leave types + departments for filter dropdowns
        $leaveTypes = LeaveType::orderBy('name')->get();
        $departments = Department::orderBy('name')->get();

        return view('leaves.index', compact('leaveRequests', 'stats', 'leaveTypes', 'departments', 'breakdowns'));
    }

    /**
     * Show the form for creating a new leave request
     */
    public function create(Request $request)
    {
        // Get employee for current user
        $employee = Auth::user()->employee;
        if (!$employee) {
            abort(403, 'Employee profile not found.');
        }

        $leaveTypes = LeaveType::where('is_active', true)->get();
        $currentYear = Carbon::now()->year;

        // Get leave balance dynamically using calculation engine
        $calculationService = app(LeaveCalculationService::class);
        $analysis = $calculationService->analyze($employee);
        $yearlyData = $analysis['years'] ?? [];
        $currentYearData = $yearlyData[$currentYear] ?? null;

        // Visible-balance policy: the card shows the VISIBLE progression
        // (current-year entitlement hidden until 1 January of next year).
        $visibleBalance = $calculationService->visibleBalanceData($employee);
        $leaveBalances = collect();
        foreach ($visibleBalance['rows'] as $row) {
            $leaveBalances->push((object) [
                'year' => $row['year'],
                'entitled_days' => $row['entitled'],
                'used_days' => $row['used'],
                'ticket_count' => 0,
                'exit_reentry_count' => 0,
                'balance' => $row['remaining'],
            ]);
        }

        // Visible balance for the form's balance check (current-year
        // entitlement excluded until 1 January of the next year).
        $annualBalance = (object) [
            'year' => $currentYear,
            'entitled_days' => $visibleBalance['visible_entitled'],
            'used_days' => $visibleBalance['visible_used'],
            'ticket_count' => 0,
            'exit_reentry_count' => 0,
            'balance' => $visibleBalance['visible_balance'],
        ];

        // Get holidays in date range for calendar
        $holidays = Holiday::where('start_date', '>=', Carbon::now()->format('Y-m-d'))
            ->orderBy('start_date')
            ->get();

        $showForm = $request->query('open_form', false);

        return view('leaves.create', compact('employee', 'leaveTypes', 'leaveBalances', 'annualBalance', 'visibleBalance', 'holidays', 'showForm'));
    }

    /**
     * View own leave requests and balances (employee self-service)
     */
    public function myLeaves(Request $request)
    {
        $employee = auth()->user()->employee;

        if (!$employee) {
            abort(403, 'Employee profile not found.');
        }

        $leavesQuery = LeaveRequest::where('employee_id', $employee->id)
            ->with('leaveType');

        // Search by leave type name or reason
        if ($request->filled('search')) {
            $search = $request->search;
            $leavesQuery->where(function ($q) use ($search) {
                $q->whereHas('leaveType', function ($q2) use ($search) {
                    $q2->where('name', 'like', "%{$search}%");
                })->orWhere('reason', 'like', "%{$search}%");
            });
        }

        // Status filter (whitelisted values only)
        if ($request->filled('status') && in_array($request->status, ['pending', 'hr_approved', 'manager_approved', 'approved', 'rejected', 'cancelled'], true)) {
            $leavesQuery->where('status', $request->status);
        }

        // Leave type filter
        if ($request->filled('leave_type_id') && is_numeric($request->leave_type_id)) {
            $leavesQuery->where('leave_type_id', (int) $request->leave_type_id);
        }

        // Year + leave-date range filters
        if ($request->filled('year') && is_numeric($request->year)) {
            $leavesQuery->whereYear('start_date', (int) $request->year);
        }
        if ($request->filled('from_date') && strtotime($request->from_date)) {
            $leavesQuery->whereDate('end_date', '>=', date('Y-m-d', strtotime($request->from_date)));
        }
        if ($request->filled('to_date') && strtotime($request->to_date)) {
            $leavesQuery->whereDate('start_date', '<=', date('Y-m-d', strtotime($request->to_date)));
        }

        $leaveRequests = $leavesQuery->orderBy('created_at', 'desc')->paginate(20)->withQueryString();

        $leaveTypes = LeaveType::where('is_active', true)->get();
        $currentYear = Carbon::now()->year;

        // Get leave balance dynamically using calculation engine
        $calculationService = app(LeaveCalculationService::class);
        $analysis = $calculationService->analyze($employee);
        $yearlyData = $analysis['years'] ?? [];
        $currentYearData = $yearlyData[$currentYear] ?? null;

        // Visible balance for the form's balance check (current-year
        // entitlement excluded until 1 January of the next year).
        // Merge-time fix: $visibleBalance was referenced below but never
        // defined in this method (unlike create()) — define it here.
        $visibleBalance = $calculationService->visibleBalanceData($employee);
        $annualBalance = (object) [
            'year' => $currentYear,
            'entitled_days' => $visibleBalance['visible_entitled'],
            'used_days' => $visibleBalance['visible_used'],
            'ticket_count' => 0,
            'exit_reentry_count' => 0,
            'balance' => $visibleBalance['visible_balance'],
        ];

        $holidays = Holiday::where('start_date', '>=', Carbon::now()->format('Y-m-d'))
            ->orderBy('start_date')
            ->get();

        // Get yearly summary with carried balances (from calculation engine)
        $yearlySummary = $this->getYearlyLeaveSummary($employee);

        // Leave-clarity: same live breakdowns as index(), plus the
        // approved-annual requests behind each year's Used figure.
        $breakdowns = [];
        foreach ($leaveRequests as $leave) {
            $breakdowns[$leave->id] = $calculationService->calculateWorkingDaysBreakdown(
                Carbon::parse($leave->start_date),
                Carbon::parse($leave->end_date)
            );
        }
        $yearRequests = $calculationService->approvedAnnualRequestsByYear($employee);

        $showForm = $request->query('open_form', false);

        return view('leaves.my-leaves', compact(
            'leaveRequests',
            'employee',
            'leaveTypes',
            'annualBalance',
            'visibleBalance',
            'holidays',
            'showForm',
            'yearlySummary',
            'breakdowns',
            'yearRequests'
        ));
    }

    /**
     * Get employee's year-by-year leave summary (for self-service)
     * Shows: year, entitled, used, balance (with carry forward)
     * Uses dynamic calculation engine - no LeaveBalance table
     */
    private function getYearlyLeaveSummary(Employee $employee): array
    {
        $calculationService = app(LeaveCalculationService::class);
        $analysis = $calculationService->analyze($employee);
        $yearlyData = $analysis['years'] ?? [];

        // Visible-balance policy: the current year's entitlement is hidden
        // (null) and counts as 0 until 1 January of the next year; the
        // running balance therefore counts closed years only.
        $visible = $calculationService->visibleBalanceData($employee);
        $visibleRows = collect($visible['rows'])->keyBy('year');

        $summary = [];

        foreach ($yearlyData as $year => $data) {
            $vRow = $visibleRows[$year] ?? null;
            $daysEntitled = $vRow ? $vRow['entitled'] : ($data['days_entitled'] ?? 0);
            $daysUsed = $data['days_used'] ?? 0;
            $currentBalance = $vRow ? $vRow['remaining'] : ($data['balance'] ?? 0);
            $carryForward = $currentBalance - ($daysEntitled ?? 0) + $daysUsed;

            $summary[] = [
                'year' => $year,
                'months_worked' => $data['months_from_hiring'] ?? 0,
                'days_entitled' => $daysEntitled,
                'days_used' => $daysUsed,
                'balance' => round($currentBalance, 2),
                'carry_forward' => round($carryForward, 2),
                'ticket_count' => $data['ticket_count'] ?? 0,
                'exit_reentry_count' => $data['exit_reentry_count'] ?? 0,
            ];
        }

        return $summary;
    }

    /**
     * Calculate months worked in a specific year
     */
    private function getMonthsWorkedInYear(Employee $employee, int $year): int
    {
        $joiningDate = Carbon::parse($employee->joining_date);
        $yearStart = Carbon::createFromDate($year, 1, 1)->startOfDay();
        $yearEnd = Carbon::createFromDate($year, 12, 31)->endOfDay();

        // If joined after this year, 0 months
        if ($joiningDate->gt($yearEnd)) {
            return 0;
        }

        $workStart = $joiningDate->lt($yearStart) ? $yearStart : $joiningDate;
        $effectiveEnd = min($yearEnd, Carbon::now());

        if ($workStart->gt($effectiveEnd)) {
            return 0;
        }

        $months = ($effectiveEnd->year - $workStart->year) * 12 +
                  ($effectiveEnd->month - $workStart->month) + 1;

        return max(0, min(12, $months));
    }

    /**
     * Store a new leave request
     */
    public function storeLeave(Request $request)
    {
        $employee = auth()->user()->employee;
        if (!$employee) {
            abort(403, 'Employee profile not found.');
        }

        $request->validate([
            // WF-18: self-service only. The HTML min=today is client-side
            // only, so past dates are rejected server-side here, and the
            // leave type must exist AND be active. HR paths
            // (storeForEmployee/storeHistorical) are exempt by design.
            'leave_type_id' => ['required', Rule::exists('leave_types', 'id')->where('is_active', true)],
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|max:2000',
            'flight_ticket_required' => 'nullable|boolean',
            'exit_reentry_required' => 'nullable|boolean',
            'hotel_booking_required' => 'nullable|boolean',
            'transfer_required' => 'nullable|boolean',
        ], [
            'leave_type_id.required' => 'Leave type is required.',
            'leave_type_id.exists' => 'Selected leave type is not available.',
            'start_date.required' => 'Start date is required.',
            'start_date.after_or_equal' => 'Start date cannot be in the past.',
            'end_date.required' => 'End date is required.',
            'end_date.after_or_equal' => 'End date must be after or equal to start date.',
            'reason.required' => 'Reason is required.',
        ]);

        $leaveType = LeaveType::findOrFail($request->leave_type_id);

        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);

        // WF-19: bound the day-by-day working-days loop below. Ranges over
        // 366 calendar days are rejected before any calculation runs.
        $calendarDays = $startDate->diffInDays($endDate) + 1;
        if ($calendarDays > 366) {
            return back()->with('error', 'Leave range must not exceed 366 days.')->withInput();
        }

        // WF-04/M2: reject overlaps with any existing approved, hr_approved
        // or pending leave of the same employee (hr_approved is included
        // because it is on its way to approved; rejected/cancelled rows and
        // soft-deleted rows do not conflict).
        if ($conflict = $this->findOverlappingLeave($employee->id, $request->start_date, $request->end_date)) {
            return back()->with('error', $this->overlapMessage($conflict))->withInput();
        }

        // Calculate working days using the service
        $calculationService = app(LeaveCalculationService::class);
        $workingDays = $calculationService->calculateWorkingDays(
            $startDate,
            $endDate,
            $employee->id
        );

        // WF-19: a range covering only weekends/holidays computes 0 working
        // days — reject it instead of storing a meaningless 0-day record.
        if ($workingDays <= 0) {
            return back()->with('error', 'The selected dates contain no working days (weekends/holidays only). Please choose different dates.')->withInput();
        }

        // Visible-balance policy: NO balance gate here by design. The
        // current year's annual entitlement is not counted until 1 January
        // of the next year, and requests are approved even when the visible
        // balance goes negative — they deduct from the carried balance of
        // closed years (see LeaveCalculationService::visibleBalanceData()).

        // Create leave request (balance is calculated dynamically from requests)
        $leaveRequest = LeaveRequest::create([
            'employee_id' => $employee->id,
            'leave_type_id' => $leaveType->id,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'total_days' => $workingDays,
            'calendar_days' => $calendarDays,
            'reason' => $request->reason,
            // M6: all four service-request flags are persisted (hotel and
            // transfer were previously validated but dropped here).
            'flight_ticket_required' => $request->boolean('flight_ticket_required', false),
            'exit_reentry_required' => $request->boolean('exit_reentry_required', false),
            'hotel_booking_required' => $request->boolean('hotel_booking_required', false),
            'transfer_required' => $request->boolean('transfer_required', false),
            'status' => 'pending',
            'created_by' => Auth::id(),
        ]);

        // Email notification (no-op until HR_NOTIFICATIONS_ENABLED=true).
        // WF-14: send failures are logged, never allowed to 500 the transition.
        $this->notifySafely(fn () => app(\App\Services\LeaveNotificationService::class)->notifyNewRequest($leaveRequest));

        // Log activity
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'create',
            'model_type' => LeaveRequest::class,
            'model_id' => $leaveRequest->id,
            'description' => "Leave request submitted: {$leaveType->name} from {$request->start_date} to {$request->end_date}",
            'ip_address' => $request->ip(),
        ]);

        return redirect()->route('leaves.my')
            ->with('success', 'Leave request submitted successfully.');
    }

    /**
     * Show leave statistics/dashboard
     */
    public function dashboard()
    {
        // Only super_admin and hr_manager can access
        if (!Auth::user()->hasAnyRole(['super_admin', 'hr_manager'])) {
            abort(403);
        }

        $currentYear = Carbon::now()->year;

        // Department-wise leave usage
        $deptStats = LeaveRequest::selectRaw('
                departments.name,
                COUNT(*) as total_requests,
                SUM(CASE WHEN status = "approved" THEN 1 ELSE 0 END) as approved_count,
                SUM(CASE WHEN status = "pending" THEN 1 ELSE 0 END) as pending_count
            ')
            ->join('employees', 'leave_requests.employee_id', '=', 'employees.id')
            ->join('departments', 'employees.department_id', '=', 'departments.id')
            ->whereYear('start_date', $currentYear)
            ->groupBy('departments.id', 'departments.name')
            ->orderBy('departments.name')
            ->get();

        // Leave type distribution
        $typeStats = LeaveRequest::selectRaw('
                leave_types.name,
                COUNT(*) as count
            ')
            ->join('leave_types', 'leave_requests.leave_type_id', '=', 'leave_types.id')
            ->whereYear('start_date', $currentYear)
            ->groupBy('leave_types.id', 'leave_types.name')
            ->orderBy('count', 'desc')
            ->get();

        // Monthly trend
        $monthlyStats = LeaveRequest::selectRaw('
                MONTH(start_date) as month,
                COUNT(*) as count,
                SUM(CASE WHEN status = "approved" THEN 1 ELSE 0 END) as approved_count
            ')
            ->whereYear('start_date', $currentYear)
            ->groupBy(DB::raw('MONTH(start_date)'))
            ->orderBy('month')
            ->get()
            ->mapWithKeys(function ($item) {
                return [Carbon::createFromDate(null, $item->month, 1)->format('M') => $item];
            });

        return view('leaves.dashboard', compact('deptStats', 'typeStats', 'monthlyStats', 'currentYear'));
    }

    /**
     * Display leave balances for HR management
     */
    public function manageBalances(Request $request)
    {
        if (!Auth::user()->hasAnyRole(['super_admin', 'hr_manager'])) {
            abort(403);
        }

        // Build balances dynamically using calculation engine
        $calculationService = app(LeaveCalculationService::class);
        $employees = Employee::with('user')->when($request->filled('employee'), function($q) use ($request) {
            $search = $request->employee;
            $q->whereHas('user', function ($q2) use ($search) {
                $q2->where('name', 'like', "%{$search}%");
            })->orWhere('employee_number', 'like', "%{$search}%");
        })->when($request->filled('department_id') && is_numeric($request->department_id), function ($q) use ($request) {
            $q->where('department_id', (int) $request->department_id);
        })->orderBy('employee_number')->get();

        $filterYear = $request->filled('year') ? $request->year : null;

        $balances = collect();
        foreach ($employees as $employee) {
            $analysis = $calculationService->analyze($employee);
            $yearlyData = $analysis['years'] ?? [];
            $currentYear = Carbon::now()->year;

            // Visible-balance policy: current-year entitlement hidden until 1 January.
            $visibleRows = collect($calculationService->visibleBalanceData($employee)['rows'])->keyBy('year');

            foreach ($yearlyData as $year => $data) {
                if ($filterYear !== null && $year != $filterYear) continue;

                $vRow = $visibleRows[$year] ?? null;

                $balances->push((object) [
                    'id' => null, // No DB record in dynamic system
                    'employee_id' => $employee->id,
                    'employee' => $employee,
                    'year' => $year,
                    'entitled_days' => $vRow ? $vRow['entitled'] : ($data['days_entitled'] ?? 0),
                    'used_days' => $data['days_used'] ?? 0,
                    'ticket_count' => $data['ticket_count'] ?? 0,
                    'exit_reentry_count' => $data['exit_reentry_count'] ?? 0,
                    'balance' => $vRow ? $vRow['remaining'] : ($data['balance'] ?? 0),
                    'months_from_hiring' => $data['months_from_hiring'] ?? 0,
                ]);
            }
        }

        // Paginate manually
        $perPage = 20;
        $page = $request->input('page', 1);
        $paginated = $balances->slice(($page - 1) * $perPage, $perPage);
        $balances = new \Illuminate\Pagination\LengthAwarePaginator(
            $paginated,
            $balances->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'page' => 'page', 'query' => $request->query()]
        );

        $years = LeaveYear::orderBy('year', 'asc')->pluck('year');
        $departments = Department::where('is_active', true)->orderBy('name')->get();

        return view('leaves.manage-balances', compact('balances', 'years', 'departments'));
    }

    /**
     * Show form to view employee balance (read-only - no manual editing)
     * In the new system, balances are calculated dynamically
     */
    public function editBalance($id)
    {
        if (!Auth::user()->hasAnyRole(['super_admin', 'hr_manager'])) {
            abort(403);
        }

        // In dynamic system, $id is now the employee ID, not a balance record ID
        $employee = Employee::with('user')->findOrFail($id);
        $calculationService = app(LeaveCalculationService::class);
        $analysis = $calculationService->analyze($employee);
        $yearlyData = $analysis['years'] ?? [];

        $leaveYears = LeaveYear::orderBy('year', 'asc')->get();

        // Build balance data for view (VISIBLE semantics: current-year
        // entitlement hidden until 1 January of the next year).
        $visibleBalance = $calculationService->visibleBalanceData($employee);
        $visibleRows = collect($visibleBalance['rows'])->keyBy('year');

        $balancesByYear = [];
        foreach ($yearlyData as $year => $data) {
            $vRow = $visibleRows[$year] ?? null;
            $balancesByYear[$year] = (object) [
                'year' => $year,
                'entitled_days' => $vRow ? $vRow['entitled'] : ($data['days_entitled'] ?? 0),
                'used_days' => $data['days_used'] ?? 0,
                'ticket_count' => $data['ticket_count'] ?? 0,
                'exit_reentry_count' => $data['exit_reentry_count'] ?? 0,
                'balance' => $vRow ? $vRow['remaining'] : ($data['balance'] ?? 0),
                'months_from_hiring' => $data['months_from_hiring'] ?? 0,
            ];
        }

        // For view compatibility, pass the most recent year's data
        $balance = end($balancesByYear) ?: (object) [
            'year' => Carbon::now()->year,
            'entitled_days' => 0,
            'used_days' => 0,
            'balance' => 0,
        ];

        $monthsWorked = $balance->months_from_hiring ?? 0;

        return view('leaves.edit-balance', compact('balance', 'leaveYears', 'monthsWorked', 'employee', 'balancesByYear', 'visibleBalance'));
    }

    /**
     * Update leave balance - DISABLED in dynamic calculation system
     * Balances are calculated automatically from Hire Date + Leave Requests
     */
    public function updateBalance(Request $request, $id)
    {
        if (!Auth::user()->hasAnyRole(['super_admin', 'hr_manager'])) {
            abort(403);
        }

        return redirect()->route('leaves.manage-balances')
            ->with('info', 'Manual balance editing is disabled. Balances are calculated dynamically from employee hire date and approved leave requests.');
    }

    /**
     * Delete a leave request (any status, any employee).
     * Restricted to super_admin and hr_manager by route middleware AND the
     * role check below. This is a SOFT delete (LeaveRequest uses SoftDeletes):
     * the row leaves all lists and dynamic balances recalculate automatically,
     * but the record remains recoverable in the database.
     * (Taken verbatim from the staged hr-leave-delete version in the merge.)
     */
    /**
     * Leave-clarity (Task 1): HR/admin edit form for a leave REQUEST.
     * This is not editBalance (which edits balance rows): it edits the
     * request itself — dates, type, reason. employee_id and status are
     * deliberately NOT editable here: ownership never changes, and status
     * changes stay in the approve/reject flow.
     */
    public function edit(LeaveRequest $leave)
    {
        if (!Auth::user()->hasAnyRole(['super_admin', 'hr_manager'])) {
            abort(403);
        }

        $leave->load(['employee.user', 'leaveType']);
        $leaveTypes = LeaveType::where('is_active', true)->orderBy('name')->get();
        $holidays = Holiday::orderBy('start_date')->get();
        $weekendDays = \App\Models\WeekendSetting::getWeekendDays();
        $breakdown = app(LeaveCalculationService::class)->calculateWorkingDaysBreakdown(
            Carbon::parse($leave->start_date),
            Carbon::parse($leave->end_date)
        );

        return view('leaves.edit', compact('leave', 'leaveTypes', 'holidays', 'weekendDays', 'breakdown'));
    }

    /**
     * Leave-clarity (Task 1): apply the HR/admin request edit. Days are
     * recomputed from the CURRENT weekend settings + holidays, exactly
     * like storeLeave. Any status may be edited; because balances are
     * computed dynamically from requests, editing an approved request
     * corrects that employee's balance automatically — no manual
     * adjustment, and no status/employee change is accepted here.
     */
    public function update(Request $request, LeaveRequest $leave)
    {
        if (!Auth::user()->hasAnyRole(['super_admin', 'hr_manager'])) {
            abort(403);
        }

        $request->validate([
            'leave_type_id' => ['required', Rule::exists('leave_types', 'id')->where('is_active', true)],
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|max:2000',
        ]);

        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);
        $calendarDays = $startDate->diffInDays($endDate) + 1;
        if ($calendarDays > 366) {
            return back()->with('error', 'Leave range must not exceed 366 days.')->withInput();
        }

        // Same overlap rule as creation, excluding this request itself.
        $conflict = LeaveRequest::with('leaveType')
            ->where('employee_id', $leave->employee_id)
            ->where('id', '!=', $leave->id)
            ->whereIn('status', ['pending', 'hr_approved', 'approved'])
            ->where('start_date', '<=', $request->end_date)
            ->where('end_date', '>=', $request->start_date)
            ->orderBy('start_date')
            ->first();
        if ($conflict) {
            return back()->with('error', $this->overlapMessage($conflict))->withInput();
        }

        $calculationService = app(LeaveCalculationService::class);
        $workingDays = $calculationService->calculateWorkingDays($startDate, $endDate, $leave->employee_id);
        if ($workingDays <= 0) {
            return back()->with('error', 'The selected dates contain no working days (weekends/holidays only). Please choose different dates.')->withInput();
        }

        $oldType = $leave->leaveType->name ?? 'N/A';
        $oldStart = $leave->start_date ? $leave->start_date->format('Y-m-d') : 'N/A';
        $oldEnd = $leave->end_date ? $leave->end_date->format('Y-m-d') : 'N/A';
        $oldDays = $leave->total_days;
        $employeeName = $leave->employee->user->name ?? 'Unknown';

        $leave->update([
            'leave_type_id' => $request->leave_type_id,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'total_days' => $workingDays,
            'calendar_days' => $calendarDays,
            'reason' => $request->reason,
        ]);

        $newType = LeaveType::find($request->leave_type_id)->name ?? 'N/A';
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'update',
            'model_type' => LeaveRequest::class,
            'model_id' => $leave->id,
            'description' => "Leave request edited (employee: {$employeeName}, was: {$oldType} {$oldStart} to {$oldEnd}, {$oldDays} days; now: {$newType} {$request->start_date} to {$request->end_date}, {$workingDays} days; status unchanged: {$leave->status})",
            'ip_address' => $request->ip(),
        ]);

        return redirect()->route('leaves.index')
            ->with('success', "Leave request for {$employeeName} updated ({$workingDays} working days). Balances recalculate automatically.");
    }

    public function destroy(Request $request, LeaveRequest $leave)
    {
        if (!Auth::user()->hasAnyRole(['super_admin', 'hr_manager'])) {
            abort(403);
        }

        // Capture details BEFORE deleting, for the audit trail.
        $employeeName = $leave->employee->user->name ?? 'Unknown';
        $employeeNumber = $leave->employee->employee_number ?? 'N/A';
        $leaveTypeName = $leave->leaveType->name ?? 'N/A';
        $startDate = $leave->start_date ? $leave->start_date->format('Y-m-d') : 'N/A';
        $endDate = $leave->end_date ? $leave->end_date->format('Y-m-d') : 'N/A';
        $totalDays = $leave->total_days;
        $oldStatus = $leave->status;
        $wasHistorical = $leave->is_historical ? 'yes' : 'no';

        // Log activity (same pattern as hrApprove)
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'delete',
            'model_type' => LeaveRequest::class,
            'model_id' => $leave->id,
            'description' => "Leave request deleted (employee: {$employeeName} [{$employeeNumber}], type: {$leaveTypeName}, dates: {$startDate} to {$endDate}, days: {$totalDays}, was status: {$oldStatus}, historical: {$wasHistorical})",
            'ip_address' => $request->ip(),
        ]);

        // No manual balance adjustment needed - balances are calculated dynamically from leave requests.
        // Deleting a request only affects future dynamic calculations.

        $leave->delete();

        return redirect()->route('leaves.index')
            ->with('success', "Leave request for {$employeeName} deleted.");
    }

    // NOTE (WF-17): the former export() method was REMOVED here. It delegated
    // to ReportsController::export(), which does not exist, so wiring it to
    // any route would 500. It is deleted, not rewired; use the Reports
    // exports (reports.export.*) instead. No route referenced it.

    // ========================================
    // HR Methods - Create leave for any employee
    // ========================================

    /**
     * Show form to create leave for any employee (HR only)
     */
    public function createForEmployee()
    {
        if (!Auth::user()->hasAnyRole(['super_admin', 'hr_manager'])) {
            abort(403);
        }

        $leaveTypes = LeaveType::where('is_active', true)->get();
        $employees = Employee::with('user')->get();

        return view('leaves.create-for-employee', compact('leaveTypes', 'employees'));
    }

     /**
     * Get employee data for AJAX request
     * WF-10 (LeaveController part only): returns the VISIBLE balance figure
     * from visibleBalanceData() — current-year entitlement excluded until
     * 1 January of the next year — instead of analyze(), whose current-year
     * balance counted that entitlement. Reports/Dashboard surfaces are
     * another worker's scope and are untouched here. JSON key names are
     * unchanged; ticket/exit-reentry counts are dropped from
     * current_balance because visible rows do not carry them (they were
     * analyze()-only data).
     */
    public function getEmployeeData(Employee $employee)
     {
        if (!Auth::user()->hasAnyRole(['super_admin', 'hr_manager'])) {
            abort(403);
        }

        $currentYear = Carbon::now()->year;
        $calculationService = app(\App\Services\LeaveCalculationService::class);

        // Visible-balance policy only: closed years' entitlements minus all
        // approved annual used days; current-year entitlement is null
        // (hidden) until 1 January of the next year; negatives shown as-is.
        $visible = $calculationService->visibleBalanceData($employee);
        $visibleRows = collect($visible['rows'] ?? [])->keyBy('year');

        $currentYearRow = $visibleRows[$currentYear] ?? null;
        $previousYearRow = $visibleRows[$currentYear - 1] ?? null;

        // The single visible figure the HR create form previews.
        $availableBalance = $visible['visible_balance'] ?? 0;

        // Get recent leave requests (all types for display)
        $recentLeaves = LeaveRequest::where('employee_id', $employee->id)
            ->with('leaveType')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return response()->json([
            'employee' => [
                'id' => $employee->id,
                'employee_number' => $employee->employee_number,
                'name' => $employee->user->name ?? 'Unknown',
                'department' => $employee->department->name ?? 'No Department',
                'joining_date' => $employee->joining_date ? $employee->joining_date->format('Y-m-d') : null,
                'employment_status' => $employee->employment_status,
            ],
            'current_balance' => $currentYearRow ? [
                // entitled_days is null for the current year by policy
                // (hidden until 1 January of the next year).
                'entitled_days' => $currentYearRow['entitled'],
                'used_days' => $currentYearRow['used'],
                'balance' => $currentYearRow['remaining'],
            ] : null,
            'previous_balance' => $previousYearRow ? [
                'balance' => $previousYearRow['remaining'],
                'entitled_days' => $previousYearRow['entitled'],
                'used_days' => $previousYearRow['used'],
            ] : null,
            'available_balance' => $availableBalance,
            'balances' => $visibleRows->map(function ($row) {
                return [
                    'year' => $row['year'],
                    'entitled_days' => $row['entitled'],
                    'used_days' => $row['used'],
                    'balance' => $row['remaining'],
                ];
            })->values()->toArray(),
            'recent_leaves' => $recentLeaves->map(function($leave) {
                return [
                    'date' => $leave->start_date->format('Y-m-d') . ' to ' . $leave->end_date->format('Y-m-d'),
                    'type' => $leave->leaveType->name ?? 'Unknown',
                    'days' => $leave->total_days,
                    'status' => $leave->status,
                ];
            }),
        ]);
     }

    /**
     * Store leave request for employee (HR action)
     */
    public function storeForEmployee(Request $request)
    {
        if (!Auth::user()->hasAnyRole(['super_admin', 'hr_manager'])) {
            abort(403);
        }

        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'leave_type_id' => 'required|exists:leave_types,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|max:2000',
            'flight_ticket_required' => 'nullable|boolean',
            'exit_reentry_required' => 'nullable|boolean',
            'hotel_booking_required' => 'nullable|boolean',
            'transfer_required' => 'nullable|boolean',
        ]);

        $employee = Employee::findOrFail($request->employee_id);
        $leaveType = LeaveType::findOrFail($request->leave_type_id);

        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);

        // WF-19: bound the day-by-day working-days loop below.
        $calendarDays = $startDate->diffInDays($endDate) + 1;
        if ($calendarDays > 366) {
            return back()->with('error', 'Leave range must not exceed 366 days.')->withInput();
        }

        // WF-04/M2: HR-created requests are stored as approved, so an
        // overlap here would double-count immediately.
        if ($conflict = $this->findOverlappingLeave($employee->id, $request->start_date, $request->end_date)) {
            return back()->with('error', $this->overlapMessage($conflict))->withInput();
        }

        // Calculate working days using service
        $workingDays = app(LeaveCalculationService::class)->calculateWorkingDays(
            $startDate,
            $endDate,
            $employee->id
        );

        // WF-19: reject weekend/holiday-only ranges instead of storing a
        // 0-day record.
        if ($workingDays <= 0) {
            return back()->with('error', 'The selected dates contain no working days (weekends/holidays only). Please choose different dates.')->withInput();
        }

        // Visible-balance policy: NO balance gate here by design. The
        // current year's annual entitlement is not counted until 1 January
        // of the next year, and requests are approved even when the visible
        // balance goes negative — they deduct from the carried balance of
        // closed years (see LeaveCalculationService::visibleBalanceData()).

        // Create leave request
        $leaveRequest = LeaveRequest::create([
            'employee_id' => $employee->id,
            'leave_type_id' => $leaveType->id,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'total_days' => $workingDays,
            'calendar_days' => $calendarDays,
            'reason' => $request->reason,
            // M6: all four service-request flags are persisted.
            'flight_ticket_required' => $request->boolean('flight_ticket_required', false),
            'exit_reentry_required' => $request->boolean('exit_reentry_required', false),
            'hotel_booking_required' => $request->boolean('hotel_booking_required', false),
            'transfer_required' => $request->boolean('transfer_required', false),
            'status' => 'approved',
            'hr_approved_by' => Auth::user()->employee?->id,
            'hr_approved_at' => now(),
            'created_by' => Auth::id(),
        ]);

        // Log activity
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'create',
            'model_type' => LeaveRequest::class,
            'model_id' => $leaveRequest->id,
            'description' => "Leave request created by HR for {$employee->user->name}: {$leaveType->name} from {$request->start_date}",
            'ip_address' => $request->ip(),
        ]);

        return redirect()->route('leaves.index')
            ->with('success', 'Leave request created successfully.');
    }

    /**
     * HR approves leave request
     * M10/WF-06: the transition is a CONDITIONAL update inside a DB
     * transaction (UPDATE ... WHERE id = ? AND status = 'pending'). Two
     * concurrent approvals cannot both succeed: the loser affects 0 rows
     * and gets the "already processed" error. The pre-check below only
     * produces the fast-path message; the conditional update is the
     * correctness mechanism (no lockForUpdate-only solution).
     */
    public function hrApprove(Request $request, LeaveRequest $leave)
    {
        if (!Auth::user()->hasAnyRole(['super_admin', 'hr_manager'])) {
            abort(403, 'Unauthorized.');
        }

        if ($leave->status !== 'pending') {
            return back()->with('error', 'Leave request already processed.');
        }

        $oldStatus = $leave->status;
        $affected = DB::transaction(function () use ($leave, $request) {
            return LeaveRequest::where('id', $leave->id)
                ->where('status', 'pending')
                ->update([
                    'status' => 'hr_approved',
                    'hr_approved_by' => Auth::user()->employee?->id,
                    'hr_approved_at' => now(),
                    'hr_comments' => $request->hr_comments ?? null,
                ]);
        });

        if ($affected === 0) {
            return back()->with('error', 'Leave request already processed.');
        }

        $leave->refresh();

        // Email notification (no-op until enabled). WF-14: failures are
        // logged, never allowed to 500 the transition.
        $this->notifySafely(fn () => app(\App\Services\LeaveNotificationService::class)->notifyStatusChanged($leave));

        // Log activity
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'update',
            'model_type' => LeaveRequest::class,
            'model_id' => $leave->id,
            'description' => "Leave request HR approved (was {$oldStatus})",
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', 'Leave request HR approved.');
    }

    /**
     * Manager approves leave request
     * M1: manager/dm actors must belong to the leave employee's department
     * (via their managedDepartment); super_admin/hr_manager are exempt.
     * M10/WF-06: conditional update (WHERE status = 'hr_approved') inside a
     * DB transaction, as in hrApprove().
     */
    public function managerApprove(Request $request, LeaveRequest $leave)
    {
        if (!Auth::user()->hasAnyRole(['super_admin', 'hr_manager', 'manager', 'dm'])) {
            abort(403, 'Unauthorized.');
        }

        $this->assertManagerDepartmentScope($leave);

        if ($leave->status !== 'hr_approved') {
            return back()->with('error', 'Leave request must be HR approved first.');
        }

        $oldStatus = $leave->status;
        $affected = DB::transaction(function () use ($leave, $request) {
            return LeaveRequest::where('id', $leave->id)
                ->where('status', 'hr_approved')
                ->update([
                    'status' => 'approved',
                    'manager_approved_by' => Auth::user()->employee?->id,
                    'manager_approved_at' => now(),
                    'manager_comments' => $request->manager_comments ?? null,
                ]);
        });

        if ($affected === 0) {
            return back()->with('error', 'Leave request must be HR approved first.');
        }

        $leave->refresh();

        // Email notification (no-op until enabled). WF-14: failures are
        // logged, never allowed to 500 the transition.
        $this->notifySafely(fn () => app(\App\Services\LeaveNotificationService::class)->notifyStatusChanged($leave));

        // Log activity
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'update',
            'model_type' => LeaveRequest::class,
            'model_id' => $leave->id,
            'description' => "Leave request manager approved (was {$oldStatus})",
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', 'Leave request fully approved.');
    }

    /**
     * Reject leave request
     * M1: manager/dm actors are department-scoped (see
     * assertManagerDepartmentScope); super_admin/hr_manager are exempt.
     * H7/WF-05: rejection is allowed ONLY from 'pending' or 'hr_approved' —
     * NEVER from 'approved' (or 'cancelled'/'rejected'). An approved leave
     * needs a dedicated reversal flow, not a reject. M10/WF-06: the
     * transition is a conditional update inside a DB transaction, so a
     * concurrent approval cannot be silently overwritten by a reject.
     */
    public function reject(Request $request, LeaveRequest $leave)
    {
        if (!Auth::user()->hasAnyRole(['super_admin', 'hr_manager', 'manager', 'dm'])) {
            abort(403, 'Unauthorized.');
        }

        $this->assertManagerDepartmentScope($leave);

        $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ]);

        if (!in_array($leave->status, ['pending', 'hr_approved'], true)) {
            return back()->with('error', 'Only pending or HR-approved requests can be rejected.');
        }

        $oldStatus = $leave->status;
        $affected = DB::transaction(function () use ($leave, $request) {
            return LeaveRequest::where('id', $leave->id)
                ->whereIn('status', ['pending', 'hr_approved'])
                ->update([
                    'status' => 'rejected',
                    'rejected_by' => Auth::user()->employee?->id,
                    'rejected_at' => now(),
                    'rejection_reason' => $request->rejection_reason,
                ]);
        });

        if ($affected === 0) {
            return back()->with('error', 'Leave request already processed.');
        }

        $leave->refresh();

        // Email notification (no-op until enabled). WF-14: failures are
        // logged, never allowed to 500 the transition.
        $this->notifySafely(fn () => app(\App\Services\LeaveNotificationService::class)->notifyStatusChanged($leave));

        // No manual balance adjustment needed - balances are calculated dynamically.
        // Rejected leave requests are not counted in the calculation engine's
        // days_used (only approved annual leave requests reduce balance).

        // Log activity
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'update',
            'model_type' => LeaveRequest::class,
            'model_id' => $leave->id,
            'description' => "Leave request rejected: {$request->rejection_reason}",
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', 'Leave request rejected.');
    }

    /**
     * Cancel pending leave request (by employee)
     * Ownership + pending-status guards are unchanged (they are what the
     * route patch relies on — see routes PATCH-NOTE). WF-13: the cancel
     * is now written to the activity log before the delete, in the same
     * pattern as hrApprove.
     */
    public function cancel(Request $request, LeaveRequest $leave)
    {
        $employee = auth()->user()->employee;
        if (!$employee || $leave->employee_id !== $employee->id) {
            abort(403, 'Unauthorized.');
        }

        if ($leave->status !== 'pending') {
            return back()->with('error', 'Only pending requests can be cancelled.');
        }

        // No manual balance adjustment needed - balances are calculated dynamically.
        // Deleting a pending leave request only affects future dynamic calculations.

        // WF-13: audit-log the cancellation BEFORE deleting, so a cancelled
        // request is distinguishable from "never existed" in the trail.
        $leaveTypeName = $leave->leaveType->name ?? 'N/A';
        $startDate = $leave->start_date ? $leave->start_date->format('Y-m-d') : 'N/A';
        $endDate = $leave->end_date ? $leave->end_date->format('Y-m-d') : 'N/A';
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'delete',
            'model_type' => LeaveRequest::class,
            'model_id' => $leave->id,
            'description' => "Leave request cancelled by employee (type: {$leaveTypeName}, dates: {$startDate} to {$endDate}, days: {$leave->total_days})",
            'ip_address' => $request->ip(),
        ]);

        $leave->delete();

        return redirect()->route('leaves.my-leaves')
            ->with('success', 'Leave request cancelled.');
    }

    /**
     * Store historical leave (for HR to set past balances)
     */
    public function storeHistorical(Request $request)
    {
        if (!Auth::user()->hasAnyRole(['super_admin', 'hr_manager'])) {
            abort(403);
        }

        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'leave_type_id' => 'required|exists:leave_types,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|max:2000',
            'status' => 'required|in:approved,rejected',
            // M6: the flags were stored (partially) here without ever being
            // validated on this path; validate all four as boolean.
            'flight_ticket_required' => 'nullable|boolean',
            'exit_reentry_required' => 'nullable|boolean',
            'hotel_booking_required' => 'nullable|boolean',
            'transfer_required' => 'nullable|boolean',
        ]);

        $employee = Employee::findOrFail($request->employee_id);
        $leaveType = LeaveType::findOrFail($request->leave_type_id);

        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);

        // WF-19: bound the day-by-day working-days loop below.
        $calendarDays = $startDate->diffInDays($endDate) + 1;
        if ($calendarDays > 366) {
            return back()->with('error', 'Leave range must not exceed 366 days.')->withInput();
        }

        // WF-04/M2: only when the historical record will actually count
        // (status approved). A rejected historical record deducts nothing,
        // so it cannot double-count and is not blocked by overlaps.
        if ($request->status === 'approved'
            && ($conflict = $this->findOverlappingLeave($employee->id, $request->start_date, $request->end_date))) {
            return back()->with('error', $this->overlapMessage($conflict))->withInput();
        }

        // Calculate working days
        $workingDays = app(LeaveCalculationService::class)->calculateWorkingDays(
            $startDate,
            $endDate,
            $employee->id
        );

        // WF-19: reject weekend/holiday-only ranges instead of storing a
        // 0-day record.
        if ($workingDays <= 0) {
            return back()->with('error', 'The selected dates contain no working days (weekends/holidays only). Please choose different dates.')->withInput();
        }

        // In dynamic calculation system, no LeaveBalance records are created.
        // Balance is calculated dynamically from leave requests (Hire Date + Requests).
        // Historical leave requests are stored directly in leave_requests table.

        // Create the leave request as historical
        $leaveRequest = LeaveRequest::create([
            'employee_id' => $employee->id,
            'leave_type_id' => $leaveType->id,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'total_days' => $workingDays,
            'calendar_days' => $calendarDays,
            'reason' => $request->reason,
            // M6: all four service-request flags are persisted.
            'flight_ticket_required' => $request->boolean('flight_ticket_required', false),
            'exit_reentry_required' => $request->boolean('exit_reentry_required', false),
            'hotel_booking_required' => $request->boolean('hotel_booking_required', false),
            'transfer_required' => $request->boolean('transfer_required', false),
            'status' => $request->status,
            'is_historical' => true,
            'created_by' => Auth::id(),
        ]);

        // Log activity
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'create',
            'model_type' => LeaveRequest::class,
            'model_id' => $leaveRequest->id,
            'description' => "Historical leave recorded for {$employee->user->name}: {$leaveType->name} on {$request->start_date}",
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', 'Historical leave recorded successfully.');
    }

    /**
     * Show the form to record a historical leave (past leave entry)
     */
    public function createHistorical()
    {
        if (!Auth::user()->hasAnyRole(['super_admin', 'hr_manager'])) {
            abort(403);
        }

        $leaveTypes = LeaveType::where('is_active', true)->get();
        $employees = Employee::with('user')->get();

        return view('leaves.historical', compact('leaveTypes', 'employees'));
    }

    /**
     * Download the CSV template for importing historical leaves
     * One row per employee with only the Employee Number filled in.
     */
    public function historicalTemplate()
    {
        if (!Auth::user()->hasAnyRole(['super_admin', 'hr_manager'])) {
            abort(403);
        }

        $headers = [
            'Employee Number',
            'Leave Type',
            'Start Date',
            'End Date',
            'Total Days',
            'Calendar Days',
            'Reason',
        ];

        $csvData = implode(',', $headers) . "\n";
        $employees = Employee::orderBy('employee_number')->get();
        foreach ($employees as $employee) {
            $csvData .= $employee->employee_number . ",,,,,,\n";
        }

        return response($csvData, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="historical-leaves-template.csv"',
        ]);
    }

    /**
     * Manager view: all leave requests for employees in manager's department
     */
    public function managerIndex(Request $request)
    {
        $user = Auth::user();
        $employee = $user->employee;

        if (!$employee || !in_array($user->role->name ?? '', ['manager', 'dm', 'super_admin', 'hr_manager'])) {
            abort(403);
        }

        $query = LeaveRequest::with(['employee.user', 'leaveType'])
            ->orderBy('created_at', 'desc');

        // Filter by department for manager/dm
        // L4: a manager/dm whose employee record has no department must see
        // NOTHING — scoping by a null department_id would otherwise match
        // employees with department_id NULL.
        if (in_array($user->role->name ?? '', ['manager', 'dm'])) {
            if (!$employee || !$employee->department_id) {
                $query->whereRaw('1 = 0');
            } else {
                $query->whereHas('employee', function ($q) use ($employee) {
                    $q->where('department_id', $employee->department_id);
                });
            }
        }

        // Search by employee name, employee number or reason
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('employee.user', function ($q2) use ($search) {
                    $q2->where('name', 'like', "%{$search}%");
                })->orWhereHas('employee', function ($q2) use ($search) {
                    $q2->where('employee_number', 'like', "%{$search}%");
                })->orWhere('reason', 'like', "%{$search}%");
            });
        }

        // Status filter (whitelisted values only)
        if ($request->filled('status') && in_array($request->status, ['pending', 'hr_approved', 'manager_approved', 'approved', 'rejected', 'cancelled'], true)) {
            $query->where('status', $request->status);
        }

        // Leave type filter
        if ($request->filled('leave_type_id') && is_numeric($request->leave_type_id)) {
            $query->where('leave_type_id', (int) $request->leave_type_id);
        }

        // Leave-date range
        if ($request->filled('from_date') && strtotime($request->from_date)) {
            $query->whereDate('end_date', '>=', date('Y-m-d', strtotime($request->from_date)));
        }
        if ($request->filled('to_date') && strtotime($request->to_date)) {
            $query->whereDate('start_date', '<=', date('Y-m-d', strtotime($request->to_date)));
        }

        // Department filter: only super_admin/hr_manager may narrow by
        // department here; manager/dm are already scoped to their own.
        $showDepartmentFilter = in_array($user->role->name ?? '', ['super_admin', 'hr_manager']);
        if ($showDepartmentFilter && $request->filled('department_id') && is_numeric($request->department_id)) {
            $query->whereHas('employee', function ($q) use ($request) {
                $q->where('department_id', (int) $request->department_id);
            });
        }

        $leaveRequests = $query->paginate(20)->withQueryString();
        $leaveTypes = LeaveType::orderBy('name')->get();
        $departments = Department::orderBy('name')->get();

        return view('leaves.manager-index', compact('leaveRequests', 'leaveTypes', 'departments', 'showDepartmentFilter'));
    }

    public function calendar(Request $request)
    {
        $user = Auth::user();
        $role = $user->role->name ?? '';
        $employee = $user->employee;

        $month = $request->get('month', now()->format('Y-m'));
        try {
            $start = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        } catch (\Exception $e) {
            $start = now()->startOfMonth();
        }
        $month = $start->format('Y-m');
        $end = $start->copy()->endOfMonth();

        // Department scope: managers, dms and employees only see their own department.
        // M5 (coordinator fix at merge): a scoped role must NEVER fall through
        // to the request parameter — a manager/dm/employee with a null department
        // previously could view ANY department's calendar via ?department_id=.
        // Scoped roles with no department now match nothing (-1 sentinel);
        // HR/super_admin stay unscoped and may still filter by the parameter.
        $scopedDeptId = null;
        if (in_array($role, ['manager', 'dm', 'employee']) && $employee) {
            $scopedDeptId = $employee->department_id;
        }
        if (in_array($role, ['manager', 'dm', 'employee'])) {
            $filterDeptId = $scopedDeptId ?: -1;
        } else {
            $filterDeptId = $request->filled('department_id') ? (int) $request->department_id : null;
        }

        $query = LeaveRequest::with(['employee.user', 'employee.department', 'leaveType'])
            ->whereDate('start_date', '<=', $end->toDateString())
            ->whereDate('end_date', '>=', $start->toDateString());

        // Calendar filters (applied on top of the department scope above)
        if ($request->filled('leave_type_id') && is_numeric($request->leave_type_id)) {
            $query->where('leave_type_id', (int) $request->leave_type_id);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            // L10: escape LIKE wildcards so a search term cannot act as a pattern.
            $search = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $search);
            $query->whereHas('employee.user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }
        if ($request->filled('status') && in_array($request->status, ['pending', 'hr_approved', 'manager_approved', 'approved', 'rejected', 'cancelled'], true)) {
            $query->where('status', $request->status);
        } else {
            $query->whereNotIn('status', ['rejected', 'cancelled']);
        }

        if ($filterDeptId) {
            $query->whereHas('employee', fn($q) => $q->where('department_id', $filterDeptId));
        }

        $leaves = $query->get();

        // Build date => leaves map for the month
        $days = [];
        for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
            $days[$d->toDateString()] = [];
        }
        foreach ($leaves as $leave) {
            $ls = Carbon::parse($leave->start_date);
            if ($ls->lt($start)) $ls = $start->copy();
            $le = Carbon::parse($leave->end_date);
            if ($le->gt($end)) $le = $end->copy();
            for ($d = $ls->copy(); $d->lte($le); $d->addDay()) {
                $days[$d->toDateString()][] = $leave;
            }
        }

        $holidays = Holiday::whereDate('start_date', '<=', $end->toDateString())
            ->whereDate('end_date', '>=', $start->toDateString())
            ->get();

        $departments = Department::orderBy('name')->get();
        $leaveTypes = LeaveType::where('is_active', true)->orderBy('name')->get();
        $prevMonth = $start->copy()->subMonth()->format('Y-m');
        $nextMonth = $start->copy()->addMonth()->format('Y-m');

        return view('leaves.calendar', compact(
            'days', 'start', 'end', 'month', 'holidays', 'departments',
            'filterDeptId', 'prevMonth', 'nextMonth', 'scopedDeptId', 'leaveTypes'
        ));
    }

    // ========================================
    // Leave-workflow fix helpers (WF/M-series)
    // ========================================

    /**
     * M1: department-ownership gate for manager/dm actors on approve/reject.
     * super_admin and hr_manager are exempt (company-wide). A manager/dm
     * with no managed department, or acting on another department's leave,
     * gets 403. The leave's employee must exist and belong to the actor's
     * managed department.
     */
    private function assertManagerDepartmentScope(LeaveRequest $leave): void
    {
        $user = Auth::user();
        if ($user->hasAnyRole(['super_admin', 'hr_manager'])) {
            return;
        }

        $managedDepartment = $user->managedDepartment ?? null;
        $employee = $leave->employee;
        if (!$managedDepartment || !$employee || (int) $employee->department_id !== (int) $managedDepartment->id) {
            abort(403, 'Unauthorized.');
        }
    }

    /**
     * WF-04/M2: first existing leave of this employee whose date range
     * overlaps [start_date, end_date] and whose status can still count
     * (pending, hr_approved, approved). Overlap = new.start <= existing.end
     * AND new.end >= existing.start. Rejected/cancelled rows never conflict;
     * soft-deleted rows are excluded by the default scope.
     */
    private function findOverlappingLeave(int $employeeId, string $startDate, string $endDate): ?LeaveRequest
    {
        return LeaveRequest::with('leaveType')
            ->where('employee_id', $employeeId)
            ->whereIn('status', ['pending', 'hr_approved', 'approved'])
            ->where('start_date', '<=', $endDate)
            ->where('end_date', '>=', $startDate)
            ->orderBy('start_date')
            ->first();
    }

    /**
     * WF-04/M2: the overlap error message. It names the conflicting leave
     * (type, dates, status) so the user can see exactly what collides.
     */
    private function overlapMessage(LeaveRequest $conflict): string
    {
        $type = $conflict->leaveType->name ?? 'Leave';
        $start = Carbon::parse($conflict->start_date)->format('Y-m-d');
        $end = Carbon::parse($conflict->end_date)->format('Y-m-d');
        return "These dates overlap an existing leave request: {$type} from {$start} to {$end} ({$conflict->status}). Please choose different dates.";
    }

    /**
     * WF-14 (controller side): run a notification send so a failure is
     * logged and never 500s the leave transition. The
     * LeaveNotificationService itself also catches failures; this is the
     * second layer. Making the notification classes themselves ShouldQueue
     * is a separate, still-open edit — their source files were not in the
     * staged material (see MERGE-AND-CHANGES.md). HR_NOTIFICATIONS_ENABLED
     * is deliberately NOT changed here.
     */
    private function notifySafely(callable $notify): void
    {
        try {
            $notify();
        } catch (\Throwable $e) {
            Log::warning('Leave notification failed: ' . $e->getMessage());
        }
    }
}
