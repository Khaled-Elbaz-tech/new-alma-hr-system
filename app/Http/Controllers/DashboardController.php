<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\Department;
use App\Models\Holiday;
use App\Models\ActivityLog;
use App\Models\Role;
use App\Services\LeaveCalculationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * DashboardController — FIXED batch (Reports + Dashboard worker).
 *
 * Fixes applied:
 * - WF-10: the employee branch and the HR negative-balance alert no
 *   longer use analyze() (current-year entitlement INCLUDED). Both now
 *   use the visible-balance policy (current-year entitlement EXCLUDED
 *   until 1 January; negatives shown as-is):
 *     * employee branch: visibleBalanceData() directly (single employee,
 *       bounded) — currentBalance->balance is the visible balance,
 *       currentBalance->entitled_days is null for the current year
 *       (hidden, never 0), plus visible_* totals and opens_on.
 *     * HR branch: one grouped query for all approved-annual used days
 *       + pure date arithmetic for closed-year entitlements (same
 *       1.75/month, capped, formula as the service), so the alert list
 *       matches visibleBalanceData() without a per-employee analyze().
 *   No analyze()-based balance remains in this file.
 * - WF-16: HR alert loop no longer runs analyze() per employee (several
 *   queries per employee per year); it is now 1 grouped query + PHP math.
 * - WF-20(b) phantom manager_approved stat: NOT in this file — it lives
 *   in LeaveController::index (see PATCH-NOTE); nothing removed here.
 * - L5/L10, M3/M4: this controller has no search inputs and no exports,
 *   so there is nothing to group/escape/sanitise here (verified by grep).
 * Preserved: all branch view names and data keys, POST-only mutations
 * (none here), dynamic balances, visible-balance policy.
 */
class DashboardController extends Controller
{
    protected $leaveCalculationService;

    public function __construct(LeaveCalculationService $leaveCalculationService)
    {
        $this->leaveCalculationService = $leaveCalculationService;
    }

