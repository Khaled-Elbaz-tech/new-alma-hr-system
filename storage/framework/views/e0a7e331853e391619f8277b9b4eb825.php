<?php $__env->startSection('title', 'Leave Statistics'); ?>
<?php $__env->startSection('page-title', 'Leave Statistics'); ?>

<?php $__env->startSection('content'); ?>
<!-- Year Filter -->
<div class="row mb-3">
    <div class="col-md-6">
        <form method="GET" class="d-flex gap-2 align-items-center">
            <label class="mb-0">Year:</label>
            <select name="year" class="form-select" style="width: auto;">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php for($y = date('Y') - 2; $y <= date('Y'); $y++): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <option value="<?php echo e($y); ?>" <?php echo e($year == $y ? 'selected' : ''); ?>><?php echo e($y); ?></option>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endfor; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </select>
            <button type="submit" class="btn btn-secondary">Filter</button>
        </form>
    </div>
    <div class="col-md-6 text-end">
        <span class="text-muted"><i class="fas fa-lock"></i> Read-only</span>
    </div>
</div>

<!-- Summary Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <p class="mb-0">Exit Reentry</p>
                        <h3><?php echo e($exitReentryCount); ?></h3>
                    </div>
                    <i class="fas fa-passport fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <p class="mb-0">Flight Tickets</p>
                        <h3><?php echo e($ticketCount); ?></h3>
                    </div>
                    <i class="fas fa-ticket-alt fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <p class="mb-0">Total Approved</p>
                        <h3><?php echo e(collect($monthlyStats)->sum('approved')); ?></h3>
                    </div>
                    <i class="fas fa-check-circle fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <p class="mb-0">Total Rejected</p>
                        <h3><?php echo e(collect($monthlyStats)->sum('rejected')); ?></h3>
                    </div>
                    <i class="fas fa-times-circle fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row 1 -->
