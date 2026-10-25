@extends('layouts.app')

@section('title', 'My Dashboard')
@section('page-title', 'My Dashboard')

@section('content')
<div class="row mb-4">
    <!-- Leave Balance Summary -->
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-calendar-check"></i> Current Leave Balance ({{ date('Y') }})</h5>
            </div>
            <div class="card-body">
                @if(isset($currentBalance) && $currentBalance)
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Leave Type</th>
                                    <th>Entitled</th>
                                    <th>Used</th>
                                    <th>Balance</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Annual Leave</td>
                                    <td>{{ $currentBalance->entitled_days ?? '—' }}</td>
                                    <td>{{ $currentBalance->used_days }}</td>
                                    <td class="{{ $currentBalance->balance > 0 ? 'text-success' : 'text-danger' }}">
                                        <strong>{{ $currentBalance->balance }}</strong>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted">No leave balance records found. Please contact HR to set up your leave entitlements.</p>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <!-- Previous Year Balance -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-history"></i> Previous Year Balance ({{ date('Y') - 1 }})</h5>
            </div>
            <div class="card-body">
                @if(isset($previousBalance) && $previousBalance)
                    <p class="mb-0">
                        <strong>Balance:</strong> 
                        <span class="{{ $previousBalance->balance > 0 ? 'text-success' : 'text-danger' }}">
                            {{ $previousBalance->balance }}
                        </span>
                    </p>
                    <p class="mb-0 text-muted">
                        Entitled: {{ $previousBalance->entitled_days }} | Used: {{ $previousBalance->used_days }}
                    </p>
                @else
                    <p class="text-muted mb-0">No balance record for {{ date('Y') - 1 }}</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Total Taken This Year -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-calendar-alt"></i> Total Taken This Year</h5>
            </div>
            <div class="card-body">
                <p class="mb-0">
                    <strong>{{ isset($totalTakenThisYear) ? $totalTakenThisYear : 0 }}</strong> days
                </p>
                <p class="mb-0 text-muted">Approved leaves in {{ date('Y') }}</p>
            </div>
        </div>
    </div>

    <!-- Current Balance Summary -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-wallet"></i> Current Balance Summary</h5>
            </div>
            <div class="card-body">
                @if(isset($currentBalance) && $currentBalance)
                    <p class="mb-0">
                        <strong>Available:</strong> 
                        <span class="{{ $currentBalance->balance > 0 ? 'text-success' : 'text-danger' }}">
                            {{ $currentBalance->balance }} days
                        </span>
                    </p>
                    <p class="mb-0 text-muted">
                        Carry-forward included
                    </p>
                @else
                    <p class="text-muted mb-0">No balance data available</p>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Notifications / Alerts -->
@if(isset($nextApprovedLeave))
<div class="alert alert-info">
    <h5><i class="fas fa-bell"></i> Your Next Approved Leave</h5>
    <p class="mb-0">
        <strong>{{ $nextApprovedLeave->leaveType->name }}</strong> from 
        {{ $nextApprovedLeave->start_date->format('d M Y') }} to {{ $nextApprovedLeave->end_date->format('d M Y') }} 
        ({{ $nextApprovedLeave->total_days }} days)
    </p>
</div>
@endif

<div class="row">
    <!-- Leave Request Status List -->
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-list"></i> My Leave Requests</h5>
                <a href="{{ route('leaves.my') }}" class="btn btn-sm btn-primary">
                    <i class="fas fa-plus"></i> Apply Leave
                </a>
            </div>
            <div class="card-body">
                @if(isset($allLeaveRequests) && $allLeaveRequests->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Type</th>
                                    <th>Dates</th>
                                    <th>Days</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($allLeaveRequests as $leave)
                                    <tr>
                                        <td>{{ $leave->leaveType->name }}</td>
                                        <td>{{ $leave->start_date->format('d M') }} - {{ $leave->end_date->format('d M') }}</td>
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
                    <div class="mt-3">
                        <a href="{{ route('leaves.my') }}" class="btn btn-outline-secondary btn-sm">View All My Leaves</a>
                    </div>
                @else
                    <p class="text-muted">No leave requests yet.</p>
                    <a href="{{ route('leaves.my') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Apply for Leave
                    </a>
                @endif
            </div>
        </div>
    </div>

    <!-- Status Summary -->
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-chart-pie"></i> Request Summary</h5>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span>Pending</span>
                    <span class="badge bg-warning">{{ $pendingCount ?? 0 }}</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span>Approved</span>
                    <span class="badge bg-success">{{ $approvedCount ?? 0 }}</span>
                </div>
                <div class="d-flex justify-content-between">
                    <span>Rejected</span>
                    <span class="badge bg-danger">{{ $rejectedCount ?? 0 }}</span>
                </div>
            </div>
        </div>

        <!-- Upcoming Holidays -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-calendar-day"></i> Upcoming Holidays</h5>
            </div>
            <div class="card-body">
                @if(isset($upcomingHolidays) && $upcomingHolidays->count() > 0)
                    <div class="list-group">
                        @foreach($upcomingHolidays as $holiday)
                            <div class="list-group-item">
                                <strong>{{ $holiday->name }}</strong>
                                <br>
                                <small class="text-muted">
                                    {{ $holiday->start_date->format('d M') }} - {{ $holiday->end_date->format('d M') }}
                                </small>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-muted">No upcoming holidays.</p>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row mt-3">
    <div class="col-md-12">
        <a href="{{ route('leaves.my', ['open_form' => 1]) }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Apply for Leave
        </a>
        <a href="{{ route('leaves.my') }}" class="btn btn-outline-secondary">
            <i class="fas fa-list"></i> View All Leaves
        </a>
    </div>
</div>
@endsection