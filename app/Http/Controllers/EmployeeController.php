<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\User;
use App\Models\Department;
use App\Models\Role;
use App\Models\ActivityLog;
use App\Services\LeaveCalculationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $query = Employee::with(['user', 'department']);
        
        // Filter by department
        if ($request->has('department_id') && $request->department_id) {
            $query->where('department_id', $request->department_id);
        }
        
        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('employment_status', $request->status);
        }
        
        // Search by name or employee number
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            })->orWhere('employee_number', 'like', "%{$search}%");
        }
        
        $employees = $query->orderBy('created_at', 'desc')->paginate(20);
        $departments = Department::where('is_active', true)->get();
        
        return view('employees.index', compact('employees', 'departments'));
    }

    public function create()
    {
        $departments = Department::where('is_active', true)->get();
        $roles = Role::whereIn('name', ['employee', 'manager', 'dm', 'hr_manager'])->get();
        
        return view('employees.create', compact('departments', 'roles'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8',
            'employee_number' => 'required|unique:employees,employee_number',
            'joining_date' => 'required|date',
            'department_id' => 'nullable|exists:departments,id',
            'role_id' => 'required|exists:roles,id',
            'annual_leave_entitlement' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Create user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role_id' => $request->role_id,
            'is_active' => true,
        ]);

        // Create employee profile
        $employee = Employee::create([
            'user_id' => $user->id,
            'employee_number' => $request->employee_number,
            'joining_date' => $request->joining_date,
            'contract_start_date' => $request->contract_start_date,
            'contract_end_date' => $request->contract_end_date,
            'department_id' => $request->department_id,
            'employment_status' => 'active',
            'annual_leave_entitlement' => $request->annual_leave_entitlement ?? 21,
        ]);

        // Log activity
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'create',
            'model_type' => Employee::class,
            'model_id' => $employee->id,
            'new_values' => $request->all(),
            'ip_address' => $request->ip(),
        ]);

        return redirect()->route('employees.index')->with('success', 'Employee created successfully');
    }

    public function show(Employee $employee)
    {
        $employee->load(['user', 'department', 'fieldValues.field', 'leaveRequests.leaveType']);
        
        // Build vacation history from hiring year to current year using dynamic calculation engine
        $leaveCalculationService = app(LeaveCalculationService::class);
        $analysis = $leaveCalculationService->analyze($employee);
        $yearlyData = $analysis['years'] ?? [];
        
        $vacationHistory = [];
        foreach ($yearlyData as $year => $data) {
            $vacationHistory[] = [
                'year' => $year,
                'entitled_days' => $data['days_entitled'] ?? 0,
                'used_days' => $data['days_used'] ?? 0,
                'carry_forward' => ($data['balance'] - ($data['days_entitled'] ?? 0) + ($data['days_used'] ?? 0)),
                'balance' => $data['balance'] ?? 0,
                'ticket_count' => $data['ticket_count'] ?? 0,
                'exit_reentry_count' => $data['exit_reentry_count'] ?? 0,
            ];
        }
        
        return view('employees.show', compact('employee', 'vacationHistory'));
    }

    public function edit(Employee $employee)
    {
        $employee->load('user');
        $departments = Department::where('is_active', true)->get();
        $roles = Role::whereIn('name', ['employee', 'manager', 'dm', 'hr_manager'])->get();
        
        return view('employees.edit', compact('employee', 'departments', 'roles'));
    }

    public function update(Request $request, Employee $employee)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $employee->user_id,
            'employee_number' => 'required|unique:employees,employee_number,' . $employee->id,
            'joining_date' => 'required|date',
            'department_id' => 'nullable|exists:departments,id',
            'role_id' => 'required|exists:roles,id',
            'annual_leave_entitlement' => 'nullable|numeric|min:0',
            'employment_status' => 'required|in:active,resigned,terminated,on_leave',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $oldValues = $employee->toArray();

        // Update user
        $employee->user->update([
            'name' => $request->name,
            'email' => $request->email,
            'role_id' => $request->role_id,
        ]);

        // Update password if provided
        if ($request->password) {
            $employee->user->update([
                'password' => Hash::make($request->password),
            ]);
        }

        // Update employee
        $employee->update([
            'employee_number' => $request->employee_number,
            'joining_date' => $request->joining_date,
            'contract_start_date' => $request->contract_start_date,
            'contract_end_date' => $request->contract_end_date,
            'department_id' => $request->department_id,
            'employment_status' => $request->employment_status,
            'annual_leave_entitlement' => $request->annual_leave_entitlement ?? 21,
        ]);

        // Log activity
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'update',
            'model_type' => Employee::class,
            'model_id' => $employee->id,
            'old_values' => $oldValues,
            'new_values' => $request->all(),
            'ip_address' => $request->ip(),
        ]);

        return redirect()->route('employees.index')->with('success', 'Employee updated successfully');
    }

    public function destroy(Request $request, Employee $employee)
    {
        // Soft delete employee
        $employee->delete();

        // Deactivate user
        $employee->user->update(['is_active' => false]);

        // Log activity
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'delete',
            'model_type' => Employee::class,
            'model_id' => $employee->id,
            'ip_address' => $request->ip(),
        ]);

        return redirect()->route('employees.index')->with('success', 'Employee deactivated successfully');
    }

    public function activate(Request $request, Employee $employee)
    {
        $employee->user->update(['is_active' => true]);
        $employee->update(['employment_status' => 'active']);

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'activate',
            'model_type' => Employee::class,
            'model_id' => $employee->id,
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', 'Employee activated successfully');
    }

    // Manager/DM - view employees in their department (read-only)
    public function teamIndex(Request $request)
    {
        $user = auth()->user();
        $managedDepartment = $user->managedDepartment;

        if (!$managedDepartment) {
            return redirect()->route('dashboard')->with('error', 'You are not assigned to manage any department.');
        }

        $query = Employee::with(['user', 'department'])
            ->where('department_id', $managedDepartment->id);
        
        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('employment_status', $request->status);
        }
        
        $employees = $query->orderBy('created_at', 'desc')->paginate(20);
        
        return view('employees.team-index', compact('employees', 'managedDepartment'));
    }

    public function teamShow(Employee $employee)
    {
        $user = auth()->user();
        $managedDepartment = $user->managedDepartment;

        // Only allow viewing employees in managed department
        if (!$managedDepartment || $employee->department_id != $managedDepartment->id) {
            abort(403, 'Unauthorized access to this employee.');
        }

        $employee->load(['user', 'department', 'fieldValues.field', 'leaveRequests.leaveType']);
        
        // Build vacation history from hiring year to current year using dynamic calculation engine
        $leaveCalculationService = app(LeaveCalculationService::class);
        $analysis = $leaveCalculationService->analyze($employee);
        $yearlyData = $analysis['years'] ?? [];
        
        $vacationHistory = [];
        foreach ($yearlyData as $year => $data) {
            $vacationHistory[] = [
                'year' => $year,
                'entitled_days' => $data['days_entitled'] ?? 0,
                'used_days' => $data['days_used'] ?? 0,
                'carry_forward' => ($data['balance'] - ($data['days_entitled'] ?? 0) + ($data['days_used'] ?? 0)),
                'balance' => $data['balance'] ?? 0,
                'ticket_count' => $data['ticket_count'] ?? 0,
                'exit_reentry_count' => $data['exit_reentry_count'] ?? 0,
            ];
        }
        
        return view('employees.team-show', compact('employee', 'vacationHistory'));
    }

    // Employee - view own profile
    public function myProfile()
    {
        $user = auth()->user();
        $employee = $user->employee;

        if (!$employee) {
            return redirect()->route('dashboard')->with('error', 'Employee profile not found.');
        }

        $employee->load(['user', 'department', 'fieldValues.field', 'leaveRequests.leaveType']);
        $fields = \App\Models\EmployeeField::where('is_active', true)
            ->orderBy('field_order')
            ->get()
            ->groupBy('field_section');
        
        // Build vacation history from hiring year to current year using dynamic calculation engine
        $leaveCalculationService = app(LeaveCalculationService::class);
        $analysis = $leaveCalculationService->analyze($employee);
        $yearlyData = $analysis['years'] ?? [];
        
        $vacationHistory = [];
        foreach ($yearlyData as $year => $data) {
            $vacationHistory[] = [
                'year' => $year,
                'entitled_days' => $data['days_entitled'] ?? 0,
                'used_days' => $data['days_used'] ?? 0,
                'carry_forward' => ($data['balance'] - ($data['days_entitled'] ?? 0) + ($data['days_used'] ?? 0)),
                'balance' => $data['balance'] ?? 0,
                'ticket_count' => $data['ticket_count'] ?? 0,
                'exit_reentry_count' => $data['exit_reentry_count'] ?? 0,
            ];
        }
        
        return view('employees.my-profile', compact('employee', 'fields', 'vacationHistory'));
    }
    
    // Employee Documents - HR/Super Admin view to access employee custom fields
    public function documents(Request $request)
    {
        $query = Employee::with(['user', 'department']);
        
        // Filter by department
        if ($request->has('department_id') && $request->department_id) {
            $query->where('department_id', $request->department_id);
        }
        
        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('employment_status', $request->status);
        }
        
        // Search by name or employee number
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            })->orWhere('employee_number', 'like', "%{$search}%");
        }
        
        $employees = $query->orderBy('created_at', 'desc')->paginate(20);
        $departments = Department::where('is_active', true)->get();
        
        return view('employees.documents', compact('employees', 'departments'));
    }
    
    // Export employees to Excel
    public function export()
    {
        $employees = Employee::with(['user', 'department'])->get();
        
        $data = $employees->map(function($emp) {
            $nameParts = explode(' ', $emp->user->name ?? '', 2);
            return [
                'Employee Number' => $emp->employee_number,
                'First Name' => $nameParts[0] ?? '',
                'Last Name' => $nameParts[1] ?? '',
                'Email' => $emp->user->email ?? '',
                'Department' => $emp->department->name ?? '',
                'Joining Date' => $emp->joining_date ? $emp->joining_date->format('Y-m-d') : '',
                'Employment Status' => $emp->employment_status,
                'Annual Leave Entitlement' => $emp->annual_leave_entitlement ?? '',
                'Contract Start Date' => $emp->contract_start_date ? $emp->contract_start_date->format('Y-m-d') : '',
                'Contract End Date' => $emp->contract_end_date ? $emp->contract_end_date->format('Y-m-d') : '',
            ];
        });
        
        $filename = 'employees_' . date('Y-m-d') . '.csv';
        
        $csvData = "";
        if ($data->count() > 0) {
            $csvData .= implode(',', array_keys($data->first())) . "\n";
            foreach ($data as $row) {
                $csvData .= implode(',', $row) . "\n";
            }
        }
        
        return response($csvData, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
    
    // Download import template
    public function downloadTemplate()
    {
        $headers = [
            'Employee Number',
            'First Name', 
            'Last Name',
            'Email',
            'Department',
            'Joining Date',
            'Employment Status',
            'Annual Leave Entitlement',
            'Contract Start Date',
            'Contract End Date',
            'Role',
        ];
        
        $rows = [
            [
                'EMP001',
                'John',
                'Doe',
                'john.doe@company.com',
                'IT',
                '2024-01-15',
                'active',
                '21',
                '2024-01-01',
                '',
                'employee'
            ],
            [
                'EMP002',
                'Jane',
                'Smith',
                'jane.smith@company.com',
                'HR',
                '2023-06-01',
                'active',
                '21',
                '2023-06-01',
                '2025-05-31',
                'employee'
            ],
        ];
        
        $csvData = implode(',', $headers) . "\n";
        foreach ($rows as $row) {
            $csvData .= implode(',', $row) . "\n";
        }
        
        return response($csvData, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="employee_template.csv"',
        ]);
    }
    
    // Import employees from Excel/CSV
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv',
        ]);
        
        $file = $request->file('file');
        $extension = $file->getClientOriginalExtension();
        
        if (in_array($extension, ['xlsx', 'xls'])) {
            return back()->with('error', 'Excel import requires PhpSpreadsheet. Please convert to CSV or install the package.');
        }
        
        $handle = fopen($file->getRealPath(), 'r');
        $header = fgetcsv($handle);
        $rowCount = 0;
        $skipCount = 0;
        $errors = [];
        
        while (($row = fgetcsv($handle)) !== false) {
            $data = array_combine($header, $row);
            
            $email = trim($data['Email'] ?? '');
            
            // Skip if email is empty or user already exists
            if (empty($email)) {
                $errors[] = "Row " . ($rowCount + 1) . ": Empty email";
                continue;
            }
            
            // Check if user already exists
            if (User::where('email', $email)->exists()) {
                $skipCount++;
                continue;
            }
            
            try {
                $roleName = strtolower(trim($data['Role'] ?? 'employee'));
                $role = Role::where('name', $roleName)->first();
                if (!$role) {
                    $errors[] = "Row " . ($rowCount + 1) . ": Role '{$data['Role']}' not found";
                    continue;
                }

                $user = User::create([
                    'name' => ($data['First Name'] ?? '') . ' ' . ($data['Last Name'] ?? ''),
                    'email' => $email,
                    'password' => Hash::make('password123'),
                    'role_id' => $role->id,
                    'is_active' => true,
                ]);
                
                $departmentName = trim($data['Department'] ?? '');
                if ($departmentName) {
                    $department = Department::whereRaw('LOWER(name) = ?', [strtolower($departmentName)])->first();
                    if (!$department) {
                        $errors[] = "Row " . ($rowCount + 1) . ": Department '{$departmentName}' not found";
                        continue;
                    }
                } else {
                    $department = null;
                }
                
                $employee = Employee::create([
                    'user_id' => $user->id,
                    'employee_number' => $data['Employee Number'] ?? 'EMP' . (time() + $rowCount),
                    'department_id' => $department ? $department->id : null,
                    'joining_date' => isset($data['Joining Date']) ? Carbon::parse($data['Joining Date']) : now(),
                    'employment_status' => $data['Employment Status'] ?? 'active',
                    'annual_leave_entitlement' => $data['Annual Leave Entitlement'] ?? 21,
                    'contract_start_date' => isset($data['Contract Start Date']) ? Carbon::parse($data['Contract Start Date']) : null,
                    'contract_end_date' => isset($data['Contract End Date']) ? Carbon::parse($data['Contract End Date']) : null,
                ]);
                
                $rowCount++;
            } catch (\Exception $e) {
                $errors[] = "Row " . ($rowCount + 1) . ": " . $e->getMessage();
            }
        }
        
        fclose($handle);
        
        if (count($errors) > 0) {
            return back()->with('error', 'Import completed. ' . $rowCount . ' imported, ' . $skipCount . ' skipped (already exists), errors: ' . implode(', ', $errors));
        }
        
        return back()->with('success', "Successfully imported $rowCount employees. ($skipCount were skipped - already exist)");
    }
}