    public function index()
    {
        $user = auth()->user()->load('role');
        
        $data = [
            'user' => $user,
        ];

        // Super Admin Dashboard
        if ($user->isSuperAdmin()) {
            $totalUsers = User::count();
            $totalEmployees = Employee::count();
            $totalDepartments = Department::count();
            $totalLeaveRequests = LeaveRequest::count();
            $systemActivityCount = ActivityLog::where('created_at', '>=', Carbon::now()->subDays(30))->count();
            $recentActivityLogs = ActivityLog::with('user')
                ->orderBy('created_at', 'desc')
                ->take(10)
                ->get();
            
            // Employee stats
            $activeEmployees = Employee::where('employment_status', 'active')->count();
            $inactiveEmployees = Employee::where('employment_status', '!=', 'active')->count();
            
            // Leave stats
            $pendingLeavesCount = LeaveRequest::where('status', 'pending')->count();
            $approvedLeavesThisMonth = LeaveRequest::where('status', 'approved')
                ->whereMonth('start_date', Carbon::now()->month)->count();
            
            // Upcoming holidays
            $upcomingHolidays = Holiday::where('start_date', '>=', Carbon::now()->format('Y-m-d'))
                ->orderBy('start_date')->take(5)->get();

            // Department breakdown
            $departments = Department::withCount('employees')->get();

            $data['totalUsers'] = $totalUsers;
            $data['totalEmployees'] = $totalEmployees;
            $data['totalDepartments'] = $totalDepartments;
            $data['totalLeaveRequests'] = $totalLeaveRequests;
            $data['systemActivityCount'] = $systemActivityCount;
            $data['recentActivityLogs'] = $recentActivityLogs;
            $data['activeEmployees'] = $activeEmployees;
            $data['inactiveEmployees'] = $inactiveEmployees;
            $data['pendingLeavesCount'] = $pendingLeavesCount;
            $data['approvedLeavesThisMonth'] = $approvedLeavesThisMonth;
            $data['upcomingHolidays'] = $upcomingHolidays;
            $data['departments'] = $departments;
            
            return view('dashboard.super-admin-view', $data);
        }
        
        // HR Manager Dashboard
        if ($user->isHrManager()) {
            $employeesCount = Employee::count();
            $activeEmployees = Employee::where('employment_status', 'active')->count();
            
            // Pending leave requests
            $pendingLeaveRequests = LeaveRequest::where('status', 'pending')
                ->with(['employee.user', 'leaveType'])
                ->orderBy('created_at', 'desc')
                ->take(10)
                ->get();
            $pendingLeavesCount = LeaveRequest::where('status', 'pending')->count();
            
            // Approved this month
            $approvedLeavesThisMonth = LeaveRequest::where('status', 'approved')
                ->whereMonth('start_date', Carbon::now()->month)->count();
            
            // Upcoming official holidays
            $upcomingHolidays = Holiday::where('start_date', '>=', Carbon::now()->format('Y-m-d'))
                ->orderBy('start_date')->take(5)->get();
            
            // Alerts - negative VISIBLE balances (WF-10/WF-16). The base
            // version ran analyze() per employee (several queries per
            // employee per year) and flagged the OLD balance (current-year
            // entitlement included). Now: ONE grouped query for total
            // approved-annual used days per employee, plus closed-year
            // entitlements computed in PHP with the service's own formula
            // (1.75 days per month worked, capped at the employee's annual
            // entitlement). Visible balance = closed-year entitlements
            // minus ALL approved-annual used days; negative shown as-is.
            $currentYear = Carbon::now()->year;
            $lowBalanceEmployees = collect();

            $employees = Employee::with('user')->get();
            $usedByEmployee = LeaveRequest::selectRaw('employee_id, SUM(total_days) as used')
                ->whereIn('employee_id', $employees->pluck('id')->all())
                ->where('status', 'approved')
                ->whereHas('leaveType', function ($q) {
                    $q->where('code', 'annual');
                })
                ->groupBy('employee_id')
                ->get()
                ->keyBy('employee_id');

            foreach ($employees as $employee) {
                $closedEntitled = $this->closedYearsEntitlement($employee, $currentYear);
                $used = $usedByEmployee->get($employee->id)->used ?? 0;
                if (($closedEntitled - (float) $used) < 0) {
                    $lowBalanceEmployees->push($employee);
                }
            }
            
            // Department breakdown
            $departments = Department::withCount('employees')->get();

            $data['employeesCount'] = $employeesCount;
            $data['activeEmployees'] = $activeEmployees;
            $data['pendingLeaveRequests'] = $pendingLeaveRequests;
            $data['pendingLeavesCount'] = $pendingLeavesCount;
            $data['approvedLeavesThisMonth'] = $approvedLeavesThisMonth;
            $data['upcomingHolidays'] = $upcomingHolidays;
            $data['lowBalanceEmployees'] = $lowBalanceEmployees;
            $data['departments'] = $departments;
            
            return view('dashboard.hr-view', $data);
        }
        
        // Manager / DM Dashboard
        if ($user->isManager() || $user->isDM()) {
            $managedDepartment = $user->managedDepartment;
            
            if ($managedDepartment) {
                $teamEmployees = Employee::where('department_id', $managedDepartment->id)
                    ->with('user')
                    ->get();
                
                $teamEmployeeIds = $teamEmployees->pluck('id');
                
                // Pending approvals
                $pendingApprovals = LeaveRequest::whereIn('employee_id', $teamEmployeeIds)
                    ->where('status', 'pending')
                    ->with(['employee.user', 'leaveType'])
                    ->orderBy('start_date')
                    ->get();
                $pendingCount = $pendingApprovals->count();
                
                // Approved/rejected counts
                $approvedCount = LeaveRequest::whereIn('employee_id', $teamEmployeeIds)
                    ->where('status', 'approved')->count();
                $rejectedCount = LeaveRequest::whereIn('employee_id', $teamEmployeeIds)
                    ->where('status', 'rejected')->count();
                
                // Team members on leave today
                $today = Carbon::now()->format('Y-m-d');
                $onLeaveToday = LeaveRequest::whereIn('employee_id', $teamEmployeeIds)
                    ->where('status', 'approved')
                    ->whereDate('start_date', '<=', $today)
                    ->whereDate('end_date', '>=', $today)
                    ->with(['employee.user', 'leaveType'])
                    ->get();
                
                // Upcoming team leaves
                $upcomingLeaves = LeaveRequest::whereIn('employee_id', $teamEmployeeIds)
                    ->where('status', 'approved')
                    ->whereDate('start_date', '>', $today)
                    ->with(['employee.user', 'leaveType'])
                    ->orderBy('start_date')
                    ->take(5)
                    ->get();

                $data['managedDepartment'] = $managedDepartment;
                $data['teamEmployees'] = $teamEmployees;
                $data['pendingApprovals'] = $pendingApprovals;
                $data['pendingCount'] = $pendingCount;
                $data['approvedCount'] = $approvedCount;
                $data['rejectedCount'] = $rejectedCount;
                $data['onLeaveToday'] = $onLeaveToday;
                $data['upcomingLeaves'] = $upcomingLeaves;
            }
            
            return view('dashboard.manager-view', $data);
        }
        
        // Employee Dashboard
        $employee = $user->employee;
        
        if ($employee) {
            $currentYear = Carbon::now()->year;
            $previousYear = $currentYear - 1;
            
            // WF-10: visible balance from visibleBalanceData() (single
            // employee, bounded) — current-year entitlement is EXCLUDED
            // until 1 January. currentBalance keeps the view's object
            // shape, but: entitled_days is null for the current year
            // (hidden, never rendered as 0), balance is the visible
            // running balance, and visible_* totals + opens_on are added
            // for the view batch to render the policy explainer.
            $visible = $this->leaveCalculationService->visibleBalanceData($employee);
            $visibleRows = collect($visible['rows'] ?? [])->keyBy('year');

            $currentRow = $visibleRows->get($currentYear);
            $previousRow = $visibleRows->get($previousYear);
            // Ticket / exit-re-entry counts are NOT balances; the service
            // does not return them in visibleBalanceData(), so they are
            // counted directly (one query, current year, annual only).
            $serviceCounts = $this->currentYearServiceCounts($employee->id, $currentYear);

            // Build balance objects compatible with the view
            $currentBalance = $currentRow ? (object) [
                'year' => $currentYear,
                'entitled_days' => $currentRow['entitled'],
                'used_days' => $currentRow['used'] ?? 0,
                'balance' => $visible['visible_balance'] ?? $currentRow['remaining'] ?? 0,
                'visible_entitled' => $visible['visible_entitled'] ?? 0,
                'visible_used' => $visible['visible_used'] ?? 0,
                'visible_balance' => $visible['visible_balance'] ?? 0,
                'opens_on' => $visible['opens_on'] ?? null,
                'ticket_count' => $serviceCounts['tickets'],
                'exit_reentry_count' => $serviceCounts['exits'],
            ] : null;

            $previousBalance = $previousRow ? (object) [
                'year' => $previousYear,
                'entitled_days' => $previousRow['entitled'] ?? 0,
                'used_days' => $previousRow['used'] ?? 0,
                'balance' => $previousRow['remaining'] ?? 0,
            ] : null;
            
            // Total taken this year - ONLY annual leave (per business rule)
            $totalTakenThisYear = LeaveRequest::where('employee_id', $employee->id)
                ->whereYear('start_date', $currentYear)
                ->where('status', 'approved')
                ->whereHas('leaveType', function($q) {
                    $q->where('code', 'annual');
                })
                ->sum('total_days');
            
            // Next approved leave
            $nextApprovedLeave = LeaveRequest::where('employee_id', $employee->id)
                ->where('status', 'approved')
                ->whereDate('start_date', '>', Carbon::now()->format('Y-m-d'))
                ->with('leaveType')
                ->orderBy('start_date')
                ->first();
            
            // Leave request status list (all statuses)
            $allLeaveRequests = LeaveRequest::where('employee_id', $employee->id)
                ->with('leaveType')
                ->orderBy('created_at', 'desc')
                ->take(10)
                ->get();
            
            $pendingCount = LeaveRequest::where('employee_id', $employee->id)->where('status', 'pending')->count();
            $approvedCount = LeaveRequest::where('employee_id', $employee->id)->where('status', 'approved')->count();
            $rejectedCount = LeaveRequest::where('employee_id', $employee->id)->where('status', 'rejected')->count();
            
            // Get upcoming holidays
            $upcomingHolidays = Holiday::where('start_date', '>=', Carbon::now()->format('Y-m-d'))
                ->orderBy('start_date')
                ->take(5)
                ->get();
            
            $data['employee'] = $employee;
            $data['currentBalance'] = $currentBalance;
            $data['previousBalance'] = $previousBalance;
            $data['visibleBalance'] = $visible;
            $data['totalTakenThisYear'] = $totalTakenThisYear;
            $data['nextApprovedLeave'] = $nextApprovedLeave;
            $data['allLeaveRequests'] = $allLeaveRequests;
            $data['pendingCount'] = $pendingCount;
            $data['approvedCount'] = $approvedCount;
            $data['rejectedCount'] = $rejectedCount;
            $data['upcomingHolidays'] = $upcomingHolidays;
        }
        
        return view('dashboard.employee-view', $data);
    }

