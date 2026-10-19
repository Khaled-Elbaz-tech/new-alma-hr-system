@extends('layouts.app')

@section('title', 'Tasks')

@section('page-title')
    <i class="fas fa-tasks"></i> Task Management
@endsection

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">All Tasks</h5>
        <a href="{{ route('tasks.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus"></i> Create Task
        </a>
    </div>
    <div class="card-body">
        <form method="GET" class="mb-3">
            <div class="row g-2">
                <div class="col-md-2">
                    <select name="status" class="form-select" onchange="this.form.submit()">
                        <option value="">All Status</option>
                        <option value="todo" {{ request('status') == 'todo' ? 'selected' : '' }}>To Do</option>
                        <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="blocked" {{ request('status') == 'blocked' ? 'selected' : '' }}>Blocked</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="priority" class="form-select" onchange="this.form.submit()">
                        <option value="">All Priority</option>
                        <option value="low" {{ request('priority') == 'low' ? 'selected' : '' }}>Low</option>
                        <option value="medium" {{ request('priority') == 'medium' ? 'selected' : '' }}>Medium</option>
                        <option value="high" {{ request('priority') == 'high' ? 'selected' : '' }}>High</option>
                        <option value="critical" {{ request('priority') == 'critical' ? 'selected' : '' }}>Critical</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="assigned_to" class="form-select" onchange="this.form.submit()">
                        <option value="">All Assignees</option>
                        @foreach($users as $userItem)
                            <option value="{{ $userItem->id }}" {{ request('assigned_to') == $userItem->id ? 'selected' : '' }}>
                                {{ $userItem->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="department_id" class="form-select" onchange="this.form.submit()">
                        <option value="">All Departments</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>
                                {{ $dept->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <a href="{{ route('tasks.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times"></i> Clear
                    </a>
                </div>
            </div>
        </form>

        @if($tasks->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Project</th>
                            <th>Assigned To</th>
                            <th>Assigned By</th>
                            <th>Priority</th>
                            <th>Status</th>
                            <th>Due Date</th>
                            <th>Progress</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($tasks as $task)
                            <tr class="{{ $task->isOverdue() ? 'table-warning' : '' }}">
                                <td>
                                    <a href="{{ route('tasks.show', $task) }}">{{ Str::limit($task->title, 40) }}</a>
                                    @if($task->isOverdue())
                                        <i class="fas fa-exclamation-circle text-danger" title="Overdue"></i>
                                    @endif
                                </td>
                                <td>
                                    @if($task->project)
                                        <span class="badge bg-info">{{ Str::limit($task->project->name, 20) }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>{{ $task->assignee->name ?? 'Unassigned' }}</td>
                                <td>{{ $task->creator->name }}</td>
                                <td>
                                    <span class="badge {{ $task->getPriorityBadgeClass() }}">
                                        {{ ucfirst($task->priority) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge {{ $task->getStatusBadgeClass() }}">
                                        {{ ucfirst(str_replace('_', ' ', $task->status)) }}
                                    </span>
                                </td>
                                <td>
                                    @if($task->due_date)
                                        {{ $task->due_date->format('M d, Y') }}
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="progress" style="width: 80px; height: 20px;">
                                        <div class="progress-bar" role="progressbar" style="width: {{ $task->progress }}%">
                                            <small>{{ $task->progress }}%</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <a href="{{ route('tasks.show', $task) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('tasks.edit', $task) }}" class="btn btn-sm btn-outline-secondary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            {{ $tasks->links() }}
        @else
            <p class="text-center text-muted py-4">No tasks found.</p>
        @endif
    </div>
</div>
@endsection

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">All Tasks</h5>
        <a href="{{ route('tasks.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus"></i> Create Task
        </a>
    </div>
    <div class="card-body">
        <form method="GET" class="mb-3">
            <div class="row g-2">
                <div class="col-md-2">
                    <select name="status" class="form-select" onchange="this.form.submit()">
                        <option value="">All Status</option>
                        <option value="todo" {{ request('status') == 'todo' ? 'selected' : '' }}>To Do</option>
                        <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="blocked" {{ request('status') == 'blocked' ? 'selected' : '' }}>Blocked</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="priority" class="form-select" onchange="this.form.submit()">
                        <option value="">All Priority</option>
                        <option value="low" {{ request('priority') == 'low' ? 'selected' : '' }}>Low</option>
                        <option value="medium" {{ request('priority') == 'medium' ? 'selected' : '' }}>Medium</option>
                        <option value="high" {{ request('priority') == 'high' ? 'selected' : '' }}>High</option>
                        <option value="critical" {{ request('priority') == 'critical' ? 'selected' : '' }}>Critical</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="assigned_to" class="form-select" onchange="this.form.submit()">
                        <option value="">All Assignees</option>
                        @foreach($users as $userItem)
                            <option value="{{ $userItem->id }}" {{ request('assigned_to') == $userItem->id ? 'selected' : '' }}>
                                {{ $userItem->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="department_id" class="form-select" onchange="this.form.submit()">
                        <option value="">All Departments</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>
                                {{ $dept->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <a href="{{ route('tasks.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times"></i> Clear
                    </a>
                </div>
            </div>
        </form>

        @if($tasks->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Project</th>
                            <th>Assigned To</th>
                            <th>Assigned By</th>
                            <th>Priority</th>
                            <th>Status</th>
                            <th>Due Date</th>
                            <th>Progress</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($tasks as $task)
                            <tr class="{{ $task->isOverdue() ? 'table-warning' : '' }}">
                                <td>
                                    <a href="{{ route('tasks.show', $task) }}">{{ Str::limit($task->title, 40) }}</a>
                                    @if($task->isOverdue())
                                        <i class="fas fa-exclamation-circle text-danger" title="Overdue"></i>
                                    @endif
                                </td>
                                <td>
                                    @if($task->project)
                                        <span class="badge bg-info">{{ Str::limit($task->project->name, 20) }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>{{ $task->assignee->name ?? 'Unassigned' }}</td>
                                <td>{{ $task->creator->name }}</td>
                                <td>
                                    <span class="badge {{ $task->getPriorityBadgeClass() }}">
                                        {{ ucfirst($task->priority) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge {{ $task->getStatusBadgeClass() }}">
                                        {{ ucfirst(str_replace('_', ' ', $task->status)) }}
                                    </span>
                                </td>
                                <td>
                                    @if($task->due_date)
                                        {{ $task->due_date->format('M d, Y') }}
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="progress" style="width: 80px; height: 20px;">
                                        <div class="progress-bar" role="progressbar" style="width: {{ $task->progress }}%">
                                            <small>{{ $task->progress }}%</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <a href="{{ route('tasks.show', $task) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('tasks.edit', $task) }}" class="btn btn-sm btn-outline-secondary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            {{ $tasks->links() }}
        @else
            <p class="text-center text-muted py-4">No tasks found.</p>
        @endif
    </div>
</div>
@endsection>
                        <option value="high" {{ request('priority') == 'high' ? 'selected' : '' }}>High</option>
                        <option value="critical" {{ request('priority') == 'critical' ? 'selected' : '' }}>Critical</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="assigned_to" class="form-select" onchange="this.form.submit()">
                        <option value="">All Assignees</option>
                        @foreach($users as $userItem)
                            <option value="{{ $userItem->id }}" {{ request('assigned_to') == $userItem->id ? 'selected' : '' }}>
                                {{ $userItem->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <a href="{{ route('tasks.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times"></i> Clear
                    </a>
                </div>
            </div>
        </form>

        @if($tasks->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Assignee</th>
                            <th>Priority</th>
                            <th>Status</th>
                            <th>Due Date</th>
                            <th>Progress</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($tasks as $task)
                            <tr class="{{ $task->isOverdue() ? 'table-warning' : '' }}">
                                <td>
                                    <a href="{{ route('tasks.show', $task) }}">{{ Str::limit($task->title, 40) }}</a>
                                    @if($task->isOverdue())
                                        <i class="fas fa-exclamation-circle text-danger" title="Overdue"></i>
                                    @endif
                                </td>
                                <td>{{ $task->assignee->name ?? 'Unassigned' }}</td>
                                <td>
                                    <span class="badge {{ $task->getPriorityBadgeClass() }}">
                                        {{ ucfirst($task->priority) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge {{ $task->getStatusBadgeClass() }}">
                                        {{ ucfirst(str_replace('_', ' ', $task->status)) }}
                                    </span>
                                </td>
                                <td>
                                    @if($task->due_date)
                                        {{ $task->due_date->format('M d, Y') }}
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="progress" style="width: 80px; height: 20px;">
                                        <div class="progress-bar" role="progressbar" style="width: {{ $task->progress }}%">
                                            <small>{{ $task->progress }}%</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <a href="{{ route('tasks.show', $task) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('tasks.edit', $task) }}" class="btn btn-sm btn-outline-secondary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            {{ $tasks->links() }}
        @else
            <p class="text-center text-muted py-4">No tasks found.</p>
        @endif
    </div>
</div>
@endsection