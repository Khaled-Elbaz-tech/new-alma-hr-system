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
            
            // Alerts - negative balances (calculated dynamically from hire date + leave requests)
            $currentYear = Carbon::now()->year;
            $lowBalanceEmployees = collect();
            
            $employees = Employee::with('user')->get();
            foreach ($employees as $employee) {
                $analysis = $this->leaveCalculationService->analyze($employee);
                $yearlyData = $analysis['years'] ?? [];
                $currentYearData = $yearlyData[$currentYear] ?? null;
                
                if ($currentYearData && ($currentYearData['balance'] ?? 0) < 0) {
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
            
            // Get leave analysis from calculation service (dynamic, based on hire date + requests)
            $analysis = $this->leaveCalculationService->analyze($employee);
            $yearlyData = $analysis['years'] ?? [];
            
            $currentYearData = $yearlyData[$currentYear] ?? null;
            $previousYearData = $yearlyData[$previousYear] ?? null;
            
            // Build balance objects compatible with the view
            $currentBalance = $currentYearData ? (object) [
                'year' => $currentYear,
                'entitled_days' => $currentYearData['days_entitled'] ?? 0,
                'used_days' => $currentYearData['days_used'] ?? 0,
                'balance' => $currentYearData['balance'] ?? 0,
                'ticket_count' => $currentYearData['ticket_count'] ?? 0,
                'exit_reentry_count' => $currentYearData['exit_reentry_count'] ?? 0,
            ] : null;
            
            $previousBalance = $previousYearData ? (object) [
                'year' => $previousYear,
                'entitled_days' => $previousYearData['days_entitled'] ?? 0,
                'used_days' => $previousYearData['days_used'] ?? 0,
                'balance' => $previousYearData['balance'] ?? 0,
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
}