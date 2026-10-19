@extends('layouts.app')

@section('title', 'Department Details')
@section('page-title', 'Department - ' . $department->name)

@section('content')
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4>{{ $department->name }}</h4>
                        <p class="text-muted mb-0">Code: {{ $department->code }}</p>
                    </div>
                    <div>
                        <span class="badge bg-{{ $department->is_active ? 'success' : 'secondary' }}">
                            {{ $department->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Department Info -->
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-info-circle"></i> Department Information</h5>
            </div>
            <div class="card-body">
                <p><strong>Department Name:</strong> {{ $department->name }}</p>
                <p><strong>Department Code:</strong> {{ $department->code }}</p>
                <p><strong>Manager:</strong> 
                    @if($department->manager)
                        <span class="badge bg-primary">{{ $department->manager->name }}</span>
                    @else
                        <span class="text-danger">Not Assigned</span>
                    @endif
                </p>
                <p><strong>Parent Department:</strong> 
                    @if($department->parent)
                        {{ $department->parent->name }}
                    @else
                        <span class="text-muted">None (Top Level)</span>
                    @endif
                </p>
                <p><strong>Status:</strong> 
                    <span class="badge bg-{{ $department->is_active ? 'success' : 'secondary' }}">
                        {{ $department->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </p>
            </div>
            <div class="card-footer">
                <a href="{{ route('departments.edit', $department) }}" class="btn btn-warning">
                    <i class="fas fa-edit"></i> Edit
                </a>
            </div>
        </div>
    </div>

    <!-- Employee List -->
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-users"></i> Employees ({{ $department->employees->count() }})</h5>
            </div>
            <div class="card-body">
                @if($department->employees->count() > 0)
                    <div class="list-group">
                        @foreach($department->employees as $employee)
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <strong>{{ $employee->user->name }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $employee->employee_number }}</small>
                                    </div>
                                    <span class="badge bg-{{ $employee->employment_status === 'active' ? 'success' : 'secondary' }}">
                                        {{ ucfirst($employee->employment_status) }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-muted">No employees in this department.</p>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Sub-departments -->
@if($department->children->count() > 0)
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-sitemap"></i> Sub-departments ({{ $department->children->count() }})</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Code</th>
                                <th>Manager</th>
                                <th>Employees</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($department->children as $child)
                                <tr>
                                    <td>{{ $child->name }}</td>
                                    <td>{{ $child->code }}</td>
                                    <td>{{ $child->manager->name ?? 'Not Assigned' }}</td>
                                    <td>{{ $child->employees_count }}</td>
                                    <td>
                                        <a href="{{ route('departments.show', $child) }}" class="btn btn-sm btn-info">View</a>
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
@endif

<div class="mt-3">
    <a href="{{ route('departments.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Back to Departments
    </a>
</div>
@endsection