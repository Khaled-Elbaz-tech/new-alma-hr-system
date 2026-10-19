<?php $__env->startSection('title', 'Leave History Report'); ?>
<?php $__env->startSection('page-title', 'Leave History Report'); ?>

<?php $__env->startSection('content'); ?>
<div class="row mb-3">
    <div class="col-md-12">
        <form method="GET" class="row g-3">
            <div class="col-md-2">
                <label class="form-label">Employee</label>
                <select name="employee_id" class="form-select">
                    <option value="">Select Employee</option>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $employees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $emp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <option value="<?php echo e($emp->id); ?>" <?php echo e(request('employee_id') == $emp->id ? 'selected' : ''); ?>>
                            <?php echo e($emp->user->name); ?> (<?php echo e($emp->employee_number); ?>)
                        </option>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Year</label>
                <select name="year" class="form-select">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php for($y = date('Y') - 5; $y <= date('Y'); $y++): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <option value="<?php echo e($y); ?>" <?php echo e($year == $y ? 'selected' : ''); ?>><?php echo e($y); ?></option>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endfor; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Leave Type</label>
                <select name="leave_type_id" class="form-select">
                    <option value="">All Types</option>
                    <?php
                        $leaveTypes = \App\Models\LeaveType::where('is_active', true)->get();
                    ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $leaveTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <option value="<?php echo e($type->id); ?>" <?php echo e(request('leave_type_id') == $type->id ? 'selected' : ''); ?>>
                            <?php echo e($type->name); ?>

                        </option>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="pending" <?php echo e(request('status') == 'pending' ? 'selected' : ''); ?>>Pending</option>
                    <option value="hr_approved" <?php echo e(request('status') == 'hr_approved' ? 'selected' : ''); ?>>HR Approved</option>
                    <option value="approved" <?php echo e(request('status') == 'approved' ? 'selected' : ''); ?>>Approved</option>
                    <option value="rejected" <?php echo e(request('status') == 'rejected' ? 'selected' : ''); ?>>Rejected</option>
                    <option value="cancelled" <?php echo e(request('status') == 'cancelled' ? 'selected' : ''); ?>>Cancelled</option>
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-secondary">Filter</button>
                <a href="<?php echo e(route('reports.leave-history')); ?>" class="btn btn-outline-secondary ms-2">Reset</a>
            </div>
            <div class="col-md-2 text-end d-flex align-items-end justify-content-end">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($selectedEmployee): ?>
                <a href="<?php echo e(route('reports.export.leave-history', ['employee_id' => $selectedEmployee->id, 'year' => $year])); ?>" class="btn btn-primary">
                    <i class="fas fa-file-csv"></i> Export
                </a>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </form>
    </div>
</div>

