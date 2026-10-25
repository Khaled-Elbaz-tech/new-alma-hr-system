@extends('layouts.app')

@section('title', 'Department Report')
@section('page-title', 'Department Report')

@section('content')
<!-- Filters -->
<div class="card mb-4">
    <div class="card-header">
        <form method="GET" class="row g-3">
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
                <label class="form-label">Start Date</label>
                <input type="date" name="start_date" class="form-control" value="{{ $startDate }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">End Date</label>
                <input type="date" name="end_date" class="form-control" value="{{ $endDate }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">Leave Type</label>
                <select name="leave_type_id" class="form-select">
                    <option value="">All Types</option>
                    @foreach($leaveTypes as $type)
                        <option value="{{ $type->id }}" {{ $leaveTypeId == $type->id ? 'selected' : '' }}>
                            {{ $type->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Search</label>
                <input type="text" name="search" class="form-control" placeholder="Search department..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-secondary">Filter</button>
                <a href="{{ route('reports.department') }}" class="btn btn-outline-secondary ms-2">Reset</a>
            </div>
            <div class="col-md-2 text-end d-flex align-items-end justify-content-end">
                <a href="{{ route('reports.export.leave-balance', ['year' => date('Y')]) }}" class="btn btn-primary">
                    <i class="fas fa-file-csv"></i> Export
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Department Overview Table -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-building"></i> Department Overview</h5>
    </div>
    <div class="card-body">
        @if($departments->count() > 0)
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Department</th>
                            <th>Code</th>
                            <th>Manager</th>
                            <th>Total Employees</th>
                            <th>Leaves Taken</th>
                            <th>Days Taken</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($departments as $dept)
                        <tr>
                            <td>
                                <strong>{{ $dept->name }}</strong>
                            </td>
                            <td>{{ $dept->code }}</td>
                            <td>{{ $dept->manager->name ?? 'Not Assigned' }}</td>
                            <td>
                                <span class="badge bg-primary">{{ $dept->employees_count }}</span>
                            </td>
                            <td>
                                {{ $departmentData[$dept->id]['leaves_taken'] ?? 0 }}
                            </td>
                            <td>
                                <span class="badge bg-info">{{ $departmentData[$dept->id]['days_taken'] ?? 0 }}</span>
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

<!-- Upcoming Leaves -->
<div class="row">
    @if($departmentId)
    @php
        $selectedDept = $departments->find($departmentId);
        $upcoming = $selectedDept && isset($departmentData[$departmentId]) ? $departmentData[$departmentId]['upcoming_leaves'] : collect();
    @endphp
    @else
    @php
        $upcoming = collect();
        foreach($departments as $dept) {
            if(isset($departmentData[$dept->id])) {
                $upcoming = $upcoming->merge($departmentData[$dept->id]['upcoming_leaves']);
            }
        }
        $upcoming = $upcoming->sortBy('start_date')->take(20);
    @endphp
    @endif
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-calendar-plus"></i> Upcoming Leaves (Next 30 Days)</h5>
            </div>
            <div class="card-body">
                @if($upcoming->count() > 0)
                    <div class="list-group">
                        @foreach($upcoming as $leave)
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <strong>{{ $leave->employee->user->name }}</strong>
                                    <br>
                                    <small class="text-muted">
                                        {{ $leave->leaveType->name }} | {{ $leave->total_days }} days
                                    </small>
                                </div>
                                <div class="text-end">
                                    <span class="badge bg-success">{{ $leave->start_date->format('d M') }} - {{ $leave->end_date->format('d M') }}</span>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-muted">No upcoming leaves in the next 30 days.</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Absence Trend Chart (Table) -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-chart-line"></i> Absence Trend ({{ date('Y') }})</h5>
            </div>
            <div class="card-body">
                @if($departments->count() > 0)
                    <div class="table-responsive" style="max-height: 300px;">
                        <table class="table table-sm table-bordered">
                            <thead>
                                <tr>
                                    <th>Department</th>
                                    @for($m = 1; $m <= 12; $m++)
                                    <th>{{ date('M', mktime(0, 0, 0, $m, 1)) }}</th>
                                    @endfor
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($departments as $dept)
                                <tr>
                                    <td><strong>{{ $dept->name }}</strong></td>
                                    @for($m = 1; $m <= 12; $m++)
                                    <td class="text-center">
                                        @php
                                            $count = isset($departmentData[$dept->id]['monthly_trend'][$m]) ? $departmentData[$dept->id]['monthly_trend'][$m] : 0;
                                        @endphp
                                        @if($count > 0)
                                            <span class="badge bg-{{ $count > 5 ? 'danger' : ($count > 2 ? 'warning' : 'success') }}">
                                                {{ $count }}
                                            </span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    @endfor
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-2">
                        <small class="text-muted">
                            <span class="badge bg-success">Low</span>
                            <span class="badge bg-warning">Medium</span>
                            <span class="badge bg-danger">High</span>
                        </small>
                    </div>
                @else
                    <p class="text-muted">No department data available.</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection