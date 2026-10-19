@extends('layouts.app')

@section('title', 'Employee Report')
@section('page-title', 'Employee Report')

@section('content')
<!-- Filters -->
<div class="card mb-4">
    <div class="card-header">
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Employee</label>
                <select name="employee_id" class="form-select">
                    <option value="">Select Employee</option>
                    @foreach($employees as $emp)
                        <option value="{{ $emp->id }}" {{ $employeeId == $emp->id ? 'selected' : '' }}>
                            {{ $emp->user->name }} ({{ $emp->employee_number }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Department</label>
                <select name="department_id" class="form-select">
                    <option value="">All Departments</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}" {{ $departmentId == $dept->id ? 'selected' : '' }}>
                            {{ $dept->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="active" {{ $status == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="resigned" {{ $status == 'resigned' ? 'selected' : '' }}>Resigned</option>
                    <option value="terminated" {{ $status == 'terminated' ? 'selected' : '' }}>Terminated</option>
                    <option value="on_leave" {{ $status == 'on_leave' ? 'selected' : '' }}>On Leave</option>
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-secondary">Filter</button>
                <a href="{{ route('reports.employee') }}" class="btn btn-outline-secondary ms-2">Reset</a>
            </div>
            <div class="col-md-2 text-end d-flex align-items-end justify-content-end">
                @if($selectedEmployee)
                <a href="{{ route('employees.show', $selectedEmployee) }}" class="btn btn-info">
                    <i class="fas fa-eye"></i> Full Profile
                </a>
                @endif
            </div>
        </form>
    </div>
</div>

@if($selectedEmployee)
<!-- Employee Detailed Report -->
<div class="row">
    <!-- Profile Summary -->
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-user"></i> Profile Summary</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <td><strong>Employee #</strong></td>
                        <td>{{ $selectedEmployee->employee_number }}</td>
                    </tr>
                    <tr>
                        <td><strong>Name</strong></td>
                        <td>{{ $selectedEmployee->user->name }}</td>
                    </tr>
                    <tr>
                        <td><strong>Email</strong></td>
                        <td>{{ $selectedEmployee->user->email }}</td>
                    </tr>
                    <tr>
                        <td><strong>Role</strong></td>
                        <td>{{ $selectedEmployee->user->role->display_name ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td><strong>Department</strong></td>
                        <td>{{ $selectedEmployee->department->name ?? 'Not Assigned' }}</td>
                    </tr>
                    <tr>
                        <td><strong>Manager</strong></td>
                        <td>{{ $selectedEmployee->department->manager->name ?? 'Not Assigned' }}</td>
                    </tr>
                    <tr>
                        <td><strong>Joining Date</strong></td>
                        <td>{{ $selectedEmployee->joining_date->format('d M Y') }}</td>
                    </tr>
                    <tr>
                        <td><strong>Status</strong></td>
                        <td>
                            <span class="badge bg-{{ $selectedEmployee->employment_status === 'active' ? 'success' : 'secondary' }}">
                                {{ ucfirst($selectedEmployee->employment_status) }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Annual Entitlement</strong></td>
                        <td>{{ $selectedEmployee->annual_leave_entitlement }} days</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <!-- Leave Balance Over Years -->
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-calendar-check"></i> Leave Balance Over Years</h5>
            </div>
            <div class="card-body">
                @if(!empty($yearByYearBalances))
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Year</th>
                                    <th>Entitled</th>
                                    <th>Used</th>
                                    <th>Balance</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($yearByYearBalances as $data)
                                <tr>
                                    <td><strong>{{ $data['year'] }}</strong></td>
                                    <td>{{ $data['entitled'] }}</td>
                                    <td>{{ $data['used'] }}</td>
                                    <td>
                                        <strong class="{{ $data['balance'] > 0 ? 'text-success' : 'text-danger' }}">
                                            {{ $data['balance'] }}
                                        </strong>
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

        <!-- Leave History -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-history"></i> Leave History (Last 3 Years)</h5>
            </div>
            <div class="card-body">
                @if(count($leaveHistory) > 0)
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>Year</th>
                                    <th>Type</th>
                                    <th>Dates</th>
                                    <th>Days</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($leaveHistory as $leave)
                                <tr>
                                    <td>{{ $leave->start_date->year }}</td>
                                    <td>{{ $leave->leaveType->name }}</td>
                                    <td>{{ $leave->start_date->format('d M') }} - {{ $leave->end_date->format('d M') }}</td>
                                    <td>{{ $leave->total_days }}</td>
                                    <td>
                                        @if($leave->status === 'approved')
                                            <span class="badge bg-success">Approved</span>
                                        @elseif($leave->status === 'pending')
                                            <span class="badge bg-warning">Pending</span>
                                        @elseif($leave->status === 'rejected')
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
                    
                    <!-- Summary -->
                    <div class="alert alert-light mt-3">
                        <div class="row text-center">
                            <div class="col-md-3">
                                <strong>Total Leaves:</strong> {{ count($leaveHistory) }}
                            </div>
                            <div class="col-md-3">
                                <strong>Total Days:</strong> {{ $leaveHistory->sum('total_days') }}
                            </div>
                            <div class="col-md-3">
                                <strong>Approved:</strong> {{ $leaveHistory->where('status', 'approved')->count() }}
                            </div>
                            <div class="col-md-3">
                                <strong>Exit/Reentry:</strong> {{ $leaveHistory->where('exit_reentry_required', true)->count() }}
                            </div>
                        </div>
                    </div>
                @else
                    <p class="text-muted">No leave history found.</p>
                @endif
            </div>
        </div>

        <!-- Attachments Overview -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-paperclip"></i> Attachments Overview</h5>
            </div>
            <div class="card-body">
                @if(count($attachments) > 0)
                    <div class="list-group">
                        @foreach($attachments as $attachment)
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <strong>{{ $attachment->field->field_label }}</strong>
                                    <br>
                                    <small class="text-muted">{{ $attachment->value }}</small>
                                </div>
                                @if($attachment->value)
                                <a href="{{ asset('storage/' . $attachment->value) }}" target="_blank" class="btn btn-sm btn-info">
                                    <i class="fas fa-download"></i> View
                                </a>
                                @else
                                <span class="badge bg-warning">No file</span>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-muted">No attachment fields configured for this employee.</p>
                @endif
            </div>
        </div>
    </div>
</div>

@else
<!-- Employee List (when no specific employee selected) -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-users"></i> Employee List</h5>
    </div>
    <div class="card-body">
        @if($employees->count() > 0)
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Employee #</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Department</th>
                            <th>Joining Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($employees as $employee)
                        <tr>
                            <td>{{ $employee->employee_number }}</td>
                            <td>
                                <a href="{{ route('reports.employee', ['employee_id' => $employee->id]) }}">
                                    <strong>{{ $employee->user->name }}</strong>
                                </a>
                            </td>
                            <td>{{ $employee->user->email }}</td>
                            <td>{{ $employee->department->name ?? 'N/A' }}</td>
                            <td>{{ $employee->joining_date->format('d M Y') }}</td>
                            <td>
                                <span class="badge bg-{{ $employee->employment_status === 'active' ? 'success' : 'secondary' }}">
                                    {{ ucfirst($employee->employment_status) }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('reports.employee', ['employee_id' => $employee->id]) }}" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i> View Report
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-muted">No employees found.</p>
        @endif
    </div>
</div>
@endif
@endsection