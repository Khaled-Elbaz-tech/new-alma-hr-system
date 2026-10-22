@extends('layouts.app')

@section('title', 'Leave Requests')
@section('page-title', 'Leave Requests')

@section('content')
<div class="row mb-3">
    <div class="col-md-12">
        <a href="{{ route('leaves.my') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Apply Leave
        </a>
        @if(auth()->user()->isHrManager() || auth()->user()->isSuperAdmin())
        <a href="{{ route('leaves.create-for-employee') }}" class="btn btn-success">
            <i class="fas fa-user-plus"></i> Create Leave for Employee
        </a>
        <a href="{{ route('leaves.historical.create') }}" class="btn btn-warning">
            <i class="fas fa-history"></i> Record Historical Leave
        </a>
        @endif
    </div>
</div>

<div class="card">
    <div class="card-header">
        <form method="GET" class="row g-3">
            <div class="col-md-2">
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="hr_approved" {{ request('status') == 'hr_approved' ? 'selected' : '' }}>HR Approved</option>
                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
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
                <button type="submit" class="btn btn-secondary">Filter</button>
                <a href="{{ route('leaves.index') }}" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Employee</th>
                        <th>Department</th>
                        <th>Leave Type</th>
                        <th>Dates</th>
                        <th>Days</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($leaveRequests as $leave)
                        <tr>
                            <td>#{{ $leave->id }}</td>
                            <td>
                                <strong>{{ $leave->employee->user->name }}</strong>
                                <br>
                                <small class="text-muted">{{ $leave->employee->employee_number }}</small>
                            </td>
                            <td>{{ $leave->employee->department->name ?? 'N/A' }}</td>
                            <td>{{ $leave->leaveType->name }}</td>
                            <td>
                                {{ $leave->start_date->format('d M') }} - {{ $leave->end_date->format('d M') }}
                            </td>
                            <td>
                                <span class="badge bg-info">{{ $leave->total_days }}</span>
                            </td>
                            <td>
                                <span class="badge bg-{{ $leave->status === 'pending' ? 'warning' : ($leave->status === 'approved' ? 'success' : ($leave->status === 'rejected' ? 'danger' : 'info')) }}">
                                    @if($leave->status === 'pending') Pending
                                    @elseif($leave->status === 'hr_approved') HR Approved
                                    @elseif($leave->status === 'approved') Approved
                                    @elseif($leave->status === 'rejected') Rejected
                                    @elseif($leave->status === 'cancelled') Cancelled
                                    @else {{ ucfirst($leave->status) }}
                                    @endif
                                </span>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#viewModal{{ $leave->id }}">
                                    <i class="fas fa-eye"></i>
                                </button>
                                
                                @if($leave->status === 'pending' && (auth()->user()->isHrManager() || auth()->user()->isSuperAdmin()))
                                <form action="{{ route('leaves.hr-approve', $leave) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success" title="HR Approve">
                                        <i class="fas fa-check"></i>
                                    </button>
                                </form>
                                @endif
                                
                                @if($leave->status === 'hr_approved')
                                    @if(auth()->user()->isManager() && $leave->employee->user->role->name === 'employee')
                                    <form action="{{ route('leaves.manager-approve', $leave) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-primary" title="Manager Approve">
                                            <i class="fas fa-check-double"></i>
                                        </button>
                                    </form>
                                    @elseif(auth()->user()->isDM() && in_array($leave->employee->user->role->name, ['manager', 'dm']))
                                    <form action="{{ route('leaves.manager-approve', $leave) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-primary" title="DM Approve">
                                            <i class="fas fa-check-double"></i>
                                        </button>
                                    </form>
                                    @endif
                                @endif
                                
                                @if(in_array($leave->status, ['pending', 'hr_approved']))
                                <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $leave->id }}" title="Reject">
                                    <i class="fas fa-times"></i>
                                </button>
                                @endif
                            </td>
                        </tr>
                        
                        <!-- View Modal -->
                        <div class="modal fade" id="viewModal{{ $leave->id }}" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Leave Request #{{ $leave->id }}</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <p><strong>Employee:</strong> {{ $leave->employee->user->name }}</p>
                                                <p><strong>Employee ID:</strong> {{ $leave->employee->employee_number }}</p>
                                                <p><strong>Department:</strong> {{ $leave->employee->department->name ?? 'N/A' }}</p>
                                            </div>
                                            <div class="col-md-6">
                                                <p><strong>Leave Type:</strong> {{ $leave->leaveType->name }}</p>
                                                <p><strong>Calendar Days:</strong> {{ $leave->calendar_days }}</p>
                                                <p><strong>Working Days:</strong> {{ $leave->total_days }}</p>
                                            </div>
                                        </div>
                                        <hr>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <p><strong>Start Date:</strong> {{ $leave->start_date->format('d M Y') }}</p>
                                                <p><strong>End Date:</strong> {{ $leave->end_date->format('d M Y') }}</p>
                                            </div>
                                            <div class="col-md-6">
                                                <p><strong>Exit/Reentry:</strong> {{ $leave->exit_reentry_required ? 'Yes' : 'No' }}</p>
                                                <p><strong>Flight Ticket:</strong> {{ $leave->flight_ticket_required ? 'Yes' : 'No' }}</p>
                                            </div>
                                        </div>
                                        @if($leave->reason)
                                        <hr>
                                        <p><strong>Reason:</strong></p>
                                        <p>{{ $leave->reason }}</p>
                                        @endif
                                        <hr>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <p><strong>Status:</strong> 
                                                    <span class="badge bg-{{ $leave->status === 'pending' ? 'warning' : ($leave->status === 'approved' ? 'success' : ($leave->status === 'rejected' ? 'danger' : 'info')) }}">
                                                        {{ ucfirst($leave->status) }}
                                                    </span>
                                                </p>
                                            </div>
                                            <div class="col-md-6">
                                                <p><strong>Created:</strong> {{ $leave->created_at->format('d M Y H:i') }}</p>
                                            </div>
                                        </div>
                                        @if($leave->hr_approved_at)
                                        <p><strong>HR Approved By:</strong> {{ $leave->hrApprover->name ?? 'N/A' }} on {{ $leave->hr_approved_at->format('d M Y H:i') }}</p>
                                        @endif
                                        @if($leave->manager_approved_at)
                                        <p><strong>Manager Approved By:</strong> {{ $leave->managerApprover->name ?? 'N/A' }} on {{ $leave->manager_approved_at->format('d M Y H:i') }}</p>
                                        @endif
                                        @if($leave->rejection_reason)
                                        <div class="alert alert-danger mt-2">
                                            <strong>Rejection Reason:</strong> {{ $leave->rejection_reason }}
                                        </div>
                                        @endif
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Reject Modal -->
                        @if(in_array($leave->status, ['pending', 'hr_approved']))
                        <div class="modal fade" id="rejectModal{{ $leave->id }}" tabindex="-1">
                            <div class="modal-dialog">
                                <form action="{{ route('leaves.reject', $leave) }}" method="POST">
                                    @csrf
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Reject Leave Request #{{ $leave->id }}</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label class="form-label">Rejection Reason <span class="text-danger">*</span></label>
                                                <textarea name="rejection_reason" class="form-control" required rows="3" placeholder="Enter reason for rejection..."></textarea>
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
                        @endif
                    @endforeach
                </tbody>
            </table>
        </div>
        {{ $leaveRequests->links() }}
    </div>
</div>
@endsection