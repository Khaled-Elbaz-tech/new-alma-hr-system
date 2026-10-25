@extends('layouts.app')

@section('title', 'Apply for Leave')
@section('page-title', 'Apply for Leave')

@section('content')
<div class="row">
    <!-- Info Cards on Top -->
    <div class="col-md-12 mb-3">
        <div class="row">
            <div class="col-md-4">
                <div class="card mb-3">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-wallet"></i> Available Leave Balance</h6>
                    </div>
                    <div class="card-body">
                        @if($leaveBalances->count() > 0)
                            <table class="table table-sm">
                                <tbody>
                                    @foreach($leaveBalances as $balance)
                                    <tr>
                                        <td>Annual Leave</td>
                                        <td>
                                            <strong class="{{ $balance->balance <= 0 ? 'text-danger' : 'text-success' }}">
                                                {{ number_format($balance->balance, 2) }}
                                            </strong> days
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @else
                            <p class="text-muted">No balance records found.</p>
                        @endif
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card mb-3">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-calendar-day"></i> Upcoming Holidays</h6>
                    </div>
                    <div class="card-body">
                        @if($holidays->count() > 0)
                            <ul class="list-group list-group-flush">
                                @foreach($holidays as $holiday)
                                    <li class="list-group-item px-0">
                                        <strong>{{ $holiday->name }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $holiday->start_date->format('d M') }} - {{ $holiday->end_date->format('d M') }}</small>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <p class="text-muted">No upcoming holidays.</p>
                        @endif
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
                <form method="POST" action="{{ route('leaves.store') }}" id="leaveForm">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Leave Type <span class="text-danger">*</span></label>
                        <select name="leave_type_id" class="form-select" required id="leaveTypeSelect">
                            <option value="">Select Leave Type</option>
                            @foreach($leaveTypes as $type)
                                <option value="{{ $type->id }}" 
                                        data-deductible="{{ $type->deduct_from_balance }}"
                                        data-requires-attachment="{{ $type->requires_attachment }}">
                                    {{ $type->name }} @if($type->default_days)({{ $type->default_days }} days)@endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Start Date <span class="text-danger">*</span></label>
                            <input type="date" name="start_date" class="form-control" required id="startDate" min="{{ date('Y-m-d') }}">
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
                                <p class="mb-1"><strong>Holiday names:</strong> <span id="holidayNames">&mdash;</span></p>
                            </div>
                        </div>
                        <p class="mb-0 mt-2" id="breakdownLine"></p>
                        <hr>
                        <div id="balanceCheck">
                            <p class="mb-1"><strong>Visible Balance:</strong> <span id="currentBalance">0</span> days</p>
                            <p class="mb-1"><strong>After Approval:</strong> <span id="remainingBalance">0</span> days</p>
                            <div class="alert alert-warning mt-2 mb-0" id="insufficientBalance" style="display: none;">
                                <i class="fas fa-exclamation-triangle"></i> Note: this request exceeds the visible balance. It can still be submitted and approved — the balance will go negative.
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
                    <a href="{{ route('leaves.my') }}" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>

@section('scripts')
@php
    $weekendDays = \App\Models\WeekendSetting::getWeekendDays();
    $dayMap = ['sunday'=>0,'monday'=>1,'tuesday'=>2,'wednesday'=>3,'thursday'=>4,'friday'=>5,'saturday'=>6];
    $weekendNums = array_values(array_filter(array_map(fn($d) => $dayMap[strtolower($d)] ?? null, $weekendDays)));
    if (empty($weekendNums)) { $weekendNums = [5, 6]; }
@endphp
<script>
document.addEventListener('DOMContentLoaded', function() {
    const startDate = document.getElementById('startDate');
    const endDate = document.getElementById('endDate');
    const leaveTypeSelect = document.getElementById('leaveTypeSelect');
    const calculationResult = document.getElementById('calculationResult');
    const submitBtn = document.getElementById('submitBtn');
    
    // Annual leave balance (single value)
    const annualBalance = @json($annualBalance ? $annualBalance->balance : 0);
    const holidays = @json($holidays->toArray());
    const weekendDays = @json($weekendNums);
    
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
            if (!weekendDays.includes(dayOfWeek)) {
                workingDays++;
            } else {
                weekendsExcluded++;
            }
            current.setDate(current.getDate() + 1);
        }
        
        // Holidays excluded
        let holidaysExcluded = 0;
        const holidayNames = new Set();
        holidays.forEach(holiday => {
            const holidayStart = new Date(holiday.start_date);
            // A holiday with no end_date is a single day (same fix as the
            // server-side breakdown); new Date(null) would be 1970 and
            // would silently exclude nothing.
            const holidayEnd = holiday.end_date ? new Date(holiday.end_date) : holidayStart;
            current = new Date(start);
            while (current <= end) {
                if (current >= holidayStart && current <= holidayEnd) {
                    const dayOfWeek = current.getDay();
                    if (!weekendDays.includes(dayOfWeek)) {
                        holidaysExcluded++;
                        holidayNames.add(holiday.name);
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
        document.getElementById('holidayNames').textContent = holidayNames.size > 0 ? Array.from(holidayNames).join(', ') : '—';
        document.getElementById('breakdownLine').textContent = calendarDays + ' calendar days \u2212 ' + weekendsExcluded + ' weekend days \u2212 ' + holidaysExcluded + ' holiday days = ' + workingDays + ' working days';
        calculationResult.style.display = 'block';
        
        // Check balance for deductible leaves
        const selectedOption = leaveTypeSelect.options[leaveTypeSelect.selectedIndex];
        const isDeductible = selectedOption.dataset.deductible === '1';
        
         if (isDeductible) {
             const currentBalance = annualBalance;
             
             document.getElementById('currentBalance').textContent = currentBalance;
             document.getElementById('remainingBalance').textContent = currentBalance - workingDays;
            
            // Visible-balance policy: a request may take the balance negative.
            // Show a warning only — never block submission.
            if (workingDays > currentBalance) {
                document.getElementById('insufficientBalance').style.display = 'block';
            } else {
                document.getElementById('insufficientBalance').style.display = 'none';
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
@endsection