<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($selectedEmployee): ?>
<div class="card mb-3">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <h5 class="mb-0"><i class="fas fa-history"></i> Leave History</h5>
            <p class="mb-0 text-muted"><?php echo e($selectedEmployee->user->name); ?> (<?php echo e($selectedEmployee->employee_number); ?>) - <?php echo e($year); ?></p>
        </div>
    </div>
    <div class="card-body">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($leaveRequests->count() > 0): ?>
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Leave Type</th>
                            <th>Dates</th>
                            <th>Working Days</th>
                            <th>Calendar Days</th>
                            <th>Status</th>
                            <th>HR Approver</th>
                            <th>Manager Approver</th>
                            <th>Created By</th>
                            <th>Created At</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $leaveRequests; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $leave): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <tr>
                            <td><?php echo e($leave->id); ?></td>
                            <td>
                                <strong><?php echo e($leave->leaveType->name); ?></strong>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($leave->is_historical): ?>
                                    <span class="badge bg-warning ms-1">Historical</span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </td>
                            <td>
                                <?php echo e($leave->start_date->format('d M')); ?> - <?php echo e($leave->end_date->format('d M Y')); ?>

                            </td>
                            <td><span class="badge bg-primary"><?php echo e($leave->total_days); ?></span></td>
                            <td><?php echo e($leave->calendar_days); ?></td>
                            <td>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($leave->status === 'pending'): ?>
                                    <span class="badge bg-warning">Pending</span>
                                <?php elseif($leave->status === 'hr_approved'): ?>
                                    <span class="badge bg-info">HR Approved</span>
                                <?php elseif($leave->status === 'approved'): ?>
                                    <span class="badge bg-success">Approved</span>
                                <?php elseif($leave->status === 'rejected'): ?>
                                    <span class="badge bg-danger">Rejected</span>
                                <?php elseif($leave->status === 'cancelled'): ?>
                                    <span class="badge bg-secondary">Cancelled</span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </td>
                            <td>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($leave->hrApprover): ?>
                                    <?php echo e($leave->hrApprover->name); ?>

                                    <br><small class="text-muted"><?php echo e($leave->hr_approved_at->format('d M Y')); ?></small>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </td>
                            <td>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($leave->managerApprover): ?>
                                    <?php echo e($leave->managerApprover->name); ?>

                                    <br><small class="text-muted"><?php echo e($leave->manager_approved_at->format('d M Y')); ?></small>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </td>
                            <td>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($leave->creator): ?>
                                    <?php echo e($leave->creator->name); ?>

                                <?php else: ?>
                                    Self
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </td>
                            <td><?php echo e($leave->created_at->format('d M Y')); ?></td>
                        </tr>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Summary -->
            <div class="row mt-3">
                <div class="col-md-12">
                    <div class="alert alert-light">
                        <h6>Summary for <?php echo e($year); ?></h6>
                        <div class="row">
                            <div class="col-md-2">
                                <strong>Pending:</strong> <?php echo e($leaveRequests->where('status', 'pending')->count()); ?>

                            </div>
                            <div class="col-md-2">
                                <strong>Approved:</strong> <?php echo e($leaveRequests->where('status', 'approved')->count()); ?>

                            </div>
                            <div class="col-md-2">
                                <strong>HR Approved:</strong> <?php echo e($leaveRequests->where('status', 'hr_approved')->count()); ?>

                            </div>
                            <div class="col-md-2">
                                <strong>Rejected:</strong> <?php echo e($leaveRequests->where('status', 'rejected')->count()); ?>

                            </div>
                            <div class="col-md-2">
                                <strong>Total Days:</strong> <?php echo e($leaveRequests->sum('total_days')); ?>

                            </div>
                            <div class="col-md-2">
                                <strong>Exit/Reentry:</strong> <?php echo e($leaveRequests->where('exit_reentry_required', true)->count()); ?>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> No leave requests found for this employee in <?php echo e($year); ?>.
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
</div>

<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(request('employee_id')): ?>
<?php
    // Group by year for previous years
    $allLeaveRequests = \App\Models\LeaveRequest::where('employee_id', request('employee_id'))
        ->whereYear('start_date', '<=', date('Y'))
        ->with(['leaveType', 'hrApprover', 'managerApprover', 'creator'])
        ->orderBy('start_date', 'desc')
        ->get()
        ->groupBy(function($item) {
            return $item->start_date->year;
        });
?>

<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($allLeaveRequests->count() > 1): ?>
<div class="card mt-4">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-layer-group"></i> All Years Overview</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-sm">
                <thead>
                    <tr>
                        <th>Year</th>
                        <th>Total Leaves</th>
                        <th>Total Days</th>
                        <th>Approved</th>
                        <th>Rejected</th>
                        <th>Pending</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $allLeaveRequests; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $yr => $requests): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <tr>
                        <td>
                            <a href="<?php echo e(route('reports.leave-history', ['employee_id' => request('employee_id'), 'year' => $yr])); ?>">
                                <strong><?php echo e($yr); ?></strong>
                            </a>
                        </td>
                        <td><?php echo e($requests->count()); ?></td>
                        <td><?php echo e($requests->sum('total_days')); ?></td>
                        <td><?php echo e($requests->where('status', 'approved')->count()); ?></td>
                        <td><?php echo e($requests->where('status', 'rejected')->count()); ?></td>
                        <td><?php echo e($requests->where('status', 'pending')->count()); ?></td>
                    </tr>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

<?php else: ?>
<div class="alert alert-info">
    <i class="fas fa-info-circle"></i> Please select an employee to view their leave history.
</div>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp1\htdocs\hr-system\resources\views\reports\leave-history.blade.php ENDPATH**/ ?>