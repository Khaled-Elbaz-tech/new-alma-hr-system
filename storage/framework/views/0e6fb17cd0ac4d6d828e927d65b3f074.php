<?php $__env->startSection('title', 'Manager Dashboard - Leave Requests'); ?>
<?php $__env->startSection('page-title', 'My Team Leave Requests'); ?>

<?php $__env->startSection('content'); ?>
<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-users"></i> Leave Requests from My Team</h5>
    </div>
    <div class="card-body">
        <form method="GET" class="mb-3">
            <div class="row">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="Search employee..."
                           value="<?php echo e(request('search')); ?>">
                </div>
                <div class="col-md-4">
                    <select name="status" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="pending" <?php echo e(request('status') == 'pending' ? 'selected' : ''); ?>>Pending</option>
                        <option value="hr_approved" <?php echo e(request('status') == 'hr_approved' ? 'selected' : ''); ?>>HR Approved</option>
                        <option value="approved" <?php echo e(request('status') == 'approved' ? 'selected' : ''); ?>>Approved</option>
                        <option value="rejected" <?php echo e(request('status') == 'rejected' ? 'selected' : ''); ?>>Rejected</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Filter</button>
                    <a href="<?php echo e(route('leaves.manager.index')); ?>" class="btn btn-outline-secondary">Reset</a>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Leave Type</th>
                        <th>Period</th>
                        <th>Days</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $leaveRequests; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $leave): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <tr>
                            <td>
                                <strong><?php echo e($leave->employee->user->name); ?></strong><br>
                                <small><?php echo e($leave->employee->employee_id); ?></small>
                            </td>
                            <td><?php echo e($leave->leaveType->name); ?></td>
                            <td>
                                <?php echo e($leave->start_date->format('d M Y')); ?> - <?php echo e($leave->end_date->format('d M Y')); ?>

                            </td>
                            <td><?php echo e($leave->total_days); ?></td>
                            <td>
                                <span class="badge bg-<?php echo e($leave->status === 'pending' ? 'warning' : ($leave->status === 'approved' ? 'success' : ($leave->status === 'rejected' ? 'danger' : 'info'))); ?>">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($leave->status === 'pending'): ?> Pending
                                    <?php elseif($leave->status === 'hr_approved'): ?> HR Approved
                                    <?php elseif($leave->status === 'approved'): ?> Approved
                                    <?php elseif($leave->status === 'rejected'): ?> Rejected
                                    <?php else: ?> <?php echo e(ucfirst($leave->status)); ?>

                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </span>
                            </td>
                            <td>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($leave->status === 'hr_approved'): ?>
                                    <form action="<?php echo e(route('leaves.manager-approve', $leave)); ?>" method="POST" class="d-inline">
                                        <?php echo csrf_field(); ?>
                                        <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Approve this leave request?')">
                                            Approve
                                        </button>
                                    </form>
                                    <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal<?php echo e($leave->id); ?>">
                                        Reject
                                    </button>
                                <?php else: ?>
                                    <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#viewModal<?php echo e($leave->id); ?>">View</button>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                                <!-- View Modal -->
                                <div class="modal fade" id="viewModal<?php echo e($leave->id); ?>" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Leave Request Details</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                 <p><strong>Employee:</strong> <?php echo e($leave->employee->user->name); ?></p>
                                                <p><strong>Leave Type:</strong> <?php echo e($leave->leaveType->name); ?></p>
                                                <p><strong>Period:</strong> <?php echo e($leave->start_date->format('d M Y')); ?> to <?php echo e($leave->end_date->format('d M Y')); ?></p>
                                                <p><strong>Working Days:</strong> <?php echo e($leave->total_days); ?></p>
                                                <p><strong>Calendar Days:</strong> <?php echo e($leave->calendar_days); ?></p>
                                                <p><strong>Status:</strong> <?php echo e(ucfirst($leave->status)); ?></p>
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($leave->reason): ?>
                                                <p><strong>Reason:</strong> <?php echo e($leave->reason); ?></p>
                                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($leave->flight_ticket_required): ?>
                                                <p><span class="badge bg-info">Flight Ticket Required</span></p>
                                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($leave->exit_reentry_required): ?>
                                                <p><span class="badge bg-warning">Exit Re-entry Required</span></p>
                                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Reject Modal -->
                                <div class="modal fade" id="rejectModal<?php echo e($leave->id); ?>" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Reject Leave Request</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <form action="<?php echo e(route('leaves.reject', $leave)); ?>" method="POST">
                                                <?php echo csrf_field(); ?>
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label class="form-label">Rejection Reason</label>
                                                        <textarea name="rejection_reason" class="form-control" required></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-danger">Reject</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted">No leave requests found.</td>
                        </tr>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php echo e($leaveRequests->links()); ?>

    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp1\htdocs\hr-system\resources\views\leaves\manager-index.blade.php ENDPATH**/ ?>