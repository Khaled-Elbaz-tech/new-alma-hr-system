@extends('layouts.app')

@section('title', 'Vacation History Report')
@section('page-title', 'Vacation History Report')

@section('content')
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-filter"></i> Filters</h5>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('reports.vacation-history') }}" class="row g-3">
                    <div class="col-md-4">
                        <label for="employee_id" class="form-label">Employee</label>
                        <select name="employee_id" id="employee_id" class="form-select">
                            <option value="">All Employees</option>
                            @foreach($employees as $emp)
                                <option value="{{ $emp->id }}" {{ $employeeId == $emp->id ? 'selected' : '' }}>
                                    {{ $emp->user->name }} ({{ $emp->employee_number }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="department_id" class="form-label">Department</label>
                        <select name="department_id" id="department_id" class="form-select">
                            <option value="">All Departments</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}" {{ $departmentId == $dept->id ? 'selected' : '' }}>
                                    {{ $dept->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="search" class="form-label">Search</label>
                        <input type="text" name="search" id="search" class="form-control" placeholder="Search by name or employee number" value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <label for="year" class="form-label">Year</label>
                        <input type="number" name="year" id="year" class="form-control" 
                               value="{{ $year ?? date('Y') }}" min="2000" max="{{ date('Y') }}">
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="fas fa-search"></i> Apply
                        </button>
                        @if($selectedEmployee && $reportData->count() > 0)
                            <a href="{{ route('reports.export.vacation-history', ['employee_id' => $selectedEmployee->id, 'year' => $year ?? date('Y')]) }}" 
                               class="btn btn-success">
                                <i class="fas fa-file-excel"></i> Export Excel
                            </a>
                        @endif
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@if($selectedEmployee && $reportData->count() > 0)
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-user"></i> 
                    {{ $selectedEmployee->user->name }} 
                    <span class="text-muted">({{ $selectedEmployee->employee_number }})</span>
                </h5>
            </div>
            <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Year</th>
                                    <th>Entitled Days</th>
                                    <th>Used Days</th>
                                    <th>Carry Forward</th>
                                    <th>Balance</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $cumulativeBalance = 0;
                                @endphp
                                @foreach($reportData as $row)
                                    @php
                                        $carryForward = $cumulativeBalance;
                                        $cumulativeBalance += round($row['entitled_days'] - $row['used_days'], 2);
                                    @endphp
                                    <tr>
                                        <td>{{ $row['year'] }}</td>
                                        <td>{{ $row['entitled_days'] ?? '—' }}</td>
                                        <td>{{ $row['used_days'] }}</td>
                                        <td class="{{ $carryForward > 0 ? 'text-success' : ($carryForward < 0 ? 'text-danger' : 'text-muted') }}">
                                            {{ $carryForward }}
                                        </td>
                                        <td class="{{ $cumulativeBalance > 0 ? 'text-success' : 'text-danger' }}">
                                            <strong>{{ $cumulativeBalance }}</strong>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
            </div>
        </div>
    </div>
</div>
@elseif($employeeId)
    <div class="alert alert-info">
        No vacation history found for the selected employee.
    </div>
@endif

<div class="mt-3">
    <a href="{{ route('reports.leave-balance') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Back to Reports
    </a>
</div>
@endsection
