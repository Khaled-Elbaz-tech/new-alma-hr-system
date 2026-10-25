<?php

use Illuminate\Support\Facades\Route;

// Redirect root to login
Route::get('/', function () {
    return redirect()->route('login');
});

// Public routes
Route::get('/login', [App\Http\Controllers\AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [App\Http\Controllers\AuthController::class, 'login'])->middleware('throttle:5,1');
Route::post('/logout', [App\Http\Controllers\AuthController::class, 'logout'])->name('logout');

// Protected routes
Route::middleware(['auth'])->group(function () {
    
    // Dashboard
    Route::get('/dashboard', [App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');
    
    // Employee self-service routes
    Route::get('/my-leaves', [App\Http\Controllers\LeaveController::class, 'myLeaves'])->name('leaves.my');
    Route::post('/my-leaves', [App\Http\Controllers\LeaveController::class, 'storeLeave'])->name('leaves.store');
    Route::get('/apply-leave', function() {
        return redirect()->route('leaves.my', ['open_form' => 1]);
    })->name('leaves.apply');

    // Employee self-cancel (H6/WF-02): auth-only on purpose. The controller
    // is the gate — it aborts 403 unless the leave belongs to the caller's
    // own employee record, and refuses anything not pending. No role group.
    Route::post('/leaves/{leaveRequest}/cancel', [App\Http\Controllers\LeaveController::class, 'cancel'])->name('leaves.cancel');

    // Team leave calendar (all roles; controller scopes by department)
    Route::get('/leaves/calendar', [App\Http\Controllers\LeaveController::class, 'calendar'])->name('leaves.calendar');
    
    // HR Manager routes
    Route::middleware(['role:super_admin,hr_manager'])->group(function () {
        Route::get('/employees/documents', [App\Http\Controllers\EmployeeController::class, 'documents'])->name('employees.documents');
        Route::resource('employees', App\Http\Controllers\EmployeeController::class);
    });
    
    // Import/Export - accessible by hr_manager and super_admin
    Route::middleware(['auth', 'role:super_admin,hr_manager'])->group(function () {
        Route::get('/employees/data/export', [App\Http\Controllers\EmployeeController::class, 'export'])->name('employees.export');
        Route::get('/employees/data/export-excel', [App\Http\Controllers\EmployeeController::class, 'exportExcel'])->name('employees.export-excel');
        Route::post('/employees/data/import', [App\Http\Controllers\EmployeeController::class, 'import'])->name('employees.import');
        Route::get('/employees/data/template', [App\Http\Controllers\EmployeeController::class, 'downloadTemplate'])->name('employees.download-template');
    });
    
    // HR Manager routes continued
    Route::middleware(['role:super_admin,hr_manager'])->group(function () {
        Route::resource('departments', App\Http\Controllers\DepartmentController::class);
        Route::delete('/departments/{department}/force-delete', [App\Http\Controllers\DepartmentController::class, 'forceDelete'])->name('departments.force-delete');
        
        // Leave Management
        Route::get('/leaves', [App\Http\Controllers\LeaveController::class, 'index'])->name('leaves.index');
        Route::delete('/leaves/{leaveRequest}', [App\Http\Controllers\LeaveController::class, 'destroy'])->name('leaves.destroy');
        // Leave-clarity: HR/admin edit of a leave REQUEST (not a balance row).
        Route::get('/leaves/{leaveRequest}/edit', [App\Http\Controllers\LeaveController::class, 'edit'])->name('leaves.edit');
        Route::put('/leaves/{leaveRequest}', [App\Http\Controllers\LeaveController::class, 'update'])->name('leaves.update');
        Route::get('/leaves/create-for-employee', [App\Http\Controllers\LeaveController::class, 'createForEmployee'])->name('leaves.create-for-employee');
        Route::post('/leaves/create-for-employee', [App\Http\Controllers\LeaveController::class, 'storeForEmployee'])->name('leaves.store-for-employee');
        Route::get('/leaves/employee/{employee}', [App\Http\Controllers\LeaveController::class, 'getEmployeeData'])->name('leaves.employee-data');
        Route::post('/leaves/{leaveRequest}/hr-approve', [App\Http\Controllers\LeaveController::class, 'hrApprove'])->name('leaves.hr-approve');
        Route::post('/leaves/historical', [App\Http\Controllers\LeaveController::class, 'storeHistorical'])->name('leaves.historical');
        Route::get('/leaves/historical/create', [App\Http\Controllers\LeaveController::class, 'createHistorical'])->name('leaves.historical.create');
        Route::get('/leaves/historical/template', [App\Http\Controllers\LeaveController::class, 'historicalTemplate'])->name('leaves.historical.template');

        // Leave Balance Management (HR)
        Route::get('/leaves/balances', [App\Http\Controllers\LeaveController::class, 'manageBalances'])->name('leaves.manage-balances');
        Route::get('/leaves/balance/{balance}/edit', [App\Http\Controllers\LeaveController::class, 'editBalance'])->name('leaves.edit-balance');
        Route::put('/leaves/balance/{balance}', [App\Http\Controllers\LeaveController::class, 'updateBalance'])->name('leaves.update-balance');
        Route::post('/leave-balances/import', [App\Http\Controllers\LeaveBalanceController::class, 'importHistorical'])->name('leave-balances.import');

        // Holidays
        Route::resource('holidays', App\Http\Controllers\HolidayController::class);
        Route::post('/weekend-settings', [App\Http\Controllers\HolidayController::class, 'updateWeekendSettings'])->name('weekend-settings.update');
        
        // Reports
        Route::get('/reports/leave-balance', [App\Http\Controllers\ReportsController::class, 'leaveBalance'])->name('reports.leave-balance');
        Route::get('/reports/leave-history', [App\Http\Controllers\ReportsController::class, 'leaveHistory'])->name('reports.leave-history');
        Route::get('/reports/department', [App\Http\Controllers\ReportsController::class, 'department'])->name('reports.department');
        Route::get('/reports/employee', [App\Http\Controllers\ReportsController::class, 'employee'])->name('reports.employee');
        Route::get('/reports/leave-statistics', [App\Http\Controllers\ReportsController::class, 'leaveStatistics'])->name('reports.leave-statistics');
        Route::get('/reports/vacation-history', [App\Http\Controllers\ReportsController::class, 'vacationHistory'])->name('reports.vacation-history');
        
        // Export Reports
        Route::get('/reports/export/leave-balance', [App\Http\Controllers\ReportsController::class, 'exportLeaveBalance'])->name('reports.export.leave-balance');
        Route::get('/reports/export/year-by-year', [App\Http\Controllers\ReportsController::class, 'exportYearByYear'])->name('reports.export.year-by-year');
        Route::get('/reports/export/leave-history', [App\Http\Controllers\ReportsController::class, 'exportLeaveHistory'])->name('reports.export.leave-history');
        Route::get('/reports/export/vacation-history', [App\Http\Controllers\ReportsController::class, 'exportVacationHistory'])->name('reports.export.vacation-history');
    });
    
    // Manager/DM routes - read-only access to their department employees
    Route::middleware(['role:super_admin,hr_manager,manager,dm'])->group(function () {
        // View employees in managed department (read-only)
        Route::get('/team/employees', [App\Http\Controllers\EmployeeController::class, 'teamIndex'])->name('team.employees');
        Route::get('/team/employees/{employee}', [App\Http\Controllers\EmployeeController::class, 'teamShow'])->name('team.employees.show');
        
        // Manager can view all leave requests (for their department)
        Route::get('/manager/leaves', [App\Http\Controllers\LeaveController::class, 'managerIndex'])->name('leaves.manager.index');
        // Manager approval step (H6/WF-01): reachable by manager/dm now.
        // Controller enforces department scope for manager/dm actors
        // (M1) and the hr_approved status guard; HR/super_admin exempt.
        Route::post('/leaves/{leaveRequest}/manager-approve', [App\Http\Controllers\LeaveController::class, 'managerApprove'])->name('leaves.manager-approve');
        // Rejection (H6/WF-01 + H7/WF-05): controller enforces department
        // scope for manager/dm and allows only pending / hr_approved.
        Route::post('/leaves/{leaveRequest}/reject', [App\Http\Controllers\LeaveController::class, 'reject'])->name('leaves.reject');
    });
    
    // Employee - view own profile
    Route::middleware(['auth'])->group(function () {
        Route::get('/my-profile', [App\Http\Controllers\EmployeeController::class, 'myProfile'])->name('employees.my-profile');
    });
    
    // Super Admin routes only
    Route::middleware(['role:super_admin'])->group(function () {
        // User Management
        Route::resource('users', App\Http\Controllers\UserController::class);
        Route::post('/users/{user}/activate', [App\Http\Controllers\UserController::class, 'activate'])->name('users.activate');

        // Activity Logs
        Route::get('/activity-logs', [App\Http\Controllers\ActivityLogController::class, 'index'])->name('activity-logs.index');
        Route::get('/activity-logs/{activityLog}', [App\Http\Controllers\ActivityLogController::class, 'show'])->name('activity-logs.show');
        Route::get('/activity-logs/user/{userId}', [App\Http\Controllers\ActivityLogController::class, 'userActivity'])->name('activity-logs.user');

        // Dynamic Form Builder (Admin only - field definitions)
        Route::resource('dynamic-forms', App\Http\Controllers\DynamicFormController::class);
    });

      // Employee Custom Field Access (Employees can view/edit their own field values)
      Route::middleware(['auth'])->group(function () {
          Route::get('/employee/{employee}/fields', [App\Http\Controllers\DynamicFormController::class, 'employeeFields'])->name('dynamic-forms.employee-fields');
          Route::put('/employee/{employee}/fields', [App\Http\Controllers\DynamicFormController::class, 'updateEmployeeFields'])->name('dynamic-forms.employee-fields.update');
          Route::get('/employee/attachment/{fieldValue}', [App\Http\Controllers\DynamicFormController::class, 'downloadAttachment'])->name('dynamic-forms.attachment.download');
      });

     // Change Password
    Route::get('/change-password', [App\Http\Controllers\AuthController::class, 'showChangePasswordForm'])->name('password.change');
    Route::post('/change-password', [App\Http\Controllers\AuthController::class, 'changePassword']);
});
