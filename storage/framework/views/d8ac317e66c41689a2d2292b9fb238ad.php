<?php $__env->startSection('title', 'Manage Leave Balances'); ?>
<?php $__env->startSection('page-title', 'Manage Leave Balances'); ?>

<?php $__env->startSection('content'); ?>
<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-balance-scale"></i> All Leave Balances</h5>
    </div>
    <div class="card-body">
        <!-- Filters -->
        <form method="GET" class="mb-3">
            <div class="row">
                <div class="col-md-4">
                    <input type="text" name="employee" class="form-control" placeholder="Search employee..."
                           value="<?php echo e(request('employee')); ?>">
                </div>
                <div class="col-md-3">
                    <select name="year" class="form-select">
                        <option value="">All Years</option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $years; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $y): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <option value="<?php echo e($y); ?>" <?php echo e(request('year') == $y ? 'selected' : ''); ?>><?php echo e($y); ?></option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Filter</button>
                    <a href="<?php echo e(route('leaves.manage-balances')); ?>" class="btn btn-outline-secondary">Reset</a>
                </div>
                <div class="col-md-3 text-end">
                    <a href="<?php echo e(route('reports.leave-balance')); ?>" class="btn btn-success"><i class="fas fa-download"></i> Export</a>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Employee</th>
                        <th>Year</th>
                        <th>Months Worked</th>
                        <th>Entitled</th>
                        <th>Used</th>
                        <th>Balance</th>
                        <th>Tickets</th>
                        <th>Exit/Re-entry</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $balances; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $balance): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <tr>
                            <td>
                                <strong><?php echo e($balance->employee->user->name); ?></strong><br>
                                <small><?php echo e($balance->employee->employee_id); ?></small>
                            </td>
                            <td><?php echo e($balance->year); ?></td>
                            <td><?php echo e($balance->months_from_hiring); ?></td>
                            <td><?php echo e(number_format($balance->entitled_days, 2)); ?></td>
                            <td><?php echo e(number_format($balance->used_days, 2)); ?></td>
                            <td>
                                <strong class="<?php echo e($balance->balance >= 0 ? 'text-success' : 'text-danger'); ?>">
                                    <?php echo e(number_format($balance->balance, 2)); ?>

                                </strong>
                            </td>
                            <td><?php echo e($balance->ticket_count); ?></td>
                            <td><?php echo e($balance->exit_reentry_count); ?></td>
                            <td>
                                <a href="<?php echo e(route('leaves.edit-balance', $balance)); ?>" class="btn btn-sm btn-warning">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                            </td>
                        </tr>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <tr>
                            <td colspan="9" class="text-center text-muted">No leave balances found.</td>
                        </tr>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php echo e($balances->links()); ?>

    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp1\htdocs\hr-system\resources\views\leaves\manage-balances.blade.php ENDPATH**/ ?>