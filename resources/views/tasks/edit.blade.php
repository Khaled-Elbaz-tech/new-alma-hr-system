@extends('layouts.app')

@section('title', 'Edit Task')

@section('page-title')
    <i class="fas fa-edit"></i> Edit Task
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Task Details</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('tasks.update', $task) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="row">
                <div class="col-md-8">
                    <div class="mb-3">
                        <label for="title" class="form-label">Title *</label>
                        <input type="text" name="title" id="title" class="form-control @error('title') is-invalid @enderror" 
                               value="{{ old('title', $task->title) }}" required>
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="priority" class="form-label">Priority *</label>
                        <select name="priority" id="priority" class="form-select @error('priority') is-invalid @enderror" required>
                            <option value="low" {{ old('priority', $task->priority) == 'low' ? 'selected' : '' }}>Low</option>
                            <option value="medium" {{ old('priority', $task->priority) == 'medium' ? 'selected' : '' }}>Medium</option>
                            <option value="high" {{ old('priority', $task->priority) == 'high' ? 'selected' : '' }}>High</option>
                            <option value="critical" {{ old('priority', $task->priority) == 'critical' ? 'selected' : '' }}>Critical</option>
                        </select>
                        @error('priority')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea name="description" id="description" rows="4" class="form-control @error('description') is-invalid @enderror">{{ old('description', $task->description) }}</textarea>
                @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div

            <div class="row">
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="assigned_to" class="form-label">Assign To *</label>
                        <select name="assigned_to" id="assigned_to" class="form-select @error('assigned_to') is-invalid @enderror" required>
                            <option value="">-- Select User --</option>
                            @foreach($users as $userItem)
                                @php
                                    $hasAccess = $userItem->taskAccess && $userItem->taskAccess->can_access_tasks;
                                    $isCurrentUser = $userItem->id == auth()->id();
                                    $optionClass = $hasAccess ? '' : 'text-muted';
                                    $optionStyle = !$hasAccess ? 'style="opacity: 0.7;"' : '';
                                @endphp
                                <option value="{{ $userItem->id }}" 
                                        {{ (old('assigned_to', $task->assigned_to) == $userItem->id) ? 'selected' : '' }}
                                        class="{{ $optionClass }}"
                                        {{ $optionStyle }}>
                                    {{ $userItem->name }}@if(!$hasAccess) (No Task Access)@elseif($isCurrentUser && !$hasAccess) (You)@endif
                                </option>
                            @endforeach
                        </select>
                        @error('assigned_to')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="department_id" class="form-label">Department</label>
                        <select name="department_id" id="department_id" class="form-select @error('department_id') is-invalid @enderror">
                            <option value="">Select Department</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}" {{ old('department_id', $task->department_id) == $dept->id ? 'selected' : '' }}>
                                    {{ $dept->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('department_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="project_id" class="form-label">Project (Optional)</label>
                        <select name="project_id" id="project_id" class="form-select @error('project_id') is-invalid @enderror">
                            <option value="">No Project</option>
                            @foreach($projects as $project)
                                <option value="{{ $project->id }}" {{ old('project_id', $task->project_id) == $project->id ? 'selected' : '' }}>
                                    {{ $project->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('project_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label for="due_date" class="form-label">Due Date</label>
                <input type="date" name="due_date" id="due_date" class="form-control @error('due_date') is-invalid @enderror" 
                       value="{{ old('due_date', $task->due_date?->format('Y-m-d')) }}">
                @error('due_date')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="category" class="form-label">Category</label>
                <input type="text" name="category" id="category" class="form-control @error('category') is-invalid @enderror" 
                       value="{{ old('category', $task->category) }}" placeholder="e.g., Project Alpha, Maintenance, etc.">
                @error('category')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="status" class="form-label">Status *</label>
                        <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
                            <option value="todo" {{ old('status', $task->status) == 'todo' ? 'selected' : '' }}>To Do</option>
                            <option value="in_progress" {{ old('status', $task->status) == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                            <option value="blocked" {{ old('status', $task->status) == 'blocked' ? 'selected' : '' }}>Blocked</option>
                            <option value="completed" {{ old('status', $task->status) == 'completed' ? 'selected' : '' }}>Completed</option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="start_date" class="form-label">Start Date</label

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Task Details</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('tasks.update', $task) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="row">
                <div class="col-md-8">
                    <div class="mb-3">
                        <label for="title" class="form-label">Title *</label>
                        <input type="text" name="title" id="title" class="form-control @error('title') is-invalid @enderror" 
                               value="{{ old('title', $task->title) }}" required>
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="priority" class="form-label">Priority *</label>
                        <select name="priority" id="priority" class="form-select @error('priority') is-invalid @enderror" required>
                            <option value="low" {{ old('priority', $task->priority) == 'low' ? 'selected' : '' }}>Low</option>
                            <option value="medium" {{ old('priority', $task->priority) == 'medium' ? 'selected' : '' }}>Medium</option>
                            <option value="high" {{ old('priority', $task->priority) == 'high' ? 'selected' : '' }}>High</option>
                            <option value="critical" {{ old('priority', $task->priority) == 'critical' ? 'selected' : '' }}>Critical</option>
                        </select>
                        @error('priority')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea name="description" id="description" rows="4" class="form-control @error('description') is-invalid @enderror">{{ old('description', $task->description) }}</textarea>
                @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="assigned_to" class="form-label">Assign To *</label>
                        <select name="assigned_to" id="assigned_to" class="form-select @error('assigned_to') is-invalid @enderror" required>
                            <option value="">-- Select User --</option>
                            @foreach($users as $userItem)
                                @php
                                    $hasAccess = $userItem->taskAccess && $userItem->taskAccess->can_access_tasks;
                                    $isCurrentUser = $userItem->id == auth()->id();
                                    $optionClass = $hasAccess ? '' : 'text-muted';
                                    $optionStyle = !$hasAccess ? 'style="opacity: 0.7;"' : '';
                                @endphp
                                <option value="{{ $userItem->id }}" 
                                        {{ (old('assigned_to', $task->assigned_to) == $userItem->id) ? 'selected' : '' }}
                                        class="{{ $optionClass }}"
                                        {{ $optionStyle }}>
                                    {{ $userItem->name }}@if(!$hasAccess) (No Task Access)@elseif($isCurrentUser && !$hasAccess) (You)@endif
                                </option>
                            @endforeach
                        </select>
                        @error('assigned_to')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="department_id" class="form-label">Department</label>
                        <input type="date" name="due_date" id="due_date" class="form-control @error('due_date') is-invalid @enderror" 
                               value="{{ old('due_date', $task->due_date?->format('Y-m-d')) }}">
                        @error('due_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label for="category" class="form-label">Category</label>
                <input type="text" name="category" id="category" class="form-control @error('category') is-invalid @enderror" 
                       value="{{ old('category', $task->category) }}" placeholder="e.g., Project Alpha, Maintenance, etc.">
                @error('category')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="status" class="form-label">Status *</label>
                        <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
                            <option value="todo" {{ old('status', $task->status) == 'todo' ? 'selected' : '' }}>To Do</option>
                            <option value="in_progress" {{ old('status', $task->status) == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                            <option value="blocked" {{ old('status', $task->status) == 'blocked' ? 'selected' : '' }}>Blocked</option>
                            <option value="completed" {{ old('status', $task->status) == 'completed' ? 'selected' : '' }}>Completed</option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="start_date" class="form-label">Start Date</label>
                        <input type="date" name="start_date" id="start_date" class="form-control @error('start_date') is-invalid @enderror" 
                               value="{{ old('start_date', $task->start_date?->format('Y-m-d')) }}">
                        @error('start_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="due_date" class="form-label">Due Date</label>
                        <input type="date" name="due_date" id="due_date" class="form-control @error('due_date') is-invalid @enderror" 
                               value="{{ old('due_date', $task->due_date?->format('Y-m-d')) }}">
                        @error('due_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end">
                <a href="{{ route('tasks.index') }}" class="btn btn-secondary me-2">Cancel</a>
                <button type="submit" class="btn btn-primary">Update Task</button>
            </div>
        </form>
    </div>
</div>
@endsection