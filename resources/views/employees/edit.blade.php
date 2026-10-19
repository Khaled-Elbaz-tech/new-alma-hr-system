@extends('layouts.app')

@section('title', 'Edit Employee')
@section('page-title', 'Edit Employee')

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Edit Employee</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('employees.update', $employee) }}">
                    @csrf
                    @method('PUT')
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Full Name</label>
                            <input type="text" name="name" class="form-control" value="{{ old('name', $employee->user->name) }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" value="{{ old('email', $employee->user->email) }}" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">New Password (leave blank to keep current)</label>
                            <input type="password" name="password" class="form-control" minlength="8">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Role</label>
                            <select name="role_id" class="form-select" required>
                                @foreach($roles as $role)
                                    <option value="{{ $role->id }}" {{ $employee->user->role_id == $role->id ? 'selected' : '' }}>
                                        {{ $role->display_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Employee Number</label>
                            <input type="text" name="employee_number" class="form-control" value="{{ old('employee_number', $employee->employee_number) }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Department</label>
                            <select name="department_id" class="form-select">
                                <option value="">Select Department</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->id }}" {{ $employee->department_id == $dept->id ? 'selected' : '' }}>
                                        {{ $dept->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Joining Date</label>
                            <input type="date" name="joining_date" class="form-control" value="{{ old('joining_date', $employee->joining_date->format('Y-m-d')) }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Annual Leave Entitlement</label>
                            <input type="number" name="annual_leave_entitlement" class="form-control" value="{{ old('annual_leave_entitlement', $employee->annual_leave_entitlement) }}" min="0">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Contract Start Date</label>
                            <input type="date" name="contract_start_date" class="form-control" value="{{ old('contract_start_date', $employee->contract_start_date?->format('Y-m-d')) }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Contract End Date</label>
                            <input type="date" name="contract_end_date" class="form-control" value="{{ old('contract_end_date', $employee->contract_end_date?->format('Y-m-d')) }}">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Employment Status</label>
                        <select name="employment_status" class="form-select" required>
                            <option value="active" {{ $employee->employment_status === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="resigned" {{ $employee->employment_status === 'resigned' ? 'selected' : '' }}>Resigned</option>
                            <option value="terminated" {{ $employee->employment_status === 'terminated' ? 'selected' : '' }}>Terminated</option>
                            <option value="on_leave" {{ $employee->employment_status === 'on_leave' ? 'selected' : '' }}>On Leave</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Update Employee</button>
                    <a href="{{ route('employees.index') }}" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection