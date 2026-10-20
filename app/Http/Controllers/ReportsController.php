<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\Department;
use App\Services\LeaveCalculationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportsController extends Controller
{
    protected $leaveCalculationService;

    public function __construct(LeaveCalculationService $leaveCalculationService)
    {
        $this->leaveCalculationService = $leaveCalculationService;
    }

    // Leave Balance Report - Year by year breakdown per employee
    public function leaveBalance(Request $request)
    {
        $employeeId = $request->employee_id;
        $year = $request->year ?? Carbon::now()->year;
        
        $employees = Employee::with(['user', 'department'])
            ->where('employment_status', 'active')
            ->join('users', 'employees.user_id', '=', 'users.id')
            ->orderBy('users.name')
            ->get(['employees.*']);
        
        $leaveTypes = LeaveType::where('is_active', true)->get();
        
        // If specific employee selected, show year-by-year breakdown
        $selectedEmployee = null;
        $yearByYearData = [];
        
        if ($employeeId) {
            $selectedEmployee = Employee::with(['user', 'department'])->find($employeeId);
            
            $analysis = $this->leaveCalculationService->analyze($selectedEmployee);
            $yearlyData = $analysis['years'] ?? [];
            
            foreach ($yearlyData as $y => $data) {
                $yearByYearData[] = [
                    'year' => $y,
                    'months_from_hiring' => $data['months_from_hiring'] ?? 0,
                    'entitled_days' => $data['days_entitled'] ?? 0,
                    'used_days' => $data['days_used'] ?? 0,
                    'exit_reentry_count' => $data['exit_reentry_count'] ?? 0,
                    'ticket_count' => $data['ticket_count'] ?? 0,
                    'balance' => $data['balance'] ?? 0,
                ];
            }
        }
        
        // Pre-load all balances for active employees using calculation engine
        $employeeBalances = [];
        foreach ($employees as $emp) {
            $analysis = $this->leaveCalculationService->analyze($emp);
            $yearlyData = $analysis['years'] ?? [];
            $currentYearData = $yearlyData[$year] ?? null;
            
            $employeeBalances[$emp->id] = $yearlyData;
        }
        
        // Summary for all employees for the selected year
        $summaryData = $employees->map(function ($emp) use ($year, $employeeBalances) {
            $yearData = $employeeBalances[$emp->id][$year] ?? null;
            
            return [
                'employee' => $emp,
                'entitled_days' => $yearData['days_entitled'] ?? 0,
                'used_days' => $yearData['days_used'] ?? 0,
                'balance' => $yearData['balance'] ?? 0,
            ];
        });
        
        return view('reports.leave-balance', compact(
            'employees', 
            'leaveTypes', 
            'year',
            'employeeId',
            'selectedEmployee',
            'yearByYearData',
            'summaryData'
        ));
    }

    // Leave History Report
    public function leaveHistory(Request $request)
    {
        $employeeId = $request->employee_id;
        $year = $request->year ?? Carbon::now()->year;
        $leaveTypeId = $request->leave_type_id;
        $status = $request->status;
        
        $employees = Employee::with('user')->where('employment_status', 'active')->get();
        
        $leaveRequests = collect([]);
        $selectedEmployee = null;
        
        if ($employeeId) {
            $selectedEmployee = Employee::with('user')->find($employeeId);
            
            $query = LeaveRequest::where('employee_id', $employeeId)
                ->whereYear('start_date', $year)
                ->with(['leaveType', 'hrApprover', 'managerApprover', 'creator']);
            
            if ($leaveTypeId) {
                $query->where('leave_type_id', $leaveTypeId);
            }
            
            if ($status) {
                $query->where('status', $status);
            }
            
            $leaveRequests = $query->orderBy('start_date', 'desc')->get();
        }
        
        return view('reports.leave-history', compact('employees', 'leaveRequests', 'selectedEmployee', 'year'));
    }

    // Department Report
    public function department(Request $request)
    {
        $departmentId = $request->department_id;
        $startDate = $request->start_date;
        $endDate = $request->end_date;
        $leaveTypeId = $request->leave_type_id;
        
        $departments = Department::with(['manager', 'parent'])
            ->withCount('employees')
            ->get();
        
        $leaveTypes = LeaveType::where('is_active', true)->get();
        
        // Get detailed data for each department
        $departmentData = [];
        
        foreach ($departments as $dept) {
            $employeeIds = $dept->employees->pluck('id');
            
            // Leaves taken (approved) in date range
            $leavesQuery = LeaveRequest::whereIn('employee_id', $employeeIds)
                ->where('status', 'approved');
            
            if ($startDate) {
                $leavesQuery->where('end_date', '>=', $startDate);
            }
            if ($endDate) {
                $leavesQuery->where('start_date', '<=', $endDate);
            }
            if ($leaveTypeId) {
                $leavesQuery->where('leave_type_id', $leaveTypeId);
            }
            
            $leavesTaken = $leavesQuery->count();
            $daysTaken = $leavesQuery->sum('total_days');
            
            // Upcoming leaves
            $upcomingLeaves = LeaveRequest::whereIn('employee_id', $employeeIds)
                ->where('status', 'approved')
                ->where('start_date', '>=', Carbon::now()->format('Y-m-d'))
                ->where('start_date', '<=', Carbon::now()->addDays(30)->format('Y-m-d'))
                ->with(['employee.user', 'leaveType'])
                ->orderBy('start_date')
                ->get();
            
            // Monthly breakdown for absence trend
            $monthlyData = [];
            for ($month = 1; $month <= 12; $month++) {
                $monthLeaves = LeaveRequest::whereIn('employee_id', $employeeIds)
                    ->whereYear('start_date', Carbon::now()->year)
                    ->whereMonth('start_date', $month)
                    ->where('status', 'approved')
                    ->count();
                
                $monthlyData[$month] = $monthLeaves;
            }
            
            $departmentData[$dept->id] = [
                'leaves_taken' => $leavesTaken,
                'days_taken' => $daysTaken,
                'upcoming_leaves' => $upcomingLeaves,
                'monthly_trend' => $monthlyData,
            ];
        }
        
        return view('reports.department', compact(
            'departments', 
            'leaveTypes',
            'departmentId',
            'startDate',
            'endDate',
            'leaveTypeId',
            'departmentData'
        ));
    }

    // Employee Report
    public function employee(Request $request)
    {
        $employeeId = $request->employee_id;
        $departmentId = $request->department_id;
        $status = $request->status;
        
        $query = Employee::with(['user', 'department'])
            ->join('users', 'employees.user_id', '=', 'users.id');
        
        if ($departmentId) {
            $query->where('employees.department_id', $departmentId);
        }
        
        if ($status) {
            $query->where('employees.employment_status', $status);
        }
        
        $employees = $query->orderBy('users.name')->get(['employees.*']);
        $departments = Department::where('is_active', true)->get();
        
        // If specific employee selected, show detailed report
        $selectedEmployee = null;
        $yearByYearBalances = [];
        $leaveHistory = [];
        $attachments = [];
        
        if ($employeeId) {
            $selectedEmployee = Employee::with(['user', 'department', 'user.role', 'fieldValues.field'])
                ->find($employeeId);
            
            // Year-by-year leave balances (calculated dynamically)
            $analysis = $this->leaveCalculationService->analyze($selectedEmployee);
            $yearlyData = $analysis['years'] ?? [];
            
            foreach ($yearlyData as $y => $data) {
                $yearByYearBalances[] = [
                    'year' => $y,
                    'entitled' => $data['days_entitled'] ?? 0,
                    'used' => $data['days_used'] ?? 0,
                    'balance' => $data['balance'] ?? 0,
                ];
            }
            
            // Leave history (last 3 years)
            $leaveHistory = LeaveRequest::where('employee_id', $employeeId)
                ->whereYear('start_date', '>=', Carbon::now()->subYears(3)->year)
                ->with('leaveType')
                ->orderBy('start_date', 'desc')
                ->get();
            
            // Attachments from dynamic fields
            $attachments = $selectedEmployee->fieldValues()
                ->whereHas('field', function ($q) {
                    $q->where('field_type', 'attachment');
                })
                ->with('field')
                ->get();
        }
        
        return view('reports.employee', compact(
            'employees', 
            'departments',
            'employeeId',
            'departmentId',
            'status',
            'selectedEmployee',
            'yearByYearBalances',
            'leaveHistory',
            'attachments'
        ));
    }

    // Vacation History Report
    public function vacationHistory(Request $request)
    {
        $employeeId = $request->employee_id;
        $departmentId = $request->department_id;
        $year = $request->year;
        
        $query = Employee::with(['user', 'department'])
            ->join('users', 'users.id', '=', 'employees.user_id')
            ->select('employees.*')
            ->where('employment_status', 'active');
        
        if ($departmentId) {
            $query->where('department_id', $departmentId);
        }
        
        $employees = $query->orderBy('users.name')->get();
        $departments = Department::where('is_active', true)->get();
        
        $reportData = collect();
        $selectedEmployee = null;
        
        if ($employeeId) {
            $selectedEmployee = Employee::with('user', 'department')->find($employeeId);
            
            if ($selectedEmployee) {
                // Use dynamic calculation engine
                $analysis = $this->leaveCalculationService->analyze($selectedEmployee);
                $yearlyData = $analysis['years'] ?? [];
                
                $joiningYear = $selectedEmployee->joining_date ? $selectedEmployee->joining_date->year : Carbon::now()->year;
                $currentYear = $year ? Carbon::parse($year)->year : Carbon::now()->year;
                
                $cumulativeBalance = 0;
                for ($y = $joiningYear; $y <= $currentYear; $y++) {
                    $data = $yearlyData[$y] ?? null;
                    
                    if ($data) {
                        $entitledDays = $data['days_entitled'] ?? 0;
                        $usedDays = $data['days_used'] ?? 0;
                        $carryForward = $cumulativeBalance;
                        $cumulativeBalance = $data['balance'] ?? 0;
                    } else {
                        $entitledDays = 0;
                        $usedDays = 0;
                        $carryForward = $cumulativeBalance;
                    }
                    
                    $reportData->push([
                        'year' => $y,
                        'entitled_days' => $entitledDays,
                        'used_days' => $usedDays,
                        'carry_forward' => $carryForward,
                        'balance' => $cumulativeBalance,
                    ]);
                }
            }
        }
        
        return view('reports.vacation-history', compact(
            'employees',
            'departments',
            'employeeId',
            'departmentId',
            'year',
            'selectedEmployee',
            'reportData'
        ));
    }

    // Export Vacation History to Excel
    public function exportVacationHistory(Request $request)
    {
        $employeeId = $request->employee_id;
        $year = $request->year;
        
        $employee = Employee::with('user', 'department')->find($employeeId);
        
        if (!$employee) {
            return back()->with('error', 'Employee not found.');
        }
        
        // Use dynamic calculation engine
        $analysis = $this->leaveCalculationService->analyze($employee);
        $yearlyData = $analysis['years'] ?? [];
        
        $joiningYear = $employee->joining_date ? $employee->joining_date->year : Carbon::now()->year;
        $currentYear = $year ? Carbon::parse($year)->year : Carbon::now()->year;
        
        $headers = ['Year', 'Entitled Days', 'Used Days', 'Carry Forward', 'Balance'];
        $rows = [];
        
        $cumulativeBalance = 0;
        for ($y = $joiningYear; $y <= $currentYear; $y++) {
            $data = $yearlyData[$y] ?? null;
            
            if ($data) {
                $entitledDays = $data['days_entitled'] ?? 0;
                $usedDays = $data['days_used'] ?? 0;
                $carryForward = $cumulativeBalance;
                $cumulativeBalance = $data['balance'] ?? 0;
            } else {
                $entitledDays = 0;
                $usedDays = 0;
                $carryForward = $cumulativeBalance;
            }
            
            $rows[] = [
                $y,
                $entitledDays,
                $usedDays,
                $carryForward,
                $cumulativeBalance,
            ];
        }
        
        return $this->exportToExcel($headers, $rows, "vacation_history_{$employee->user->name}_{$currentYear}.csv");
    }

    // Leave Statistics
    public function leaveStatistics(Request $request)
    {
        $year = $request->year ?? Carbon::now()->year;
        
        // Monthly leave statistics
        $monthlyStats = [];
        for ($month = 1; $month <= 12; $month++) {
            $approved = LeaveRequest::whereYear('start_date', $year)
                ->whereMonth('start_date', $month)
                ->where('status', 'approved')
                ->count();
            
            $rejected = LeaveRequest::whereYear('start_date', $year)
                ->whereMonth('start_date', $month)
                ->where('status', 'rejected')
                ->count();
            
            $pending = LeaveRequest::whereYear('start_date', $year)
                ->whereMonth('start_date', $month)
                ->where('status', 'pending')
                ->count();
            
            $monthlyStats[$month] = [
                'approved' => $approved,
                'rejected' => $rejected,
                'pending' => $pending,
                'total' => $approved + $rejected + $pending,
            ];
        }
        
        // Leave type breakdown
        $leaveTypeStats = LeaveType::where('is_active', true)->get()->map(function ($type) use ($year) {
            $count = LeaveRequest::whereYear('start_date', $year)
                ->where('leave_type_id', $type->id)
                ->where('status', 'approved')
                ->count();
            
            $days = LeaveRequest::whereYear('start_date', $year)
                ->where('leave_type_id', $type->id)
                ->where('status', 'approved')
                ->sum('total_days');
            
            return [
                'name' => $type->name,
                'count' => $count,
                'days' => $days,
            ];
        });
        
        // Exit reentry and ticket stats
        $exitReentryCount = LeaveRequest::whereYear('start_date', $year)
            ->where('exit_reentry_required', true)
            ->where('status', 'approved')
            ->count();
        
        $ticketCount = LeaveRequest::whereYear('start_date', $year)
            ->where('flight_ticket_required', true)
            ->where('status', 'approved')
            ->count();
        
        return view('reports.leave-statistics', compact('year', 'monthlyStats', 'leaveTypeStats', 'exitReentryCount', 'ticketCount'));
    }

    // Export to Excel
    public function exportLeaveBalance(Request $request)
    {
        $year = $request->year ?? Carbon::now()->year;
        
        $employees = Employee::with(['user', 'department'])
            ->where('employment_status', 'active')->get();
        
        // Generate CSV content using dynamic calculation
        $headers = ['Employee', 'Department', 'Entitled', 'Used', 'Balance'];
        $rows = [];
        
        foreach ($employees as $emp) {
            $analysis = $this->leaveCalculationService->analyze($emp);
            $yearlyData = $analysis['years'] ?? [];
            $currentYearData = $yearlyData[$year] ?? null;
            
            $rows[] = [
                $emp->user->name ?? 'N/A',
                $emp->department->name ?? 'N/A',
                $currentYearData['days_entitled'] ?? 0,
                $currentYearData['days_used'] ?? 0,
                $currentYearData['balance'] ?? 0,
            ];
        }
        
        return $this->exportToExcel($headers, $rows, "leave_balance_{$year}.csv");
    }

    // Export to PDF
    public function exportLeaveHistory(Request $request)
    {
        $employeeId = $request->employee_id;
        $year = $request->year ?? Carbon::now()->year;
        
        $employee = Employee::with('user', 'department')->find($employeeId);
        
        $leaveRequests = LeaveRequest::where('employee_id', $employeeId)
            ->whereYear('start_date', $year)
            ->with(['leaveType', 'hrApprover', 'managerApprover', 'creator'])
            ->orderBy('start_date', 'desc')
            ->get();

        // Export as CSV
        $headers = ['ID', 'Leave Type', 'Start Date', 'End Date', 'Working Days', 'Calendar Days', 'Status', 'HR Approver', 'Manager Approver', 'Created By', 'Created At'];
        $rows = [];
        
        foreach ($leaveRequests as $leave) {
            $rows[] = [
                $leave->id,
                $leave->leaveType->name,
                $leave->start_date->format('Y-m-d'),
                $leave->end_date->format('Y-m-d'),
                $leave->total_days,
                $leave->calendar_days,
                $leave->status,
                $leave->hrApprover->name ?? '-',
                $leave->managerApprover->name ?? '-',
                $leave->creator->name ?? 'Self',
                $leave->created_at->format('Y-m-d H:i'),
            ];
        }
        
        return $this->exportToExcel($headers, $rows, "leave_history_{$employee->user->name}_{$year}.csv");
    }

    // Export Year-by-Year Report
    public function exportYearByYear(Request $request)
    {
        $employeeId = $request->employee_id;
        
        $employee = Employee::with('user', 'department')->find($employeeId);
        
        // Use dynamic calculation engine
        $analysis = $this->leaveCalculationService->analyze($employee);
        $yearlyData = $analysis['years'] ?? [];
        
        $yearByYearData = [];
        foreach ($yearlyData as $y => $data) {
            $yearByYearData[] = [
                'year' => $y,
                'months_from_hiring' => $data['months_from_hiring'] ?? 0,
                'entitled_days' => $data['days_entitled'] ?? 0,
                'used_days' => $data['days_used'] ?? 0,
                'exit_reentry_count' => $data['exit_reentry_count'] ?? 0,
                'ticket_count' => $data['ticket_count'] ?? 0,
                'balance' => $data['balance'] ?? 0,
            ];
        }
        
        // Export as CSV
        $headers = ['Year', 'Months from Hiring', 'Entitled', 'Used', 'Exit/Re-entry', 'Tickets', 'Balance'];
        $rows = [];
        
        foreach ($yearByYearData as $data) {
            $rows[] = [
                $data['year'],
                $data['months_from_hiring'],
                $data['entitled_days'],
                $data['used_days'],
                $data['exit_reentry_count'],
                $data['ticket_count'],
                $data['balance'],
            ];
        }
        
        return $this->exportToExcel($headers, $rows, "leave_history_{$employee->user->name}_{$currentYear}.csv");
    }

    private function exportToExcel($headers, $rows, $filename)
    {
        $csv = implode(',', $headers) . "\n";
        foreach ($rows as $row) {
            $csv .= implode(',', $row) . "\n";
        }
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename={$filename}",
        ];
        
        return response($csv, 200, $headers);
    }
}