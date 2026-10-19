<?php $__env->startSection('title', 'Create Leave for Employee'); ?>
<?php $__env->startSection('page-title', 'Create Leave Request for Employee'); ?>

<?php $__env->startSection('content'); ?>
<!-- Page Header -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <h4 class="mb-1"><i class="fas fa-calendar-plus text-primary"></i> Create Leave Request for Employee</h4>
                <p class="text-muted mb-0">Create annual, sick, business, emergency, marriage, bereavement, or business trip leave records.</p>
            </div>
        </div>
    </div>
</div>

<!-- Employee Selection Card -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-user-search"></i> Select Employee</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <label class="form-label">Employee <span class="text-danger">*</span></label>
                        <select name="employee_id" class="form-select" required id="employeeSelect">
                            <option value="">Search and select employee...</option>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $employees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $emp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <option value="<?php echo e($emp->id); ?>" data-name="<?php echo e($emp->user->name ?? ''); ?>" data-email="<?php echo e($emp->user->email ?? ''); ?>">
                                    <?php echo e($emp->user->name ?? 'No User Assigned'); ?> (<?php echo e($emp->employee_number); ?>)
                                </option>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </select>
                    </div>
                </div>
                <div id="loadingIndicator" style="display: none;" class="mt-3">
                    <div class="alert alert-info">
                        <i class="fas fa-spinner fa-spin"></i> Loading employee data...
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Employee Info & Balance Cards (Hidden by default) -->
<div class="row mb-4" id="employeeInfoSection" style="display: none;">
    <!-- Employee Information -->
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-user-circle"></i> Employee Information</h5>
            </div>
            <div class="card-body">
                <div class="text-center mb-3">
                    <div class="bg-primary rounded-circle p-3 d-inline-flex">
                        <i class="fas fa-user text-white fa-2x"></i>
                    </div>
                    <h5 class="mt-2 mb-0" id="empName">-</h5>
                    <p class="text-muted mb-0" id="empNumber">-</p>
                </div>
                <table class="table table-sm mb-0">
                    <tr>
                        <td><strong>Department</strong></td>
                        <td id="empDepartment">-</td>
                    </tr>
                    <tr>
                        <td><strong>Joining Date</strong></td>
                        <td id="empJoiningDate">-</td>
                    </tr>
                    <tr>
                        <td><strong>Status</strong></td>
                        <td id="empStatus">-</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <!-- Current Balance Cards -->
    <div class="col-md-8">
        <div class="row">
            <div class="col-md-4">
                <div class="card h-100 border-primary">
                    <div class="card-body text-center">
                        <h6 class="text-muted mb-2">Current Balance</h6>
                        <h2 class="text-primary mb-0" id="currentBalance">-</h2>
                        <p class="text-muted mb-0">Days</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 border-success">
                    <div class="card-body text-center">
                        <h6 class="text-muted mb-2">Current Year Entitlement</h6>
                        <h2 class="text-success mb-0" id="currentEntitlement">-</h2>
                        <p class="text-muted mb-0">Days</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 border-warning">
                    <div class="card-body text-center">
                        <h6 class="text-muted mb-2">Used Days</h6>
                        <h2 class="text-warning mb-0" id="usedDays">-</h2>
                        <p class="text-muted mb-0">Days</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Leave Request Form -->
<div class="row mb-4" id="leaveRequestSection" style="display: none;">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-calendar-alt"></i> Leave Request Details</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="<?php echo e(route('leaves.store-for-employee')); ?>" id="leaveForm">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="employee_id" id="employeeIdInput" required>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Leave Type <span class="text-danger">*</span></label>
                            <select name="leave_type_id" class="form-select" required id="leaveTypeSelect">
                                <option value="">Select Leave Type</option>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $leaveTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                    <option value="<?php echo e($type->id); ?>" data-code="<?php echo e($type->code ?? ''); ?>">
                                        <?php echo e($type->name); ?> <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($type->default_days): ?>(<?php echo e($type->default_days); ?> days)<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </option>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Start Date <span class="text-danger">*</span></label>
                            <input type="date" name="start_date" class="form-control" required id="startDate">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">End Date <span class="text-danger">*</span></label>
                            <input type="date" name="end_date" class="form-control" required id="endDate">
                        </div>
                        <div class="col-md-2 mb-3">
                            <label class="form-label">Requested Days</label>
                            <input type="text" class="form-control" id="requestedDays" readonly value="0">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Reason <span class="text-danger">*</span></label>
                        <textarea name="reason" class="form-control" rows="3" required placeholder="Enter reason for leave..."></textarea>
                    </div>

                    <!-- Business Trip Requirements -->
                    <div class="mb-3" id="businessTripSection" style="display: none;">
                        <div class="card border-warning">
                            <div class="card-header bg-warning text-dark">
                                <h6 class="mb-0"><i class="fas fa-briefcase"></i> Business Trip Requirements</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <div class="form-check">
                                            <input type="checkbox" name="flight_ticket_required" class="form-check-input" id="flightTicket" value="1">
                                            <label class="form-check-label" for="flightTicket">Flight Ticket Required</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="form-check">
                                            <input type="checkbox" name="hotel_booking_required" class="form-check-input" id="hotelBooking" value="1">
                                            <label class="form-check-label" for="hotelBooking">Hotel Booking Required</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="form-check">
                                            <input type="checkbox" name="transfer_required" class="form-check-input" id="transfer" value="1">
                                            <label class="form-check-label" for="transfer">Transfer Required</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="form-check">
                                            <input type="checkbox" name="exit_reentry_required" class="form-check-input" id="exitReentry" value="1">
                                            <label class="form-check-label" for="exitReentry">Exit/Re-entry Visa Required</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Annual Leave Additional Fields -->
                    <div class="mb-3" id="annualLeaveSection" style="display: none;">
                        <div class="card border-info">
                            <div class="card-header bg-info text-white">
                                <h6 class="mb-0"><i class="fas fa-umbrella-beach"></i> Annual Leave Additional Information</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <input type="checkbox" name="flight_ticket_required" class="form-check-input" id="annualFlightTicket" value="1">
                                            <label class="form-check-label" for="annualFlightTicket">Flight Ticket Required</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <input type="checkbox" name="exit_reentry_required" class="form-check-input" id="annualExitReentry" value="1">
                                            <label class="form-check-label" for="annualExitReentry">Exit/Re-entry Visa Required</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sick Leave Attachment -->
                    <div class="mb-3" id="sickLeaveSection" style="display: none;">
                        <div class="card border-secondary">
                            <div class="card-header bg-secondary text-white">
                                <h6 class="mb-0"><i class="fas fa-file-medical"></i> Sick Leave Documentation</h6>
                            </div>
                            <div class="card-body">
                                <div class="form-check">
                                    <input type="checkbox" name="attachment_required" class="form-check-input" id="sickLeaveAttachment" value="1">
                                    <label class="form-check-label" for="sickLeaveAttachment">Medical Certificate Attached</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Leave Impact Summary -->
                    <div class="mb-3" id="leaveImpactSection" style="display: none;">
                        <div class="card" id="leaveImpactCard">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="fas fa-calculator"></i> Leave Impact Summary</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="text-center">
                                            <h6 class="text-muted">Current Balance</h6>
                                            <h3 class="text-primary mb-0" id="impactCurrentBalance">-</h3>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="text-center">
                                            <h6 class="text-muted">Requested Days</h6>
                                            <h3 class="text-warning mb-0" id="impactRequestedDays">-</h3>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="text-center">
                                            <h6 class="text-muted">Remaining Balance</h6>
                                            <h3 class="mb-0" id="impactRemainingBalance">-</h3>
                                        </div>
                                    </div>
                                </div>
                                <div class="alert mt-3" id="balanceAlert" style="display: none;">
                                    <i class="fas fa-exclamation-triangle"></i> <span id="balanceAlertMessage"></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> This leave will be created as approved immediately.
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="<?php echo e(route('leaves.index')); ?>" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-primary" id="submitBtn">
                            <i class="fas fa-save"></i> Create Leave
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Vacation History Preview -->
<div class="row mb-4" id="vacationHistorySection" style="display: none;">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-history"></i> Vacation History</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <!-- Recent Leave Requests -->
                    <div class="col-md-6">
                        <h6 class="mb-3">Last 5 Leave Requests</h6>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Type</th>
                                        <th>Days</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody id="recentLeavesTable">
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">No recent leaves</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Yearly Summary -->
                    <div class="col-md-6">
                        <h6 class="mb-3">Yearly Summary</h6>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Year</th>
                                        <th>Entitled</th>
                                        <th>Used</th>
                                        <th>Balance</th>
                                    </tr>
                                </thead>
                                <tbody id="yearlySummaryTable">
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">No balance records</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const employeeSelect = document.getElementById('employeeSelect');
    const leaveTypeSelect = document.getElementById('leaveTypeSelect');
    const startDateInput = document.getElementById('startDate');
    const endDateInput = document.getElementById('endDate');
    const requestedDaysInput = document.getElementById('requestedDays');
    const leaveForm = document.getElementById('leaveForm');
    
    // Set minimum date to today
    const today = new Date().toISOString().split('T')[0];
    startDateInput.min = today;
    endDateInput.min = today;
    
    // Employee selection change
    if (employeeSelect) {
        employeeSelect.addEventListener('change', function() {
            const employeeId = this.value;
            
            if (employeeId) {
                loadEmployeeData(employeeId);
            } else {
                hideEmployeeSections();
            }
        });
    }
    
    // Load employee data via AJAX
    function loadEmployeeData(employeeId) {
        const url = `<?php echo e(route('leaves.employee-data', ['employee' => ':employeeId'])); ?>`.replace(':employeeId', employeeId);
        const loadingIndicator = document.getElementById('loadingIndicator');
        
        // Show loading
        if (loadingIndicator) {
            loadingIndicator.style.display = 'block';
        }
        
        fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            // Use try-catch for each display function to ensure showEmployeeSections is always called
            try {
                displayEmployeeInfo(data);
            } catch (e) {
                console.error('Error displaying employee info:', e);
            }
            
            try {
                displayBalanceCards(data);
            } catch (e) {
                console.error('Error displaying balance cards:', e);
            }
            
            try {
                displayVacationHistory(data);
            } catch (e) {
                console.error('Error displaying vacation history:', e);
            }
            
            // Always show sections after loading data
            showEmployeeSections();
        })
        .catch(error => {
            console.error('Error loading employee data:', error);
            alert('Failed to load employee data. Please try again.\n\nError: ' + error.message);
            hideEmployeeSections();
        })
        .finally(() => {
            // Hide loading
            if (loadingIndicator) {
                loadingIndicator.style.display = 'none';
            }
        });
    }
    
    // Display employee information
    function displayEmployeeInfo(data) {
        document.getElementById('empName').textContent = data.employee.name;
        document.getElementById('empNumber').textContent = data.employee.employee_number;
        document.getElementById('empDepartment').textContent = data.employee.department;
        document.getElementById('empJoiningDate').textContent = new Date(data.employee.joining_date).toLocaleDateString();
        document.getElementById('empStatus').innerHTML = `<span class="badge bg-${data.employee.employment_status === 'active' ? 'success' : 'secondary'}">${data.employee.employment_status}</span>`;
        
        // Set hidden input
        document.getElementById('employeeIdInput').value = data.employee.id;
    }
    
    // Display balance cards
    function displayBalanceCards(data) {
        const currentBalance = data.current_balance;
        const availableBalance = data.available_balance || 0;
        
        document.getElementById('currentBalance').textContent = parseFloat(availableBalance).toFixed(1);
        document.getElementById('currentEntitlement').textContent = currentBalance ? parseFloat(currentBalance.entitled_days).toFixed(1) : '0';
        document.getElementById('usedDays').textContent = currentBalance ? parseFloat(currentBalance.used_days).toFixed(1) : '0';
    }
    
    // Display vacation history
    function displayVacationHistory(data) {
        // Recent leaves
        const recentLeavesTable = document.getElementById('recentLeavesTable');
        if (data.recent_leaves.length > 0) {
            recentLeavesTable.innerHTML = data.recent_leaves.map(leave => `
                <tr>
                    <td>${leave.date}</td>
                    <td>${leave.type}</td>
                    <td>${leave.days}</td>
                    <td><span class="badge bg-${getStatusColor(leave.status)}">${leave.status}</span></td>
                </tr>
            `).join('');
        } else {
            recentLeavesTable.innerHTML = '<tr><td colspan="4" class="text-center text-muted">No recent leaves</td></tr>';
        }
        
        // Yearly summary
        const yearlySummaryTable = document.getElementById('yearlySummaryTable');
        if (data.balances.length > 0) {
            yearlySummaryTable.innerHTML = data.balances.map(balance => `
                <tr>
                    <td>${balance.year}</td>
                    <td>${balance.entitled_days.toFixed(1)}</td>
                    <td>${balance.used_days.toFixed(1)}</td>
                    <td><strong>${balance.balance.toFixed(1)}</strong></td>
                </tr>
            `).join('');
        } else {
            yearlySummaryTable.innerHTML = '<tr><td colspan="4" class="text-center text-muted">No balance records</td></tr>';
        }
    }
    
    // Get status color
    function getStatusColor(status) {
        switch(status) {
            case 'approved': return 'success';
            case 'pending': return 'warning';
            case 'rejected': return 'danger';
            case 'cancelled': return 'secondary';
            default: return 'info';
        }
    }
    
    // Show employee sections
    function showEmployeeSections() {
        const sections = [
            { id: 'employeeInfoSection', display: 'flex' },
            { id: 'leaveRequestSection', display: 'flex' },
            { id: 'vacationHistorySection', display: 'flex' }
        ];
        sections.forEach(section => {
            const el = document.getElementById(section.id);
            if (el) {
                el.style.display = section.display;
            }
        });
    }
    
    // Hide employee sections
    function hideEmployeeSections() {
        document.getElementById('employeeInfoSection').style.display = 'none';
        document.getElementById('leaveRequestSection').style.display = 'none';
        document.getElementById('vacationHistorySection').style.display = 'none';
        document.getElementById('leaveImpactSection').style.display = 'none';
    }
    
    // Leave type change
    if (leaveTypeSelect) {
        leaveTypeSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const code = selectedOption.dataset.code || '';
            
            // Show/hide conditional sections
            document.getElementById('businessTripSection').style.display = code === 'business' ? 'block' : 'none';
            document.getElementById('annualLeaveSection').style.display = code === 'annual' ? 'block' : 'none';
            document.getElementById('sickLeaveSection').style.display = code === 'sick' ? 'block' : 'none';
            
            // Recalculate impact if dates are set
            calculateLeaveImpact();
        });
    }
    
    // Date change
    if (startDateInput && endDateInput) {
        startDateInput.addEventListener('change', calculateLeaveImpact);
        endDateInput.addEventListener('change', calculateLeaveImpact);
    }
    
    // Calculate leave impact
    function calculateLeaveImpact() {
        const employeeId = document.getElementById('employeeIdInput').value;
        const startDate = startDateInput.value;
        const endDate = endDateInput.value;
        const leaveTypeId = leaveTypeSelect.value;
        
        if (!employeeId || !startDate || !endDate || !leaveTypeId) {
            document.getElementById('leaveImpactSection').style.display = 'none';
            return;
        }
        
        // Calculate requested days
        const start = new Date(startDate);
        const end = new Date(endDate);
        const requestedDays = Math.ceil((end - start) / (1000 * 60 * 60 * 24)) + 1;
        
        document.getElementById('requestedDays').value = requestedDays;
        
        // Get current balance from the displayed value
        const currentBalanceText = document.getElementById('currentBalance').textContent;
        const currentBalance = parseFloat(currentBalanceText) || 0;
        
        const remainingBalance = currentBalance - requestedDays;
        
        // Display impact
        document.getElementById('leaveImpactSection').style.display = 'block';
        document.getElementById('impactCurrentBalance').textContent = currentBalance.toFixed(1);
        document.getElementById('impactRequestedDays').textContent = requestedDays;
        
        const remainingBalanceElement = document.getElementById('impactRemainingBalance');
        remainingBalanceElement.textContent = remainingBalance.toFixed(1);
        
        // Color code remaining balance
        remainingBalanceElement.className = 'mb-0 ';
        if (remainingBalance < 0) {
            remainingBalanceElement.className += 'text-danger';
            showBalanceAlert('Employee does not have sufficient leave balance.', 'danger');
        } else if (remainingBalance < 5) {
            remainingBalanceElement.className += 'text-warning';
            showBalanceAlert('Warning: Low balance after this leave.', 'warning');
        } else {
            remainingBalanceElement.className += 'text-success';
            hideBalanceAlert();
        }
    }
    
    // Show balance alert
    function showBalanceAlert(message, type) {
        const alert = document.getElementById('balanceAlert');
        const messageElement = document.getElementById('balanceAlertMessage');
        
        alert.className = `alert alert-${type} mt-3`;
        alert.style.display = 'block';
        messageElement.textContent = message;
    }
    
    // Hide balance alert
    function hideBalanceAlert() {
        document.getElementById('balanceAlert').style.display = 'none';
    }
    
    // Form submission validation
    if (leaveForm) {
        leaveForm.addEventListener('submit', function(e) {
            const employeeId = document.getElementById('employeeIdInput').value;
            const startDate = startDateInput.value;
            const endDate = endDateInput.value;
            
            if (!employeeId) {
                e.preventDefault();
                alert('Please select an employee.');
                return;
            }
            
            // Additional validation can be added here
        });
    }
});
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp1\htdocs\hr-system\resources\views\leaves\create-for-employee.blade.php ENDPATH**/ ?>