<div class="row">
    <!-- Monthly Leave Usage Chart -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-chart-bar"></i> Monthly Leave Usage</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <thead class="table-light">
                            <tr>
                                <th>Month</th>
                                <th>Approved</th>
                                <th>Visual</th>
                                <th>Rejected</th>
                                <th>Pending</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']; ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php for($m = 1; $m <= 12; $m++): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <?php $total = $monthlyStats[$m]['total'] ?? 0; $max = 20; $width = min(($total / $max) * 100, 100); ?>
                            <tr>
                                <td><?php echo e($months[$m-1]); ?></td>
                                <td>
                                    <span class="badge bg-success"><?php echo e($monthlyStats[$m]['approved'] ?? 0); ?></span>
                                </td>
                                <td>
                                    <div class="progress" style="height: 20px;">
                                        <div class="progress-bar bg-success" style="width: <?php echo e((($monthlyStats[$m]['approved'] ?? 0) / $max) * 100); ?>%"></div>
                                        <div class="progress-bar bg-danger" style="width: <?php echo e((($monthlyStats[$m]['rejected'] ?? 0) / $max) * 100); ?>%"></div>
                                        <div class="progress-bar bg-warning" style="width: <?php echo e((($monthlyStats[$m]['pending'] ?? 0) / $max) * 100); ?>%"></div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-danger"><?php echo e($monthlyStats[$m]['rejected'] ?? 0); ?></span>
                                </td>
                                <td>
                                    <span class="badge bg-warning"><?php echo e($monthlyStats[$m]['pending'] ?? 0); ?></span>
                                </td>
                            </tr>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endfor; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <div class="mt-2">
                    <small class="text-muted">
                        <span class="badge bg-success">Approved</span>
                        <span class="badge bg-danger">Rejected</span>
                        <span class="badge bg-warning">Pending</span>
                    </small>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Leave Type Distribution -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-chart-pie"></i> Leave Type Distribution</h5>
            </div>
            <div class="card-body">
                <?php $totalDays = collect($leaveTypeStats)->sum('days'); ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($totalDays > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>Leave Type</th>
                                    <th>Count</th>
                                    <th>Days</th>
                                    <th>Percentage</th>
                                    <th>Visual</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $leaveTypeStats; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <?php $percentage = $totalDays > 0 ? round(($stat['days'] / $totalDays) * 100, 1) : 0; ?>
                                <tr>
                                    <td><strong><?php echo e($stat['name']); ?></strong></td>
                                    <td><?php echo e($stat['count']); ?></td>
                                    <td><?php echo e($stat['days']); ?></td>
                                    <td><?php echo e($percentage); ?>%</td>
                                    <td>
                                        <div class="progress" style="height: 15px;">
                                            <div class="progress-bar bg-primary" style="width: <?php echo e($percentage); ?>%"></div>
                                        </div>
                                    </td>
                                </tr>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            </tbody>
                            <tfoot>
                                <tr class="table-light">
                                    <td><strong>Total</strong></td>
                                    <td><strong><?php echo e(collect($leaveTypeStats)->sum('count')); ?></strong></td>
                                    <td><strong><?php echo e($totalDays); ?></strong></td>
                                    <td>100%</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-muted">No leave data available for this year.</p>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row 2 -->
<div class="row">
    <!-- Sick vs Annual Trends -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-heartbeat"></i> Sick vs Annual Leave Trends</h5>
            </div>
            <div class="card-body">
                <?php 
                    $annualType = collect($leaveTypeStats)->firstWhere('name', 'Annual Leave');
                    $sickType = collect($leaveTypeStats)->firstWhere('name', 'Sick Leave');
                    $annualDays = $annualType['days'] ?? 0;
                    $sickDays = $sickType['days'] ?? 0;
                ?>
                <div class="row text-center mb-4">
                    <div class="col-md-6">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <h4><?php echo e($annualDays); ?></h4>
                                <p class="mb-0">Annual Leave Days</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card bg-warning text-white">
                            <div class="card-body">
                                <h4><?php echo e($sickDays); ?></h4>
                                <p class="mb-0">Sick Leave Days</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Monthly comparison -->
                <table class="table table-bordered table-sm">
                    <thead class="table-light">
                        <tr>
                            <th>Month</th>
                            <th>Annual</th>
                            <th>Annual Bar</th>
                            <th>Sick</th>
                            <th>Sick Bar</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                            $annualLeaveTypeId = \App\Models\LeaveType::where('name', 'Annual Leave')->first()?->id;
                            $sickLeaveTypeId = \App\Models\LeaveType::where('name', 'Sick Leave')->first()?->id;
                        ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php for($m = 1; $m <= 12; $m++): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <?php 
                            $annualCount = \App\Models\LeaveRequest::whereYear('start_date', $year)->whereMonth('start_date', $m)->where('leave_type_id', $annualLeaveTypeId)->where('status', 'approved')->sum('total_days');
                            $sickCount = \App\Models\LeaveRequest::whereYear('start_date', $year)->whereMonth('start_date', $m)->where('leave_type_id', $sickLeaveTypeId)->where('status', 'approved')->sum('total_days');
                            $maxVal = max($annualCount, $sickCount, 1);
                        ?>
                        <tr>
                            <td><?php echo e($months[$m-1]); ?></td>
                            <td><?php echo e($annualCount); ?></td>
                            <td>
                                <div class="progress" style="height: 15px;">
                                    <div class="progress-bar bg-success" style="width: <?php echo e(($annualCount / $maxVal) * 100); ?>%"></div>
                                </div>
                            </td>
                            <td><?php echo e($sickCount); ?></td>
                            <td>
                                <div class="progress" style="height: 15px;">
                                    <div class="progress-bar bg-warning" style="width: <?php echo e(($sickCount / $maxVal) * 100); ?>%"></div>
                                </div>
                            </td>
                        </tr>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endfor; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Department Absence Comparison -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-building"></i> Department Absence Comparison</h5>
            </div>
            <div class="card-body">
                <?php 
                    $departments = \App\Models\Department::with('employees')->get();
                    $deptData = [];
                    foreach($departments as $dept) {
                        $employeeIds = $dept->employees->pluck('id');
                        $days = \App\Models\LeaveRequest::whereIn('employee_id', $employeeIds)->whereYear('start_date', $year)->where('status', 'approved')->sum('total_days');
                        $deptData[] = ['name' => $dept->name, 'days' => $days, 'count' => $dept->employees_count];
                    }
                    $deptData = collect($deptData)->sortByDesc('days');
                    $maxDeptDays = $deptData->max('days') ?: 1;
                ?>
                
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($deptData->count() > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>Department</th>
                                    <th>Employees</th>
                                    <th>Leave Days</th>
                                    <th>Avg/Employee</th>
                                    <th>Visual</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $deptData; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dept): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <?php $avg = $dept['count'] > 0 ? round($dept['days'] / $dept['count'], 1) : 0; ?>
                                <tr>
                                    <td><strong><?php echo e($dept['name']); ?></strong></td>
                                    <td><?php echo e($dept['count']); ?></td>
                                    <td><?php echo e($dept['days']); ?></td>
                                    <td><?php echo e($avg); ?></td>
                                    <td>
                                        <div class="progress" style="height: 15px;">
                                            <div class="progress-bar bg-info" style="width: <?php echo e(($dept['days'] / $maxDeptDays) * 100); ?>%"></div>
                                        </div>
                                    </td>
                                </tr>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-muted">No department data available.</p>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Summary -->
<div class="row">
    <div class="col-md-12">
        <div class="alert alert-light">
            <h5><i class="fas fa-info-circle"></i> Statistics Summary for <?php echo e($year); ?></h5>
            <div class="row">
                <div class="col-md-3">
                    <strong>Total Leave Requests:</strong> <?php echo e(collect($monthlyStats)->sum('total')); ?>

                </div>
                <div class="col-md-3">
                    <strong>Approval Rate:</strong> <?php echo e(collect($monthlyStats)->sum('total') > 0 ? round((collect($monthlyStats)->sum('approved') / collect($monthlyStats)->sum('total')) * 100, 1) : 0); ?>%
                </div>
                <div class="col-md-3">
                    <strong>Rejection Rate:</strong> <?php echo e(collect($monthlyStats)->sum('total') > 0 ? round((collect($monthlyStats)->sum('rejected') / collect($monthlyStats)->sum('total')) * 100, 1) : 0); ?>%
                </div>
                <div class="col-md-3">
                    <strong>Total Days Taken:</strong> <?php echo e(collect($leaveTypeStats)->sum('days')); ?>

                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp1\htdocs\hr-system\resources\views\reports\leave-statistics.blade.php ENDPATH**/ ?>