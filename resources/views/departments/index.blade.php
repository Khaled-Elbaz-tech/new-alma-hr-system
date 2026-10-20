@extends('layouts.app')

@section('title', 'Departments')
@section('page-title', 'Departments')

@section('content')
<div class="row mb-3">
    <div class="col-md-12">
        <a href="{{ route('departments.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add Department
        </a>
    </div>
</div>

@if($departments->count() > 0)
    <div class="row">
        @foreach($departments as $department)
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0">{{ $department->name }}</h5>
                        <small class="text-muted">{{ $department->code }}</small>
                    </div>
                    <span class="badge bg-{{ $department->is_active ? 'success' : 'secondary' }}">
                        {{ $department->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6">
                            <p class="mb-1"><strong>Manager:</strong></p>
                            @if($department->manager)
                                <span class="text-primary">{{ $department->manager->name }}</span>
                            @else
                                <span class="text-danger">Not Assigned</span>
                            @endif
                        </div>
                        <div class="col-6">
                            <p class="mb-1"><strong>Employees:</strong></p>
                            <span class="badge bg-info">{{ $department->employees_count }}</span>
                        </div>
                    </div>
                    
                    @if($department->parent)
                    <p class="mb-1"><strong>Parent:</strong> {{ $department->parent->name }}</p>
                    @endif
                    
                    @if($department->children->count() > 0)
                    <p class="mb-1"><strong>Sub-departments:</strong> {{ $department->children->count() }}</p>
                    @endif
                </div>
                <div class="card-footer">
                    <a href="{{ route('departments.show', $department) }}" class="btn btn-sm btn-info">
                        <i class="fas fa-eye"></i> View
                    </a>
                    <a href="{{ route('departments.edit', $department) }}" class="btn btn-sm btn-warning">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                    @if($department->employees_count == 0)
                        @if($department->is_active)
                        <form action="{{ route('departments.destroy', $department) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-warning" onclick="return confirm('Deactivate this department?')">
                                <i class="fas fa-ban"></i> Deactivate
                            </button>
                        </form>
                        @endif
                        <form action="{{ route('departments.force-delete', $department) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('PERMANENTLY DELETE this department? This cannot be undone!')">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>
@else
    <div class="alert alert-info">
        <h5>No departments found.</h5>
        <p>Create your first department to get started.</p>
        <a href="{{ route('departments.create') }}" class="btn btn-primary">Add Department</a>
    </div>
@endif
@endsection