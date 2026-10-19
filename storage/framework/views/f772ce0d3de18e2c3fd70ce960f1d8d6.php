<?php $__env->startSection('title', 'Task Management Access'); ?>

<?php $__env->startSection('page-title'); ?>
    <i class="fas fa-user-cog"></i> Task Management Access
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Task Management Access Control</h5>
        <p class="text-muted mb-0">Enable or disable Task Management access for users. Super Admins and HR Managers always have access.</p>
    </div>
    <div class="card-body">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($users->count() > 0): ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Employee ID</th>
                            <th>Task Access</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <?php
                                $taskAccess = $user->taskAccess;
                                $hasAccess = $taskAccess && $taskAccess->can_access_tasks;
                                $isSuperAdminOrHR = $user->isSuperAdmin() || $user->isHrManager();
                            ?>
                            <tr>
                                <td><?php echo e($index + 1); ?></td>
                                <td><strong><?php echo e($user->name); ?></strong></td>
                                <td><?php echo e($user->email); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo e($user->role->name == 'super_admin' ? 'danger' : ($user->role->name == 'hr_manager' ? 'primary' : ($user->role->name == 'manager' ? 'info' : 'secondary'))); ?>">
                                        <?php echo e($user->role->display_name ?? 'N/A'); ?>

                                    </span>
                                </td>
                                <td>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($user->employee): ?>
                                        <span class="text-success">
                                            <i class="fas fa-check-circle"></i> 
                                            <?php echo e($user->employee->employee_number); ?>

                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">Not bound</span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </td>
                                <td>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isSuperAdminOrHR): ?>
                                        <span class="badge bg-success">
                                            <i class="fas fa-lock"></i> Always Enabled
                                        </span>
                                    <?php else: ?>
                                        <form action="<?php echo e(route('users.toggle-task-access', $user)); ?>" method="POST" class="d-inline">
                                            <?php echo csrf_field(); ?>
                                            <button type="submit" class="btn btn-sm <?php echo e($hasAccess ? 'btn-success' : 'btn-outline-secondary'); ?>">
                                                <i class="fas <?php echo e($hasAccess ? 'fa-toggle-on' : 'fa-toggle-off'); ?>"></i>
                                                <?php echo e($hasAccess ? 'Enabled' : 'Disabled'); ?>

                                            </button>
                                        </form>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-<?php echo e($user->is_active ? 'success' : 'secondary'); ?>">
                                        <?php echo e($user->is_active ? 'Active' : 'Inactive'); ?>

                                    </span>
                                </td>
                            </tr>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </tbody>
                </table>
            </div>
            <?php echo e($users->links()); ?>

        <?php else: ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> No users found.
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
</div>

<!-- Rules Info -->
<div class="alert alert-light mt-3">
    <h6><i class="fas fa-info-circle"></i> Access Control Rules</h6>
    <ul class="mb-0">
        <li>Super Administrators and HR Managers always have Task Management access (cannot be disabled)</li>
        <li>For other roles, access can be toggled on/off using the switch above</li>
        <li>When access is disabled, users cannot view or access any task management screens</li>
        <li>The "Task Management" sidebar link is automatically hidden for users without access</li>
        <li>Only users with active Task Management access appear in the "Assigned To" dropdown when creating/editing tasks</li>
    </ul>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp1\htdocs\hr-system\resources\views\users\task-access.blade.php ENDPATH**/ ?>