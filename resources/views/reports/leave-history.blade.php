@extends('layouts.app')

@section('title', 'Leave History Report')
@section('page-title', 'Leave History Report')

@section('content')
<div class="row mb-3">
    <div class="col-md-12">
        <form method="GET" class="row g-3">
            <div class="col-md-2">
                <label class="form-label">Employee</label>
                <select name="employee_id" class="form-select">
                    <option value="">Select Employee</option>
                    @foreach($employees as $emp)
                        <option value="{{ $emp->id }}" {{ request('employee_id') == $emp->id ? 'selected' : '' }}>
                            {{ $emp->user->name }} ({{ $emp->employee_number }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Year</label>
                <select name="year" class="form-select">
                    @for($y = date('Y') - 5; $y <= date('Y'); $y++)
                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Leave Type</label>
                <select name="leave_type_id" class="form-select">
                    <option value="">All Types</option>
                    @php
                        $leaveTypes = \App\Models\LeaveType::where('is_active', true)->get();
                    @endphp
                    @foreach($leaveTypes as $type)
                        <option value="{{ $type->id }}" {{ request('leave_type_id') == $type->id ? 'selected' : '' }}>
                            {{ $type->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="hr_approved" {{ request('status') == 'hr_approved' ? 'selected' : '' }}>HR Approved</option>
                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Department</label>
                <select name="department_id" class="form-select">
                    <option value="">All Departments</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">From Date</label>
                <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">To Date</label>
                <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}">
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-secondary">Filter</button>
                <a href="{{ route('reports.leave-history') }}" class="btn btn-outline-secondary ms-2">Reset</a>
            </div>
            <div class="col-md-2 text-end d-flex align-items-end justify-content-end">
                @if($selectedEmployee)
                <a href="{{ route('reports.export.leave-history', ['employee_id' => $selectedEmployee->id, 'year' => $year]) }}" class="btn btn-primary">
                    <i class="fas fa-file-csv"></i> Export
                </a>
                @endif
            </div>
        </form>
    </div>
</div>

@if($selectedEmployee)
<div class="card mb-3">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <h5 class="mb-0"><i class="fas fa-history"></i> Leave History</h5>
            <p class="mb-0 text-muted">{{ $selectedEmployee->user->name }} ({{ $selectedEmployee->employee_number }}) - {{ $year }}</p>
        </div>
    </div>
    <div class="card-body">
        @if($leaveRequests->count() > 0)
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Leave Type</th>
                            <th>Dates</th>
                            <th>Working Days</th>
                            <th>Calendar Days</th>
                            <th>Status</th>
                            <th>HR Approver</th>
                            <th>Manager Approver</th>
                            <th>Created By</th>
                            <th>Created At</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($leaveRequests as $leave)
                        <tr>
                            <td>{{ $leave->id }}</td>
                            <td>
                                <strong>{{ $leave->leaveType->name }}</strong>
                                @if($leave->is_historical)
                                    <span class="badge bg-warning ms-1">Historical</span>
                                @endif
                            </td>
                            <td>
                                {{ $leave->start_date->format('d M') }} - {{ $leave->end_date->format('d M Y') }}
                            </td>
                            <td><span class="badge bg-primary">{{ $leave->total_days }}</span></td>
                            <td>{{ $leave->calendar_days }}</td>
                            <td>
                                @if($leave->status === 'pending')
                                    <span class="badge bg-warning">Pending</span>
                                @elseif($leave->status === 'hr_approved')
                                    <span class="badge bg-info">HR Approved</span>
                                @elseif($leave->status === 'approved')
                                    <span class="badge bg-success">Approved</span>
                                @elseif($leave->status === 'rejected')
                                    <span class="badge bg-danger">Rejected</span>
                                @elseif($leave->status === 'cancelled')
                                    <span class="badge bg-secondary">Cancelled</span>
                                @endif
                            </td>
                            <td>
                                @if($leave->hrApprover)
                                    {{ $leave->hrApprover->name }}
                                    <br><small class="text-muted">{{ $leave->hr_approved_at->format('d M Y') }}</small>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($leave->managerApprover)
                                    {{ $leave->managerApprover->name }}
                                    <br><small class="text-muted">{{ $leave->manager_approved_at->format('d M Y') }}</small>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($leave->creator)
                                    {{ $leave->creator->name }}
                                @else
                                    Self
                                @endif
                            </td>
                            <td>{{ $leave->created_at->format('d M Y') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <!-- Summary -->
            <div class="row mt-3">
                <div class="col-md-12">
                    <div class="alert alert-light">
                        <h6>Summary for {{ $year }}</h6>
                        <div class="row">
                            <div class="col-md-2">
                                <strong>Pending:</strong> {{ $leaveRequests->where('status', 'pending')->count() }}
                            </div>
                            <div class="col-md-2">
                                <strong>Approved:</strong> {{ $leaveRequests->where('status', 'approved')->count() }}
                            </div>
                            <div class="col-md-2">
                                <strong>HR Approved:</strong> {{ $leaveRequests->where('status', 'hr_approved')->count() }}
                            </div>
                            <div class="col-md-2">
                                <strong>Rejected:</strong> {{ $leaveRequests->where('status', 'rejected')->count() }}
                            </div>
                            <div class="col-md-2">
                                <strong>Total Days:</strong> {{ $leaveRequests->sum('total_days') }}
                            </div>
                            <div class="col-md-2">
                                <strong>Exit/Reentry:</strong> {{ $leaveRequests->where('exit_reentry_required', true)->count() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> No leave requests found for this employee in {{ $year }}.
            </div>
        @endif
    </div>
</div>

@if(request('employee_id'))
@php
    // Group by year for previous years
    $allLeaveRequests = \App\Models\LeaveRequest::where('employee_id', request('employee_id'))
        ->whereYear('start_date', '<=', date('Y'))
        ->with(['leaveType', 'hrApprover', 'managerApprover', 'creator'])
        ->orderBy('start_date', 'desc')
        ->get()
        ->groupBy(function($item) {
            return $item->start_date->year;
        });
@endphp

@if($allLeaveRequests->count() > 1)
<div class="card mt-4">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-layer-group"></i> All Years Overview</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-sm">
                <thead>
                    <tr>
                        <th>Year</th>
                        <th>Total Leaves</th>
                        <th>Total Days</th>
                        <th>Approved</th>
                        <th>Rejected</th>
                        <th>Pending</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($allLeaveRequests as $yr => $requests)
                    <tr>
                        <td>
                            <a href="{{ route('reports.leave-history', ['employee_id' => request('employee_id'), 'year' => $yr]) }}">
                                <strong>{{ $yr }}</strong>
                            </a>
                        </td>
                        <td>{{ $requests->count() }}</td>
                        <td>{{ $requests->sum('total_days') }}</td>
                        <td>{{ $requests->where('status', 'approved')->count() }}</td>
                        <td>{{ $requests->where('status', 'rejected')->count() }}</td>
                        <td>{{ $requests->where('status', 'pending')->count() }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif
@endif

@else
<div class="alert alert-info">
    <i class="fas fa-info-circle"></i> Please select an employee to view their leave history.
</div>
@endif
@endsection