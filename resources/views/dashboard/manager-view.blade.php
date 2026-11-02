@extends('layouts.app')

@section('title', 'Manager Dashboard')
@section('page-title', 'Manager Dashboard')

@section('content')
@if(isset($managedDepartment) && $managedDepartment)
<!-- Department Header -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <h4><i class="fas fa-building"></i> {{ $managedDepartment->name }}</h4>
                <p class="text-muted mb-0">Team Size: {{ isset($teamEmployees) ? $teamEmployees->count() : 0 }} members</p>
            </div>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4>{{ $pendingCount ?? 0 }}</h4>
                        <p class="mb-0">Awaiting HR Approval</p>
                    </div>
                    <i class="fas fa-clock fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4>{{ $approvedCount ?? 0 }}</h4>
                        <p class="mb-0">Approved</p>
                    </div>
                    <i class="fas fa-check fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-danger text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4>{{ $rejectedCount ?? 0 }}</h4>
                        <p class="mb-0">Rejected</p>
                    </div>
                    <i class="fas fa-times fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4>{{ isset($onLeaveToday) ? $onLeaveToday->count() : 0 }}</h4>
                        <p class="mb-0">On Leave Today</p>
                    </div>
                    <i class="fas fa-user-clock fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- HR Approved — Awaiting Your Approval (dashboard visibility) -->
<div class="row">
    <div class="col-md-12">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-check-double"></i> HR Approved &mdash; Awaiting Your Approval
                    <span class="badge bg-warning ms-2">{{ (isset($hrApprovedLeaves) ? $hrApprovedLeaves->count() : 0) + (isset($dmFinalisationLeaves) ? $dmFinalisationLeaves->count() : 0) }} awaiting action</span>
                </h5>
            </div>
            <div class="card-body">
                @if(isset($hrApprovedLeaves) && $hrApprovedLeaves->count() > 0)
                    <div class="list-group mb-3">
                        @foreach($hrApprovedLeaves as $leave)
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <strong>{{ $leave->employee->user->name }}</strong>
                                        @if($leave->status === 'pending' && $leave->skipsHRStep())
                                            <span class="badge bg-secondary ms-2">HR staff &mdash; HR step waived</span>
                                        @else
                                            <span class="badge bg-info ms-2">HR Approved</span>
                                        @endif
                                        <br>
                                        <small class="text-muted">
                                            {{ $leave->leaveType->name }} |
                                            {{ $leave->start_date->format('d M Y') }} - {{ $leave->end_date->format('d M Y') }}
                                            ({{ $leave->total_days }} days)
                                        </small>
                                    </div>
                                    <div>
                                        <form action="{{ route('leaves.manager-approve', $leave) }}" method="POST" class="d-inline" onsubmit="if(!confirm('Approve this leave request?')) return false; this.querySelector('button[type=submit]').disabled=true;">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success">Approve</button>
                                        </form>
                                        <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $leave->id }}">Reject</button>

                                        <!-- Reject Modal -->
                                        <div class="modal fade" id="rejectModal{{ $leave->id }}" tabindex="-1">
                                            <div class="modal-dialog">
                                                <form action="{{ route('leaves.reject', $leave) }}" method="POST" onsubmit="this.querySelector('button[type=submit]').disabled=true;">
                                                    @csrf
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Reject Leave Request</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            @if($errors->any())
                                                                <div class="alert alert-danger">
                                                                    <i class="fas fa-exclamation-circle me-2"></i>{{ $errors->first() }}
                                                                </div>
                                                            @endif
                                                            <div class="mb-3">
                                                                <label class="form-label">Rejection Reason</label>
                                                                <textarea name="rejection_reason" class="form-control" required></textarea>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                            <button type="submit" class="btn btn-danger">Reject</button>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-muted">No requests are waiting for your approval. Requests appear here as soon as HR approves them, or immediately for HR staff requests (HR step waived).</p>
                @endif

                @if(auth()->user()->isDM())
                    <h6 class="mt-3"><i class="fas fa-user-check"></i> Manager Approved &mdash; Awaiting DM Final Approval</h6>
                    @if(isset($dmFinalisationLeaves) && $dmFinalisationLeaves->count() > 0)
                        <div class="list-group">
                            @foreach($dmFinalisationLeaves as $leave)
                                <div class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <strong>{{ $leave->employee->user->name }}</strong>
                                            <span class="badge bg-info ms-2">Manager Approved &mdash; Awaiting DM</span>
                                            <br>
                                            <small class="text-muted">
                                                {{ $leave->leaveType->name }} |
                                                {{ $leave->start_date->format('d M Y') }} - {{ $leave->end_date->format('d M Y') }}
                                                ({{ $leave->total_days }} days)
                                            </small>
                                        </div>
                                        <div>
                                            <form action="{{ route('leaves.manager-approve', $leave) }}" method="POST" class="d-inline" onsubmit="if(!confirm('Give final DM approval to this leave request?')) return false; this.querySelector('button[type=submit]').disabled=true;">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-success">DM Approve</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted mb-0">No requests are waiting for DM final approval.</p>
                    @endif
                @endif
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Awaiting HR Approval (read-only: pending rows await HR, not the manager) -->
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-calendar-minus"></i> Awaiting HR Approval</h5>
            </div>
            <div class="card-body">
                <p class="text-muted small">These requests are waiting for HR approval. They will appear under &ldquo;HR Approved &mdash; Awaiting Your Approval&rdquo; above once HR approves them.</p>
                @if(isset($pendingApprovals) && $pendingApprovals->count() > 0)
                    <div class="list-group">
                        @foreach($pendingApprovals as $leave)
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <strong>{{ $leave->employee->user->name }}</strong>
                                        <span class="badge bg-warning ms-2">Awaiting HR</span>
                                        <br>
                                        <small class="text-muted">
                                            {{ $leave->leaveType->name }} |
                                            {{ $leave->start_date->format('d M') }} - {{ $leave->end_date->format('d M') }}
                                            ({{ $leave->total_days }} days)
                                        </small>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-muted">No requests awaiting HR approval.</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Team Members on Leave Today -->
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-user-clock"></i> On Leave Today</h5>
            </div>
            <div class="card-body">
                @if(isset($onLeaveToday) && $onLeaveToday->count() > 0)
                    <div class="list-group">
                        @foreach($onLeaveToday as $leave)
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <strong>{{ $leave->employee->user->name }}</strong>
                                        <br>
                                        <small class="text-muted">
                                            {{ $leave->leaveType->name }} until {{ $leave->end_date->format('d M') }}
                                        </small>
                                    </div>
                                    <span class="badge bg-info">On Leave</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-muted">No team members on leave today.</p>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Upcoming Team Leaves -->
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-calendar"></i> Upcoming Team Leaves</h5>
            </div>
            <div class="card-body">
                @if(isset($upcomingLeaves) && $upcomingLeaves->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Employee</th>
                                    <th>Leave Type</th>
                                    <th>Start Date</th>
                                    <th>End Date</th>
                                    <th>Days</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($upcomingLeaves as $leave)
                                    <tr>
                                        <td>{{ $leave->employee->user->name }}</td>
                                        <td>{{ $leave->leaveType->name }}</td>
                                        <td>{{ $leave->start_date->format('d M Y') }}</td>
                                        <td>{{ $leave->end_date->format('d M Y') }}</td>
                                        <td>{{ $leave->total_days }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted">No upcoming leaves.</p>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Team Members List -->
<div class="row mt-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-users"></i> Team Members</h5>
            </div>
            <div class="card-body">
                @if(isset($teamEmployees) && $teamEmployees->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Employee Number</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($teamEmployees as $employee)
                                    <tr>
                                        <td>{{ $employee->user->name }}</td>
                                        <td>{{ $employee->employee_number }}</td>
                                        <td>
                                            <span class="badge bg-{{ $employee->employment_status === 'active' ? 'success' : 'secondary' }}">
                                                {{ ucfirst($employee->employment_status) }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted">No team members found.</p>
                @endif
            </div>
        </div>
    </div>
</div>

@else
<div class="alert alert-warning">
    <h4><i class="fas fa-exclamation-triangle"></i> No Department Assigned</h4>
    <p class="mb-0">You are not assigned to manage any department yet. Please contact your administrator.</p>
</div>
@endif
@endsection