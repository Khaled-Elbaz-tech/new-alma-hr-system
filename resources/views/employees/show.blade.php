@extends('layouts.app')

@section('title', 'Employee Details')
@section('page-title', 'Employee Details - ' . $employee->user->name)

@section('content')
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="bg-primary rounded-circle p-3 me-3">
                        <i class="fas fa-user text-white fa-2x"></i>
                    </div>
                    <div>
                        <h4 mb-0>{{ $employee->user->name }}</h4>
                        <p class="text-muted mb-0">{{ $employee->employee_number }} | {{ $employee->department->name ?? 'No Department' }}</p>
                    </div>
                    <div class="ms-auto">
                        <span class="badge bg-{{ $employee->employment_status === 'active' ? 'success' : 'secondary' }}">
                            {{ ucfirst($employee->employment_status) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tabs -->
<ul class="nav nav-tabs mb-4" role="tablist">
    <li class="nav-item">
        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#personal" type="button">
            <i class="fas fa-user"></i> Personal Info
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#employment" type="button">
            <i class="fas fa-briefcase"></i> Employment Info
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#dynamic-fields" type="button">
            <i class="fas fa-list"></i> Dynamic Fields
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#leave-summary" type="button">
            <i class="fas fa-calendar-check"></i> Leave Summary
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#leave-history" type="button">
            <i class="fas fa-history"></i> Leave History
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#vacation-history" type="button">
            <i class="fas fa-book"></i> Vacation History
        </button>
    </li>
</ul>

<!-- Tab Content -->
<div class="tab-content">
    <!-- Personal Info Tab -->
    <div class="tab-pane fade show active" id="personal">
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <h5 class="mb-0"><i class="fas fa-user"></i> Personal Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Full Name:</strong> {{ $employee->user->name }}</p>
                        <p><strong>Email:</strong> {{ $employee->user->email }}</p>
                        <p><strong>Employee Number:</strong> {{ $employee->employee_number }}</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Role:</strong> {{ $employee->user->role->display_name ?? 'N/A' }}</p>
                        <p><strong>Status:</strong> 
                            <span class="badge bg-{{ $employee->employment_status === 'active' ? 'success' : 'secondary' }}">
                                {{ ucfirst($employee->employment_status) }}
                            </span>
                        </p>
                        <p><strong>Account:</strong> 
                            <span class="badge bg-{{ $employee->user->is_active ? 'success' : 'danger' }}">
                                {{ $employee->user->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Employment Info Tab -->
    <div class="tab-pane fade" id="employment">
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <h5 class="mb-0"><i class="fas fa-briefcase"></i> Employment Information</h5>
                @can('edit', $employee)
                <a href="{{ route('employees.edit', $employee) }}" class="btn btn-sm btn-warning">
                    <i class="fas fa-edit"></i> Edit
                </a>
                @endcan
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Department:</strong> {{ $employee->department->name ?? 'Not Assigned' }}</p>
                        <p><strong>Joining Date:</strong> {{ $employee->joining_date ? $employee->joining_date->format('d M Y') : 'N/A' }}</p>
                        <p><strong>Annual Leave Entitlement:</strong> {{ $employee->annual_leave_entitlement }} days</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Contract Start:</strong> {{ $employee->contract_start_date ? $employee->contract_start_date->format('d M Y') : 'N/A' }}</p>
                        <p><strong>Contract End:</strong> {{ $employee->contract_end_date ? $employee->contract_end_date->format('d M Y') : 'N/A' }}</p>
                        <p><strong>Employment Status:</strong> {{ ucfirst($employee->employment_status) }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Dynamic Fields Tab -->
    <div class="tab-pane fade" id="dynamic-fields">
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <h5 class="mb-0"><i class="fas fa-list"></i> Dynamic Fields</h5>
                @if(auth()->user()->isHrManager() || auth()->user()->isSuperAdmin())
                <a href="{{ route('dynamic-forms.employee-fields', $employee) }}" class="btn btn-sm btn-primary">
                    <i class="fas fa-cog"></i> Manage Fields
                </a>
                @endif
            </div>
            <div class="card-body">
                @php
                    $fields = \App\Models\EmployeeField::where('is_active', true)->orderBy('field_order')->get()->groupBy('field_section');
                @endphp
                @if($fields->count() > 0)
                    @foreach($fields as $section => $sectionFields)
                        <div class="mb-4">
                            <h6 class="text-uppercase text-muted mb-3 border-bottom pb-2">{{ ucfirst($section) }}</h6>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th style="width: 30%;">Field</th>
                                            <th>Type</th>
                                            <th>Value</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($sectionFields as $field)
                                            @php
                                                $fieldValue = $employee->fieldValues->where('field_id', $field->id)->first();
                                            @endphp
                                            <tr>
                                                <td><strong>{{ $field->field_label }}</strong></td>
                                                <td><span class="badge bg-info">{{ $field->field_type }}</span></td>
                                                <td>
                                                    @if($field->field_type === 'attachment')
                                                        @if($fieldValue && $fieldValue->value)
                                                            <a href="{{ asset('storage/' . $fieldValue->value) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                                                <i class="fas fa-file-download"></i> Download
                                                            </a>
                                                        @else
                                                            <span class="text-muted">No file uploaded</span>
                                                        @endif
                                                    @elseif($field->field_type === 'checkbox')
                                                        {{ $fieldValue && $fieldValue->value ? 'Yes' : 'No' }}
                                                    @elseif($field->field_type === 'yes_no')
                                                        {{ $fieldValue ? $fieldValue->value : 'N/A' }}
                                                    @else
                                                        {{ $fieldValue ? $fieldValue->value : 'N/A' }}
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endforeach
                @else
                    <p class="text-muted">No dynamic fields configured for this employee.</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Leave Summary Tab -->
    <div class="tab-pane fade" id="leave-summary">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-calendar-check"></i> Leave Balance (Yearly View)</h5>
            </div>
            <div class="card-body">
                @if(!empty($vacationHistory))
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Year</th>
                                    <th>Entitled</th>
                                    <th>Used</th>
                                    <th>Balance</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($vacationHistory as $balance)
                                    <tr>
                                        <td>{{ $balance['year'] }}</td>
                                        <td>{{ $balance['entitled_days'] }}</td>
                                        <td>{{ $balance['used_days'] }}</td>
                                        <td class="{{ $balance['balance'] > 0 ? 'text-success' : 'text-danger' }}">
                                            <strong>{{ $balance['balance'] }}</strong>
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
    </div>

    <!-- Leave History Tab -->
    <div class="tab-pane fade" id="leave-history">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-history"></i> Leave History</h5>
            </div>
            <div class="card-body">
                @if($employee->leaveRequests->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Type</th>
                                    <th>Start Date</th>
                                    <th>End Date</th>
                                    <th>Days</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($employee->leaveRequests as $leave)
                                    <tr>
                                        <td>{{ $leave->leaveType->name }}</td>
                                        <td>{{ $leave->start_date->format('d M Y') }}</td>
                                        <td>{{ $leave->end_date->format('d M Y') }}</td>
                                        <td>{{ $leave->total_days }}</td>
                                        <td>
                                            @if($leave->status == 'pending')
                                                <span class="badge bg-warning">Pending</span>
                                            @elseif($leave->status == 'approved')
                                                <span class="badge bg-success">Approved</span>
                                            @elseif($leave->status == 'rejected')
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
                @else
                    <p class="text-muted">No leave requests found.</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Vacation History Tab -->
    <div class="tab-pane fade show active" id="vacation-history">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-book"></i> Vacation History</h5>
            </div>
            <div class="card-body">
                @if(isset($vacationHistory) && count($vacationHistory) > 0)
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
                                @foreach($vacationHistory as $yearData)
                                    @php
                                        $carryForward = $cumulativeBalance;
                                        $cumulativeBalance += round($yearData['entitled_days'] - $yearData['used_days'], 2);
                                    @endphp
                                    <tr>
                                        <td>{{ $yearData['year'] }}</td>
                                        <td>{{ $yearData['entitled_days'] }}</td>
                                        <td>{{ $yearData['used_days'] }}</td>
                                        <td class="{{ $carryForward > 0 ? 'text-success' : 'text-muted' }}">
                                            {{ $carryForward > 0 ? $carryForward : '-' }}
                                        </td>
                                        <td class="{{ $cumulativeBalance > 0 ? 'text-success' : 'text-danger' }}">
                                            <strong>{{ $cumulativeBalance }}</strong>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted">No vacation history found.</p>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="mt-3">
    <a href="{{ route('employees.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Back to List
    </a>
    @can('update', $employee)
    <a href="{{ route('employees.edit', $employee) }}" class="btn btn-warning">
        <i class="fas fa-edit"></i> Edit
    </a>
    @endcan
</div>
@endsection