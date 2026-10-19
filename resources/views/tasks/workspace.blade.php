@extends('layouts.app')

@section('title', 'Workspace')

@section('page-title')
    <i class="fas fa-tasks"></i> My Workspace
@endsection

@section('content')
<div class="row">
    <!-- Personal Dashboard -->
    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <i class="fas fa-user"></i> My Tasks for Today
            </div>
            <div class="card-body">
                @forelse($myTasks as $task)
                    <div class="border-bottom pb-2 mb-2">
                        <h6 class="mb-1">{{ Str::limit($task->title, 50) }}</h6>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="badge {{ $task->getPriorityBadgeClass() }}">{{ ucfirst($task->priority) }}</span>
                            @if($task->due_date)
                                <small class="text-muted">Due: {{ $task->due_date->format('M d') }}</small>
                            @endif
                        </div>
                        @if($task->progress > 0)
                            <div class="progress mt-2" style="height: 6px;">
                                <div class="progress-bar" role="progressbar" style="width: {{ $task->progress }}%"></div>
                            </div>
                        @endif
                    </div>
                @empty
                    <p class="text-muted mb-0">No tasks assigned to you today.</p>
                @endforelse
            </div>
        </div>

        <!-- Overdue Tasks -->
        @if($overdueTasks->count() > 0)
            <div class="card mb-4 border-danger">
                <div class="card-header bg-danger text-white">
                    <i class="fas fa-exclamation-triangle"></i> Overdue Milestones
                </div>
                <div class="card-body">
                    @foreach($overdueTasks as $task)
                        <div class="border-bottom pb-2 mb-2">
                            <h6 class="mb-1 text-danger">{{ Str::limit($task->title, 40) }}</h6>
                            <small class="text-muted">Due: {{ $task->due_date->format('M d, Y') }}</small>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Recent Activity -->
        <div class="card">
            <div class="card-header">
                <i class="fas fa-bell"></i> Recent Updates
            </div>
            <div class="card-body">
                @forelse($recentComments as $comment)
                    <div class="border-bottom pb-2 mb-2">
                        <strong>{{ $comment->user->name }}</strong> commented on:
                        <a href="{{ route('tasks.show', $comment->task) }}">{{ Str::limit($comment->task->title, 30) }}</a>
                        <br><small class="text-muted">{{ $comment->created_at->diffForHumans() }}</small>
                    </div>
                @empty
                    <p class="text-muted mb-0">No recent updates.</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Pipeline View (Kanban Board) -->
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Task Pipeline (Kanban Board)</h5>
            </div>
            <div class="card-body">
                <div class="row g-4">
                    <!-- To Do Column -->
                    <div class="col-md-3">
                        <div class="card h-100 border-primary">
                            <div class="card-header bg-primary text-white">
                                <h6 class="mb-0">To Do</h6>
                            </div>
                            <div class="card-body p-0">
                                @foreach($myTasks->where('status', 'todo') as $task)
                                    <div class="p-3 border-bottom">
                                        <div class="d-flex justify-content-between">
                                            <h6 class="mb-1">{{ Str::limit($task->title, 30) }}</h6>
                                            <span class="badge bg-light text-dark">{{ $task->project->name ?? 'No Project' }}</span>
                                        </div>
                                        <p class="text-muted small mb-1">{{ Str::limit($task->description, 60) }}</p>
                                        <div class="mb-2">
                                            <small class="text-muted">Assigned by: {{ $task->creator->name }}</small>
                                        </div>
                                        @if($task->due_date)
                                            <div class="mb-1">
                                                <small class="text-muted {{ $task->isOverdue() ? 'text-danger' : '' }}>
                                                    Due: {{ $task->due_date->format('M d, Y') }}
                                                    @if($task->isOverdue())
                                                        <i class="fas fa-exclamation-circle"></i>
                                                    @endif
                                                </small>
                                            </div>
                                        @endif
                                        <div class="progress" style="height: 4px;">
                                            <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $task->progress }}%"></div>
                                        </div>
                                        <div class="mt-2">
                                            <form action="{{ route('tasks.update-status', $task) }}" method="POST" class="d-inline">
                                                @csrf
                                                <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                                                    <option value="todo" selected>To Do</option>
                                                    <option value="in_progress">In Progress</option>
                                                    <option value="blocked">Blocked</option>
                                                    <option value="completed">Completed</option>
                                                </select>
                                            </form>
                                        </div>
                                    </div>
                                @endforeach
                                @if($myTasks->where('status', 'todo')->isEmpty())
                                    <div class="p-3 text-center text-muted">
                                        <i class="fas fa-inbox mb-2"></i>
                                        <p class="mb-0">No tasks in To Do</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- In Progress Column -->
                    <div class="col-md-3">
                        <div class="card h-100 border-info">
                            <div class="card-header bg-info text-white">
                                <h6 class="mb-0">In Progress</h6>
                            </div>
                            <div class="card-body p-0">
                                @foreach($myTasks->where('status', 'in_progress') as $task)
                                    <div class="p-3 border-bottom">
                                        <div class="d-flex justify-content-between">
                                            <h6 class="mb-1">{{ Str::limit($task->title, 30) }}</h6>
                                            <span class="badge bg-light text-dark">{{ $task->project->name ?? 'No Project' }}</span>
                                        </div>
                                        <p class="text-muted small mb-1">{{ Str::limit($task->description, 60) }}</p>
                                        <div class="mb-2">
                                            <small class="text-muted">Assigned by: {{ $task->creator->name }}</small>
                                        </div>
                                        @if($task->due_date)
                                            <div class="mb-1">
                                                <small class="text-muted {{ $task->isOverdue() ? 'text-danger' : '' }}>
                                                    Due: {{ $task->due_date->format('M d, Y') }}
                                                    @if($task->isOverdue())
                                                        <i class="fas fa-exclamation-circle"></i>
                                                    @endif
                                                </small>
                                            </div>
                                        @endif
                                        <div class="progress" style="height: 4px;">
                                            <div class="progress-bar bg-info" role="progressbar" style="width: {{ $task->progress }}%"></div>
                                        </div>
                                        <div class="mt-2">
                                            <form action="{{ route('tasks.update-status', $task) }}" method="POST" class="d-inline">
                                                @csrf
                                                <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                                                    <option value="todo">To Do</option>
                                                    <option value="in_progress" selected>In Progress</option>
                                                    <option value="blocked">Blocked</option>
                                                    <option value="completed">Completed</option>
                                                </select>
                                            </form>
                                        </div>
                                    </div>
                                @endforeach
                                @if($myTasks->where('status', 'in_progress')->isEmpty())
                                    <div class="p-3 text-center text-muted">
                                        <i class="fas fa-sync-alt mb-2"></i>
                                        <p class="mb-0">No tasks in Progress</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Blocked Column -->
                    <div class="col-md-3">
                        <div class="card h-100 border-warning">
                            <div class="card-header bg-warning text-white">
                                <h6 class="mb-0">Blocked</h6>
                            </div>
                            <div class="card-body p-0">
                                @foreach($myTasks->where('status', 'blocked') as $task)
                                    <div class="p-3 border-bottom>
                                        <div class="d-flex justify-content-between">
                                            <h6 class="mb-1">{{ Str::limit($task->title, 30) }}</h6>
                                            <span class="badge bg-light text-dark">{{ $task->project->name ?? 'No Project' }}</span>
                                        </div>
                                        <p class="text-muted small mb-1">{{ Str::limit($task->description, 60) }}</p>
                                        <div class="mb-2">
                                            <small class="text-muted">Assigned by: {{ $task->creator->name }}</small>
                                        </div>
                                        @if($task->due_date)
                                            <div class="mb-1">
                                                <small class="text-muted {{ $task->isOverdue() ? 'text-danger' : '' }}>
                                                    Due: {{ $task->due_date->format('M d, Y') }}
                                                    @if($task->isOverdue())
                                                        <i class="fas fa-exclamation-circle"></i>
                                                    @endif
                                                </small>
                                            </div>
                                        @endif
                                        <div class="progress" style="height: 4px;">
                                            <div class="progress-bar bg-warning" role="progressbar" style="width: {{ $task->progress }}%"></div>
                                        </div>
                                        <div class="mt-2">
                                            <form action="{{ route('tasks.update-status', $task) }}" method="POST" class="d-inline">
                                                @csrf
                                                <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                                                    <option value="todo">To Do</option>
                                                    <option value="in_progress">In Progress</option>
                                                    <option value="blocked" selected>Blocked</option>
                                                    <option value="completed">Completed</option>
                                                </select>
                                            </form>
                                        </div>
                                    </div>
                                @endforeach
                                @if($myTasks->where('status', 'blocked')->isEmpty())
                                    <div class="p-3 text-center text-muted">
                                        <i class="fas fa-ban mb-2"></i>
                                        <p class="mb-0">No blocked tasks</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Completed Column -->
                    <div class="col-md-3">
                        <div class="card h-100 border-success">
                            <div class="card-header bg-success text-white">
                                <h6 class="mb-0">Completed</h6>
                            </div>
                            <div class="card-body p-0">
                                @foreach($createdTasks->where('status', 'completed') as $task)
                                    <div class="p-3 border-bottom">
                                        <div class="d-flex justify-content-between">
                                            <h6 class="mb-1">{{ Str::limit($task->title, 30) }}</h6>
                                            <span class="bg-light text-dark">{{ $task->project->name ?? 'No Project' }}</span>
                                        </div>
                                        <p class="text-muted small mb-1">{{ Str::limit($task->description, 60) }}</p>
                                        <div class="mb-2">
                                            <small class="text-muted">Assigned by: {{ $task->creator->name }}</small>
                                        </div>
                                        @if($task->due_date)
                                            <div class="mb-1">
                                                <small class="text-muted">
                                                    Completed: {{ $task->updated_at->format('M d, Y') }}
                                                </small>
                                            </div>
                                        @endif
                                        <div class="progress" style="height: 4px;">
                                            <div class="progress-bar bg-success" role="progressbar" style="width: 100%"></div>
                                        </div>
                                        <div class="mt-2 text-end">
                                            <small class="text-muted">{{ $task->updated_at->diffForHumans() }} ago</small>
                                        </div>
                                    </div>
                                @endforeach
                                @if($createdTasks->where('status', 'completed')->isEmpty())
                                    <div class="p-3 text-center text-muted">
                                        <i class="fas fa-check-circle mb-2"></i>
                                        <p class="mb-0">No completed tasks</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Created Tasks Summary -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">My Created Tasks</h5>
            </div>
            <div class="card-body">
                @forelse($createdTasks as $task)
                    <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                        <div>
                            <h6 class="mb-1">{{ Str::limit($task->title, 40) }}</h6>
                            @if($task->assignee)
                                <small class="text-muted">Assigned to: {{ $task->assignee->name }}</small>
                            @else
                                <small class="text-muted">Unassigned</small>
                            @endif
                        </div>
                        <div class="text-end">
                            <span class="badge {{ $task->getStatusBadgeClass() }}">{{ ucfirst(str_replace('_', ' ', $task->status)) }}</span>
                            @if($task->progress > 0 && $task->status !== 'completed')
                                <div class="progress mt-1" style="width: 80px; height: 6px;">
                                    <div class="progress-bar" role="progressbar" style="width: {{ $task->progress }}%"></div>
                                </div>
                            @endif
                        </div>
                    </div>
                @empty
                    <p class="text-muted mb-0">You haven't created any tasks yet.</p>
                @endforelse
            </div>
            <div class="card-footer text-end">
                <a href="{{ route('tasks.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> Create New Task
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
           