    /**
     * WF-10/WF-16 helpers. Months/entitlement math mirrors
     * LeaveCalculationService::calculateMonthsAtYearEnd() and
     * calculateDaysEntitled() exactly (pure date arithmetic, no queries),
     * so the HR alert list agrees with visibleBalanceData().
     */
    protected function monthsFromHiring(?Employee $employee, int $year): int
    {
        if (!$employee || !$employee->joining_date) {
            return 0;
        }
        $joiningDate = Carbon::parse($employee->joining_date);
        if ($joiningDate->year > $year) {
            return 0;
        }
        $yearStart = Carbon::create($year, 1, 1)->startOfDay();
        $yearEnd = Carbon::create($year, 12, 31)->endOfDay();
        $workStart = $joiningDate->lt($yearStart) ? $yearStart : $joiningDate;
        $effectiveEnd = min($yearEnd, Carbon::now());
        if ($workStart->gt($effectiveEnd)) {
            return 0;
        }
        $months = ($effectiveEnd->year - $workStart->year) * 12
            + ($effectiveEnd->month - $workStart->month) + 1;
        return max(0, min(12, $months));
    }

    protected function closedYearsEntitlement(?Employee $employee, int $currentYear): float
    {
        if (!$employee || !$employee->joining_date) {
            return 0.0;
        }
        $joiningYear = Carbon::parse($employee->joining_date)->year;
        $total = 0.0;
        for ($y = $joiningYear; $y < $currentYear; $y++) {
            $total += min(round(1.75 * $this->monthsFromHiring($employee, $y), 2), $employee->annual_leave_entitlement ?? 21);
        }
        return round($total, 2);
    }

    protected function currentYearServiceCounts(int $employeeId, int $year): array
    {
        $row = LeaveRequest::selectRaw(
                'SUM(CASE WHEN flight_ticket_required THEN 1 ELSE 0 END) as tickets, '
                . 'SUM(CASE WHEN exit_reentry_required THEN 1 ELSE 0 END) as exits'
            )
            ->where('employee_id', $employeeId)
            ->where('status', 'approved')
            ->whereYear('start_date', $year)
            ->whereHas('leaveType', function ($q) {
                $q->where('code', 'annual');
            })
            ->first();

        return [
            'tickets' => $row ? (int) $row->tickets : 0,
            'exits' => $row ? (int) $row->exits : 0,
        ];
    }
}