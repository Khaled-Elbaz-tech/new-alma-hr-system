@extends('layouts.app')

@section('title', 'Employee Profile')
@section('page-title', 'Employee Profile - ' . $employee->user->name)

@section('content')
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="bg-primary rounded-circle p-3 me-3">
                        <i class="fas fa-user text-white fa-2x"></i>
                    </div>
                    <div>
                        <h4 mb-0>{{ $employee->user->name }}</h4>
                        <p class="text-muted mb-0">{{ $employee->employee_number }} | {{ $employee->department->name ?? 'No Department' }}</p>
                    </div>
                    <div class="ms-auto">
                        <span class="badge bg-{{ $employee->employment_status === 'active' ? 'success' : 'secondary' }}">
                            {{ ucfirst($employee->employment_status) }}
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
                        <p><strong>Full Name:</strong> {{ $employee->user->name }}</p>
                        <p><strong>Email:</strong> {{ $employee->user->email }}</p>
                        <p><strong>Employee Number:</strong> {{ $employee->employee_number }}</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Role:</strong> {{ $employee->user->role->display_name ?? 'N/A' }}</p>
                        <p><strong>Status:</strong> 
                            <span class="badge bg-{{ $employee->employment_status === 'active' ? 'success' : 'secondary' }}">
                                {{ ucfirst($employee->employment_status) }}
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
                        <p><strong>Department:</strong> {{ $employee->department->name ?? 'Not Assigned' }}</p>
                        <p><strong>Joining Date:</strong> {{ $employee->joining_date ? $employee->joining_date->format('d M Y') : 'N/A' }}</p>
                        <p><strong>Annual Leave Entitlement:</strong> {{ $employee->annual_leave_entitlement }} days</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Contract Start:</strong> {{ $employee->contract_start_date ? $employee->contract_start_date->format('d M Y') : 'N/A' }}</p>
                        <p><strong>Contract End:</strong> {{ $employee->contract_end_date ? $employee->contract_end_date->format('d M Y') : 'N/A' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Leave Summary Tab -->
    <div class="tab-pane fade" id="leave-summary">
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
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-calendar-check"></i> Leave Balance (Yearly View)</h5>
            </div>
            <div class="card-body">
                @if(!empty($vacationHistory))
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
                                @foreach($vacationHistory as $balance)
                                    <tr>
                                        <td>{{ $balance['year'] }}</td>
                                        <td>{{ $balance['entitled_days'] === null ? '—' : $balance['entitled_days'] }}</td>
                                        <td>{{ $balance['used_days'] }}</td>
                                        <td class="{{ $balance['balance'] > 0 ? 'text-success' : 'text-danger' }}">
                                            <strong>{{ $balance['balance'] }}</strong>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted">No leave balance data available.</p>
                @endif
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
                @if($employee->leaveRequests->count() > 0)
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
                                @foreach($employee->leaveRequests as $leave)
                                    <tr>
                                        <td>{{ $leave->leaveType->name }}</td>
                                        <td>{{ $leave->start_date->format('d M Y') }}</td>
                                        <td>{{ $leave->end_date->format('d M Y') }}</td>
                                        <td>{{ $leave->total_days }}</td>
                                        <td>
                                            @if($leave->status == 'pending')
                                                <span class="badge bg-warning">Pending</span>
                                            @elseif($leave->status == 'approved')
                                                <span class="badge bg-success">Approved</span>
                                            @elseif($leave->status == 'rejected')
                                                <span class="badge bg-danger">Rejected</span>
                                            @else
                                                <span class="badge bg-secondary">{{ $leave->status }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted">No leave requests found.</p>
                @endif
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
                @if(isset($vacationHistory) && count($vacationHistory) > 0)
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
                                @php
                                    $cumulativeBalance = 0;
                                @endphp
                                @foreach($vacationHistory as $yearData)
                                    @php
                                        $carryForward = $cumulativeBalance;
                                        $cumulativeBalance += round($yearData['entitled_days'] - $yearData['used_days'], 2);
                                    @endphp
                                    <tr>
                                        <td>{{ $yearData['year'] }}</td>
                                        <td>{{ $yearData['entitled_days'] === null ? '—' : $yearData['entitled_days'] }}</td>
                                        <td>{{ $yearData['used_days'] }}</td>
                                        <td class="{{ $carryForward > 0 ? 'text-success' : ($carryForward < 0 ? 'text-danger' : 'text-muted') }}">
                                            {{ $carryForward }}
                                        </td>
                                        <td class="{{ $cumulativeBalance > 0 ? 'text-success' : 'text-danger' }}">
                                            <strong>{{ $cumulativeBalance }}</strong>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted">No vacation history found.</p>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="mt-3">
    <a href="{{ route('team.employees') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Back to Team
    </a>
</div>
@endsection