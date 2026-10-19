<?php $__env->startSection('title', 'Apply for Leave'); ?>
<?php $__env->startSection('page-title', 'Apply for Leave'); ?>

<?php $__env->startSection('content'); ?>
<div class="row">
    <!-- Info Cards on Top -->
    <div class="col-md-12 mb-3">
        <div class="row">
            <div class="col-md-4">
                <div class="card mb-3">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-wallet"></i> Current Leave Balance (<?php echo e(date('Y')); ?>)</h6>
                    </div>
                    <div class="card-body">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($leaveBalances->count() > 0): ?>
                            <table class="table table-sm">
                                <tbody>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $leaveBalances; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $balance): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                    <tr>
                                        <td>Annual Leave</td>
                                        <td>
                                            <strong class="<?php echo e($balance->balance <= 0 ? 'text-danger' : 'text-success'); ?>">
                                                <?php echo e(number_format($balance->balance, 2)); ?>

                                            </strong> days
                                        </td>
                                    </tr>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <p class="text-muted">No balance records found.</p>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card mb-3">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-calendar-day"></i> Upcoming Holidays</h6>
                    </div>
                    <div class="card-body">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($holidays->count() > 0): ?>
                            <ul class="list-group list-group-flush">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $holidays; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $holiday): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                    <li class="list-group-item px-0">
                                        <strong><?php echo e($holiday->name); ?></strong>
                                        <br>
                                        <small class="text-muted"><?php echo e($holiday->start_date->format('d M')); ?> - <?php echo e($holiday->end_date->format('d M')); ?></small>
                                    </li>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            </ul>
                        <?php else: ?>
                            <p class="text-muted">No upcoming holidays.</p>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card mb-3">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-info-circle"></i> Leave Status Flow</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-flex flex-column gap-2">
                            <div class="d-flex align-items-center">
                                <span class="badge bg-warning">Pending</span>
                                <small class="ms-2">Submitted</small>
                            </div>
                            <div class="d-flex align-items-center">
                                <span class="badge bg-info">HR Approved</span>
                                <small class="ms-2">HR Review</small>
                            </div>
                            <div class="d-flex align-items-center">
                                <span class="badge bg-primary">Manager Approved</span>
                                <small class="ms-2">Manager Review</small>
                            </div>
                            <div class="d-flex align-items-center">
                                <span class="badge bg-success">Approved</span>
                                <small class="ms-2">Final</small>
                            </div>
                            <div class="d-flex align-items-center">
                                <span class="badge bg-danger">Rejected</span>
                                <small class="ms-2">Not Approved</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Form at Bottom -->
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Leave Request Form</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="<?php echo e(route('leaves.store')); ?>" id="leaveForm">
                    <?php echo csrf_field(); ?>
                    <div class="mb-3">
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
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Start Date <span class="text-danger">*</span></label>
                            <input type="date" name="start_date" class="form-control" required id="startDate" min="<?php echo e(date('Y-m-d')); ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">End Date <span class="text-danger">*</span></label>
                            <input type="date" name="end_date" class="form-control" required id="endDate">
                        </div>
                    </div>

                    <!-- Live Calculation Display -->
                    <div class="alert alert-info" id="calculationResult" style="display: none;">
                        <h6><i class="fas fa-calculator"></i> Leave Calculation</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <p class="mb-1"><strong>Calendar Days:</strong> <span id="calendarDays">0</span></p>
                                <p class="mb-1"><strong>Working Days:</strong> <span id="workingDays" class="text-primary">0</span></p>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-1"><strong>Weekends Excluded:</strong> <span id="weekendsExcluded">0</span></p>
                                <p class="mb-1"><strong>Holidays Excluded:</strong> <span id="holidaysExcluded">0</span></p>
                            </div>
                        </div>
                        <hr>
                        <div id="balanceCheck">
                            <p class="mb-1"><strong>Current Balance:</strong> <span id="currentBalance">0</span> days</p>
                            <p class="mb-1"><strong>After Approval:</strong> <span id="remainingBalance">0</span> days</p>
                            <div class="alert alert-danger mt-2 mb-0" id="insufficientBalance" style="display: none;">
                                <i class="fas fa-exclamation-triangle"></i> Insufficient leave balance!
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Reason (Optional)</label>
                        <textarea name="reason" class="form-control" rows="3" placeholder="Enter reason for leave..."></textarea>
                    </div>
                    
                    <div id="businessLeaveSection">
                        <div class="alert alert-warning">
                            <i class="fas fa-briefcase"></i> <strong>Business Trip Requirements</strong>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="form-check">
                                    <input type="checkbox" name="exit_reentry_required" class="form-check-input" id="exitReentry" value="1">
                                    <label class="form-check-label" for="exitReentry">
                                        Exit / Re-entry Visa Required
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="form-check">
                                    <input type="checkbox" name="flight_ticket_required" class="form-check-input" id="flightTicket" value="1">
                                    <label class="form-check-label" for="flightTicket">
                                        Flight Ticket Required
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="form-check">
                                    <input type="checkbox" name="hotel_booking_required" class="form-check-input" id="hotelBooking" value="1">
                                    <label class="form-check-label" for="hotelBooking">
                                        Hotel Booking Required
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="form-check">
                                    <input type="checkbox" name="transfer_required" class="form-check-input" id="transfer" value="1">
                                    <label class="form-check-label" for="transfer">
                                        Transfer Required
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="attachmentSection" style="display: none;">
                        <div class="mb-3">
                            <label class="form-label">Attachment</label>
                            <input type="file" name="attachment" class="form-control" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                            <small class="text-muted">Upload required document (PDF, JPG, PNG, DOC)</small>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary" id="submitBtn">
                        <i class="fas fa-paper-plane"></i> Submit Leave Request
                    </button>
                    <a href="<?php echo e(route('leaves.my')); ?>" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>

