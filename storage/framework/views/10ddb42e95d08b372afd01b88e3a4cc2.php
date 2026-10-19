<?php $__env->startSection('title', 'Leave Balance Report'); ?>
<?php $__env->startSection('page-title', 'Leave Balance Report'); ?>

<?php $__env->startSection('content'); ?>
<div class="row mb-3">
    <div class="col-md-12">
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Employee</label>
                <select name="employee_id" class="form-select">
                    <option value="">All Employees</option>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $employees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $emp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <option value="<?php echo e($emp->id); ?>" <?php echo e($employeeId == $emp->id ? 'selected' : ''); ?>>
                            <?php echo e($emp->user->name); ?> (<?php echo e($emp->employee_number); ?>)
                        </option>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Year</label>
                <select name="year" class="form-select">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php for($y = date('Y') - 2; $y <= date('Y') + 1; $y++): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <option value="<?php echo e($y); ?>" <?php echo e($year == $y ? 'selected' : ''); ?>><?php echo e($y); ?></option>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endfor; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-secondary">Filter</button>
            </div>
            <div class="col-md-5 text-end d-flex align-items-end justify-content-end gap-2">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($employeeId && $selectedEmployee): ?>
                <a href="<?php echo e(route('reports.export.year-by-year', ['employee_id' => $employeeId])); ?>" class="btn btn-success">
                    <i class="fas fa-file-excel"></i> Export Year-by-Year
                </a>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <a href="<?php echo e(route('reports.export.leave-balance', ['year' => $year])); ?>" class="btn btn-primary">
                    <i class="fas fa-file-csv"></i> Export Excel
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Year-by-Year Breakdown (when specific employee selected) -->
<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($selectedEmployee && !empty($yearByYearData)): ?>
<div class="card mb-4">
    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
        <div>
            <h5 class="mb-0"><i class="fas fa-history"></i> Year-by-Year Leave History</h5>
            <small><?php echo e($selectedEmployee->user->name); ?> (<?php echo e($selectedEmployee->employee_number); ?>)</small>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Year</th>
                        <th>Months from Hiring</th>
                        <th>Entitled Days</th>
                        <th>Used Days</th>
                        <th>Exit/Re-entry</th>
                        <th>Tickets</th>
                        <th>Balance</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $yearByYearData; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $data): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <tr class="<?php echo e($data['year'] == $year ? 'table-primary' : ''); ?>">
                        <td><strong><?php echo e($data['year']); ?></strong></td>
                        <td><?php echo e($data['months_from_hiring']); ?></td>
                        <td><?php echo e($data['entitled_days']); ?></td>
                        <td><?php echo e($data['used_days']); ?></td>
                        <td><?php echo e($data['exit_reentry_count']); ?></td>
                        <td><?php echo e($data['ticket_count']); ?></td>
                        <td>
                            <strong class="<?php echo e($data['balance'] > 0 ? 'text-success' : 'text-danger'); ?>">
                                <?php echo e($data['balance']); ?>

                            </strong>
                        </td>
                    </tr>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

<!-- Summary Table for All Employees -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-users"></i> Leave Balance Summary for <?php echo e($year); ?></h5>
    </div>
    <div class="card-body">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($summaryData) && $summaryData->count() > 0): ?>
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Employee</th>
                            <th>Employee #</th>
                            <th>Department</th>
                            <th>Entitled</th>
                            <th>Used</th>
                            <th>Balance</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $summaryData; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <tr>
                            <td>
                                <a href="<?php echo e(route('reports.leave-balance', ['employee_id' => $item['employee']->id, 'year' => $year])); ?>">
                                    <?php echo e($item['employee']->user->name); ?>

                                </a>
                            </td>
                            <td><?php echo e($item['employee']->employee_number); ?></td>
                            <td><?php echo e($item['employee']->department->name ?? 'N/A'); ?></td>
                            <td><?php echo e($item['entitled_days']); ?></td>
                            <td><?php echo e($item['used_days']); ?></td>
                            <td>
                                <strong class="<?php echo e($item['balance'] > 0 ? 'text-success' : 'text-danger'); ?>">
                                    <?php echo e($item['balance']); ?>

                                </strong>
                            </td>
                        </tr>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> 
                Select an employee to view their year-by-year breakdown, or view the summary below.
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
</div>

<!-- Rules Information -->
<div class="card mt-4">
    <div class="card-header bg-light">
        <h6 class="mb-0"><i class="fas fa-info-circle"></i> Leave Balance Rules</h6>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item px-0">
                        <i class="fas fa-calendar text-primary"></i> <strong>21 days</strong> per year (full entitlement)
                    </li>
                    <li class="list-group-item px-0">
                        <i class="fas fa-calculator text-primary"></i> <strong>1.75 days</strong> per month (partial year)
                    </li>
                </ul>
            </div>
            <div class="col-md-4">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item px-0">
                        <i class="fas fa-history text-warning"></i> <strong>Carry over:</strong> Max 5 days
                    </li>
                    <li class="list-group-item px-0">
                        <i class="fas fa-calendar-minus text-info"></i> Calculated from joining date
                    </li>
                </ul>
            </div>
            <div class="col-md-4">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item px-0">
                        <i class="fas fa-plane text-success"></i> Exit/Re-entry: Count tracked
                    </li>
                    <li class="list-group-item px-0">
                        <i class="fas fa-ticket-alt text-success"></i> Flight tickets: Count tracked
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp1\htdocs\hr-system\resources\views\reports\leave-balance.blade.php ENDPATH**/ ?>