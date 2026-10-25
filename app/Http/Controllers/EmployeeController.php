<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\User;
use App\Models\Department;
use App\Models\Role;
use App\Models\ActivityLog;
use App\Services\LeaveCalculationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $query = Employee::with(['user', 'department']);
        
        // Filter by department
        if ($request->filled('department_id') && is_numeric($request->department_id)) {
            $query->where('department_id', (int) $request->department_id);
        }
        
        // Filter by status (whitelisted values only)
        if ($request->filled('status') && in_array($request->status, ['active', 'resigned', 'terminated', 'on_leave'], true)) {
            $query->where('employment_status', $request->status);
        }

        // Search by name, email or employee number. Grouped in a single
        // where() so it never escapes the department/status filters above.
        if ($request->filled('search')) {
            $search = $request->search;
            // L10: escape LIKE wildcards so a search term cannot act as a pattern.
            $search = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $search);
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', function ($q2) use ($search) {
                    $q2->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                })->orWhere('employee_number', 'like', "%{$search}%");
            });
        }

        // Joined-date range
        if ($request->filled('joined_from') && strtotime($request->joined_from)) {
            $query->whereDate('joining_date', '>=', date('Y-m-d', strtotime($request->joined_from)));
        }
        if ($request->filled('joined_to') && strtotime($request->joined_to)) {
            $query->whereDate('joining_date', '<=', date('Y-m-d', strtotime($request->joined_to)));
        }

        $employees = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();
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
            'password' => 'required|string|min:8|confirmed',
            'employee_number' => 'required|unique:employees,employee_number',
            'joining_date' => 'required|date',
            'department_id' => 'nullable|exists:departments,id',
            'role_id' => 'required|exists:roles,id',
            'annual_leave_entitlement' => 'nullable|numeric|min:0|max:60',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // H1: an hr_manager must never create a super_admin account through the
        // HR-gated employee route (users.* routes are super_admin-only for a reason).
        $superAdminRoleId = Role::where('name', 'super_admin')->value('id');
        if ((int) $request->role_id === (int) $superAdminRoleId && !auth()->user()->isSuperAdmin()) {
            return back()->with('error', 'Only a super_admin can create a super_admin account.')->withInput();
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

        // Handle avatar upload
        if ($request->hasFile('avatar')) {
            $avatarPath = $request->file('avatar')->store('avatars', 'public');
            $employee->update(['avatar' => $avatarPath]);
        }

        // Log activity
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'create',
            'model_type' => Employee::class,
            'model_id' => $employee->id,
            'new_values' => $request->except(['password', 'password_confirmation']),
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
        
        // Visible-balance policy: history rows use VISIBLE semantics —
        // the current year's entitlement is hidden (null) and counts as 0
        // until 1 January of the next year.
        $visibleBalance = $leaveCalculationService->visibleBalanceData($employee);
        $visibleRows = collect($visibleBalance['rows'])->keyBy('year');

        $vacationHistory = [];
        foreach ($yearlyData as $year => $data) {
            $vRow = $visibleRows[$year] ?? null;
            $entitled = $vRow ? $vRow['entitled'] : ($data['days_entitled'] ?? 0);
            $used = $data['days_used'] ?? 0;
            $running = $vRow ? $vRow['remaining'] : ($data['balance'] ?? 0);
            $vacationHistory[] = [
                'year' => $year,
                'entitled_days' => $entitled,
                'used_days' => $used,
                'carry_forward' => ($running - ($entitled ?? 0) + $used),
                'balance' => $running,
                'ticket_count' => $data['ticket_count'] ?? 0,
                'exit_reentry_count' => $data['exit_reentry_count'] ?? 0,
            ];
        }

        $yearRequests = $this->yearRequests($employee);
        return view('employees.show', compact('employee', 'vacationHistory', 'visibleBalance', 'yearRequests'));
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
            // L1: min:8 + confirmation, same policy as creation (no stricter rule invented).
            'password' => 'nullable|string|min:8|confirmed',
            'annual_leave_entitlement' => 'nullable|numeric|min:0|max:60',
            'employment_status' => 'required|in:active,resigned,terminated,on_leave',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // H1 (user-management part): through this HR-gated path, only a super_admin
        // may touch another user's role_id or password, and nobody may change
        // their own role. A non-super_admin submitting the SAME role_id as the
        // user already has (the normal edit-form case) is NOT an error — only an
        // actual role change or a password reset is blocked.
        $actor = auth()->user();
        $targetUser = $employee->user;
        $roleIsChanging = (int) $request->role_id !== (int) $targetUser->role_id;
        if ($targetUser->id === $actor->id && $roleIsChanging) {
            return back()->with('error', 'You cannot change your own role.')->withInput();
        }
        if (!$actor->isSuperAdmin() && ($roleIsChanging || $request->filled('password'))) {
            return back()->with('error', 'Only a super_admin can change a user\'s role or reset a user\'s password.')->withInput();
        }
        // H4: never demote the last remaining active super_admin via this path.
        if ($targetUser->isSuperAdmin() && $roleIsChanging) {
            $activeSuperAdmins = User::whereHas('role', function ($query) {
                $query->where('name', 'super_admin');
            })->where('is_active', true)->count();
            if ($activeSuperAdmins <= 1) {
                return back()->with('error', 'Cannot demote the last remaining super admin.')->withInput();
            }
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

        // Handle avatar upload
        if ($request->hasFile('avatar')) {
            // Delete old avatar if exists
            if ($employee->avatar) {
                Storage::disk('public')->delete($employee->avatar);
            }
            $avatarPath = $request->file('avatar')->store('avatars', 'public');
            $employee->update(['avatar' => $avatarPath]);
        }

        // Log activity
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'update',
            'model_type' => Employee::class,
            'model_id' => $employee->id,
            'old_values' => $oldValues,
            'new_values' => $request->except(['password', 'password_confirmation']),
            'ip_address' => $request->ip(),
        ]);

        return redirect()->route('employees.index')->with('success', 'Employee updated successfully');
    }

    public function destroy(Request $request, Employee $employee)
    {
        // H4: destroy() soft-deletes the employee AND deactivates the linked
        // user, so it must not remove the last remaining active super_admin.
        // Guard placed at the very start (before the soft delete).
        if ($employee->user->isSuperAdmin()) {
            $activeSuperAdmins = User::whereHas('role', function ($query) {
                $query->where('name', 'super_admin');
            })->where('is_active', true)->count();
            if ($activeSuperAdmins <= 1) {
                return back()->with('error', 'Cannot deactivate the last remaining super admin.');
            }
        }
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

        // Filter by status (whitelisted values only)
        if ($request->filled('status') && in_array($request->status, ['active', 'resigned', 'terminated', 'on_leave'], true)) {
            $query->where('employment_status', $request->status);
        }

        // Search by name, email or employee number
        if ($request->filled('search')) {
            $search = $request->search;
            // L10: escape LIKE wildcards so a search term cannot act as a pattern.
            $search = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $search);
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', function ($q2) use ($search) {
                    $q2->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                })->orWhere('employee_number', 'like', "%{$search}%");
            });
        }

        // Joined-date range
        if ($request->filled('joined_from') && strtotime($request->joined_from)) {
            $query->whereDate('joining_date', '>=', date('Y-m-d', strtotime($request->joined_from)));
        }
        if ($request->filled('joined_to') && strtotime($request->joined_to)) {
            $query->whereDate('joining_date', '<=', date('Y-m-d', strtotime($request->joined_to)));
        }

        $employees = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();
        
        return view('employees.team-index', compact('employees', 'managedDepartment'));
    }

    public function teamShow(Employee $employee)
    {
        $user = auth()->user();

        // super_admin/hr_manager are routed here too (routes/web.php team
        // group) but have no managedDepartment — they may view any employee.
        // Managers/dm still pass through the department check below.
        if (!($user->isSuperAdmin() || $user->isHrManager())) {
            $managedDepartment = $user->managedDepartment;

            // Only allow viewing employees in managed department
            if (!$managedDepartment || $employee->department_id != $managedDepartment->id) {
                abort(403, 'Unauthorized access to this employee.');
            }
        }

        $employee->load(['user', 'department', 'fieldValues.field', 'leaveRequests.leaveType']);
        
        // Build vacation history from hiring year to current year using dynamic calculation engine
        $leaveCalculationService = app(LeaveCalculationService::class);
        $analysis = $leaveCalculationService->analyze($employee);
        $yearlyData = $analysis['years'] ?? [];
        
        // Visible-balance policy: history rows use VISIBLE semantics —
        // the current year's entitlement is hidden (null) and counts as 0
        // until 1 January of the next year.
        $visibleBalance = $leaveCalculationService->visibleBalanceData($employee);
        $visibleRows = collect($visibleBalance['rows'])->keyBy('year');

        $vacationHistory = [];
        foreach ($yearlyData as $year => $data) {
            $vRow = $visibleRows[$year] ?? null;
            $entitled = $vRow ? $vRow['entitled'] : ($data['days_entitled'] ?? 0);
            $used = $data['days_used'] ?? 0;
            $running = $vRow ? $vRow['remaining'] : ($data['balance'] ?? 0);
            $vacationHistory[] = [
                'year' => $year,
                'entitled_days' => $entitled,
                'used_days' => $used,
                'carry_forward' => ($running - ($entitled ?? 0) + $used),
                'balance' => $running,
                'ticket_count' => $data['ticket_count'] ?? 0,
                'exit_reentry_count' => $data['exit_reentry_count'] ?? 0,
            ];
        }
        
        $yearRequests = $this->yearRequests($employee);
        return view('employees.team-show', compact('employee', 'vacationHistory', 'visibleBalance', 'yearRequests'));
    }


    /**
     * Leave-clarity: approved ANNUAL requests behind each year's Used
     * figure, grouped by start_date year — the same population as
     * LeaveCalculationService::calculateDaysUsed(), so each year's list
     * sums to that year's Used total. Non-annual types, pending /
     * rejected / cancelled rows, and other years never appear here.
     */
    private function yearRequests(Employee $employee): array
    {
        $service = app(LeaveCalculationService::class);
        return $service->approvedAnnualRequestsByYear($employee);
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
        
        // Visible-balance policy: history rows use VISIBLE semantics —
        // the current year's entitlement is hidden (null) and counts as 0
        // until 1 January of the next year.
        $visibleBalance = $leaveCalculationService->visibleBalanceData($employee);
        $visibleRows = collect($visibleBalance['rows'])->keyBy('year');

        $vacationHistory = [];
        foreach ($yearlyData as $year => $data) {
            $vRow = $visibleRows[$year] ?? null;
            $entitled = $vRow ? $vRow['entitled'] : ($data['days_entitled'] ?? 0);
            $used = $data['days_used'] ?? 0;
            $running = $vRow ? $vRow['remaining'] : ($data['balance'] ?? 0);
            $vacationHistory[] = [
                'year' => $year,
                'entitled_days' => $entitled,
                'used_days' => $used,
                'carry_forward' => ($running - ($entitled ?? 0) + $used),
                'balance' => $running,
                'ticket_count' => $data['ticket_count'] ?? 0,
                'exit_reentry_count' => $data['exit_reentry_count'] ?? 0,
            ];
        }
        
        $yearRequests = $this->yearRequests($employee);
        return view('employees.my-profile', compact('employee', 'fields', 'vacationHistory', 'visibleBalance', 'yearRequests'));
    }
    
    // Employee Documents - HR/Super Admin view to access employee custom fields
    public function documents(Request $request)
    {
        $query = Employee::with(['user', 'department']);
        
        // Filter by department
        if ($request->filled('department_id') && is_numeric($request->department_id)) {
            $query->where('department_id', (int) $request->department_id);
        }
        
        // Filter by status (whitelisted values only)
        if ($request->filled('status') && in_array($request->status, ['active', 'resigned', 'terminated', 'on_leave'], true)) {
            $query->where('employment_status', $request->status);
        }

        // Search by name, email or employee number. Grouped in a single
        // where() so it never escapes the department/status filters above.
        if ($request->filled('search')) {
            $search = $request->search;
            // L10: escape LIKE wildcards so a search term cannot act as a pattern.
            $search = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $search);
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', function ($q2) use ($search) {
                    $q2->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                })->orWhere('employee_number', 'like', "%{$search}%");
            });
        }

        // Joined-date range
        if ($request->filled('joined_from') && strtotime($request->joined_from)) {
            $query->whereDate('joining_date', '>=', date('Y-m-d', strtotime($request->joined_from)));
        }
        if ($request->filled('joined_to') && strtotime($request->joined_to)) {
            $query->whereDate('joining_date', '<=', date('Y-m-d', strtotime($request->joined_to)));
        }

        $employees = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();
        $departments = Department::where('is_active', true)->get();
        
        return view('employees.documents', compact('employees', 'departments'));
    }
    
    // Export employees to CSV (chunked; formula-injection sanitized — WF-15/M3)
    public function export()
    {
        $filename = 'employees_' . date('Y-m-d') . '.csv';
        $headers = ['Employee Number', 'First Name', 'Last Name', 'Email', 'Department', 'Joining Date', 'Employment Status', 'Annual Leave Entitlement', 'Contract Start Date', 'Contract End Date'];

        return response()->streamDownload(function () use ($headers) {
            $out = fopen('php://output', 'w');
            fputcsv($out, $headers);
            // Chunked so a large roster is never loaded into memory at once (WF-16).
            Employee::with(['user', 'department'])->orderBy('id')->chunk(self::IMPORT_EXPORT_CHUNK_SIZE, function ($employees) use ($out) {
                foreach ($employees as $emp) {
                    $nameParts = explode(' ', $emp->user->name ?? '', 2);
                    $row = [
                        $emp->employee_number,
                        $nameParts[0] ?? '',
                        $nameParts[1] ?? '',
                        $emp->user->email ?? '',
                        $emp->department->name ?? '',
                        $emp->joining_date ? $emp->joining_date->format('Y-m-d') : '',
                        $emp->employment_status,
                        $emp->annual_leave_entitlement ?? '',
                        $emp->contract_start_date ? $emp->contract_start_date->format('Y-m-d') : '',
                        $emp->contract_end_date ? $emp->contract_end_date->format('Y-m-d') : '',
                    ];
                    fputcsv($out, array_map([$this, 'sanitizeExportCell'], $row));
                }
            });
            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
    
    // Download import template (properly CSV-quoted via fputcsv — WF-15/M3)
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
            ['EMP001', 'John', 'Doe', 'john.doe@company.com', 'IT', '2024-01-15', 'active', '21', '2024-01-01', '', 'employee'],
            ['EMP002', 'Jane', 'Smith', 'jane.smith@company.com', 'HR', '2023-06-01', 'active', '21', '2023-06-01', '2025-05-31', 'employee'],
        ];

        $out = fopen('php://temp', 'r+');
        fputcsv($out, $headers);
        foreach ($rows as $row) {
            fputcsv($out, array_map([$this, 'sanitizeExportCell'], $row));
        }
        rewind($out);
        $csvData = stream_get_contents($out);
        fclose($out);

        return response($csvData, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="employee_template.csv"',
        ]);
    }
    
    // Import employees from CSV.
    //
    // Security / integrity contract (WF-03, WF-11, H8, L6/WF-09):
    // - Role whitelist: an hr_manager importer may only import employee/manager/dm.
    //   super_admin and hr_manager rows from an hr_manager are REJECTED per row
    //   (never silently downgraded). Only a super_admin importer may import
    //   hr_manager/super_admin rows. Unknown roles are rejected per row.
    // - No known default password: every imported user gets a cryptographically
    //   random 16-char one-time password (Str::random). The passwords are flashed
    //   ONCE in the session under 'import_passwords' for the importing admin to
    //   copy (see PATCH-NOTES.md for the one-time display snippet) and are
    //   NEVER written to logs or activity logs.
    // - Forced reset: imported users are created with must_change_password=true.
    //   *** REQUIRES migration-add-must-change-password.sql to have run first,
    //   *** plus the User-model $fillable/casts patch and the AuthController
    //   *** login/change-password enforcement in PATCH-NOTES.md. Without the
    //   *** migration the User::create below fails closed (row error, nothing
    //   *** created) instead of silently skipping the forced reset.
    // - No orphans (WF-11): the ENTIRE row is validated (email, role, department,
    //   employee number, dates, status, entitlement) BEFORE any write, and the
    //   User + Employee pair is created inside one DB transaction per row, so a
    //   failed row leaves zero records behind.
    // - Robustness (L6/WF-09): column-count mismatches and malformed rows produce
    //   per-row errors, never a 500; exception text is logged server-side only
    //   and users see a generic message.
    // - Limits: 2 MB file (validation max:2048) and 1,000 data rows max.
    // - Audit: every run (including early rejections) writes one ActivityLog row
    //   with file name and counts; role rejections are listed explicitly.
    // Atomicity choice: PER-ROW transactions (not whole-file), because the
    // import's established behaviour is partial success (skip existing users,
    // per-row errors, continue with the rest). The historical import in
    // LeaveBalanceController uses whole-file atomicity instead — see that file.
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:2048',
        ]);

        $file = $request->file('file');
        $fileName = $file->getClientOriginalName();
        $extension = strtolower($file->getClientOriginalExtension());

        if (in_array($extension, ['xlsx', 'xls'], true)) {
            $this->logImportAudit($request, $fileName, ['imported' => 0, 'skipped' => 0, 'failed' => 0, 'errors' => ['Excel import rejected: xlsx/xls not supported, CSV required.']]);
            return back()->with('error', 'Excel import requires PhpSpreadsheet. Please convert to CSV or install the package.');
        }

        $handle = fopen($file->getRealPath(), 'r');
        if ($handle === false) {
            $this->logImportAudit($request, $fileName, ['imported' => 0, 'skipped' => 0, 'failed' => 0, 'errors' => ['Could not read the uploaded file.']]);
            return back()->with('error', 'Could not read the uploaded file. Please try again.');
        }

        $headerRow = fgetcsv($handle);
        if ($headerRow === false) {
            fclose($handle);
            $this->logImportAudit($request, $fileName, ['imported' => 0, 'skipped' => 0, 'failed' => 0, 'errors' => ['Empty file: no header row.']]);
            return back()->with('error', 'The uploaded file is empty.');
        }
        $header = array_map(function ($h) {
            return trim((string) $h);
        }, $headerRow);
        // Strip a UTF-8 BOM from the first header cell so 'Email' still matches.
        if (isset($header[0])) {
            $header[0] = ltrim($header[0], "\xEF\xBB\xBF");
        }
        if (!in_array('Email', $header, true)) {
            fclose($handle);
            $this->logImportAudit($request, $fileName, ['imported' => 0, 'skipped' => 0, 'failed' => 0, 'errors' => ['Missing required Email header column.']]);
            return back()->with('error', 'Import failed: the CSV header must include an Email column.');
        }

        $importer = auth()->user();
        $allowedRoles = $this->allowedImportRoles($importer);

        $imported = 0;
        $skipped = 0;
        $failed = 0;
        $errors = [];
        $roleRejections = [];
        $passwords = [];
        $seenEmails = [];
        $seenEmployeeNumbers = [];
        $dataRowNumber = 0;

        try {
            while (($row = fgetcsv($handle)) !== false) {
                // Skip blank lines (e.g. a trailing newline) without counting them.
                $nonEmpty = array_filter($row, function ($v) {
                    return trim((string) $v) !== '';
                });
                if (count($nonEmpty) === 0) {
                    continue;
                }
                $dataRowNumber++;

                if ($dataRowNumber > self::IMPORT_MAX_ROWS) {
                    $errors[] = "Row {$dataRowNumber}: Import row limit (" . self::IMPORT_MAX_ROWS . ") exceeded; remaining rows were not processed.";
                    $failed++;
                    break;
                }

                // L6: column-count mismatch is a per-row error, never a 500
                // (array_combine() would throw ValueError on mismatched counts).
                if (count($row) !== count($header)) {
                    $errors[] = "Row {$dataRowNumber}: Column count does not match the header; row skipped.";
                    $failed++;
                    continue;
                }

                try {
                    $data = array_combine($header, $row);

                    // ---- Validate the ENTIRE row before creating anything (WF-11) ----
                    $email = trim($data['Email'] ?? '');
                    if ($email === '') {
                        $errors[] = "Row {$dataRowNumber}: Empty email; row skipped.";
                        $failed++;
                        continue;
                    }
                    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        $errors[] = "Row {$dataRowNumber}: Invalid email address; row skipped.";
                        $failed++;
                        continue;
                    }
                    $emailKey = strtolower($email);
                    if (isset($seenEmails[$emailKey]) || User::where('email', $email)->exists()) {
                        $skipped++;
                        continue;
                    }

                    // ---- Role whitelist (WF-03/H8): reject, never downgrade ----
                    $requestedRole = strtolower(trim($data['Role'] ?? ''));
                    if ($requestedRole === '') {
                        $requestedRole = 'employee';
                    }
                    if (!in_array($requestedRole, $allowedRoles, true)) {
                        $errors[] = "Row {$dataRowNumber}: Role '{$requestedRole}' may not be assigned by your account via import; row skipped.";
                        $roleRejections[] = "Row {$dataRowNumber}: requested role '{$requestedRole}'";
                        $failed++;
                        continue;
                    }
                    $role = Role::where('name', $requestedRole)->first();
                    if (!$role) {
                        $errors[] = "Row {$dataRowNumber}: Role '{$requestedRole}' not found; row skipped.";
                        $failed++;
                        continue;
                    }

                    $fullName = trim(trim($data['First Name'] ?? '') . ' ' . trim($data['Last Name'] ?? ''));
                    if ($fullName === '') {
                        $errors[] = "Row {$dataRowNumber}: Missing employee name; row skipped.";
                        $failed++;
                        continue;
                    }

                    $employeeNumber = trim($data['Employee Number'] ?? '');
                    if ($employeeNumber === '') {
                        $employeeNumber = 'EMP' . (time() + $dataRowNumber);
                    }
                    if (isset($seenEmployeeNumbers[$employeeNumber]) || Employee::where('employee_number', $employeeNumber)->exists()) {
                        $errors[] = "Row {$dataRowNumber}: Employee number '{$employeeNumber}' already exists; row skipped.";
                        $failed++;
                        continue;
                    }

                    $department = null;
                    $departmentName = trim($data['Department'] ?? '');
                    if ($departmentName !== '') {
                        $department = Department::whereRaw('LOWER(name) = ?', [strtolower($departmentName)])->first();
                        if (!$department) {
                            $errors[] = "Row {$dataRowNumber}: Department '{$departmentName}' not found; row skipped.";
                            $failed++;
                            continue;
                        }
                    }

                    $joiningDate = null;
                    $joiningRaw = trim($data['Joining Date'] ?? '');
                    if ($joiningRaw !== '') {
                        try {
                            $joiningDate = Carbon::parse($joiningRaw);
                        } catch (\Throwable $e) {
                            $errors[] = "Row {$dataRowNumber}: Invalid joining date; row skipped.";
                            $failed++;
                            continue;
                        }
                    } else {
                        $joiningDate = now();
                    }

                    $employmentStatus = trim($data['Employment Status'] ?? '');
                    if ($employmentStatus === '') {
                        $employmentStatus = 'active';
                    }
                    if (!in_array($employmentStatus, ['active', 'resigned', 'terminated', 'on_leave'], true)) {
                        $errors[] = "Row {$dataRowNumber}: Invalid employment status; row skipped.";
                        $failed++;
                        continue;
                    }

                    $entitlementRaw = trim($data['Annual Leave Entitlement'] ?? '');
                    if ($entitlementRaw === '') {
                        $entitlement = 21;
                    } elseif (!is_numeric($entitlementRaw) || (float) $entitlementRaw < 0) {
                        $errors[] = "Row {$dataRowNumber}: Invalid annual leave entitlement; row skipped.";
                        $failed++;
                        continue;
                    } else {
                        $entitlement = $entitlementRaw;
                    }

                    $contractStart = null;
                    $contractStartRaw = trim($data['Contract Start Date'] ?? '');
                    if ($contractStartRaw !== '') {
                        try {
                            $contractStart = Carbon::parse($contractStartRaw);
                        } catch (\Throwable $e) {
                            $errors[] = "Row {$dataRowNumber}: Invalid contract start date; row skipped.";
                            $failed++;
                            continue;
                        }
                    }
                    $contractEnd = null;
                    $contractEndRaw = trim($data['Contract End Date'] ?? '');
                    if ($contractEndRaw !== '') {
                        try {
                            $contractEnd = Carbon::parse($contractEndRaw);
                        } catch (\Throwable $e) {
                            $errors[] = "Row {$dataRowNumber}: Invalid contract end date; row skipped.";
                            $failed++;
                            continue;
                        }
                    }
                    if ($contractStart && $contractEnd && $contractEnd->lt($contractStart)) {
                        $errors[] = "Row {$dataRowNumber}: Contract end date is before the contract start date; row skipped.";
                        $failed++;
                        continue;
                    }

                    // ---- All validations passed: create User + Employee atomically ----
                    // H8: cryptographically random one-time password, never a known default password.
                    $oneTimePassword = Str::random(16);
                    DB::transaction(function () use ($fullName, $email, $oneTimePassword, $role, $employeeNumber, $department, $joiningDate, $employmentStatus, $entitlement, $contractStart, $contractEnd) {
                        $user = User::create([
                            'name' => $fullName,
                            'email' => $email,
                            'password' => Hash::make($oneTimePassword),
                            'role_id' => $role->id,
                            'is_active' => true,
                            // REQUIRES migration-add-must-change-password.sql + the
                            // User-model patch in PATCH-NOTES.md; fails closed if absent.
                            'must_change_password' => true,
                        ]);
                        Employee::create([
                            'user_id' => $user->id,
                            'employee_number' => $employeeNumber,
                            'department_id' => $department ? $department->id : null,
                            'joining_date' => $joiningDate,
                            'employment_status' => $employmentStatus,
                            'annual_leave_entitlement' => $entitlement,
                            'contract_start_date' => $contractStart,
                            'contract_end_date' => $contractEnd,
                        ]);
                    });

                    $seenEmails[$emailKey] = true;
                    $seenEmployeeNumbers[$employeeNumber] = true;
                    // Passwords are kept ONLY in the one-time session flash below —
                    // never in logs, activity logs, or the summary message.
                    $passwords[] = ['email' => $email, 'password' => $oneTimePassword];
                    $imported++;
                } catch (\Throwable $e) {
                    // L6/WF-09: log the exception server-side; show a generic message.
                    Log::warning('Employee import row failed', ['row' => $dataRowNumber, 'exception' => get_class($e)]);
                    $errors[] = "Row {$dataRowNumber}: Could not import this row. Please check its values and try again.";
                    $failed++;
                    continue;
                }
            }
        } catch (\Throwable $e) {
            Log::error('Employee import aborted', ['exception' => get_class($e)]);
            fclose($handle);
            return back()->with('error', 'Import failed unexpectedly. No further rows were processed; please try again.');
        }

        fclose($handle);

        $this->logImportAudit($request, $fileName, [
            'imported' => $imported,
            'skipped' => $skipped,
            'failed' => $failed,
            'role_rejections' => $roleRejections,
            'errors' => array_slice($errors, 0, 50),
        ]);

        $summary = "Import completed. {$imported} imported, {$skipped} skipped (already exist), {$failed} failed.";
        if (count($errors) > 0) {
            $shownErrors = array_slice($errors, 0, 20);
            $summary .= ' Errors: ' . implode('; ', $shownErrors);
            if (count($errors) > 20) {
                $summary .= '; and ' . (count($errors) - 20) . ' more errors (see activity log).';
            }
        }

        $response = back()->with(count($errors) > 0 ? 'error' : 'success', $summary);
        if (count($passwords) > 0) {
            // One-time flash: rendered once by the import view (PATCH-NOTES.md).
            // The admin must copy these now; they cannot be recovered afterwards.
            $response->with('import_passwords', $passwords);
        }

        return $response;
    }
    
    // Export employees to Excel (chunked; formula-injection sanitized — WF-15/M3, WF-16).
    // The PhpSpreadsheet array-coordinate fix from 2026-10-22 is preserved:
    // getCell([col, row]) / setCellValue([col, row]) throughout — no
    // removed-API column/row calls anywhere in this file.
    public function exportExcel()
    {
        $headers = ['Employee Number', 'Full Name', 'Email', 'Department', 'Joining Date', 'Employment Status', 'Annual Leave Entitlement', 'Contract Start', 'Contract End'];

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Employees');

        // Header row with Alma burgundy styling
        foreach ($headers as $col => $header) {
            $cell = $sheet->getCell([$col + 1, 1]);
            $cell->setValue($header);
            $cell->getStyle()->getFont()->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFF'));
            $cell->getStyle()->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setRGB('8B251E');
        }

        // Chunked (WF-16): the whole roster is never loaded at once.
        $row = 2;
        Employee::with(['user', 'department'])->orderBy('id')->chunk(self::IMPORT_EXPORT_CHUNK_SIZE, function ($employees) use ($sheet, &$row) {
            foreach ($employees as $emp) {
                // Explicit string cells + sanitizer: a value such as '=2+5' can
                // never become a live spreadsheet formula (WF-15/M3).
                $values = [
                    (string) ($emp->employee_number ?? ''),
                    (string) ($emp->user->name ?? ''),
                    (string) ($emp->user->email ?? ''),
                    (string) ($emp->department->name ?? ''),
                    $emp->joining_date ? $emp->joining_date->format('Y-m-d') : '',
                    (string) ($emp->employment_status ?? ''),
                    (string) ($emp->annual_leave_entitlement ?? ''),
                    $emp->contract_start_date ? $emp->contract_start_date->format('Y-m-d') : '',
                    $emp->contract_end_date ? $emp->contract_end_date->format('Y-m-d') : '',
                ];
                foreach ($values as $col => $value) {
                    $sheet->setCellValueExplicit([$col + 1, $row], $this->sanitizeExportCell($value), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                }
                $row++;
            }
        });

        foreach (range('A', 'I') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = 'employees_' . date('Y-m-d') . '.xlsx';
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    // ---- Import / export helpers (imports + exports only) ----

    private const IMPORT_MAX_ROWS = 1000;
    private const IMPORT_EXPORT_CHUNK_SIZE = 500;

    /**
     * Roles the importing user may assign via CSV import (WF-03/H8).
     * hr_manager: employee, manager, dm only. super_admin: additionally
     * hr_manager and super_admin. Anything else is rejected per row.
     */
    private function allowedImportRoles($user): array
    {
        if ($user && method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) {
            return ['employee', 'manager', 'dm', 'hr_manager', 'super_admin'];
        }

        return ['employee', 'manager', 'dm'];
    }

    /**
     * OWASP CSV/Excel formula-injection neutralization (WF-15/M3): values whose
     * first non-space character is =, +, -, @, tab or carriage-return get a
     * leading single quote. Non-string values pass through untouched.
     */
    private function sanitizeExportCell($value)
    {
        if (!is_string($value)) {
            return $value;
        }
        $trimmed = ltrim($value, " \t\r\n");
        if ($trimmed !== '' && in_array($trimmed[0], ['=', '+', '-', '@', "\t", "\r"], true)) {
            return "'" . $value;
        }

        return $value;
    }

    /**
     * One ActivityLog row per import run. Never receives passwords or raw
     * exception text — only counts, the file name, role rejections, and
     * user-safe error strings. A logging failure must never fail the import.
     */
    private function logImportAudit(Request $request, string $fileName, array $summary): void
    {
        try {
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'import',
                'model_type' => Employee::class,
                'model_id' => null,
                'new_values' => array_merge(['file_name' => $fileName], $summary),
                'ip_address' => $request->ip(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('Employee import audit log failed', ['exception' => get_class($e)]);
        }
    }

}
