<?php $__env->startSection('title', 'My Dashboard'); ?>
<?php $__env->startSection('page-title', 'My Dashboard'); ?>

<?php $__env->startSection('content'); ?>
<div class="row mb-4">
    <!-- Leave Balance Summary -->
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-calendar-check"></i> Current Leave Balance (<?php echo e(date('Y')); ?>)</h5>
            </div>
            <div class="card-body">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($currentBalance) && $currentBalance): ?>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Leave Type</th>
                                    <th>Entitled</th>
                                    <th>Used</th>
                                    <th>Balance</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Annual Leave</td>
                                    <td><?php echo e($currentBalance->entitled_days); ?></td>
                                    <td><?php echo e($currentBalance->used_days); ?></td>
                                    <td class="<?php echo e($currentBalance->balance > 0 ? 'text-success' : 'text-danger'); ?>">
                                        <strong><?php echo e($currentBalance->balance); ?></strong>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-muted">No leave balance records found. Please contact HR to set up your leave entitlements.</p>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <!-- Previous Year Balance -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-history"></i> Previous Year Balance (<?php echo e(date('Y') - 1); ?>)</h5>
            </div>
            <div class="card-body">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($previousBalance) && $previousBalance): ?>
                    <p class="mb-0">
                        <strong>Balance:</strong> 
                        <span class="<?php echo e($previousBalance->balance > 0 ? 'text-success' : 'text-danger'); ?>">
                            <?php echo e($previousBalance->balance); ?>

                        </span>
                    </p>
                    <p class="mb-0 text-muted">
                        Entitled: <?php echo e($previousBalance->entitled_days); ?> | Used: <?php echo e($previousBalance->used_days); ?>

                    </p>
                <?php else: ?>
                    <p class="text-muted mb-0">No balance record for <?php echo e(date('Y') - 1); ?></p>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Total Taken This Year -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-calendar-alt"></i> Total Taken This Year</h5>
            </div>
            <div class="card-body">
                <p class="mb-0">
                    <strong><?php echo e(isset($totalTakenThisYear) ? $totalTakenThisYear : 0); ?></strong> days
                </p>
                <p class="mb-0 text-muted">Approved leaves in <?php echo e(date('Y')); ?></p>
            </div>
        </div>
    </div>

    <!-- Current Balance Summary -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-wallet"></i> Current Balance Summary</h5>
            </div>
            <div class="card-body">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($currentBalance) && $currentBalance): ?>
                    <p class="mb-0">
                        <strong>Available:</strong> 
                        <span class="<?php echo e($currentBalance->balance > 0 ? 'text-success' : 'text-danger'); ?>">
                            <?php echo e($currentBalance->balance); ?> days
                        </span>
                    </p>
                    <p class="mb-0 text-muted">
                        Carry-forward included
                    </p>
                <?php else: ?>
                    <p class="text-muted mb-0">No balance data available</p>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Notifications / Alerts -->
<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($nextApprovedLeave)): ?>
<div class="alert alert-info">
    <h5><i class="fas fa-bell"></i> Your Next Approved Leave</h5>
    <p class="mb-0">
        <strong><?php echo e($nextApprovedLeave->leaveType->name); ?></strong> from 
        <?php echo e($nextApprovedLeave->start_date->format('d M Y')); ?> to <?php echo e($nextApprovedLeave->end_date->format('d M Y')); ?> 
        (<?php echo e($nextApprovedLeave->total_days); ?> days)
    </p>
</div>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

<div class="row">
    <!-- Leave Request Status List -->
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-list"></i> My Leave Requests</h5>
                <a href="<?php echo e(route('leaves.my')); ?>" class="btn btn-sm btn-primary">
                    <i class="fas fa-plus"></i> Apply Leave
                </a>
            </div>
            <div class="card-body">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($allLeaveRequests) && $allLeaveRequests->count() > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Type</th>
                                    <th>Dates</th>
                                    <th>Days</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $allLeaveRequests; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $leave): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                    <tr>
                                        <td><?php echo e($leave->leaveType->name); ?></td>
                                        <td><?php echo e($leave->start_date->format('d M')); ?> - <?php echo e($leave->end_date->format('d M')); ?></td>
                                        <td><?php echo e($leave->total_days); ?></td>
                                        <td>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($leave->status == 'pending'): ?>
                                                <span class="badge bg-warning">Pending</span>
                                            <?php elseif($leave->status == 'approved'): ?>
                                                <span class="badge bg-success">Approved</span>
                                            <?php elseif($leave->status == 'rejected'): ?>
                                                <span class="badge bg-danger">Rejected</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary"><?php echo e($leave->status); ?></span>
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </td>
                                    </tr>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        <a href="<?php echo e(route('leaves.my')); ?>" class="btn btn-outline-secondary btn-sm">View All My Leaves</a>
                    </div>
                <?php else: ?>
                    <p class="text-muted">No leave requests yet.</p>
                    <a href="<?php echo e(route('leaves.my')); ?>" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Apply for Leave
                    </a>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Status Summary -->
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-chart-pie"></i> Request Summary</h5>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span>Pending</span>
                    <span class="badge bg-warning"><?php echo e($pendingCount ?? 0); ?></span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span>Approved</span>
                    <span class="badge bg-success"><?php echo e($approvedCount ?? 0); ?></span>
                </div>
                <div class="d-flex justify-content-between">
                    <span>Rejected</span>
                    <span class="badge bg-danger"><?php echo e($rejectedCount ?? 0); ?></span>
                </div>
            </div>
        </div>

        <!-- Upcoming Holidays -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-calendar-day"></i> Upcoming Holidays</h5>
            </div>
            <div class="card-body">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($upcomingHolidays) && $upcomingHolidays->count() > 0): ?>
                    <div class="list-group">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $upcomingHolidays; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $holiday): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <div class="list-group-item">
                                <strong><?php echo e($holiday->name); ?></strong>
                                <br>
                                <small class="text-muted">
                                    <?php echo e($holiday->start_date->format('d M')); ?> - <?php echo e($holiday->end_date->format('d M')); ?>

                                </small>
                            </div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </div>
                <?php else: ?>
                    <p class="text-muted">No upcoming holidays.</p>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row mt-3">
    <div class="col-md-12">
        <a href="<?php echo e(route('leaves.my', ['open_form' => 1])); ?>" class="btn btn-primary">
            <i class="fas fa-plus"></i> Apply for Leave
        </a>
        <a href="<?php echo e(route('leaves.my')); ?>" class="btn btn-outline-secondary">
            <i class="fas fa-list"></i> View All Leaves
        </a>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp1\htdocs\hr-system\resources\views\dashboard\employee-view.blade.php ENDPATH**/ ?>