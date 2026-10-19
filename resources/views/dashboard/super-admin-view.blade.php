@extends('layouts.app')

@section('title', 'Super Admin Dashboard')
@section('page-title', 'Super Admin Dashboard')

@section('content')
<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-2">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4>{{ $totalUsers }}</h4>
                        <p class="mb-0">Total Users</p>
                    </div>
                    <i class="fas fa-users fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4>{{ $totalEmployees }}</h4>
                        <p class="mb-0">Employees</p>
                    </div>
                    <i class="fas fa-user-tie fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4>{{ $totalDepartments }}</h4>
                        <p class="mb-0">Departments</p>
                    </div>
                    <i class="fas fa-building fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4>{{ $totalLeaveRequests }}</h4>
                        <p class="mb-0">Leave Requests</p>
                    </div>
                    <i class="fas fa-calendar-minus fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card bg-secondary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4>{{ $systemActivityCount }}</h4>
                        <p class="mb-0">Activities (30d)</p>
                    </div>
                    <i class="fas fa-history fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card bg-danger text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4>{{ $pendingLeavesCount }}</h4>
                        <p class="mb-0">Pending Leaves</p>
                    </div>
                    <i class="fas fa-clock fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Employee Status -->
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-users"></i> Employee Status</h5>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span>Active Employees</span>
                    <span class="badge bg-success">{{ $activeEmployees }}</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span>Inactive Employees</span>
                    <span class="badge bg-secondary">{{ $inactiveEmployees }}</span>
                </div>
                <div class="d-flex justify-content-between">
                    <span>Total</span>
                    <span class="badge bg-primary">{{ $totalEmployees }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Leave Statistics -->
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-calendar-check"></i> Leave This Month</h5>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span>Approved</span>
                    <span class="badge bg-success">{{ $approvedLeavesThisMonth }}</span>
                </div>
                <div class="d-flex justify-content-between">
                    <span>Pending Approval</span>
                    <span class="badge bg-warning">{{ $pendingLeavesCount }}</span>
                </div>
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

<!-- Department Breakdown -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-building"></i> Department Overview</h5>
                <a href="{{ route('departments.index') }}" class="btn btn-sm btn-primary">Manage Departments</a>
            </div>
            <div class="card-body">
                @if(isset($departments) && $departments->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Department</th>
                                    <th>Employees</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($departments as $dept)
                                    <tr>
                                        <td>{{ $dept->name }}</td>
                                        <td>
                                            <span class="badge bg-primary">{{ $dept->employees_count }}</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted">No departments found.</p>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Recent Activity Logs -->
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-history"></i> Recent Activity Logs (Latest 10)</h5>
                <a href="{{ route('activity-logs.index') }}" class="btn btn-sm btn-primary">View All</a>
            </div>
            <div class="card-body">
                @if(isset($recentActivityLogs) && $recentActivityLogs->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Action</th>
                                    <th>Model</th>
                                    <th>Time</th>
                                    <th>IP</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentActivityLogs as $log)
                                    <tr>
                                        <td>{{ $log->user->name ?? 'System' }}</td>
                                        <td>
                                            <span class="badge bg-{{ $log->action == 'create' ? 'success' : ($log->action == 'delete' ? 'danger' : 'info') }}">
                                                {{ $log->action }}
                                            </span>
                                        </td>
                                        <td>{{ class_basename($log->model_type) ?? '-' }}</td>
                                        <td>{{ $log->created_at->format('d M H:i') }}</td>
                                        <td><small class="text-muted">{{ $log->ip_address }}</small></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted">No activity logs found.</p>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row mt-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <h5 class="mb-3">Quick Actions</h5>
                <div class="d-flex gap-2">
                    <a href="{{ route('users.index') }}" class="btn btn-primary">
                        <i class="fas fa-user-cog"></i> Manage Users
                    </a>
                    <a href="{{ route('employees.index') }}" class="btn btn-outline-primary">
                        <i class="fas fa-users"></i> Manage Employees
                    </a>
                    <a href="{{ route('departments.index') }}" class="btn btn-outline-primary">
                        <i class="fas fa-building"></i> Manage Departments
                    </a>
                    <a href="{{ route('activity-logs.index') }}" class="btn btn-outline-primary">
                        <i class="fas fa-history"></i> Activity Logs
                    </a>
                    <a href="{{ route('dynamic-forms.index') }}" class="btn btn-outline-primary">
                        <i class="fas fa-cogs"></i> System Settings
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection