@extends('layouts.app')

@section('title', 'Manager Dashboard - Leave Requests')
@section('page-title', 'My Team Leave Requests')

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-users"></i> Leave Requests from My Team</h5>
    </div>
    <div class="card-body">
        <form method="GET" class="mb-3">
            <div class="row">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="Search employee..."
                           value="{{ request('search') }}">
                </div>
                <div class="col-md-4">
                    <select name="status" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="hr_approved" {{ request('status') == 'hr_approved' ? 'selected' : '' }}>HR Approved</option>
                        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Filter</button>
                    <a href="{{ route('leaves.manager.index') }}" class="btn btn-outline-secondary">Reset</a>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Leave Type</th>
                        <th>Period</th>
                        <th>Days</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($leaveRequests as $leave)
                        <tr>
                            <td>
                                <strong>{{ $leave->employee->user->name }}</strong><br>
                                <small>{{ $leave->employee->employee_id }}</small>
                            </td>
                            <td>{{ $leave->leaveType->name }}</td>
                            <td>
                                {{ $leave->start_date->format('d M Y') }} - {{ $leave->end_date->format('d M Y') }}
                            </td>
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
                                @if($leave->status === 'hr_approved')
                                    <form action="{{ route('leaves.manager-approve', $leave) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Approve this leave request?')">
                                            Approve
                                        </button>
                                    </form>
                                    <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $leave->id }}">
                                        Reject
                                    </button>
                                @else
                                    <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#viewModal{{ $leave->id }}">View</button>
                                @endif

                                <!-- View Modal -->
                                <div class="modal fade" id="viewModal{{ $leave->id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Leave Request Details</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                 <p><strong>Employee:</strong> {{ $leave->employee->user->name }}</p>
                                                <p><strong>Leave Type:</strong> {{ $leave->leaveType->name }}</p>
                                                <p><strong>Period:</strong> {{ $leave->start_date->format('d M Y') }} to {{ $leave->end_date->format('d M Y') }}</p>
                                                <p><strong>Working Days:</strong> {{ $leave->total_days }}</p>
                                                <p><strong>Calendar Days:</strong> {{ $leave->calendar_days }}</p>
                                                <p><strong>Status:</strong> {{ ucfirst($leave->status) }}</p>
                                                @if($leave->reason)
                                                <p><strong>Reason:</strong> {{ $leave->reason }}</p>
                                                @endif
                                                @if($leave->flight_ticket_required)
                                                <p><span class="badge bg-info">Flight Ticket Required</span></p>
                                                @endif
                                                @if($leave->exit_reentry_required)
                                                <p><span class="badge bg-warning">Exit Re-entry Required</span></p>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Reject Modal -->
                                <div class="modal fade" id="rejectModal{{ $leave->id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Reject Leave Request</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <form action="{{ route('leaves.reject', $leave) }}" method="POST">
                                                @csrf
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label class="form-label">Rejection Reason</label>
                                                        <textarea name="rejection_reason" class="form-control" required></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-danger">Reject</button>
                                                </div>
                                            </form>
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
@endsection
