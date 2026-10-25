@extends('layouts.app')

@section('title', 'Leave Statistics')
@section('page-title', 'Leave Statistics')

@section('content')
<!-- Year Filter -->
<div class="row mb-3">
    <div class="col-md-6">
        <form method="GET" class="d-flex gap-2 align-items-center">
            <label class="mb-0">Year:</label>
            <select name="year" class="form-select" style="width: auto;">
                @for($y = date('Y') - 2; $y <= date('Y'); $y++)
                    <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endfor
            </select>
            <label class="mb-0">Department:</label>
            <select name="department_id" class="form-select" style="width: auto;">
                <option value="">All Departments</option>
                @foreach($departments as $dept)
                    <option value="{{ $dept->id }}" {{ $departmentId == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-secondary">Filter</button>
            <a href="{{ route('reports.leave-statistics') }}" class="btn btn-outline-secondary">Reset</a>
        </form>
    </div>
    <div class="col-md-6 text-end">
        <span class="text-muted"><i class="fas fa-lock"></i> Read-only</span>
    </div>
</div>

<!-- Summary Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <p class="mb-0">Exit Reentry</p>
                        <h3>{{ $exitReentryCount }}</h3>
                    </div>
                    <i class="fas fa-passport fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <p class="mb-0">Flight Tickets</p>
                        <h3>{{ $ticketCount }}</h3>
                    </div>
                    <i class="fas fa-ticket-alt fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <p class="mb-0">Total Approved</p>
                        <h3>{{ collect($monthlyStats)->sum('approved') }}</h3>
                    </div>
                    <i class="fas fa-check-circle fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <p class="mb-0">Total Rejected</p>
                        <h3>{{ collect($monthlyStats)->sum('rejected') }}</h3>
                    </div>
                    <i class="fas fa-times-circle fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row 1 -->
<div class="row">
    <!-- Monthly Leave Usage Chart -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-chart-bar"></i> Monthly Leave Usage</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <thead class="table-light">
                            <tr>
                                <th>Month</th>
                                <th>Approved</th>
                                <th>Visual</th>
                                <th>Rejected</th>
                                <th>Pending</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']; @endphp
                            @for($m = 1; $m <= 12; $m++)
                            @php $total = $monthlyStats[$m]['total'] ?? 0; $max = 20; $width = min(($total / $max) * 100, 100); @endphp
                            <tr>
                                <td>{{ $months[$m-1] }}</td>
                                <td>
                                    <span class="badge bg-success">{{ $monthlyStats[$m]['approved'] ?? 0 }}</span>
                                </td>
                                <td>
                                    <div class="progress" style="height: 20px;">
                                        <div class="progress-bar bg-success" style="width: {{ (($monthlyStats[$m]['approved'] ?? 0) / $max) * 100 }}%"></div>
                                        <div class="progress-bar bg-danger" style="width: {{ (($monthlyStats[$m]['rejected'] ?? 0) / $max) * 100 }}%"></div>
                                        <div class="progress-bar bg-warning" style="width: {{ (($monthlyStats[$m]['pending'] ?? 0) / $max) * 100 }}%"></div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-danger">{{ $monthlyStats[$m]['rejected'] ?? 0 }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-warning">{{ $monthlyStats[$m]['pending'] ?? 0 }}</span>
                                </td>
                            </tr>
                            @endfor
                        </tbody>
                    </table>
                </div>
                <div class="mt-2">
                    <small class="text-muted">
                        <span class="badge bg-success">Approved</span>
                        <span class="badge bg-danger">Rejected</span>
                        <span class="badge bg-warning">Pending</span>
                    </small>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Leave Type Distribution -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-chart-pie"></i> Leave Type Distribution</h5>
            </div>
            <div class="card-body">
                @php $totalDays = collect($leaveTypeStats)->sum('days'); @endphp
                @if($totalDays > 0)
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>Leave Type</th>
                                    <th>Count</th>
                                    <th>Days</th>
                                    <th>Percentage</th>
                                    <th>Visual</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($leaveTypeStats as $stat)
                                @php $percentage = $totalDays > 0 ? round(($stat['days'] / $totalDays) * 100, 1) : 0; @endphp
                                <tr>
                                    <td><strong>{{ $stat['name'] }}</strong></td>
                                    <td>{{ $stat['count'] }}</td>
                                    <td>{{ $stat['days'] }}</td>
                                    <td>{{ $percentage }}%</td>
                                    <td>
                                        <div class="progress" style="height: 15px;">
                                            <div class="progress-bar bg-primary" style="width: {{ $percentage }}%"></div>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="table-light">
                                    <td><strong>Total</strong></td>
                                    <td><strong>{{ collect($leaveTypeStats)->sum('count') }}</strong></td>
                                    <td><strong>{{ $totalDays }}</strong></td>
                                    <td>100%</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                @else
                    <p class="text-muted">No leave data available for this year.</p>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Charts Row 2 -->
<div class="row">
    <!-- Sick vs Annual Trends -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-heartbeat"></i> Sick vs Annual Leave Trends</h5>
            </div>
            <div class="card-body">
                @php 
                    $annualType = collect($leaveTypeStats)->firstWhere('name', 'Annual Leave');
                    $sickType = collect($leaveTypeStats)->firstWhere('name', 'Sick Leave');
                    $annualDays = $annualType['days'] ?? 0;
                    $sickDays = $sickType['days'] ?? 0;
                @endphp
                <div class="row text-center mb-4">
                    <div class="col-md-6">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <h4>{{ $annualDays }}</h4>
                                <p class="mb-0">Annual Leave Days</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card bg-warning text-white">
                            <div class="card-body">
                                <h4>{{ $sickDays }}</h4>
                                <p class="mb-0">Sick Leave Days</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Monthly comparison -->
                <table class="table table-bordered table-sm">
                    <thead class="table-light">
                        <tr>
                            <th>Month</th>
                            <th>Annual</th>
                            <th>Annual Bar</th>
                            <th>Sick</th>
                            <th>Sick Bar</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php 
                            $annualLeaveTypeId = \App\Models\LeaveType::where('name', 'Annual Leave')->first()?->id;
                            $sickLeaveTypeId = \App\Models\LeaveType::where('name', 'Sick Leave')->first()?->id;
                        @endphp
                        @for($m = 1; $m <= 12; $m++)
                        @php 
                            $annualCount = \App\Models\LeaveRequest::whereYear('start_date', $year)->whereMonth('start_date', $m)->where('leave_type_id', $annualLeaveTypeId)->where('status', 'approved')->sum('total_days');
                            $sickCount = \App\Models\LeaveRequest::whereYear('start_date', $year)->whereMonth('start_date', $m)->where('leave_type_id', $sickLeaveTypeId)->where('status', 'approved')->sum('total_days');
                            $maxVal = max($annualCount, $sickCount, 1);
                        @endphp
                        <tr>
                            <td>{{ $months[$m-1] }}</td>
                            <td>{{ $annualCount }}</td>
                            <td>
                                <div class="progress" style="height: 15px;">
                                    <div class="progress-bar bg-success" style="width: {{ ($annualCount / $maxVal) * 100 }}%"></div>
                                </div>
                            </td>
                            <td>{{ $sickCount }}</td>
                            <td>
                                <div class="progress" style="height: 15px;">
                                    <div class="progress-bar bg-warning" style="width: {{ ($sickCount / $maxVal) * 100 }}%"></div>
                                </div>
                            </td>
                        </tr>
                        @endfor
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Department Absence Comparison -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-building"></i> Department Absence Comparison</h5>
            </div>
            <div class="card-body">
                @php 
                    $departments = \App\Models\Department::with('employees')->get();
                    $deptData = [];
                    foreach($departments as $dept) {
                        $employeeIds = $dept->employees->pluck('id');
                        $days = \App\Models\LeaveRequest::whereIn('employee_id', $employeeIds)->whereYear('start_date', $year)->where('status', 'approved')->sum('total_days');
                        $deptData[] = ['name' => $dept->name, 'days' => $days, 'count' => $dept->employees_count];
                    }
                    $deptData = collect($deptData)->sortByDesc('days');
                    $maxDeptDays = $deptData->max('days') ?: 1;
                @endphp
                
                @if($deptData->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>Department</th>
                                    <th>Employees</th>
                                    <th>Leave Days</th>
                                    <th>Avg/Employee</th>
                                    <th>Visual</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($deptData as $dept)
                                @php $avg = $dept['count'] > 0 ? round($dept['days'] / $dept['count'], 1) : 0; @endphp
                                <tr>
                                    <td><strong>{{ $dept['name'] }}</strong></td>
                                    <td>{{ $dept['count'] }}</td>
                                    <td>{{ $dept['days'] }}</td>
                                    <td>{{ $avg }}</td>
                                    <td>
                                        <div class="progress" style="height: 15px;">
                                            <div class="progress-bar bg-info" style="width: {{ ($dept['days'] / $maxDeptDays) * 100 }}%"></div>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted">No department data available.</p>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Summary -->
<div class="row">
    <div class="col-md-12">
        <div class="alert alert-light">
            <h5><i class="fas fa-info-circle"></i> Statistics Summary for {{ $year }}</h5>
            <div class="row">
                <div class="col-md-3">
                    <strong>Total Leave Requests:</strong> {{ collect($monthlyStats)->sum('total') }}
                </div>
                <div class="col-md-3">
                    <strong>Approval Rate:</strong> {{ collect($monthlyStats)->sum('total') > 0 ? round((collect($monthlyStats)->sum('approved') / collect($monthlyStats)->sum('total')) * 100, 1) : 0 }}%
                </div>
                <div class="col-md-3">
                    <strong>Rejection Rate:</strong> {{ collect($monthlyStats)->sum('total') > 0 ? round((collect($monthlyStats)->sum('rejected') / collect($monthlyStats)->sum('total')) * 100, 1) : 0 }}%
                </div>
                <div class="col-md-3">
                    <strong>Total Days Taken:</strong> {{ collect($leaveTypeStats)->sum('days') }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection