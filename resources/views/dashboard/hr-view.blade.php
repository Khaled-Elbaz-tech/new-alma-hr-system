@extends('layouts.app')

@section('title', 'HR Dashboard')
@section('page-title', 'HR Dashboard')

@section('content')
<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4>{{ $employeesCount }}</h4>
                        <p class="mb-0">Employees</p>
                    </div>
                    <i class="fas fa-users fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4>{{ $activeEmployees }}</h4>
                        <p class="mb-0">Active</p>
                    </div>
                    <i class="fas fa-user-check fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4>{{ $pendingLeavesCount }}</h4>
                        <p class="mb-0">Pending Approval</p>
                    </div>
                    <i class="fas fa-clock fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4>{{ $approvedLeavesThisMonth }}</h4>
                        <p class="mb-0">Approved This Month</p>
                    </div>
                    <i class="fas fa-check-circle fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Alerts Section -->
@if(isset($lowBalanceEmployees) && $lowBalanceEmployees->count() > 0)
<div class="alert alert-danger">
    <h5><i class="fas fa-exclamation-triangle"></i> Alerts - Negative Leave Balances</h5>
    <ul class="mb-0">
        @foreach($lowBalanceEmployees as $emp)
            <li>{{ $emp->user->name }} ({{ $emp->employee_number }}) - Has negative leave balance</li>
        @endforeach
    </ul>
</div>
@endif

<div class="row">
    <!-- Pending Leave Requests -->
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-calendar-minus"></i> Pending Leave Requests (HR Approval)</h5>
                <a href="{{ route('leaves.index') }}" class="btn btn-sm btn-primary">View All</a>
            </div>
            <div class="card-body">
                @if(isset($pendingLeaveRequests) && $pendingLeaveRequests->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Employee</th>
                                    <th>Leave Type</th>
                                    <th>Dates</th>
                                    <th>Days</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pendingLeaveRequests as $leave)
                                    <tr>
                                        <td>{{ $leave->employee->user->name }}</td>
                                        <td>{{ $leave->leaveType->name }}</td>
                                        <td>{{ $leave->start_date->format('d M') }} - {{ $leave->end_date->format('d M') }}</td>
                                        <td>{{ $leave->total_days }}</td>
                                        <td>
                                            <form action="{{ route('leaves.hr-approve', $leave) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-success">Approve</button>
                                            </form>
                                            <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $leave->id }}">Reject</button>
                                            
                                            <!-- Reject Modal -->
                                            <div class="modal fade" id="rejectModal{{ $leave->id }}" tabindex="-1">
                                                <div class="modal-dialog">
                                                    <form action="{{ route('leaves.reject', $leave) }}" method="POST">
                                                        @csrf
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">Reject Leave Request</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                            </div>
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
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted">No pending leave requests.</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Upcoming Holidays -->
    <div class="col-md-4">
        <div class="card mb-4">
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
                                    {{ $holiday->start_date->format('d M') }} - {{ $holiday->end_date->format('d M Y') }}
                                </small>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-muted">No upcoming holidays.</p>
                @endif
            </div>
        </div>

        <!-- Department Overview -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-building"></i> Departments</h5>
            </div>
            <div class="card-body">
                @if(isset($departments) && $departments->count() > 0)
                    <div class="list-group">
                        @foreach($departments as $dept)
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <strong>{{ $dept->name }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $dept->employees_count }} employees</small>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-muted">No departments found.</p>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Quick Links -->
<div class="row mt-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <h5 class="mb-3">Quick Actions</h5>
                <div class="d-flex gap-2">
                    <a href="{{ route('employees.create') }}" class="btn btn-primary">
                        <i class="fas fa-user-plus"></i> Add Employee
                    </a>
                    <a href="{{ route('departments.create') }}" class="btn btn-outline-primary">
                        <i class="fas fa-building"></i> Add Department
                    </a>
                    <a href="{{ route('holidays.index') }}" class="btn btn-outline-primary">
                        <i class="fas fa-calendar-week"></i> Manage Holidays
                    </a>
                    <a href="{{ route('reports.leave-balance') }}" class="btn btn-outline-primary">
                        <i class="fas fa-chart-bar"></i> Leave Reports
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection