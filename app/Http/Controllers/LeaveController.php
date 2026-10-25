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
use Illuminate\Support\Facades\Storage;

class LeaveController extends Controller
{
    /**
     * Display a listing of leave requests (admin/HR view)
     */
    public function index(Request $request)
    {
        $query = LeaveRequest::with(['employee.user', 'leaveType'])
            ->orderBy('created_at', 'desc');

        // Filter by employee for non-super_admin
        if (Auth::user()->hasRole('hr_manager') && !Auth::user()->hasRole('super_admin')) {
            $query->where('employee_id', Auth::user()->employee->id);
        }

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('employee.user', function ($q2) use ($search) {
                    $q2->where('name', 'like', "%{$search}%");
                })->orWhere('employee_id', 'like', "%{$search}%");
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Leave type filter
        if ($request->filled('leave_type_id')) {
            $query->where('leave_type_id', $request->leave_type_id);
        }

        $leaveRequests = $query->paginate(20);

        // Get statistics counts
        $stats = [
            'total' => LeaveRequest::count(),
            'pending' => LeaveRequest::where('status', 'pending')->count(),
            'hr_approved' => LeaveRequest::where('status', 'hr_approved')->count(),
            'manager_approved' => LeaveRequest::where('status', 'manager_approved')->count(),
            'approved' => LeaveRequest::where('status', 'approved')->count(),
            'rejected' => LeaveRequest::where('status', 'rejected')->count(),
        ];

        // Get all leave types for filter dropdown
        $leaveTypes = LeaveType::orderBy('name')->get();

        return view('leaves.index', compact('leaveRequests', 'stats', 'leaveTypes'));
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

        $leaveRequests = LeaveRequest::where('employee_id', $employee->id)
            ->with('leaveType')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $leaveTypes = LeaveType::where('is_active', true)->get();
        $currentYear = Carbon::now()->year;

        // Get leave balance dynamically using calculation engine
        $calculationService = app(LeaveCalculationService::class);
        $analysis = $calculationService->analyze($employee);
        $yearlyData = $analysis['years'] ?? [];
        $currentYearData = $yearlyData[$currentYear] ?? null;

        // Build leaveBalances collection dynamically for view compatibility
        $leaveBalances = collect();
        foreach ($yearlyData as $year => $data) {
            $leaveBalances->push((object) [
                'year' => $year,
                'entitled_days' => $data['days_entitled'] ?? 0,
                'used_days' => $data['days_used'] ?? 0,
                'ticket_count' => $data['ticket_count'] ?? 0,
                'exit_reentry_count' => $data['exit_reentry_count'] ?? 0,
                'balance' => $data['balance'] ?? 0,
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

        $holidays = Holiday::where('start_date', '>=', Carbon::now()->format('Y-m-d'))
            ->orderBy('start_date')
            ->get();

        // Get yearly summary with carried balances (from calculation engine)
        $yearlySummary = $this->getYearlyLeaveSummary($employee);

        $showForm = $request->query('open_form', false);

        return view('leaves.my-leaves', compact(
            'leaveRequests',
            'employee',
            'leaveTypes',
            'leaveBalances',
            'annualBalance',
            'visibleBalance',
            'holidays',
            'showForm',
            'yearlySummary'
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
            'leave_type_id' => 'required|exists:leave_types,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|max:2000',
            'flight_ticket_required' => 'nullable|boolean',
            'exit_reentry_required' => 'nullable|boolean',
            'hotel_booking_required' => 'nullable|boolean',
            'transfer_required' => 'nullable|boolean',
        ], [
            'leave_type_id.required' => 'Leave type is required.',
            'start_date.required' => 'Start date is required.',
            'end_date.required' => 'End date is required.',
            'end_date.after_or_equal' => 'End date must be after or equal to start date.',
            'reason.required' => 'Reason is required.',
        ]);

        $leaveType = LeaveType::findOrFail($request->leave_type_id);
        $currentYear = Carbon::now()->year;

        // Calculate working days using the service
        $calculationService = app(LeaveCalculationService::class);
        $workingDays = $calculationService->calculateWorkingDays(
            Carbon::parse($request->start_date),
            Carbon::parse($request->end_date),
            $employee->id
        );

        // Calculate calendar days
        $calendarDays = Carbon::parse($request->start_date)->diffInDays(Carbon::parse($request->end_date)) + 1;

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
            'flight_ticket_required' => $request->boolean('flight_ticket_required', false),
            'exit_reentry_required' => $request->boolean('exit_reentry_required', false),
            'status' => 'pending',
            'created_by' => Auth::id(),
        ]);

        // Email notification (no-op until HR_NOTIFICATIONS_ENABLED=true)
        app(\App\Services\LeaveNotificationService::class)->notifyNewRequest($leaveRequest);

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
        })->orderBy('employee_number')->get();

