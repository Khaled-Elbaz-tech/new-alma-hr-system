@extends('layouts.app')

@section('title', 'Create Employee')
@section('page-title', 'Add Employee')

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Employee Information</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('employees.store') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Profile Photo</label>
                            <input type="file" name="avatar" class="form-control" accept="image/*">
                            <small class="text-muted">Max 2MB. JPG, PNG, GIF.</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Full Name</label>
                            <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" required minlength="8">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Role</label>
                            <select name="role_id" class="form-select" required>
                                <option value="">Select Role</option>
                                @foreach($roles as $role)
                                    <option value="{{ $role->id }}">{{ $role->display_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Confirm Password</label>
                            <input type="password" name="password_confirmation" class="form-control" required minlength="8">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Employee Number</label>
                            <input type="text" name="employee_number" class="form-control" value="{{ old('employee_number') }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Department</label>
                            <select name="department_id" class="form-select">
                                <option value="">Select Department</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Joining Date</label>
                            <input type="date" name="joining_date" class="form-control" value="{{ old('joining_date') }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Annual Leave Entitlement (days)</label>
                            <input type="number" name="annual_leave_entitlement" class="form-control" value="21" min="0">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Contract Start Date</label>
                            <input type="date" name="contract_start_date" class="form-control" value="{{ old('contract_start_date') }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Contract End Date</label>
                            <input type="date" name="contract_end_date" class="form-control" value="{{ old('contract_end_date') }}">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Create Employee</button>
                    <a href="{{ route('employees.index') }}" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection