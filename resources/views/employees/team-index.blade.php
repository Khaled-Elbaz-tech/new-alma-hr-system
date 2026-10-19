@extends('layouts.app')

@section('title', 'My Team')
@section('page-title', 'My Team - ' . $managedDepartment->name)

@section('content')
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <h4><i class="fas fa-building"></i> {{ $managedDepartment->name }}</h4>
                <p class="text-muted mb-0">Team Members: {{ $employees->total() }}</p>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">All Statuses</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="resigned" {{ request('status') == 'resigned' ? 'selected' : '' }}>Resigned</option>
                    <option value="terminated" {{ request('status') == 'terminated' ? 'selected' : '' }}>Terminated</option>
                    <option value="on_leave" {{ request('status') == 'on_leave' ? 'selected' : '' }}>On Leave</option>
                </select>
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button type="submit" class="btn btn-primary">Filter</button>
                <a href="{{ route('team.employees') }}" class="btn btn-secondary ms-2">Reset</a>
            </div>
        </form>
    </div>
</div>

<!-- Employee List -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-users"></i> Team Members</h5>
    </div>
    <div class="card-body">
        @if($employees->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Employee ID</th>
                            <th>Full Name</th>
                            <th>Email</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($employees as $employee)
                            <tr>
                                <td>{{ $employee->employee_number }}</td>
                                <td>
                                    <strong>{{ $employee->user->name }}</strong>
                                </td>
                                <td>{{ $employee->user->email }}</td>
                                <td>
                                    <span class="badge bg-{{ $employee->employment_status === 'active' ? 'success' : 'secondary' }}">
                                        {{ ucfirst($employee->employment_status) }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('team.employees.show', $employee) }}" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            {{ $employees->links() }}
        @else
            <p class="text-muted">No team members found.</p>
        @endif
    </div>
</div>
@endsection