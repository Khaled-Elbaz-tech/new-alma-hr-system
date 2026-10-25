@extends('layouts.app')

@section('title', 'Edit Leave Balance')
@section('page-title', 'Edit Leave Balance')

@section('content')
<div class="row">
    <div class="col-md-8 offset-md-2">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-edit"></i> Edit Balance: {{ $balance->employee->user->name }} ({{ $balance->year }})</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('leaves.update-balance', $balance) }}">
                    @csrf
                    @method('PUT')
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label class="form-label">Employee</label>
                        <input type="text" class="form-control" value="{{ $balance->employee->user->name }} ({{ $balance->employee->employee_id }})" readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Year</label>
                        <select name="year" class="form-select" required>
                            @foreach($leaveYears as $leaveYear)
                                <option value="{{ $leaveYear->year }}" {{ $balance->year == $leaveYear->year ? 'selected' : '' }}>
                                    {{ $leaveYear->year }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Months Worked in Year</label>
                        <input type="number" name="months_from_hiring" class="form-control"
                               value="{{ $monthsWorked }}" readonly>
                        <small class="text-muted">Auto-calculated from joining date</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Days Entitled</label>
                        <input type="number" step="0.01" name="entitled_days" class="form-control"
                               value="{{ old('entitled_days', $balance->entitled_days ?? '') }}" @if($balance->entitled_days !== null) required @endif>
                        <small class="text-muted">Calculated as 1.75 × months worked, but HR can override</small>
                        @if($balance->entitled_days === null)
                            <br><small class="text-muted">Current-year entitlement is hidden from balances until 1 January {{ $visibleBalance['current_year'] + 1 }} (visible-balance policy).</small>
                        @endif
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Days Used</label>
                        <input type="number" step="0.01" name="used_days" class="form-control"
                               value="{{ old('used_days', $balance->used_days) }}" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Ticket Count (Annual Leaves)</label>
                            <input type="number" name="ticket_count" class="form-control"
                                   value="{{ old('ticket_count', $balance->ticket_count) }}" min="0">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Exit/Re-entry Count</label>
                            <input type="number" name="exit_reentry_count" class="form-control"
                                   value="{{ old('exit_reentry_count', $balance->exit_reentry_count) }}" min="0">
                        </div>
                    </div>

                    <div class="alert alert-info">
                        <strong>Available Balance (visible):</strong> {{ number_format($visibleBalance['visible_balance'], 2) }} days
                        <br><small>Counts closed years only. The current year's ({{ $visibleBalance['current_year'] }}) entitlement opens on {{ $visibleBalance['opens_on'] }}. Negative balances are shown as-is.</small>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('leaves.manage-balances') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Balance
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
