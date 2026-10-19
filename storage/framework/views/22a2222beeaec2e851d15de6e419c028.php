<?php $__env->startSection('title', 'Edit Leave Balance'); ?>
<?php $__env->startSection('page-title', 'Edit Leave Balance'); ?>

<?php $__env->startSection('content'); ?>
<div class="row">
    <div class="col-md-8 offset-md-2">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-edit"></i> Edit Balance: <?php echo e($balance->employee->user->name); ?> (<?php echo e($balance->year); ?>)</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="<?php echo e(route('leaves.update-balance', $balance)); ?>">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('PUT'); ?>
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('PUT'); ?>

                    <div class="mb-3">
                        <label class="form-label">Employee</label>
                        <input type="text" class="form-control" value="<?php echo e($balance->employee->user->name); ?> (<?php echo e($balance->employee->employee_id); ?>)" readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Year</label>
                        <select name="year" class="form-select" required>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $leaveYears; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $leaveYear): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <option value="<?php echo e($leaveYear->year); ?>" <?php echo e($balance->year == $leaveYear->year ? 'selected' : ''); ?>>
                                    <?php echo e($leaveYear->year); ?>

                                </option>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Months Worked in Year</label>
                        <input type="number" name="months_from_hiring" class="form-control"
                               value="<?php echo e($monthsWorked); ?>" readonly>
                        <small class="text-muted">Auto-calculated from joining date</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Days Entitled</label>
                        <input type="number" step="0.01" name="entitled_days" class="form-control"
                               value="<?php echo e(old('entitled_days', $balance->entitled_days)); ?>" required>
                        <small class="text-muted">Calculated as 1.75 × months worked, but HR can override</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Days Used</label>
                        <input type="number" step="0.01" name="used_days" class="form-control"
                               value="<?php echo e(old('used_days', $balance->used_days)); ?>" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Ticket Count (Annual Leaves)</label>
                            <input type="number" name="ticket_count" class="form-control"
                                   value="<?php echo e(old('ticket_count', $balance->ticket_count)); ?>" min="0">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Exit/Re-entry Count</label>
                            <input type="number" name="exit_reentry_count" class="form-control"
                                   value="<?php echo e(old('exit_reentry_count', $balance->exit_reentry_count)); ?>" min="0">
                        </div>
                    </div>

                    <div class="alert alert-info">
                        <strong>Current Balance:</strong> <?php echo e(number_format($balance->balance, 2)); ?> days
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="<?php echo e(route('leaves.manage-balances')); ?>" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Balance
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp1\htdocs\hr-system\resources\views\leaves\edit-balance.blade.php ENDPATH**/ ?>