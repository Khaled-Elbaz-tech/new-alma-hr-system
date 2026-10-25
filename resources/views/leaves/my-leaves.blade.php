@extends('layouts.app')

@section('title', 'My Leaves')
@section('page-title', 'My Leave Requests')

@section('content')
<div class="row mb-3">
    <div class="col-md-12">
        <button class="btn btn-primary" type="button" data-bs-toggle="collapse" data-bs-target="#leaveFormCollapse">
            <i class="fas fa-plus"></i> Apply for Leave
        </button>
    </div>
</div>

<!-- Collapsible Leave Form -->
<div class="collapse mb-3 {{ $showForm ? 'show' : '' }}" id="leaveFormCollapse">
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-calendar-plus"></i> Leave Request Form</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('leaves.store') }}" id="leaveForm">
                @csrf
                <div class="row">
                    <div class="col-md-4 mb-3">
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
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Start Date <span class="text-danger">*</span></label>
                        <input type="date" name="start_date" class="form-control" required id="startDate" min="{{ date('Y-m-d') }}">
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
                                <p class="mb-1"><strong>Visible Balance:</strong> <span id="currentBalance">0</span> days</p>
                                <p class="mb-1"><strong>After Approval:</strong> <span id="remainingBalance">0</span> days</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="alert alert-warning mt-0" id="insufficientBalance" style="display: none;">
                                <i class="fas fa-exclamation-triangle"></i> Note: this request exceeds the visible balance. It can still be submitted and approved — the balance will go negative.
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

<!-- Leave History Filters -->
<div class="card mb-4">
    <div class="card-header">
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <input type="text" name="search" class="form-control" placeholder="Search by leave type or reason" value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select">
                    <option value="">All Statuses</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="hr_approved" {{ request('status') == 'hr_approved' ? 'selected' : '' }}>HR Approved</option>
                    <option value="manager_approved" {{ request('status') == 'manager_approved' ? 'selected' : '' }}>Manager Approved</option>
                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="leave_type_id" class="form-select">
                    <option value="">All Types</option>
                    @foreach($leaveTypes as $type)
                        <option value="{{ $type->id }}" {{ request('leave_type_id') == $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="year" class="form-select">
                    <option value="">All Years</option>
                    @for($y = date('Y'); $y >= date('Y') - 5; $y--)
                        <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>
            <div class="col-md-3">
                <div class="input-group">
                    <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}" title="Leave from date">
                    <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}" title="Leave to date">
                </div>
            </div>
            <div class="col-md-12">
                <button type="submit" class="btn btn-secondary">Filter</button>
                <a href="{{ route('leaves.my') }}" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>
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
                    @forelse($leaveRequests as $leave)
                        <tr>
                            <td>{{ $leave->leaveType->name }}</td>
                            <td>{{ $leave->start_date->format('d M Y') }}</td>
                            <td>{{ $leave->end_date->format('d M Y') }}</td>
                            <td>{{ $leave->total_days }}</td>
                            <td>
                                <span class="badge bg-{{ $leave->status === 'pending' ? 'warning' : ($leave->status === 'approved' ? 'success' : ($leave->status === 'rejected' ? 'danger' : 'info')) }}">
                                    @if($leave->status === 'pending') Pending
                                    @elseif($leave->status === 'hr_approved') HR Approved
                                    @elseif($leave->status === 'approved') Approved
                                    @elseif($leave->status === 'rejected') Rejected
                                    @else {{ ucfirst($leave->status) }}
                                    @endif
                                </span>
                            </td>
                            <td>
                                @if($leave->status === 'pending')
                                    <form action="{{ route('leaves.cancel', $leave) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Cancel this leave request?')">Cancel</button>
                                    </form>
                                @endif
                                <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#viewModal{{ $leave->id }}">View</button>

                                <div class="modal fade" id="viewModal{{ $leave->id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Leave Request Details</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p><strong>Leave Type:</strong> {{ $leave->leaveType->name }}</p>
                                                <p><strong>Start Date:</strong> {{ $leave->start_date->format('d M Y') }}</p>
                                                <p><strong>End Date:</strong> {{ $leave->end_date->format('d M Y') }}</p>
                                                <p><strong>Working Days:</strong> {{ $leave->total_days }}</p>
                                                @php $bd = $breakdowns[$leave->id] ?? null; @endphp
                                                @if($bd)
                                                <hr>
                                                <p class="mb-1"><strong>How the days are counted (live):</strong></p>
                                                <p class="mb-1">{{ $bd['calendar_days'] }} calendar days &minus; {{ $bd['weekend_days'] }} weekend days ({{ implode(', ', $bd['weekend_day_names']) }}) &minus; {{ $bd['holiday_days'] }} holiday days = <strong>{{ $bd['working_days'] }} working days</strong></p>
                                                @if(count($bd['holidays']) > 0)
                                                <p class="mb-1 small text-muted">Holidays excluded: @foreach($bd['holidays'] as $h){{ $h['date'] }} ({{ $h['name'] }})@if(!$loop->last), @endif @endforeach</p>
                                                @endif
                                                @if((float) $bd['working_days'] !== (float) $leave->total_days)
                                                <div class="alert alert-warning py-2 px-3 mb-0">
                                                    <small><strong>Note:</strong> this live count ({{ $bd['working_days'] }} days) differs from the days stored when the request was created ({{ $leave->total_days }} days). Weekends or holidays changed after creation — the stored figure is what counts until an HR/admin edit or a holiday recalculation updates it.</small>
                                                </div>
                                                @endif
                                                @endif
                                                <p><strong>Calendar Days:</strong> {{ $leave->calendar_days }}</p>
                                                <p><strong>Status:</strong> {{ ucfirst($leave->status) }}</p>
                                                @if($leave->reason)
                                                <p><strong>Reason:</strong> {{ $leave->reason }}</p>
                                                @endif
                                                @if($leave->exit_reentry_required)
                                                <p><span class="badge bg-warning">Exit Reentry Required</span></p>
                                                @endif
                                                @if($leave->flight_ticket_required)
                                                <p><span class="badge bg-info">Flight Ticket Required</span></p>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted">No leave requests found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $leaveRequests->links() }}
    </div>
</div>

@if(isset($visibleBalance))
    <div class="card mb-3 border-primary">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="fas fa-wallet"></i> Available Balance</h5>
        </div>
        <div class="card-body">
            <h2 class="{{ ($visibleBalance['visible_balance'] ?? 0) < 0 ? 'text-danger' : 'text-success' }} mb-1">
                {{ number_format($visibleBalance['visible_balance'] ?? 0, 2) }} days
            </h2>
            <p class="text-muted">
                This balance counts <strong>closed years only</strong>. The current year's ({{ $visibleBalance['current_year'] }}) annual entitlement is not counted yet — it opens automatically on <strong>{{ $visibleBalance['opens_on'] }}</strong>. Leave approved during the current year deducts from this balance, even if it goes negative.
            </p>
            <div class="table-responsive">
                <table class="table table-bordered table-sm mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Year</th>
                            <th>Entitled</th>
                            <th>Used</th>
                            <th>Remaining</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($visibleBalance['rows'] as $row)
                            <tr @if($row['is_current']) class="table-light" @endif>
                                <td><strong>{{ $row['year'] }}</strong></td>
                                <td>{{ $row['entitled'] === null ? '—' : number_format($row['entitled'], 2) }}</td>
                                <td>{{ number_format($row['used'], 2) }}
                                @if(!empty($yearRequests[$row['year']] ?? []))
                                <details class="mt-1">
                                    <summary class="small text-muted" style="cursor:pointer;">Show {{ count($yearRequests[$row['year']]) }} approved annual request(s)</summary>
                                    <ul class="small mb-0 ps-3">
                                        @foreach($yearRequests[$row['year']] as $yr)
                                        <li>{{ $yr->start_date->format('d M Y') }} &ndash; {{ $yr->end_date->format('d M Y') }}: {{ $yr->total_days }} days</li>
                                        @endforeach
                                    </ul>
                                    <small class="text-muted">Total: {{ number_format(collect($yearRequests[$row['year']])->sum('total_days'), 2) }} days &mdash; this is the Used figure above.</small>
                                </details>
                                @endif
                            </td>
                                <td class="{{ $row['remaining'] < 0 ? 'text-danger' : 'text-success' }}">
                                    <strong>{{ number_format($row['remaining'], 2) }}</strong>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <p class="text-muted small mb-0 mt-2">
                <i class="fas fa-info-circle"></i>
                {{ $visibleBalance['current_year'] }} entitlement opens on {{ $visibleBalance['opens_on'] }}.
            </p>
            <div class="alert alert-info mt-3 mb-0">
    <strong>How this is calculated:</strong> Only <strong>annual</strong> leave counts here.
    Each year's "Used" is the sum of the <strong>approved annual requests that start in that year</strong>
    (click "Show requests" on a Used figure to see them).
    The visible balance = <strong>entitlements of closed years only</strong> minus
    <strong>approved annual days used in all years, including the current year</strong>.
    The current year's entitlement is hidden until <strong>1 January of the next year</strong>, when it
    opens automatically — there is no manual rollover.
    A negative balance is shown as-is and is never blocked or floored at zero.
    Other leave types (sick, business, etc.) never change this number.
</div>
        </div>
    </div>
@endif

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
                    @foreach($yearlySummary as $yearData)
                        <tr>
                            <td><strong>{{ $yearData['year'] }}</strong></td>
                            <td>{{ $yearData['months_worked'] }}</td>
                            <td>{{ $yearData['days_entitled'] === null ? '—' : number_format($yearData['days_entitled'], 2) }}</td>
                            <td>{{ number_format($yearData['days_used'], 2) }}</td>
                            <td>
                                <strong class="{{ $yearData['balance'] >= 0 ? 'text-success' : 'text-danger' }}">
                                    {{ number_format($yearData['balance'], 2) }}
                                </strong>
                                @if($loop->last)
                                    <small class="text-muted d-block">(Current balance: {{ number_format($yearData['carry_forward'], 2) }})</small>
                                @endif
                            </td>
                            <td>{{ $yearData['ticket_count'] }}</td>
                            <td>{{ $yearData['exit_reentry_count'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <p class="text-muted small mb-0 mt-2">
            <i class="fas fa-info-circle"></i>
            <strong>Notes:</strong>
            Annual leave entitlement is prorated: 1.75 days per month worked. 
            Unused balance carries forward (can be negative). 
            Non-annual leave types (sick, business, etc.) do not affect annual balance.
            The current year's entitlement is not counted until 1 January of the next year — the Available Balance above counts closed years only.
        </p>
    </div>
</div>
@endsection

@section('scripts')
@php
    $weekendDays = \App\Models\WeekendSetting::getWeekendDays();
    $dayMap = ['sunday'=>0,'monday'=>1,'tuesday'=>2,'wednesday'=>3,'thursday'=>4,'friday'=>5,'saturday'=>6];
    $weekendNums = array_values(array_filter(array_map(fn($d) => $dayMap[strtolower($d)] ?? null, $weekendDays)));
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
@endsection