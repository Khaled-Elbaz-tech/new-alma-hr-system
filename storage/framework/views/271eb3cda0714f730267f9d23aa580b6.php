<?php $__env->startSection('title', 'Edit Employee'); ?>
<?php $__env->startSection('page-title', 'Edit Employee'); ?>

<?php $__env->startSection('content'); ?>
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Edit Employee</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="<?php echo e(route('employees.update', $employee)); ?>">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('PUT'); ?>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Full Name</label>
                            <input type="text" name="name" class="form-control" value="<?php echo e(old('name', $employee->user->name)); ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" value="<?php echo e(old('email', $employee->user->email)); ?>" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">New Password (leave blank to keep current)</label>
                            <input type="password" name="password" class="form-control" minlength="8">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Role</label>
                            <select name="role_id" class="form-select" required>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                    <option value="<?php echo e($role->id); ?>" <?php echo e($employee->user->role_id == $role->id ? 'selected' : ''); ?>>
                                        <?php echo e($role->display_name); ?>

                                    </option>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Employee Number</label>
                            <input type="text" name="employee_number" class="form-control" value="<?php echo e(old('employee_number', $employee->employee_number)); ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Department</label>
                            <select name="department_id" class="form-select">
                                <option value="">Select Department</option>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $departments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dept): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                    <option value="<?php echo e($dept->id); ?>" <?php echo e($employee->department_id == $dept->id ? 'selected' : ''); ?>>
                                        <?php echo e($dept->name); ?>

                                    </option>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Joining Date</label>
                            <input type="date" name="joining_date" class="form-control" value="<?php echo e(old('joining_date', $employee->joining_date->format('Y-m-d'))); ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Annual Leave Entitlement</label>
                            <input type="number" name="annual_leave_entitlement" class="form-control" value="<?php echo e(old('annual_leave_entitlement', $employee->annual_leave_entitlement)); ?>" min="0">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Contract Start Date</label>
                            <input type="date" name="contract_start_date" class="form-control" value="<?php echo e(old('contract_start_date', $employee->contract_start_date?->format('Y-m-d'))); ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Contract End Date</label>
                            <input type="date" name="contract_end_date" class="form-control" value="<?php echo e(old('contract_end_date', $employee->contract_end_date?->format('Y-m-d'))); ?>">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Employment Status</label>
                        <select name="employment_status" class="form-select" required>
                            <option value="active" <?php echo e($employee->employment_status === 'active' ? 'selected' : ''); ?>>Active</option>
                            <option value="resigned" <?php echo e($employee->employment_status === 'resigned' ? 'selected' : ''); ?>>Resigned</option>
                            <option value="terminated" <?php echo e($employee->employment_status === 'terminated' ? 'selected' : ''); ?>>Terminated</option>
                            <option value="on_leave" <?php echo e($employee->employment_status === 'on_leave' ? 'selected' : ''); ?>>On Leave</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Update Employee</button>
                    <a href="<?php echo e(route('employees.index')); ?>" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp1\htdocs\hr-system\resources\views\employees\edit.blade.php ENDPATH**/ ?>