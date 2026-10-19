@extends('layouts.app')

@section('title', 'Task Details')

@section('page-title')
    <i class="fas fa-tasks"></i> Task Details
@endsection

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">{{ $task->title }}</h5>
                <div>
                    <span class="badge {{ $task->getPriorityBadgeClass() }} me-2">{{ ucfirst($task->priority) }}</span>
                    <span class="badge {{ $task->getStatusBadgeClass() }}">{{ ucfirst(str_replace('_', ' ', $task->status)) }}</span>
                </div>
            </div>
            <div class="card-body">
                @if($task->description)
                    <div class="mb-4">
                        <h6>Description</h6>
                        <p class="text-muted">{{ $task->description }}</p>
                    </div>
                @endif

                <div class="row mb-4">
                    <div class="col-md-6">
                        <h6>Details</h6>
                        <ul class="list-unstyled">
                            <li><strong>Created by:</strong> {{ $task->creator->name }}</li>
                            <li><strong>Assigned to:</strong> {{ $task->assignee->name ?? 'Unassigned' }}</li>
                            @if($task->department)
                                <li><strong>Department:</strong> {{ $task->department->name }}</li>
                            @endif
                            @if($task->project)
                                <li><strong>Project:</strong> {{ $task->project->name }}</li>
                            @endif
                            @if($task->category)
                                <li><strong>Category:</strong> {{ $task->category }}</li>
                            @endif
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h6>Dates</h6>
                        <ul class="list-unstyled">
                            @if($task->start_date)
                                <li><strong>Start Date:</strong> {{ $task->start_date->format('M d, Y') }}</li>
                            @endif
                            @if($task->due_date)
                                <li><strong>Due Date:</strong> 
                                    <span class="{{ $task->isOverdue() ? 'text-danger' : '' }}">
                                        {{ $task->due_date->format('M d, Y') }}
                                        @if($task->isOverdue())
                                            <i class="fas fa-exclamation-circle" title="Overdue"></i>
                                        @endif
                                    </span>
                                </li>
                            @endif
                            <li><strong>Created:</strong> {{ $task->created_at->format('M d, Y') }}</li>
                        </ul>
                    </div>
                </div>

                <div class="mb-4">
                    <h6>Progress</h6>
                    <div class="progress" style="height: 25px;">
                        <div class="progress-bar" role="progressbar" style="width: {{ $task->progress }}%">
                            {{ $task->progress }}%
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer d-flex justify-content-between">
                <a href="{{ route('tasks.index') }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
                <div>
                    <a href="{{ route('tasks.edit', $task) }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                    <form action="{{ route('tasks.destroy', $task) }}" method="POST" class="d-inline" 
                          onsubmit="return confirm('Are you sure you want to delete this task?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Subtasks -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Subtasks</h5>
            </div>
            <div class="card-body">
                @if($task->subtasks->count() > 0)
                    @foreach($task->subtasks as $subtask)
                        <div class="d-flex align-items-center border-bottom py-2">
                            <form action="{{ route('tasks.subtasks.update', $subtask) }}" method="POST" class="d-flex align-items-center w-100">
                                @csrf
                                @method('PUT')
                                <input type="checkbox" name="is_completed" value="1" 
                                       {{ $subtask->is_completed ? 'checked' : '' }} 
                                       onchange="this.form.submit()" class="me-3">
                                <span class="{{ $subtask->is_completed ? 'text-decoration-line-through text-muted' : '' }}">
                                    {{ $subtask->title }}
                                </span>
                            </form>
                            <form action="{{ route('tasks.subtasks.destroy', $subtask) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('Remove this subtask?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-link text-danger">
                                    <i class="fas fa-times"></i>
                                </button>
                            </form>
                        </div>
                    @endforeach
                @else
                    <p class="text-muted mb-0">No subtasks yet.</p>
                @endif

                <hr>
                <form action="{{ route('tasks.subtasks.store', $task) }}" method="POST" class="d-flex">
                    @csrf
                    <input type="text" name="title" class="form-control me-2" placeholder="Add new subtask..." required>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-plus"></i>
                    </button>
                </form>
            </div>
        </div>

        <!-- Comments -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Comments & Updates</h5>
            </div>
            <div class="card-body">
                @if($task->comments->count() > 0)
                    @foreach($task->comments as $comment)
                        <div class="border-bottom pb-3 mb-3">
                            <div class="d-flex justify-content-between">
                                <strong>{{ $comment->user->name }}</strong>
                                <small class="text-muted">{{ $comment->created_at->diffForHumans() }}</small>
                            </div>
                            <p class="mb-1">{{ $comment->content }}</p>
                            @if($comment->hasAttachment())
                                <a href="{{ asset('storage/' . $comment->attachment_path) }}" class="btn btn-sm btn-outline-primary" target="_blank">
                                    <i class="fas fa-paperclip"></i> {{ $comment->attachment_name }}
                                </a>
                            @endif
                        </div>
                    @endforeach
                @else
                    <p class="text-muted">No comments yet.</p>
                @endif

                <hr>
                <form action="{{ route('tasks.comments.store', $task) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <textarea name="content" class="form-control" rows="3" placeholder="Add a comment..." required></textarea>
                    </div>
                    <div class="mb-3">
                        <input type="file" name="attachment" class="form-control">
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-comment"></i> Post Comment
                    </button>
                </form
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Quick Actions</h5>
            </div>
            <div class="card-body>
                <form action="{{ route('tasks.update-status', $task) }}" method="POST" class="mb-3">
                    @csrf
                    <label class="form-label">Change Status</label>
                    <select name="status" class="form-select" onchange="this.form.submit()">
                        <option value="todo" {{ $task->status == 'todo' ? 'selected' : '' }}>To Do</option>
                        <option value="in_progress" {{ $task->status == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="blocked" {{ $task->status == 'blocked' ? 'selected' : '' }}>Blocked</option>
                        <option value="completed" {{ $task->status == 'completed' ? 'selected' : '' }}>Completed</option>
                    </select>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">{{ $task->title }}</h5>
                <div>
                    <span class="badge {{ $task->getPriorityBadgeClass() }} me-2">{{ ucfirst($task->priority) }}</span>
                    <span class="badge {{ $task->getStatusBadgeClass() }}">{{ ucfirst(str_replace('_', ' ', $task->status)) }}</span>
                </div>
            </div>
            <div class="card-body">
                @if($task->description)
                    <div class="mb-4">
                        <h6>Description</h6>
                        <p class="text-muted">{{ $task->description }}</p>
                    </div>
                @endif

                <div class="row mb-4">
                    <div class="col-md-6">
                        <h6>Details</h6>
                        <ul class="list-unstyled">
                            <li><strong>Created by:</strong> {{ $task->creator->name }}</li>
                            <li><strong>Assigned to:</strong> {{ $task->assignee->name ?? 'Unassigned' }}</li>
                            @if($task->department)
                                <li><strong>Department:</strong> {{ $task->department->name }}</li>
                            @endif
                            @if($task->category)
                                <li><strong>Category:</strong> {{ $task->category }}</li>
                            @endif
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h6>Dates</h6>
                        <ul class="list-unstyled">
                            @if($task->start_date)
                                <li><strong>Start Date:</strong> {{ $task->start_date->format('M d, Y') }}</li>
                            @endif
                            @if($task->due_date)
                                <li><strong>Due Date:</strong> 
                                    <span class="{{ $task->isOverdue() ? 'text-danger' : '' }}">
                                        {{ $task->due_date->format('M d, Y') }}
                                        @if($task->isOverdue())
                                            <i class="fas fa-exclamation-circle" title="Overdue"></i>
                                        @endif
                                    </span>
                                </li>
                            @endif
                            <li><strong>Created:</strong> {{ $task->created_at->format('M d, Y') }}</li>
                        </ul>
                    </div>
                </div>

                <div class="mb-4">
                    <h6>Progress</h6>
                    <div class="progress" style="height: 25px;">
                        <div class="progress-bar" role="progressbar" style="width: {{ $task->progress }}%">
                            {{ $task->progress }}%
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer d-flex justify-content-between">
                <a href="{{ route('tasks.index') }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
                <div>
                    <a href="{{ route('tasks.edit', $task) }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                    <form action="{{ route('tasks.destroy', $task) }}" method="POST" class="d-inline" 
                          onsubmit="return confirm('Are you sure you want to delete this task?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Subtasks -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Subtasks</h5>
            </div>
            <div class="card-body">
                @if($task->subtasks->count() > 0)
                    @foreach($task->subtasks as $subtask)
                        <div class="d-flex align-items-center border-bottom py-2">
                            <form action="{{ route('tasks.subtasks.update', $subtask) }}" method="POST" class="d-flex align-items-center w-100">
                                @csrf
                                @method('PUT')
                                <input type="checkbox" name="is_completed" value="1" 
                                       {{ $subtask->is_completed ? 'checked' : '' }} 
                                       onchange="this.form.submit()" class="me-3">
                                <span class="{{ $subtask->is_completed ? 'text-decoration-line-through text-muted' : '' }}">
                                    {{ $subtask->title }}
                                </span>
                            </form>
                            <form action="{{ route('tasks.subtasks.destroy', $subtask) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('Remove this subtask?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-link text-danger">
                                    <i class="fas fa-times"></i>
                                </button>
                            </form>
                        </div>
                    @endforeach
                @else
                    <p class="text-muted mb-0">No subtasks yet.</p>
                @endif

                <hr>
                <form action="{{ route('tasks.subtasks.store', $task) }}" method="POST" class="d-flex">
                    @csrf
                    <input type="text" name="title" class="form-control me-2" placeholder="Add new subtask..." required>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-plus"></i>
                    </button>
                </form>
            </div>
        </div>

        <!-- Comments -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Comments & Updates</h5>
            </div>
            <div class="card-body">
                @if($task->comments->count() > 0)
                    @foreach($task->comments as $comment)
                        <div class="border-bottom pb-3 mb-3">
                            <div class="d-flex justify-content-between">
                                <strong>{{ $comment->user->name }}</strong>
                                <small class="text-muted">{{ $comment->created_at->diffForHumans() }}</small>
                            </div>
                            <p class="mb-1">{{ $comment->content }}</p>
                            @if($comment->hasAttachment())
                                <a href="{{ asset('storage/' . $comment->attachment_path) }}" class="btn btn-sm btn-outline-primary" target="_blank">
                                    <i class="fas fa-paperclip"></i> {{ $comment->attachment_name }}
                                </a>
                            @endif
                        </div>
                    @endforeach
                @else
                    <p class="text-muted">No comments yet.</p>
                @endif

                <hr>
                <form action="{{ route('tasks.comments.store', $task) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <textarea name="content" class="form-control" rows="3" placeholder="Add a comment..." required></textarea>
                    </div>
                    <div class="mb-3">
                        <input type="file" name="attachment" class="form-control">
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-comment"></i> Post Comment
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Quick Actions</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('tasks.update-status', $task) }}" method="POST" class="mb-3">
                    @csrf
                    <label class="form-label">Change Status</label>
                    <select name="status" class="form-select" onchange="this.form.submit()">
                        <option value="todo" {{ $task->status == 'todo' ? 'selected' : '' }}>To Do</option>
                        <option value="in_progress" {{ $task->status == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="blocked" {{ $task->status == 'blocked' ? 'selected' : '' }}>Blocked</option>
                        <option value="completed" {{ $task->status == 'completed' ? 'selected' : '' }}>Completed</option>
                    </select>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection