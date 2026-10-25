@extends('layouts.app')

@section('title', 'Leave Balance Report')
@section('page-title', 'Leave Balance Report')

@section('content')
<div class="row mb-3">
    <div class="col-md-12">
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Employee</label>
                <select name="employee_id" class="form-select">
                    <option value="">All Employees</option>
                    @foreach($employees as $emp)
                        <option value="{{ $emp->id }}" {{ $employeeId == $emp->id ? 'selected' : '' }}>
                            {{ $emp->user->name }} ({{ $emp->employee_number }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Year</label>
                <select name="year" class="form-select">
                    @for($y = date('Y') - 2; $y <= date('Y') + 1; $y++)
                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
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
            <div class="col-md-3">
                <label class="form-label">Search</label>
                <input type="text" name="search" class="form-control" placeholder="Search by name or employee number" value="{{ request('search') }}">
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-secondary">Filter</button>
                <a href="{{ route('reports.leave-balance') }}" class="btn btn-outline-secondary ms-2">Reset</a>
            </div>
            <div class="col-md-5 text-end d-flex align-items-end justify-content-end gap-2">
                @if($employeeId && $selectedEmployee)
                <a href="{{ route('reports.export.year-by-year', ['employee_id' => $employeeId]) }}" class="btn btn-success">
                    <i class="fas fa-file-excel"></i> Export Year-by-Year
                </a>
                @endif
                <a href="{{ route('reports.export.leave-balance', ['year' => $year]) }}" class="btn btn-primary">
                    <i class="fas fa-file-csv"></i> Export Excel
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Year-by-Year Breakdown (when specific employee selected) -->
@if($selectedEmployee && !empty($yearByYearData))
<div class="card mb-4">
    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
        <div>
            <h5 class="mb-0"><i class="fas fa-history"></i> Year-by-Year Leave History</h5>
            <small>{{ $selectedEmployee->user->name }} ({{ $selectedEmployee->employee_number }})</small>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Year</th>
                        <th>Months from Hiring</th>
                        <th>Entitled Days</th>
                        <th>Used Days</th>
                        <th>Exit/Re-entry</th>
                        <th>Tickets</th>
                        <th>Balance</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($yearByYearData as $data)
                    <tr class="{{ $data['year'] == $year ? 'table-primary' : '' }}">
                        <td><strong>{{ $data['year'] }}</strong></td>
                        <td>{{ $data['months_from_hiring'] }}</td>
                        <td>{{ $data['entitled_days'] ?? '—' }}</td>
                        <td>{{ $data['used_days'] }}</td>
                        <td>{{ $data['exit_reentry_count'] }}</td>
                        <td>{{ $data['ticket_count'] }}</td>
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
    </div>
</div>
@endif

<!-- Summary Table for All Employees -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-users"></i> Leave Balance Summary for {{ $year }}</h5>
    </div>
    <div class="card-body">
        @if(isset($summaryData) && $summaryData->count() > 0)
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Employee</th>
                            <th>Employee #</th>
                            <th>Department</th>
                            <th>Entitled</th>
                            <th>Used</th>
                            <th>Balance</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($summaryData as $item)
                        <tr>
                            <td>
                                <a href="{{ route('reports.leave-balance', ['employee_id' => $item['employee']->id, 'year' => $year]) }}">
                                    {{ $item['employee']->user->name }}
                                </a>
                            </td>
                            <td>{{ $item['employee']->employee_number }}</td>
                            <td>{{ $item['employee']->department->name ?? 'N/A' }}</td>
                            <td>{{ $item['entitled_days'] ?? '—' }}</td>
                            <td>{{ $item['used_days'] }}</td>
                            <td>
                                <strong class="{{ $item['balance'] > 0 ? 'text-success' : 'text-danger' }}">
                                    {{ $item['balance'] }}
                                </strong>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> 
                Select an employee to view their year-by-year breakdown, or view the summary below.
            </div>
        @endif
    </div>
</div>

<!-- Rules Information -->
<div class="card mt-4">
    <div class="card-header bg-light">
        <h6 class="mb-0"><i class="fas fa-info-circle"></i> Leave Balance Rules</h6>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item px-0">
                        <i class="fas fa-calendar text-primary"></i> <strong>21 days</strong> per year (full entitlement)
                    </li>
                    <li class="list-group-item px-0">
                        <i class="fas fa-calculator text-primary"></i> <strong>1.75 days</strong> per month (partial year)
                    </li>
                </ul>
            </div>
            <div class="col-md-4">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item px-0">
                        <i class="fas fa-history text-warning"></i> <strong>Carry over:</strong> Max 5 days
                    </li>
                    <li class="list-group-item px-0">
                        <i class="fas fa-calendar-minus text-info"></i> Calculated from joining date
                    </li>
                </ul>
            </div>
            <div class="col-md-4">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item px-0">
                        <i class="fas fa-plane text-success"></i> Exit/Re-entry: Count tracked
                    </li>
                    <li class="list-group-item px-0">
                        <i class="fas fa-ticket-alt text-success"></i> Flight tickets: Count tracked
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection