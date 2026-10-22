@extends('layouts.app')

@section('title', 'Historical Leaves')
@section('page-title', 'Historical Leaves')

@section('content')
<!-- Page Header -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <h4 class="mb-1"><i class="fas fa-history text-primary"></i> Historical Leaves</h4>
                <p class="text-muted mb-0">Record leaves that were already taken in the past. Historical entries are booked against their own year, and approved annual leave entries deduct from that year's balance automatically.</p>
            </div>
        </div>
    </div>
</div>

@if($errors->any())
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-circle me-2"></i>{{ $errors->first() }}
    </div>
@endif

<!-- Record Historical Leave -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-calendar-plus"></i> Record Historical Leave</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('leaves.historical') }}">
                    @csrf

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Employee <span class="text-danger">*</span></label>
                            <select name="employee_id" class="form-select" required>
                                <option value="">Select Employee</option>
                                @foreach($employees as $employee)
                                    <option value="{{ $employee->id }}">
                                        {{ $employee->user->name }} ({{ $employee->employee_number }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Leave Type <span class="text-danger">*</span></label>
                            <select name="leave_type_id" class="form-select" required>
                                <option value="">Select Leave Type</option>
                                @foreach($leaveTypes as $type)
                                    <option value="{{ $type->id }}" data-code="{{ $type->code ?? '' }}">
                                        {{ $type->name }} @if($type->default_days)({{ $type->default_days }} days)@endif
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Status <span class="text-danger">*</span></label>
                            <select name="status" class="form-select" required>
                                <option value="approved" selected>Approved</option>
                                <option value="rejected">Rejected</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Start Date <span class="text-danger">*</span></label>
                            <input type="date" name="start_date" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">End Date <span class="text-danger">*</span></label>
                            <input type="date" name="end_date" class="form-control" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Reason <span class="text-danger">*</span></label>
                        <textarea name="reason" class="form-control" rows="3" required placeholder="Enter reason for leave..."></textarea>
                    </div>

                    <!-- Additional Services -->
                    <div class="mb-3">
                        <div class="card border-warning">
                            <div class="card-header bg-warning text-dark">
                                <h6 class="mb-0"><i class="fas fa-concierge-bell"></i> Additional Services (if applicable)</h6>
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

                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Past dates are accepted here. Working days are calculated automatically (excluding weekends and holidays), and the entry is deducted from the balance of the year the leave belongs to — not the current year.
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('leaves.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Record Historical Leave
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Import Historical Leaves (CSV) -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-file-csv"></i> Import Historical Leaves (CSV)</h5>
            </div>
            <div class="card-body">
                <p class="text-muted">To record many past leaves at once, fill in the CSV template — one row per leave, with the employee number already listed for every employee — then upload it here.</p>

                <div class="mb-3">
                    <a href="{{ route('leaves.historical.template') }}" class="btn btn-outline-primary">
                        <i class="fas fa-download"></i> Download CSV Template
                    </a>
                </div>

                <form method="POST" action="{{ route('leave-balances.import') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="row align-items-end">
                        <div class="col-md-8 mb-3">
                            <label class="form-label">CSV File <span class="text-danger">*</span></label>
                            <input type="file" name="file" class="form-control" accept=".csv" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-upload"></i> Import CSV
                            </button>
                        </div>
                    </div>
                </form>

                <div class="alert alert-info mb-0">
                    <i class="fas fa-info-circle"></i> <strong>CSV format</strong> — the file must have exactly these columns, in this order:
                    <code>Employee Number, Leave Type, Start Date, End Date, Total Days, Calendar Days, Reason</code>
                    <ul class="mb-0 mt-2">
                        <li><strong>Total Days is the number of WORKING days that will be deducted from the employee's balance for that year — fill it carefully; Calendar Days is the full calendar span.</strong></li>
                        <li>Rows for the same employee, type and start date are skipped as duplicates.</li>
                        <li>Imported rows are saved as approved historical entries. Use dates in <code>YYYY-MM-DD</code> format.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
