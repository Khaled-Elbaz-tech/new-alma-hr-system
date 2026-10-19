<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $__env->yieldContent('title', 'ALMA HR System'); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f6fa;
        }
        .sidebar {
            min-height: 100vh;
            background: #2c3e50;
            color: white;
        }
        .sidebar a {
            color: #ecf0f1;
            text-decoration: none;
            padding: 12px 15px;
            display: block;
            border-radius: 4px;
            margin-bottom: 2px;
        }
        .sidebar a:hover, .sidebar a.active {
            background: #34495e;
        }
        .sidebar a i {
            margin-right: 10px;
            width: 20px;
        }
        .main-content {
            padding: 20px;
        }
        .card {
            border: none;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .card-footer {
            padding: 15px;
            border-top: 1px solid #eee;
        }
        .pagination {
            margin: 0;
            padding: 0;
        }
        .btn-primary {
            background: #3498db;
            border: none;
        }
        .btn-primary:hover {
            background: #2980b9;
        }
        .status-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
        }
        .status-pending { background: #f39c12; color: white; }
        .status-approved { background: #27ae60; color: white; }
        .status-rejected { background: #e74c3c; color: white; }
        .status-hr_approved { background: #9b59b6; color: white; }
    </style>
    <?php echo $__env->yieldContent('styles'); ?>
</head>
<body>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->guard()->check()): ?>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 sidebar p-0">
                <div class="p-3 text-center border-bottom">
                    <h5>ALMA HR System</h5>
                </div>
                <div class="p-2">
                    <a href="<?php echo e(route('dashboard')); ?>" class="<?php echo e(request()->routeIs('dashboard') ? 'active' : ''); ?>">
                        <i class="fas fa-home"></i> Dashboard
                    </a>
                    
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->isEmployee() && !auth()->user()->isHrManager() && !auth()->user()->isManager() && !auth()->user()->isSuperAdmin()): ?>
                        <a href="<?php echo e(route('leaves.my')); ?>" class="<?php echo e(request()->routeIs('leaves.my') ? 'active' : ''); ?>">
                            <i class="fas fa-calendar-minus"></i> My Leaves
                        </a>
                        <a href="<?php echo e(route('employees.my-profile')); ?>" class="<?php echo e(request()->routeIs('employees.my-profile') ? 'active' : ''); ?>">
                            <i class="fas fa-user"></i> My Profile
                        </a>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->isHrManager() || auth()->user()->isSuperAdmin()): ?>
                        <a href="<?php echo e(route('employees.index')); ?>" class="<?php echo e(request()->routeIs('employees.*') ? 'active' : ''); ?>">
                            <i class="fas fa-users"></i> Employees
                        </a>
                        <a href="<?php echo e(route('employees.documents')); ?>" class="<?php echo e(request()->routeIs('employees.documents*') ? 'active' : ''); ?>">
                            <i class="fas fa-file-alt"></i> Employees Documents
                        </a>
                        <a href="<?php echo e(route('departments.index')); ?>" class="<?php echo e(request()->routeIs('departments.*') ? 'active' : ''); ?>">
                            <i class="fas fa-building"></i> Departments
                        </a>
                        <a href="<?php echo e(route('leaves.index')); ?>" class="<?php echo e(request()->routeIs('leaves.index') ? 'active' : ''); ?>">
                            <i class="fas fa-calendar"></i> Leave Requests
                        </a>
                        <a href="<?php echo e(route('holidays.index')); ?>" class="<?php echo e(request()->routeIs('holidays.*') ? 'active' : ''); ?>">
                            <i class="fas fa-calendar-week"></i> Holidays
                        </a>
                        
                        <div class="mt-3 px-3 small fw-bold text-info">REPORTS</div>
                        <a href="<?php echo e(route('reports.leave-balance')); ?>">
                            <i class="fas fa-chart-bar"></i> Leave Balance
                        </a>
                        <a href="<?php echo e(route('reports.leave-history')); ?>">
                            <i class="fas fa-history"></i> Leave History
                        </a>
                        <a href="<?php echo e(route('reports.vacation-history')); ?>">
                            <i class="fas fa-book"></i> Vacation History
                        </a>
                        <a href="<?php echo e(route('reports.department')); ?>">
                            <i class="fas fa-chart-pie"></i> Department
                        </a>
                        <a href="<?php echo e(route('reports.employee')); ?>">
                            <i class="fas fa-user-chart"></i> Employee
                        </a>
                        <a href="<?php echo e(route('reports.leave-statistics')); ?>">
                            <i class="fas fa-chart-line"></i> Statistics
                        </a>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->isManager() || auth()->user()->isDM()): ?>
                        <a href="<?php echo e(route('team.employees')); ?>" class="<?php echo e(request()->routeIs('team.employees*') ? 'active' : ''); ?>">
                            <i class="fas fa-users"></i> My Team
                        </a>
                        <a href="<?php echo e(route('leaves.manager.index')); ?>" class="<?php echo e(request()->routeIs('leaves.manager*') ? 'active' : ''); ?>">
                            <i class="fas fa-calendar"></i> Leave Requests
                        </a>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->isSuperAdmin()): ?>
                        <div class="mt-3 px-3 small fw-bold text-warning">ADMIN</div>
                        <a href="<?php echo e(route('users.index')); ?>" class="<?php echo e(request()->routeIs('users.*') ? 'active' : ''); ?>">
                            <i class="fas fa-user-cog"></i> Users
                        </a>
                        <a href="<?php echo e(route('users.task-access')); ?>" class="<?php echo e(request()->routeIs('users.task-access') ? 'active' : ''); ?>">
                            <i class="fas fa-user-cog"></i> Task Access
                        </a>
                        <a href="<?php echo e(route('activity-logs.index')); ?>" class="<?php echo e(request()->routeIs('activity-logs.*') ? 'active' : ''); ?>">
                            <i class="fas fa-history"></i> Activity Logs
                        </a>
                        <a href="<?php echo e(route('dynamic-forms.index')); ?>" class="<?php echo e(request()->routeIs('dynamic-forms.*') ? 'active' : ''); ?>">
                            <i class="fas fa-forms"></i> Dynamic Fields
                        </a>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    
                    <div class="mt-3 px-3 small fw-bold text-danger">ACCOUNT</div>
                    
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->guard()->check()): ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->canAccessTasks()): ?>
                            <a href="<?php echo e(route('tasks.workspace')); ?>" class="<?php echo e(request()->routeIs('tasks.*') ? 'active' : ''); ?>">
                                <i class="fas fa-tasks"></i> Workspace
                            </a>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    
                    <a href="<?php echo e(route('password.change')); ?>">
                        <i class="fas fa-key"></i> Change Password
                    </a>
                    <form action="<?php echo e(route('logout')); ?>" method="POST" class="d-inline">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="btn btn-link text-start text-white" style="text-decoration: none;">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </button>
                    </form>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-10 main-content">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4><?php echo $__env->yieldContent('page-title', 'Dashboard'); ?></h4>
                    <div class="d-flex align-items-center gap-3">
                        <span>Welcome, <?php echo e(auth()->user()->name); ?></span>
                        <span class="badge bg-primary"><?php echo e(auth()->user()->role->display_name ?? 'User'); ?></span>
                    </div>
                </div>
                
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('success')): ?>
                    <div class="alert alert-success"><?php echo e(session('success')); ?></div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('error')): ?>
                    <div class="alert alert-danger"><?php echo e(session('error')); ?></div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                
                <?php echo $__env->yieldContent('content'); ?>
            </div>
        </div>
    </div>
    <?php else: ?>
        <?php echo $__env->yieldContent('content'); ?>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <?php echo $__env->yieldContent('scripts'); ?>
</body>
</html><?php /**PATH C:\xampp1\htdocs\hr-system\resources\views/layouts/app.blade.php ENDPATH**/ ?>