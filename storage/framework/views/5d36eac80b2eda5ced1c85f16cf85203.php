<?php $__env->startSection('title', 'User Details'); ?>
<?php $__env->startSection('page-title', 'User Details'); ?>

<?php $__env->startSection('content'); ?>
<div class="row">
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-user"></i> User Information</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <td><strong>Name:</strong></td>
                        <td><?php echo e($user->name); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Email:</strong></td>
                        <td><?php echo e($user->email); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Role:</strong></td>
                        <td>
                            <span class="badge bg-<?php echo e($user->role->name == 'super_admin' ? 'danger' : ($user->role->name == 'hr_manager' ? 'primary' : ($user->role->name == 'manager' ? 'info' : 'secondary'))); ?>">
                                <?php echo e($user->role->display_name ?? 'N/A'); ?>

                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Status:</strong></td>
                        <td>
                            <span class="badge bg-<?php echo e($user->is_active ? 'success' : 'secondary'); ?>">
                                <?php echo e($user->is_active ? 'Active' : 'Inactive'); ?>

                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Last Login:</strong></td>
                        <td><?php echo e($user->last_login ? $user->last_login->format('d M Y H:i') : 'Never'); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Created At:</strong></td>
                        <td><?php echo e($user->created_at->format('d M Y')); ?></td>
                    </tr>
                </table>
            </div>
            <div class="card-footer">
                <a href="<?php echo e(route('users.edit', $user)); ?>" class="btn btn-warning">
                    <i class="fas fa-edit"></i> Edit
                </a>
                <a href="<?php echo e(route('users.index')); ?>" class="btn btn-secondary">Back to List</a>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-user-tie"></i> Employee Binding</h5>
            </div>
            <div class="card-body">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($user->employee): ?>
                    <table class="table table-borderless">
                        <tr>
                            <td><strong>Employee Number:</strong></td>
                            <td><?php echo e($user->employee->employee_number); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Department:</strong></td>
                            <td><?php echo e($user->employee->department->name ?? 'Not Assigned'); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Joining Date:</strong></td>
                            <td><?php echo e($user->employee->joining_date->format('d M Y')); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Status:</strong></td>
                            <td>
                                <span class="badge bg-<?php echo e($user->employee->employment_status === 'active' ? 'success' : 'secondary'); ?>">
                                    <?php echo e(ucfirst($user->employee->employment_status)); ?>

                                </span>
                            </td>
                        </tr>
                    </table>
                <?php else: ?>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i> 
                        This user is not bound to any employee record.
                    </div>
                    <p class="text-muted">To bind this user to an employee, edit the user and select an employee, or create the user with "Employee" role.</p>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-history"></i> Recent Activity</h5>
            </div>
            <div class="card-body">
                <?php
                    $activities = \App\Models\ActivityLog::where('user_id', $user->id)
                        ->orderBy('created_at', 'desc')
                        ->take(5)
                        ->get();
                ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($activities->count() > 0): ?>
                    <ul class="list-group list-group-flush">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $activities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $activity): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <li class="list-group-item px-0">
                            <small class="text-muted"><?php echo e($activity->created_at->format('d M Y H:i')); ?></small>
                            <br>
                            <strong><?php echo e(ucfirst($activity->action)); ?></strong> <?php echo e(class_basename($activity->model_type) ?? ''); ?>

                        </li>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </ul>
                <?php else: ?>
                    <p class="text-muted">No recent activity.</p>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp1\htdocs\hr-system\resources\views\users\show.blade.php ENDPATH**/ ?>