        $filterYear = $request->filled('year') ? $request->year : null;

        $balances = collect();
        foreach ($employees as $employee) {
            $analysis = $calculationService->analyze($employee);
            $yearlyData = $analysis['years'] ?? [];
            $currentYear = Carbon::now()->year;

            foreach ($yearlyData as $year => $data) {
                if ($filterYear !== null && $year != $filterYear) continue;

                $balances->push((object) [
                    'id' => null, // No DB record in dynamic system
                    'employee_id' => $employee->id,
                    'employee' => $employee,
                    'year' => $year,
                    'entitled_days' => $data['days_entitled'] ?? 0,
                    'used_days' => $data['days_used'] ?? 0,
                    'ticket_count' => $data['ticket_count'] ?? 0,
                    'exit_reentry_count' => $data['exit_reentry_count'] ?? 0,
                    'balance' => $data['balance'] ?? 0,
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
            ['path' => $request->url(), 'page' => 'page']
        );

        $years = LeaveYear::orderBy('year', 'asc')->pluck('year');

        return view('leaves.manage-balances', compact('balances', 'years'));
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

        // Build balance data for view
        $balancesByYear = [];
        foreach ($yearlyData as $year => $data) {
            $balancesByYear[$year] = (object) [
                'year' => $year,
                'entitled_days' => $data['days_entitled'] ?? 0,
                'used_days' => $data['days_used'] ?? 0,
                'ticket_count' => $data['ticket_count'] ?? 0,
                'exit_reentry_count' => $data['exit_reentry_count'] ?? 0,
                'balance' => $data['balance'] ?? 0,
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

        return view('leaves.edit-balance', compact('balance', 'leaveYears', 'monthsWorked', 'employee', 'balancesByYear'));
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
     * Delete a leave request (only pending)
     */
    public function destroy(LeaveRequest $leave)
    {
        if (!Auth::user()->hasAnyRole(['super_admin', 'hr_manager'])) {
            abort(403);
        }

        if ($leave->status !== 'pending') {
            return back()->with('error', 'Cannot delete approved leave requests.');
        }

        // No manual balance adjustment needed - balances are calculated dynamically from leave requests.
        // Deleting a pending leave request only affects future dynamic calculations.

        $leave->delete();

        return redirect()->route('leaves.index')
            ->with('success', 'Leave request deleted.');
    }

    /**
     * Export leave reports
     */
    public function export(Request $request)
    {
        if (!Auth::user()->hasAnyRole(['super_admin', 'hr_manager'])) {
            abort(403);
        }

        $format = $request->get('format', 'csv');
        $reportType = $request->get('type', 'balance');

        // Use ReportsController for exports
        $reportsController = new \App\Http\Controllers\ReportsController();
        return $reportsController->export($request);
    }

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
     * Uses LeaveCalculationService for dynamic balance calculation
     */
    public function getEmployeeData(Employee $employee)
     {
        if (!Auth::user()->hasAnyRole(['super_admin', 'hr_manager'])) {
            abort(403);
        }

        $currentYear = Carbon::now()->year;
        $calculationService = app(\App\Services\LeaveCalculationService::class);

        // Get all leave analysis for this employee using the calculation engine
        $analysis = $calculationService->analyze($employee);

        // Build yearly summary from dynamically calculated data
        $yearlyData = $analysis['years'] ?? [];

        // Get current year data
        $currentYearData = $yearlyData[$currentYear] ?? null;

        // Get previous year data for carry-forward reference
        $previousYear = $currentYear - 1;
        $previousYearData = $yearlyData[$previousYear] ?? null;

        // Calculate available balance from current year balance
        // The service calculates balance as: PreviousBalance + Entitlement - Used
        $availableBalance = $currentYearData ? $currentYearData['balance'] : 0;

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
                'name' => $employee->user->name,
                'department' => $employee->department->name ?? 'No Department',
                'joining_date' => $employee->joining_date->format('Y-m-d'),
                'employment_status' => $employee->employment_status,
            ],
            'current_balance' => $currentYearData ? [
                'entitled_days' => $currentYearData['days_entitled'],
                'used_days' => $currentYearData['days_used'],
                'balance' => $currentYearData['balance'],
                'ticket_count' => $currentYearData['ticket_count'] ?? 0,
                'exit_reentry_count' => $currentYearData['exit_reentry_count'] ?? 0,
            ] : null,
            'previous_balance' => $previousYearData ? [
                'balance' => $previousYearData['balance'],
                'entitled_days' => $previousYearData['days_entitled'],
                'used_days' => $previousYearData['days_used'],
            ] : null,
            'available_balance' => $availableBalance,
            'balances' => collect($yearlyData)->map(function($data) {
                return [
                    'year' => $data['year'],
                    'entitled_days' => $data['days_entitled'],
                    'used_days' => $data['days_used'],
                    'balance' => $data['balance'],
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
        $currentYear = Carbon::now()->year;

        // Calculate working days using service
        $workingDays = app(LeaveCalculationService::class)->calculateWorkingDays(
            Carbon::parse($request->start_date),
            Carbon::parse($request->end_date),
            $employee->id
        );

        $calendarDays = Carbon::parse($request->start_date)->diffInDays(Carbon::parse($request->end_date)) + 1;

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
            'flight_ticket_required' => $request->boolean('flight_ticket_required', false),
            'exit_reentry_required' => $request->boolean('exit_reentry_required', false),
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
        $leave->status = 'hr_approved';
        $leave->hr_approved_by = Auth::user()->employee?->id;
        $leave->hr_approved_at = now();
        $leave->hr_comments = $request->hr_comments ?? null;
        $leave->save();

        // Email notification (no-op until enabled)
        app(\App\Services\LeaveNotificationService::class)->notifyStatusChanged($leave);

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
     */
    public function managerApprove(Request $request, LeaveRequest $leave)
    {
        if (!Auth::user()->hasAnyRole(['super_admin', 'hr_manager', 'manager', 'dm'])) {
            abort(403, 'Unauthorized.');
        }

        if ($leave->status !== 'hr_approved') {
            return back()->with('error', 'Leave request must be HR approved first.');
        }

        $oldStatus = $leave->status;
        $leave->status = 'approved';
        $leave->manager_approved_by = Auth::user()->employee?->id;
        $leave->manager_approved_at = now();
        $leave->manager_comments = $request->manager_comments ?? null;
        $leave->save();

        // Email notification (no-op until enabled)
        app(\App\Services\LeaveNotificationService::class)->notifyStatusChanged($leave);

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
     */
    public function reject(Request $request, LeaveRequest $leave)
    {
        if (!Auth::user()->hasAnyRole(['super_admin', 'hr_manager', 'manager', 'dm'])) {
            abort(403, 'Unauthorized.');
        }

        $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ]);

        $leave->status = 'rejected';
        $leave->rejected_by = Auth::user()->employee?->id;
        $leave->rejected_at = now();
        $leave->rejection_reason = $request->rejection_reason;
        $leave->save();

        // Email notification (no-op until enabled)
        app(\App\Services\LeaveNotificationService::class)->notifyStatusChanged($leave);

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
     */
    public function cancel(LeaveRequest $leave)
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
        ]);

        $employee = Employee::findOrFail($request->employee_id);
        $leaveType = LeaveType::findOrFail($request->leave_type_id);
        $year = Carbon::parse($request->start_date)->year;

        // Calculate working days
        $workingDays = app(LeaveCalculationService::class)->calculateWorkingDays(
            Carbon::parse($request->start_date),
            Carbon::parse($request->end_date),
            $employee->id
        );

        $calendarDays = Carbon::parse($request->start_date)->diffInDays(Carbon::parse($request->end_date)) + 1;

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
            'flight_ticket_required' => $request->boolean('flight_ticket_required', false),
            'exit_reentry_required' => $request->boolean('exit_reentry_required', false),
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
        if (in_array($user->role->name ?? '', ['manager', 'dm'])) {
            $query->whereHas('employee', function ($q) use ($employee) {
                $q->where('department_id', $employee->department_id);
            });
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('employee.user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $leaveRequests = $query->paginate(20);

        return view('leaves.manager-index', compact('leaveRequests'));
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

        // Department scope: managers, dms and employees only see their own department
        $scopedDeptId = null;
        if (in_array($role, ['manager', 'dm', 'employee']) && $employee) {
            $scopedDeptId = $employee->department_id;
        }
        $filterDeptId = $scopedDeptId ?: ($request->filled('department_id') ? (int) $request->department_id : null);

        $query = LeaveRequest::with(['employee.user', 'employee.department', 'leaveType'])
            ->whereNotIn('status', ['rejected', 'cancelled'])
            ->whereDate('start_date', '<=', $end->toDateString())
            ->whereDate('end_date', '>=', $start->toDateString());

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
        $prevMonth = $start->copy()->subMonth()->format('Y-m');
        $nextMonth = $start->copy()->addMonth()->format('Y-m');

        return view('leaves.calendar', compact(
            'days', 'start', 'end', 'month', 'holidays', 'departments',
            'filterDeptId', 'prevMonth', 'nextMonth', 'scopedDeptId'
        ));
    }
}
