<?php $__env->startSection('title', 'Leave Requests'); ?>
<?php $__env->startSection('page-title', 'Leave Requests'); ?>

<?php $__env->startSection('content'); ?>
<div class="row mb-3">
    <div class="col-md-12">
        <a href="<?php echo e(route('leaves.my')); ?>" class="btn btn-primary">
            <i class="fas fa-plus"></i> Apply Leave
        </a>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->isHrManager() || auth()->user()->isSuperAdmin()): ?>
        <a href="<?php echo e(route('leaves.create-for-employee')); ?>" class="btn btn-success">
            <i class="fas fa-user-plus"></i> Create Leave for Employee
        </a>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <form method="GET" class="row g-3">
            <div class="col-md-2">
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="pending" <?php echo e(request('status') == 'pending' ? 'selected' : ''); ?>>Pending</option>
                    <option value="hr_approved" <?php echo e(request('status') == 'hr_approved' ? 'selected' : ''); ?>>HR Approved</option>
                    <option value="approved" <?php echo e(request('status') == 'approved' ? 'selected' : ''); ?>>Approved</option>
                    <option value="rejected" <?php echo e(request('status') == 'rejected' ? 'selected' : ''); ?>>Rejected</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="leave_type_id" class="form-select">
                    <option value="">All Types</option>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $leaveTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <option value="<?php echo e($type->id); ?>" <?php echo e(request('leave_type_id') == $type->id ? 'selected' : ''); ?>><?php echo e($type->name); ?></option>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-secondary">Filter</button>
                <a href="<?php echo e(route('leaves.index')); ?>" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Employee</th>
                        <th>Department</th>
                        <th>Leave Type</th>
                        <th>Dates</th>
                        <th>Days</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $leaveRequests; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $leave): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <tr>
                            <td>#<?php echo e($leave->id); ?></td>
                            <td>
                                <strong><?php echo e($leave->employee->user->name); ?></strong>
                                <br>
                                <small class="text-muted"><?php echo e($leave->employee->employee_number); ?></small>
                            </td>
                            <td><?php echo e($leave->employee->department->name ?? 'N/A'); ?></td>
                            <td><?php echo e($leave->leaveType->name); ?></td>
                            <td>
                                <?php echo e($leave->start_date->format('d M')); ?> - <?php echo e($leave->end_date->format('d M')); ?>

                            </td>
                            <td>
                                <span class="badge bg-info"><?php echo e($leave->total_days); ?></span>
                            </td>
                            <td>
                                <span class="badge bg-<?php echo e($leave->status === 'pending' ? 'warning' : ($leave->status === 'approved' ? 'success' : ($leave->status === 'rejected' ? 'danger' : 'info'))); ?>">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($leave->status === 'pending'): ?> Pending
                                    <?php elseif($leave->status === 'hr_approved'): ?> HR Approved
                                    <?php elseif($leave->status === 'approved'): ?> Approved
                                    <?php elseif($leave->status === 'rejected'): ?> Rejected
                                    <?php elseif($leave->status === 'cancelled'): ?> Cancelled
                                    <?php else: ?> <?php echo e(ucfirst($leave->status)); ?>

                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </span>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#viewModal<?php echo e($leave->id); ?>">
                                    <i class="fas fa-eye"></i>
                                </button>
                                
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($leave->status === 'pending' && (auth()->user()->isHrManager() || auth()->user()->isSuperAdmin())): ?>
                                <form action="<?php echo e(route('leaves.hr-approve', $leave)); ?>" method="POST" class="d-inline">
                                    <?php echo csrf_field(); ?>
                                    <button type="submit" class="btn btn-sm btn-success" title="HR Approve">
                                        <i class="fas fa-check"></i>
                                    </button>
                                </form>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($leave->status === 'hr_approved'): ?>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->isManager() && $leave->employee->user->role->name === 'employee'): ?>
                                    <form action="<?php echo e(route('leaves.manager-approve', $leave)); ?>" method="POST" class="d-inline">
                                        <?php echo csrf_field(); ?>
                                        <button type="submit" class="btn btn-sm btn-primary" title="Manager Approve">
                                            <i class="fas fa-check-double"></i>
                                        </button>
                                    </form>
                                    <?php elseif(auth()->user()->isDM() && in_array($leave->employee->user->role->name, ['manager', 'dm'])): ?>
                                    <form action="<?php echo e(route('leaves.manager-approve', $leave)); ?>" method="POST" class="d-inline">
                                        <?php echo csrf_field(); ?>
                                        <button type="submit" class="btn btn-sm btn-primary" title="DM Approve">
                                            <i class="fas fa-check-double"></i>
                                        </button>
                                    </form>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(in_array($leave->status, ['pending', 'hr_approved'])): ?>
                                <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal<?php echo e($leave->id); ?>" title="Reject">
                                    <i class="fas fa-times"></i>
                                </button>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </td>
                        </tr>
                        
                        <!-- View Modal -->
                        <div class="modal fade" id="viewModal<?php echo e($leave->id); ?>" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Leave Request #<?php echo e($leave->id); ?></h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <p><strong>Employee:</strong> <?php echo e($leave->employee->user->name); ?></p>
                                                <p><strong>Employee ID:</strong> <?php echo e($leave->employee->employee_number); ?></p>
                                                <p><strong>Department:</strong> <?php echo e($leave->employee->department->name ?? 'N/A'); ?></p>
                                            </div>
                                            <div class="col-md-6">
                                                <p><strong>Leave Type:</strong> <?php echo e($leave->leaveType->name); ?></p>
                                                <p><strong>Calendar Days:</strong> <?php echo e($leave->calendar_days); ?></p>
                                                <p><strong>Working Days:</strong> <?php echo e($leave->total_days); ?></p>
                                            </div>
                                        </div>
                                        <hr>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <p><strong>Start Date:</strong> <?php echo e($leave->start_date->format('d M Y')); ?></p>
                                                <p><strong>End Date:</strong> <?php echo e($leave->end_date->format('d M Y')); ?></p>
                                            </div>
                                            <div class="col-md-6">
                                                <p><strong>Exit/Reentry:</strong> <?php echo e($leave->exit_reentry_required ? 'Yes' : 'No'); ?></p>
                                                <p><strong>Flight Ticket:</strong> <?php echo e($leave->flight_ticket_required ? 'Yes' : 'No'); ?></p>
                                            </div>
                                        </div>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($leave->reason): ?>
                                        <hr>
                                        <p><strong>Reason:</strong></p>
                                        <p><?php echo e($leave->reason); ?></p>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        <hr>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <p><strong>Status:</strong> 
                                                    <span class="badge bg-<?php echo e($leave->status === 'pending' ? 'warning' : ($leave->status === 'approved' ? 'success' : ($leave->status === 'rejected' ? 'danger' : 'info'))); ?>">
                                                        <?php echo e(ucfirst($leave->status)); ?>

                                                    </span>
                                                </p>
                                            </div>
                                            <div class="col-md-6">
                                                <p><strong>Created:</strong> <?php echo e($leave->created_at->format('d M Y H:i')); ?></p>
                                            </div>
                                        </div>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($leave->hr_approved_at): ?>
                                        <p><strong>HR Approved By:</strong> <?php echo e($leave->hrApprover->name ?? 'N/A'); ?> on <?php echo e($leave->hr_approved_at->format('d M Y H:i')); ?></p>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($leave->manager_approved_at): ?>
                                        <p><strong>Manager Approved By:</strong> <?php echo e($leave->managerApprover->name ?? 'N/A'); ?> on <?php echo e($leave->manager_approved_at->format('d M Y H:i')); ?></p>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($leave->rejection_reason): ?>
                                        <div class="alert alert-danger mt-2">
                                            <strong>Rejection Reason:</strong> <?php echo e($leave->rejection_reason); ?>

                                        </div>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Reject Modal -->
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(in_array($leave->status, ['pending', 'hr_approved'])): ?>
                        <div class="modal fade" id="rejectModal<?php echo e($leave->id); ?>" tabindex="-1">
                            <div class="modal-dialog">
                                <form action="<?php echo e(route('leaves.reject', $leave)); ?>" method="POST">
                                    <?php echo csrf_field(); ?>
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Reject Leave Request #<?php echo e($leave->id); ?></h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label class="form-label">Rejection Reason <span class="text-danger">*</span></label>
                                                <textarea name="rejection_reason" class="form-control" required rows="3" placeholder="Enter reason for rejection..."></textarea>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-danger">Reject</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php echo e($leaveRequests->links()); ?>

    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp1\htdocs\hr-system\resources\views\leaves\index.blade.php ENDPATH**/ ?>