<?php $__env->startSection('scripts'); ?>
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
        
        // Calendar days
        const calendarDays = Math.floor((end - start) / (1000 * 60 * 60 * 24)) + 1;
        
        // Working days (exclude weekends)
        let workingDays = 0;
        let weekendsExcluded = 0;
        let current = new Date(start);
        
        while (current <= end) {
            const dayOfWeek = current.getDay();
            if (dayOfWeek !== 0 && dayOfWeek !== 6) { // Not Sunday (0) or Saturday (6)
                workingDays++;
            } else {
                weekendsExcluded++;
            }
            current.setDate(current.getDate() + 1);
        }
        
        // Holidays excluded
        let holidaysExcluded = 0;
        holidays.forEach(holiday => {
            const holidayStart = new Date(holiday.start_date);
            const holidayEnd = new Date(holiday.end_date);
            current = new Date(start);
            while (current <= end) {
                if (current >= holidayStart && current <= holidayEnd) {
                    const dayOfWeek = current.getDay();
                    if (dayOfWeek !== 0 && dayOfWeek !== 6) {
                        holidaysExcluded++;
                    }
                }
                current.setDate(current.getDate() + 1);
            }
        });
        
        // Recalculate working days excluding holidays
        workingDays = calendarDays - weekendsExcluded - holidaysExcluded;
        
        // Show calculation
        document.getElementById('calendarDays').textContent = calendarDays;
        document.getElementById('workingDays').textContent = workingDays;
        document.getElementById('weekendsExcluded').textContent = weekendsExcluded;
        document.getElementById('holidaysExcluded').textContent = holidaysExcluded;
        calculationResult.style.display = 'block';
        
        // Check balance for deductible leaves
        const selectedOption = leaveTypeSelect.options[leaveTypeSelect.selectedIndex];
        const isDeductible = selectedOption.dataset.deductible === '1';
        
         if (isDeductible) {
             const currentBalance = annualBalance;
             
             document.getElementById('currentBalance').textContent = currentBalance;
             document.getElementById('remainingBalance').textContent = currentBalance - workingDays;
            
            if (workingDays > currentBalance) {
                document.getElementById('insufficientBalance').style.display = 'block';
                submitBtn.disabled = true;
                submitBtn.title = 'Insufficient leave balance';
            } else {
                document.getElementById('insufficientBalance').style.display = 'none';
                submitBtn.disabled = false;
                submitBtn.title = '';
            }
            document.getElementById('balanceCheck').style.display = 'block';
        } else {
            document.getElementById('balanceCheck').style.display = 'none';
            submitBtn.disabled = false;
        }
    }
    
    // Show attachment field when leave type requires it
    leaveTypeSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const requiresAttachment = selectedOption.dataset.requiresAttachment === '1';
        const attachmentSection = document.getElementById('attachmentSection');
        
        if (requiresAttachment) {
            attachmentSection.style.display = 'block';
        } else {
            attachmentSection.style.display = 'none';
        }
        
        const isBusinessLeave = selectedOption.value == '3';
        const businessLeaveSection = document.getElementById('businessLeaveSection');
        if (businessLeaveSection) {
            businessLeaveSection.style.display = isBusinessLeave ? 'block' : 'none';
        }
        
        calculateDays();
    });
    
    startDate.addEventListener('change', calculateDays);
    endDate.addEventListener('change', calculateDays);
});
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp1\htdocs\hr-system\resources\views\leaves\create.blade.php ENDPATH**/ ?>