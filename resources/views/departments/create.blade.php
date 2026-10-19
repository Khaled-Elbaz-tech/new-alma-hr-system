@extends('layouts.app')

@section('title', 'Create Department')
@section('page-title', 'Add Department')

@section('content')
<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Department Information</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('departments.store') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Department Name</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Department Code</label>
                        <input type="text" name="code" class="form-control" value="{{ old('code') }}" required placeholder="e.g., IT, HR, FIN">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Manager <span class="text-danger">*</span></label>
                        <select name="manager_id" class="form-select" required>
                            <option value="">Select Manager</option>
                            @foreach($managers as $manager)
                                <option value="{{ $manager->id }}" {{ old('manager_id') == $manager->id ? 'selected' : '' }}>
                                    {{ $manager->name }} ({{ $manager->role->display_name }})
                                </option>
                            @endforeach
                        </select>
                        @error('manager_id')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Parent Department</label>
                        <select name="parent_id" class="form-select">
                            <option value="">None (Top Level)</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Create Department</button>
                    <a href="{{ route('departments.index') }}" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection