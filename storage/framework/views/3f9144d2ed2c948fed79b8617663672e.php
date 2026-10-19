<?php $__env->startSection('title', 'Employee Profile'); ?>
<?php $__env->startSection('page-title', 'Employee Profile - ' . $employee->user->name); ?>

<?php $__env->startSection('content'); ?>
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="bg-primary rounded-circle p-3 me-3">
                        <i class="fas fa-user text-white fa-2x"></i>
                    </div>
                    <div>
                        <h4 mb-0><?php echo e($employee->user->name); ?></h4>
                        <p class="text-muted mb-0"><?php echo e($employee->employee_number); ?> | <?php echo e($employee->department->name ?? 'No Department'); ?></p>
                    </div>
                    <div class="ms-auto">
                        <span class="badge bg-<?php echo e($employee->employment_status === 'active' ? 'success' : 'secondary'); ?>">
                            <?php echo e(ucfirst($employee->employment_status)); ?>

                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tabs -->
<ul class="nav nav-tabs mb-4" role="tablist">
    <li class="nav-item">
        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#personal" type="button">
            <i class="fas fa-user"></i> Personal Info
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#employment" type="button">
            <i class="fas fa-briefcase"></i> Employment Info
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#leave-summary" type="button">
            <i class="fas fa-calendar-check"></i> Leave Summary
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#leave-history" type="button">
            <i class="fas fa-history"></i> Leave History
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#vacation-history" type="button">
            <i class="fas fa-book"></i> Vacation History
        </button>
    </li>
</ul>

<!-- Tab Content -->
<div class="tab-content">
    <!-- Personal Info Tab -->
    <div class="tab-pane fade show active" id="personal">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-user"></i> Personal Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Full Name:</strong> <?php echo e($employee->user->name); ?></p>
                        <p><strong>Email:</strong> <?php echo e($employee->user->email); ?></p>
                        <p><strong>Employee Number:</strong> <?php echo e($employee->employee_number); ?></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Role:</strong> <?php echo e($employee->user->role->display_name ?? 'N/A'); ?></p>
                        <p><strong>Status:</strong> 
                            <span class="badge bg-<?php echo e($employee->employment_status === 'active' ? 'success' : 'secondary'); ?>">
                                <?php echo e(ucfirst($employee->employment_status)); ?>

                            </span>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Employment Info Tab -->
    <div class="tab-pane fade" id="employment">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-briefcase"></i> Employment Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Department:</strong> <?php echo e($employee->department->name ?? 'Not Assigned'); ?></p>
                        <p><strong>Joining Date:</strong> <?php echo e($employee->joining_date ? $employee->joining_date->format('d M Y') : 'N/A'); ?></p>
                        <p><strong>Annual Leave Entitlement:</strong> <?php echo e($employee->annual_leave_entitlement); ?> days</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Contract Start:</strong> <?php echo e($employee->contract_start_date ? $employee->contract_start_date->format('d M Y') : 'N/A'); ?></p>
                        <p><strong>Contract End:</strong> <?php echo e($employee->contract_end_date ? $employee->contract_end_date->format('d M Y') : 'N/A'); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Leave Summary Tab -->
    <div class="tab-pane fade" id="leave-summary">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-calendar-check"></i> Leave Balance (Yearly View)</h5>
            </div>
            <div class="card-body">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($vacationHistory)): ?>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Year</th>
                                    <th>Entitled</th>
                                    <th>Used</th>
                                    <th>Balance</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $vacationHistory; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $balance): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                    <tr>
                                        <td><?php echo e($balance['year']); ?></td>
                                        <td><?php echo e($balance['entitled_days']); ?></td>
                                        <td><?php echo e($balance['used_days']); ?></td>
                                        <td class="<?php echo e($balance['balance'] > 0 ? 'text-success' : 'text-danger'); ?>">
                                            <strong><?php echo e($balance['balance']); ?></strong>
                                        </td>
                                    </tr>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-muted">No leave balance data available.</p>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Leave History Tab -->
    <div class="tab-pane fade" id="leave-history">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-history"></i> Leave History</h5>
            </div>
            <div class="card-body">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($employee->leaveRequests->count() > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Type</th>
                                    <th>Start Date</th>
                                    <th>End Date</th>
                                    <th>Days</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $employee->leaveRequests; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $leave): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                    <tr>
                                        <td><?php echo e($leave->leaveType->name); ?></td>
                                        <td><?php echo e($leave->start_date->format('d M Y')); ?></td>
                                        <td><?php echo e($leave->end_date->format('d M Y')); ?></td>
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
                <?php else: ?>
                    <p class="text-muted">No leave requests found.</p>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Vacation History Tab -->
    <div class="tab-pane fade show active" id="vacation-history">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-book"></i> Vacation History</h5>
            </div>
            <div class="card-body">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($vacationHistory) && count($vacationHistory) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Year</th>
                                    <th>Entitled Days</th>
                                    <th>Used Days</th>
                                    <th>Carry Forward</th>
                                    <th>Balance</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                    $cumulativeBalance = 0;
                                ?>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $vacationHistory; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $yearData): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                    <?php
                                        $carryForward = $cumulativeBalance;
                                        $cumulativeBalance += round($yearData['entitled_days'] - $yearData['used_days'], 2);
                                    ?>
                                    <tr>
                                        <td><?php echo e($yearData['year']); ?></td>
                                        <td><?php echo e($yearData['entitled_days']); ?></td>
                                        <td><?php echo e($yearData['used_days']); ?></td>
                                        <td class="<?php echo e($carryForward > 0 ? 'text-success' : 'text-muted'); ?>">
                                            <?php echo e($carryForward > 0 ? $carryForward : '-'); ?>

                                        </td>
                                        <td class="<?php echo e($cumulativeBalance > 0 ? 'text-success' : 'text-danger'); ?>">
                                            <strong><?php echo e($cumulativeBalance); ?></strong>
                                        </td>
                                    </tr>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-muted">No vacation history found.</p>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="mt-3">
    <a href="<?php echo e(route('team.employees')); ?>" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Back to Team
    </a>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp1\htdocs\hr-system\resources\views\employees\team-show.blade.php ENDPATH**/ ?>