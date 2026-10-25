@extends('layouts.app')

@section('title', 'Employees Documents')
@section('page-title', 'Employees Documents')

@section('content')
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Employee Documents</h5>
    </div>
    <div class="card-body">
        <form method="GET" class="row g-3 mb-4">
            <div class="col-md-4">
                <label for="search" class="form-label">Search</label>
                <input type="text" name="search" id="search" class="form-control" value="{{ request('search') }}" placeholder="Search by name, email or employee number...">
            </div>
            <div class="col-md-3">
                <label for="department_id" class="form-label">Department</label>
                <select name="department_id" id="department_id" class="form-select">
                    <option value="">All Departments</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>
                            {{ $dept->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label for="status" class="form-label">Status</label>
                <select name="status" id="status" class="form-select">
                    <option value="">All Statuses</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="resigned" {{ request('status') == 'resigned' ? 'selected' : '' }}>Resigned</option>
                    <option value="terminated" {{ request('status') == 'terminated' ? 'selected' : '' }}>Terminated</option>
                    <option value="on_leave" {{ request('status') == 'on_leave' ? 'selected' : '' }}>On Leave</option>
                </select>
            </div>
            <div class="col-md-2">
                <label for="joined_from" class="form-label">Joined From</label>
                <input type="date" name="joined_from" id="joined_from" class="form-control" value="{{ request('joined_from') }}">
            </div>
            <div class="col-md-2">
                <label for="joined_to" class="form-label">Joined To</label>
                <input type="date" name="joined_to" id="joined_to" class="form-control" value="{{ request('joined_to') }}">
            </div>
            <div class="col-md-2 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-primary w-100">Filter</button>
                <a href="{{ route('employees.documents') }}" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>

        @if($employees->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Employee Number</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Department</th>
                            <th>Status</th>
                            <th>Joining Date</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($employees as $employee)
                            <tr>
                                <td>{{ $employees->firstItem() + $loop->index }}</td>
                                <td>{{ $employee->employee_number }}</td>
                                <td>{{ $employee->user->name ?? 'N/A' }}</td>
                                <td>{{ $employee->user->email ?? 'N/A' }}</td>
                                <td>{{ $employee->department->name ?? 'N/A' }}</td>
                                <td>
                                    <span class="badge {{ $employee->employment_status == 'active' ? 'bg-success' : ($employee->employment_status == 'on_leave' ? 'bg-warning' : 'bg-danger') }}">
                                        {{ ucfirst($employee->employment_status) }}
                                    </span>
                                </td>
                                <td>{{ $employee->joining_date ? $employee->joining_date->format('Y-m-d') : 'N/A' }}</td>
                                <td class="text-center">
                                    <a href="{{ route('dynamic-forms.employee-fields', $employee) }}" class="btn btn-sm btn-primary" title="Manage Documents">
                                        <i class="fas fa-file-alt"></i> Documents
                                    </a>
                                    <a href="{{ route('employees.show', $employee) }}" class="btn btn-sm btn-outline-secondary ms-1" title="View Profile">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-center">
                {{ $employees->links() }}
            </div>
        @else
            <div class="text-center py-5">
                <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No employees found</h5>
                <p class="text-muted">Try adjusting your search or filter criteria.</p>
            </div>
        @endif
    </div>
</div>
@endsection