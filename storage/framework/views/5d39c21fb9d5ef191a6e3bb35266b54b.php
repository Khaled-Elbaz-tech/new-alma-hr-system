<?php $__env->startSection('title', 'My Leaves'); ?>
<?php $__env->startSection('page-title', 'My Leave Requests'); ?>

<?php $__env->startSection('content'); ?>
<div class="row mb-3">
    <div class="col-md-12">
        <button class="btn btn-primary" type="button" data-bs-toggle="collapse" data-bs-target="#leaveFormCollapse">
            <i class="fas fa-plus"></i> Apply for Leave
        </button>
    </div>
</div>

<!-- Collapsible Leave Form -->
<div class="collapse mb-3 <?php echo e($showForm ? 'show' : ''); ?>" id="leaveFormCollapse">
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-calendar-plus"></i> Leave Request Form</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="<?php echo e(route('leaves.store')); ?>" id="leaveForm">
                <?php echo csrf_field(); ?>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Leave Type <span class="text-danger">*</span></label>
                        <select name="leave_type_id" class="form-select" required id="leaveTypeSelect">
                            <option value="">Select Leave Type</option>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $leaveTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <option value="<?php echo e($type->id); ?>" 
                                        data-deductible="<?php echo e($type->deduct_from_balance); ?>"
                                        data-requires-attachment="<?php echo e($type->requires_attachment); ?>">
                                    <?php echo e($type->name); ?> <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($type->default_days): ?>(<?php echo e($type->default_days); ?> days)<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </option>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Start Date <span class="text-danger">*</span></label>
                        <input type="date" name="start_date" class="form-control" required id="startDate" min="<?php echo e(date('Y-m-d')); ?>">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">End Date <span class="text-danger">*</span></label>
                        <input type="date" name="end_date" class="form-control" required id="endDate">
                    </div>
                    <div class="col-md-2 mb-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100" id="submitBtn">
                            <i class="fas fa-paper-plane"></i> Submit
                        </button>
                    </div>
                </div>

                <div class="alert alert-info" id="calculationResult" style="display: none;">
                    <h6><i class="fas fa-calculator"></i> Leave Calculation</h6>
                    <div class="row">
                        <div class="col-md-3">
                            <p class="mb-1"><strong>Calendar Days:</strong> <span id="calendarDays">0</span></p>
                            <p class="mb-1"><strong>Working Days:</strong> <span id="workingDays" class="text-primary">0</span></p>
                        </div>
                        <div class="col-md-3">
                            <p class="mb-1"><strong>Weekends:</strong> <span id="weekendsExcluded">0</span></p>
                            <p class="mb-1"><strong>Holidays:</strong> <span id="holidaysExcluded">0</span></p>
                        </div>
                        <div class="col-md-3">
                            <div id="balanceCheck">
                                <p class="mb-1"><strong>Current Balance:</strong> <span id="currentBalance">0</span> days</p>
                                <p class="mb-1"><strong>After Approval:</strong> <span id="remainingBalance">0</span> days</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="alert alert-danger mt-0" id="insufficientBalance" style="display: none;">
                                <i class="fas fa-exclamation-triangle"></i> Insufficient balance!
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Reason (Optional)</label>
                        <textarea name="reason" class="form-control" rows="2" placeholder="Enter reason for leave..."></textarea>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div id="businessLeaveSection">
                            <div class="alert alert-warning py-2 px-3 mb-2">
                                <i class="fas fa-briefcase"></i> <strong>Business Trip Requirements</strong>
                            </div>
                            <div class="form-check d-inline-block me-3">
                                <input type="checkbox" name="exit_reentry_required" class="form-check-input" id="exitReentry" value="1">
                                <label class="form-check-label" for="exitReentry">Exit / Re-entry Visa</label>
                            </div>
                            <div class="form-check d-inline-block me-3">
                                <input type="checkbox" name="flight_ticket_required" class="form-check-input" id="flightTicket" value="1">
                                <label class="form-check-label" for="flightTicket">Flight Ticket</label>
                            </div>
                            <div class="form-check d-inline-block me-3">
                                <input type="checkbox" name="hotel_booking_required" class="form-check-input" id="hotelBooking" value="1">
                                <label class="form-check-label" for="hotelBooking">Hotel Booking</label>
                            </div>
                            <div class="form-check d-inline-block">
                                <input type="checkbox" name="transfer_required" class="form-check-input" id="transfer" value="1">
                                <label class="form-check-label" for="transfer">Transfer</label>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="attachmentSection" style="display: none;">
                    <div class="mb-3">
                        <label class="form-label">Attachment</label>
                        <input type="file" name="attachment" class="form-control" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Leave History Table -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-history"></i> My Leave History</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Leave Type</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Days</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $leaveRequests; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $leave): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <tr>
                            <td><?php echo e($leave->leaveType->name); ?></td>
                            <td><?php echo e($leave->start_date->format('d M Y')); ?></td>
                            <td><?php echo e($leave->end_date->format('d M Y')); ?></td>
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
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($leave->status === 'pending'): ?>
                                    <form action="<?php echo e(route('leaves.cancel', $leave)); ?>" method="POST" class="d-inline">
                                        <?php echo csrf_field(); ?>
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Cancel this leave request?')">Cancel</button>
                                    </form>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#viewModal<?php echo e($leave->id); ?>">View</button>

                                <div class="modal fade" id="viewModal<?php echo e($leave->id); ?>" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Leave Request Details</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p><strong>Leave Type:</strong> <?php echo e($leave->leaveType->name); ?></p>
                                                <p><strong>Start Date:</strong> <?php echo e($leave->start_date->format('d M Y')); ?></p>
                                                <p><strong>End Date:</strong> <?php echo e($leave->end_date->format('d M Y')); ?></p>
                                                <p><strong>Working Days:</strong> <?php echo e($leave->total_days); ?></p>
                                                <p><strong>Calendar Days:</strong> <?php echo e($leave->calendar_days); ?></p>
                                                <p><strong>Status:</strong> <?php echo e(ucfirst($leave->status)); ?></p>
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($leave->reason): ?>
                                                <p><strong>Reason:</strong> <?php echo e($leave->reason); ?></p>
                                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($leave->exit_reentry_required): ?>
                                                <p><span class="badge bg-warning">Exit Reentry Required</span></p>
                                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($leave->flight_ticket_required): ?>
                                                <p><span class="badge bg-info">Flight Ticket Required</span></p>
                                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            </div>
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

<!-- Yearly Leave Summary -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-chart-line"></i> Yearly Leave Summary</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="table-light">
                    <tr>
                        <th>Year</th>
                        <th>Months Worked</th>
                        <th>Entitled (Days)</th>
                        <th>Used (Days)</th>
                        <th>Balance (Carry Forward)</th>
                        <th>Tickets</th>
                        <th>Exit/Re-entry</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $yearlySummary; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $yearData): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <tr>
                            <td><strong><?php echo e($yearData['year']); ?></strong></td>
                            <td><?php echo e($yearData['months_worked']); ?></td>
                            <td><?php echo e(number_format($yearData['days_entitled'], 2)); ?></td>
                            <td><?php echo e(number_format($yearData['days_used'], 2)); ?></td>
                            <td>
                                <strong class="<?php echo e($yearData['balance'] >= 0 ? 'text-success' : 'text-danger'); ?>">
                                    <?php echo e(number_format($yearData['balance'], 2)); ?>

                                </strong>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($loop->last): ?>
                                    <small class="text-muted d-block">(Current balance: <?php echo e(number_format($yearData['carry_forward'], 2)); ?>)</small>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </td>
                            <td><?php echo e($yearData['ticket_count']); ?></td>
                            <td><?php echo e($yearData['exit_reentry_count']); ?></td>
                        </tr>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </tbody>
            </table>
        </div>
        <p class="text-muted small mb-0 mt-2">
            <i class="fas fa-info-circle"></i>
            <strong>Notes:</strong>
            Annual leave entitlement is prorated: 1.75 days per month worked. 
            Unused balance carries forward (can be negative). 
            Non-annual leave types (sick, business, etc.) do not affect annual balance.
        </p>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<?php
    $weekendDays = \App\Models\WeekendSetting::getWeekendDays();
    $dayMap = ['sunday'=>0,'monday'=>1,'tuesday'=>2,'wednesday'=>3,'thursday'=>4,'friday'=>5,'saturday'=>6];
    $weekendNums = array_values(array_filter(array_map(fn($d) => $dayMap[strtolower($d)] ?? null, $weekendDays)));
?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const startDate = document.getElementById('startDate');
    const endDate = document.getElementById('endDate');
    const leaveTypeSelect = document.getElementById('leaveTypeSelect');
    const calculationResult = document.getElementById('calculationResult');
    const submitBtn = document.getElementById('submitBtn');
    
    // Annual leave balance (single value)
    const annualBalance = <?php echo json_encode($annualBalance ? $annualBalance->balance : 0, 15, 512) ?>;
    const holidays = <?php echo json_encode($holidays->toArray(), 15, 512) ?>;
    const weekendDays = <?php echo json_encode($weekendNums, 15, 512) ?>;
    
    function calculateDays() {
        if (!startDate.value || !endDate.value || !leaveTypeSelect.value) {
            calculationResult.style.display = 'none';
            return;
        }
        
        const start = new Date(startDate.value);
        const end = new Date(endDate.value);
        
        if (end < start) {
            calculationResult.style.display = 'none';
            return;
        }
        
        const calendarDays = Math.floor((end - start) / (1000 * 60 * 60 * 24)) + 1;
        
        let workingDays = 0;
        let weekendsExcluded = 0;
        let current = new Date(start);
        
        while (current <= end) {
            const dayOfWeek = current.getDay();
                    if (!weekendDays.includes(dayOfWeek)) {
                workingDays++;
            } else {
                weekendsExcluded++;
            }
            current.setDate(current.getDate() + 1);
        }
        
        let holidaysExcluded = 0;
        holidays.forEach(holiday => {
            const holidayStart = new Date(holiday.start_date);
            const holidayEnd = new Date(holiday.end_date);
            current = new Date(start);
            while (current <= end) {
                if (current >= holidayStart && current <= holidayEnd) {
                    const dayOfWeek = current.getDay();
            if (!weekendDays.includes(dayOfWeek)) {
                        holidaysExcluded++;
                    }
                }
                current.setDate(current.getDate() + 1);
            }
        });
        
        workingDays = calendarDays - weekendsExcluded - holidaysExcluded;
        
        document.getElementById('calendarDays').textContent = calendarDays;
        document.getElementById('workingDays').textContent = workingDays;
        document.getElementById('weekendsExcluded').textContent = weekendsExcluded;
        document.getElementById('holidaysExcluded').textContent = holidaysExcluded;
        calculationResult.style.display = 'block';
        
        const selectedOption = leaveTypeSelect.options[leaveTypeSelect.selectedIndex];
        const isDeductible = selectedOption.dataset.deductible === '1';
        
         if (isDeductible) {
             // Only annual leave uses balance
             const currentBalance = annualBalance;
             
             document.getElementById('currentBalance').textContent = currentBalance;
             document.getElementById('remainingBalance').textContent = currentBalance - workingDays;
            
            if (workingDays > currentBalance) {
                document.getElementById('insufficientBalance').style.display = 'block';
                submitBtn.disabled = true;
            } else {
                document.getElementById('insufficientBalance').style.display = 'none';
                submitBtn.disabled = false;
            }
            document.getElementById('balanceCheck').style.display = 'block';
        } else {
            document.getElementById('balanceCheck').style.display = 'none';
            submitBtn.disabled = false;
        }
    }
    
    leaveTypeSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const requiresAttachment = selectedOption.dataset.requiresAttachment === '1';
        document.getElementById('attachmentSection').style.display = requiresAttachment ? 'block' : 'none';
        calculateDays();
    });
    
    startDate.addEventListener('change', calculateDays);
    endDate.addEventListener('change', calculateDays);
});
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp1\htdocs\hr-system\resources\views\leaves\my-leaves.blade.php ENDPATH**/ ?>