<?php $__env->startSection('title', 'Users'); ?>
<?php $__env->startSection('page-title', 'User Management'); ?>

<?php $__env->startSection('content'); ?>
<div class="row mb-3">
    <div class="col-md-12">
        <a href="<?php echo e(route('users.create')); ?>" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add User
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <form method="GET" class="row g-3">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control" placeholder="Search by name or email" value="<?php echo e(request('search')); ?>">
            </div>
            <div class="col-md-3">
                <select name="role_id" class="form-select">
                    <option value="">All Roles</option>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <option value="<?php echo e($role->id); ?>" <?php echo e(request('role_id') == $role->id ? 'selected' : ''); ?>><?php echo e($role->display_name); ?></option>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </select>
            </div>
            <div class="col-md-2">
                <select name="is_active" class="form-select">
                    <option value="">All Status</option>
                    <option value="1" <?php echo e(request('is_active') === '1' ? 'selected' : ''); ?>>Active</option>
                    <option value="0" <?php echo e(request('is_active') === '0' ? 'selected' : ''); ?>>Inactive</option>
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-secondary">Filter</button>
                <a href="<?php echo e(route('users.index')); ?>" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
    <div class="card-body">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($users->count() > 0): ?>
            <div class="table-responsive">
                <table class="table table-hover">
<thead class="table-light">
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Employee Binding</th>
                            <th>Status</th>
                            <th>Task Access</th>
                            <th>Last Login</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                        <tbody>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <?php $taskAccess = $user->taskAccess; ?>
                            <tr>
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
                                        <span class="text-danger">
                                            <i class="fas fa-exclamation-circle"></i> 
                                            Not bound
                                        </span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-<?php echo e($user->is_active ? 'success' : 'secondary'); ?>">
                                        <?php echo e($user->is_active ? 'Active' : 'Inactive'); ?>

                                    </span>
                                </td>
                                <td>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($user->role && in_array($user->role->name, ['super_admin', 'hr_manager'])): ?>
                                        <span class="badge bg-success">
                                            <i class="fas fa-check"></i> Enabled
                                        </span>
                                    <?php else: ?>
                                        <form action="<?php echo e(route('users.toggle-task-access', $user)); ?>" method="POST" class="d-inline">
                                            <?php echo csrf_field(); ?>
                                            <button type="submit" class="btn btn-sm <?php echo e($taskAccess && $taskAccess->can_access_tasks ? 'btn-success' : 'btn-outline-secondary'); ?>">
                                                <i class="fas <?php echo e($taskAccess && $taskAccess->can_access_tasks ? 'fa-toggle-on' : 'fa-toggle-off'); ?>"></i>
                                                <?php echo e($taskAccess && $taskAccess->can_access_tasks ? 'Enabled' : 'Disabled'); ?>

                                            </button>
                                        </form>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </td>
                                <td><?php echo e($user->last_login ? $user->last_login->format('d M Y H:i') : 'Never'); ?></td>
                                <td>
                                    <a href="<?php echo e(route('users.show', $user)); ?>" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="<?php echo e(route('users.edit', $user)); ?>" class="btn btn-sm btn-warning">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($user->is_active && $user->id !== auth()->id()): ?>
                                        <form action="<?php echo e(route('users.destroy', $user)); ?>" method="POST" class="d-inline">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('DELETE'); ?>
                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Deactivate this user?')">
                                                <i class="fas fa-ban"></i>
                                            </button>
                                        </form>
                                    <?php elseif(!$user->is_active): ?>
                                        <form action="<?php echo e(route('users.activate', $user)); ?>" method="POST" class="d-inline">
                                            <?php echo csrf_field(); ?>
                                            <button type="submit" class="btn btn-sm btn-success">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
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
        <h6><i class="fas fa-info-circle"></i> User Management Rules</h6>
        <ul class="mb-0">
            <li>Every employee MUST have a user account</li>
            <li>One user = one employee (unique binding)</li>
            <li>Email must be unique across all users</li>
            <li>Super Admin only can manage users</li>
            <li>Task Management access can be toggled for employees/managers</li>
        </ul>
    </div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp1\htdocs\hr-system\resources\views\users\index.blade.php ENDPATH**/ ?>