<?php

use Illuminate\Support\Facades\Route;

// Redirect root to login
Route::get('/', function () {
    return redirect()->route('login');
});

// Public routes
Route::get('/login', [App\Http\Controllers\AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [App\Http\Controllers\AuthController::class, 'login']);
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
        Route::post('/employees/data/import', [App\Http\Controllers\EmployeeController::class, 'import'])->name('employees.import');
        Route::get('/employees/data/template', [App\Http\Controllers\EmployeeController::class, 'downloadTemplate'])->name('employees.download-template');
    });
    
    // HR Manager routes continued
    Route::middleware(['role:super_admin,hr_manager'])->group(function () {
        Route::resource('departments', App\Http\Controllers\DepartmentController::class);
        
        // Leave Management
        Route::get('/leaves', [App\Http\Controllers\LeaveController::class, 'index'])->name('leaves.index');
        Route::get('/leaves/create-for-employee', [App\Http\Controllers\LeaveController::class, 'createForEmployee'])->name('leaves.create-for-employee');
        Route::post('/leaves/create-for-employee', [App\Http\Controllers\LeaveController::class, 'storeForEmployee'])->name('leaves.store-for-employee');
        Route::get('/leaves/employee/{employee}', [App\Http\Controllers\LeaveController::class, 'getEmployeeData'])->name('leaves.employee-data');
        Route::post('/leaves/{leaveRequest}/hr-approve', [App\Http\Controllers\LeaveController::class, 'hrApprove'])->name('leaves.hr-approve');
        Route::post('/leaves/{leaveRequest}/manager-approve', [App\Http\Controllers\LeaveController::class, 'managerApprove'])->name('leaves.manager-approve');
        Route::post('/leaves/{leaveRequest}/reject', [App\Http\Controllers\LeaveController::class, 'reject'])->name('leaves.reject');
        Route::post('/leaves/{leaveRequest}/cancel', [App\Http\Controllers\LeaveController::class, 'cancel'])->name('leaves.cancel');
        Route::post('/leaves/historical', [App\Http\Controllers\LeaveController::class, 'storeHistorical'])->name('leaves.historical');

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
        Route::post('/users/{user}/toggle-task-access', [App\Http\Controllers\UserController::class, 'toggleTaskAccess'])->name('users.toggle-task-access');
        Route::get('/users/task-access', [App\Http\Controllers\UserController::class, 'taskAccess'])->name('users.task-access');

        // Activity Logs
        Route::get('/activity-logs', [App\Http\Controllers\ActivityLogController::class, 'index'])->name('activity-logs.index');
        Route::get('/activity-logs/{activityLog}', [App\Http\Controllers\ActivityLogController::class, 'show'])->name('activity-logs.show');
        Route::get('/activity-logs/user/{userId}', [App\Http\Controllers\ActivityLogController::class, 'userActivity'])->name('activity-logs.user');

        // Dynamic Form Builder (Admin only - field definitions)
        Route::resource('dynamic-forms', App\Http\Controllers\DynamicFormController::class);
    });

    // Task Management - accessible by super_admin, hr_manager, and users with task access
    Route::middleware(['auth'])->group(function () {
        // Workspace (Task Dashboard)
        Route::get('/workspace', [App\Http\Controllers\TaskController::class, 'workspace'])->name('tasks.workspace');
    });

    Route::middleware(['auth'])->group(function () {
        Route::get('/tasks', [App\Http\Controllers\TaskController::class, 'index'])->name('tasks.index');
        Route::get('/tasks/create', [App\Http\Controllers\TaskController::class, 'create'])->name('tasks.create');
        Route::post('/tasks', [App\Http\Controllers\TaskController::class, 'store'])->name('tasks.store');
        Route::get('/tasks/{task}', [App\Http\Controllers\TaskController::class, 'show'])->name('tasks.show');
        Route::get('/tasks/{task}/edit', [App\Http\Controllers\TaskController::class, 'edit'])->name('tasks.edit');
        Route::put('/tasks/{task}', [App\Http\Controllers\TaskController::class, 'update'])->name('tasks.update');
        Route::delete('/tasks/{task}', [App\Http\Controllers\TaskController::class, 'destroy'])->name('tasks.destroy');
        Route::put('/tasks/{task}/status', [App\Http\Controllers\TaskController::class, 'updateStatus'])->name('tasks.update-status');
        
        // Subtasks
        Route::post('/tasks/{task}/subtasks', [App\Http\Controllers\TaskController::class, 'addSubtask'])->name('tasks.subtasks.store');
        Route::put('/subtasks/{subtask}', [App\Http\Controllers\TaskController::class, 'updateSubtask'])->name('tasks.subtasks.update');
        Route::delete('/subtasks/{subtask}', [App\Http\Controllers\TaskController::class, 'destroySubtask'])->name('tasks.subtasks.destroy');
        
         // Comments
         Route::post('/tasks/{task}/comments', [App\Http\Controllers\TaskController::class, 'addComment'])->name('tasks.comments.store');
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

Route::get('/plain-test', function () {
    return 'PLAIN TEXT WORKS';
});

Route::get('/blade-test', function () {
    return view('blade-test');
});

Route::get('/minimal-test', function () {
    return view('minimal-test');
});
