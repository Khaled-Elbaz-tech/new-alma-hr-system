<?php $__env->startSection('title', 'My Profile'); ?>
<?php $__env->startSection('page-title', 'My Profile'); ?>

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
                        <p class="text-muted mb-0"><?php echo e($employee->employee_number); ?></p>
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
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#dynamic-fields" type="button">
            <i class="fas fa-list"></i> Dynamic Fields
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

    <!-- Dynamic Fields Tab -->
    <div class="tab-pane fade" id="dynamic-fields">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-list"></i> Dynamic Fields</h5>
            </div>
            <div class="card-body">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($fields->count() > 0): ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $fields; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $section => $sectionFields): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <div class="mb-4">
                            <h6 class="text-uppercase text-muted mb-3 border-bottom pb-2"><?php echo e(ucfirst($section)); ?></h6>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th style="width: 30%;">Field</th>
                                            <th>Status / Value</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $sectionFields; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $field): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                            <tr>
                                                <td><strong><?php echo e($field->field_label); ?></strong></td>
                                                <td>
                                                    <?php
                                                        $fieldValue = $employee->fieldValues->where('field_id', $field->id)->first();
                                                    ?>
                                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($field->field_type === 'attachment'): ?>
                                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($fieldValue && $fieldValue->value): ?>
                                                            <a href="<?php echo e(asset('storage/' . $fieldValue->value)); ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                                                <i class="fas fa-file-download"></i> Download
                                                            </a>
                                                        <?php else: ?>
                                                            <span class="text-muted">No file uploaded</span>
                                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                    <?php elseif($field->field_type === 'checkbox'): ?>
                                                        <?php echo e($fieldValue && $fieldValue->value ? 'Yes' : 'No'); ?>

                                                    <?php elseif($field->field_type === 'yes_no'): ?>
                                                        <?php echo e($fieldValue ? $fieldValue->value : 'N/A'); ?>

                                                    <?php else: ?>
                                                        <?php echo e($fieldValue ? $fieldValue->value : 'N/A'); ?>

                                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                <?php else: ?>
                    <p class="text-muted">No dynamic fields have been configured for your profile.</p>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
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
                    <p class="text-muted">No leave balance data available. Please contact HR.</p>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Leave History Tab -->
    <div class="tab-pane fade" id="leave-history">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-history"></i> Leave History</h5>
                <a href="<?php echo e(route('leaves.my')); ?>" class="btn btn-sm btn-primary">
                    <i class="fas fa-plus"></i> Apply Leave
                </a>
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
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp1\htdocs\hr-system\resources\views\employees\my-profile.blade.php ENDPATH**/ ?>