<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskSubtask;
use App\Models\TaskComment;
use App\Models\User;
use App\Models\Department;
use App\Models\Project;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $this->authorizeTaskAccess();
        
        $user = auth()->user();
        $query = Task::with(['creator', 'assignee', 'department', 'project', 'subtasks']);

        // Apply accessibility filter (users can only see their own tasks unless they're admin/HR)
        if (!$user->isSuperAdmin() && !$user->isHrManager()) {
            $query->where('created_by', $user->id)
                  ->orWhere('assigned_to', $user->id);
        }

        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        if ($request->has('priority') && $request->priority) {
            $query->where('priority', $request->priority);
        }

        if ($request->has('assigned_to') && $request->assigned_to) {
            $query->where('assigned_to', $request->assigned_to);
        }

        if ($request->has('department_id') && $request->department_id) {
            $query->where('department_id', $request->department_id);
        }

        if ($request->has('project_id') && $request->project_id) {
            $query->where('project_id', $request->project_id);
        }

        $tasks = $query->latest()->paginate(20);
        $users = User::where(function ($q) {
            $q->whereHas('taskAccess', function ($subq) {
                $subq->where('can_access_tasks', true);
            })->orWhere('role_id', auth()->user()->role_id);
        })->get();
        $departments = Department::where('is_active', true)->get();
        $projects = Project::where('status', 'active')->get();

        return view('tasks.index', compact('tasks', 'users', 'departments', 'projects'));
    }

    public function create()
    {
        $this->authorizeTaskAccess();
        
        $users = User::where(function ($q) {
            $q->whereHas('taskAccess', function ($subq) {
                $subq->where('can_access_tasks', true);
            })->orWhere('role_id', auth()->user()->role_id);
        })->get();
        $departments = Department::where('is_active', true)->get();
        $projects = Project::where('status', 'active')->get();

        return view('tasks.create', compact('users', 'departments', 'projects'));
    }

    public function store(Request $request)
    {
        $this->authorizeTaskAccess();

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'required|in:low,medium,high,critical',
            'due_date' => 'nullable|date',
            'start_date' => 'nullable|date',
            'assigned_to' => 'nullable|exists:users,id',
            'department_id' => 'nullable|exists:departments,id',
            'project_id' => 'nullable|exists:projects,id',
            'category' => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // For self-assignment: if no assigned_to is provided, default to current user
        $assignedTo = $request->assigned_to ?? auth()->id();

        $task = Task::create([
            'title' => $request->title,
            'description' => $request->description,
            'priority' => $request->priority,
            'due_date' => $request->due_date,
            'start_date' => $request->start_date,
            'assigned_to' => $assignedTo,
            'department_id' => $request->department_id,
            'project_id' => $request->project_id,
            'category' => $request->category,
            'created_by' => auth()->id(),
        ]);

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'create',
            'model_type' => Task::class,
            'model_id' => $task->id,
            'new_values' => $request->all(),
            'ip_address' => $request->ip(),
        ]);

        return redirect()->route('tasks.index')->with('success', 'Task created successfully');
    }

    public function show(Task $task)
    {
        $this->authorizeTaskAccess();
        
        $task->load(['creator', 'assignee', 'department', 'subtasks', 'comments.user']);
        $users = User::where(function ($q) {
            $q->whereHas('taskAccess', function ($subq) {
                $subq->where('can_access_tasks', true);
            })->orWhere('role_id', auth()->user()->role_id);
        })->get();

        return view('tasks.show', compact('task', 'users'));
    }

    public function edit(Task $task)
    {
        $this->authorizeTaskAccess();
        
        $users = User::where(function ($q) {
            $q->whereHas('taskAccess', function ($subq) {
                $subq->where('can_access_tasks', true);
            })->orWhere('role_id', auth()->user()->role_id);
        })->get();
        $departments = Department::where('is_active', true)->get();
        $projects = Project::where('status', 'active')->get();

        return view('tasks.edit', compact('task', 'users', 'departments', 'projects'));
    }

    public function update(Request $request, Task $task)
    {
        $this->authorizeTaskAccess();

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'required|in:low,medium,high,critical',
            'status' => 'required|in:todo,in_progress,blocked,completed',
            'due_date' => 'nullable|date',
            'start_date' => 'nullable|date',
            'assigned_to' => 'nullable|exists:users,id',
            'department_id' => 'nullable|exists:departments,id',
            'project_id' => 'nullable|exists:projects,id',
            'category' => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $oldStatus = $task->status;
        $task->update([
            'title' => $request->title,
            'description' => $request->description,
            'priority' => $request->priority,
            'status' => $request->status,
            'due_date' => $request->due_date,
            'start_date' => $request->start_date,
            'assigned_to' => $request->assigned_to,
            'department_id' => $request->department_id,
            'project_id' => $request->project_id,
            'category' => $request->category,
        ]);

        // Handle status change notifications
        if ($oldStatus !== $task->status) {
            $this->updateProgressFromSubtasks($task);
            
            // Notify creator when task is completed or blocked
            if ($task->status === 'completed' && $oldStatus !== 'completed') {
                $this->notifyTaskCompletion($task);
            } elseif ($task->status === 'blocked' && $oldStatus !== 'blocked') {
                $this->notifyTaskBlocked($task);
            }
        }

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'update',
            'model_type' => Task::class,
            'model_id' => $task->id,
            'old_values' => $request->except(['_token', '_method']),
            'new_values' => $task->toArray(),
            'ip_address' => $request->ip(),
        ]);

        return redirect()->route('tasks.index')->with('success', 'Task updated successfully');
    }

    public function destroy(Request $request, Task $task)
    {
        $this->authorizeTaskAccess();

        $task->delete();

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'delete',
            'model_type' => Task::class,
            'model_id' => $task->id,
            'ip_address' => $request->ip(),
        ]);

        return redirect()->route('tasks.index')->with('success', 'Task deleted successfully');
    }

    public function workspace()
    {
        $this->authorizeTaskAccess();
        
        $user = auth()->user();

        $myTasks = Task::with(['assignee', 'subtasks', 'project'])
            ->where('assigned_to', $user->id)
            ->whereIn('status', ['todo', 'in_progress', 'blocked'])
            ->orderBy('due_date')
            ->limit(5)
            ->get();

        $createdTasks = Task::with(['assignee', 'subtasks', 'project'])
            ->where('created_by', $user->id)
            ->whereIn('status', ['todo', 'in_progress', 'blocked'])
            ->orderBy('due_date')
            ->limit(5)
            ->get();

        $overdueTasks = Task::with(['assignee'])
            ->where('due_date', '<', now())
            ->where('status', '!=', 'completed')
            ->when(!$user->isSuperAdmin() && !$user->isHrManager() && !$user->isManager(), function ($q) use ($user) {
                $q->where(function ($q) use ($user) {
                    $q->where('created_by', $user->id)
                      ->orWhere('assigned_to', $user->id);
                });
            })
            ->get();

        $recentComments = TaskComment::with(['task', 'user'])
            ->whereHas('task', function ($q) use ($user) {
                $q->where('created_by', $user->id)
                  ->orWhere('assigned_to', $user->id);
            })
            ->latest()
            ->limit(10)
            ->get();

        $pipelineCounts = [
            'todo' => Task::whereIn('status', ['todo'])->when(!$user->isSuperAdmin() && !$user->isHrManager() && !$user->isManager(), function ($q) use ($user) {
                $q->where(function ($q) use ($user) {
                    $q->where('created_by', $user->id)
                      ->orWhere('assigned_to', $user->id);
                });
            })->count(),
            'in_progress' => Task::where('status', 'in_progress')->when(!$user->isSuperAdmin() && !$user->isHrManager() && !$user->isManager(), function ($q) use ($user) {
                $q->where(function ($q) use ($user) {
                    $q->where('created_by', $user->id)
                      ->orWhere('assigned_to', $user->id);
                });
            })->count(),
            'blocked' => Task::where('status', 'blocked')->when(!$user->isSuperAdmin() && !$user->isHrManager() && !$user->isManager(), function ($q) use ($user) {
                $q->where(function ($q) use ($user) {
                    $q->where('created_by', $user->id)
                      ->orWhere('assigned_to', $user->id);
                });
            })->count(),
            'completed' => Task::where('status', 'completed')->when(!$user->isSuperAdmin() && !$user->isHrManager() && !$user->isManager(), function ($q) use ($user) {
                $q->where(function ($q) use ($user) {
                    $q->where('created_by', $user->id)
                      ->orWhere('assigned_to', $user->id);
                });
            })->count(),
        ];

        return view('tasks.workspace', compact('myTasks', 'createdTasks', 'overdueTasks', 'recentComments', 'pipelineCounts'));
    }

    public function updateStatus(Request $request, Task $task)
    {
        $this->authorizeTaskAccess();

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:todo,in_progress,blocked,completed',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $oldStatus = $task->status;
        $task->update(['status' => $request->status]);

        $this->updateProgressFromSubtasks($task);

        // Handle status change notifications for quick status updates
        if ($oldStatus !== $task->status) {
            // Notify creator when task is completed or blocked
            if ($task->status === 'completed' && $oldStatus !== 'completed') {
                $this->notifyTaskCompletion($task);
            } elseif ($task->status === 'blocked' && $oldStatus !== 'blocked') {
                $this->notifyTaskBlocked($task);
            }
        }

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'status_change',
            'model_type' => Task::class,
            'model_id' => $task->id,
            'old_values' => ['status' => $oldStatus],
            'new_values' => ['status' => $task->status],
            'ip_address' => $request->ip(),
        ]);

        return response()->json(['success' => true, 'message' => 'Status updated']);
    }

    public function updateProgress(Request $request, Task $task)
    {
        $this->authorizeTaskAccess();

        $validator = Validator::make($request->all(), [
            'progress' => 'required|integer|min:0|max:100',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        $task->update(['progress' => $request->progress]);

        return back()->with('success', 'Progress updated');
    }

    public function addComment(Request $request, Task $task)
    {
        $this->authorizeTaskAccess();

        $validator = Validator::make($request->all(), [
            'content' => 'required|string',
            'attachment' => 'nullable|file|max:5120',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        $comment = new TaskComment();
        $comment->task_id = $task->id;
        $comment->user_id = auth()->id();
        $comment->content = $request->content;

        if ($request->hasFile('attachment')) {
            $path = $request->file('attachment')->store('task_attachments', 'public');
            $comment->attachment_path = $path;
            $comment->attachment_name = $request->file('attachment')->getClientOriginalName();
        }

        $comment->save();

        $this->updateProgressFromSubtasks($task);

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'comment',
            'model_type' => Task::class,
            'model_id' => $task->id,
            'new_values' => ['comment_id' => $comment->id],
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', 'Comment added');
    }

    public function addSubtask(Request $request, Task $task)
    {
        $this->authorizeTaskAccess();

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        $order = $task->subtasks()->max('order') ?? 0;
        $order++;

        $task->subtasks()->create([
            'title' => $request->title,
            'description' => $request->description,
            'order' => $order,
        ]);

        $this->updateProgressFromSubtasks($task);

        return back()->with('success', 'Subtask added');
    }

    public function updateSubtask(Request $request, TaskSubtask $subtask)
    {
        $this->authorizeTaskAccess();

        $subtask->update(['is_completed' => $request->has('is_completed')]);

        $this->updateProgressFromSubtasks($subtask->task);

        return response()->json(['success' => true]);
    }

    public function destroySubtask(Request $request, TaskSubtask $subtask)
    {
        $this->authorizeTaskAccess();

        $task = $subtask->task;
        $subtask->delete();

        $this->updateProgressFromSubtasks($task);

        return back()->with('success', 'Subtask deleted');
    }

    private function updateProgressFromSubtasks(Task $task)
    {
        $subtasks = $task->subtasks;
        if ($subtasks->count() > 0) {
            $completed = $subtasks->where('is_completed', true)->count();
            $task->update(['progress' => round(($completed / $subtasks->count()) * 100)]);
        }
    }

    private function notifyTaskCompletion(Task $task)
    {
        // Notify the task creator (manager) that the task has been completed
        $creator = $task->creator;
        if ($creator && $creator->id !== auth()->id()) {
            ActivityLog::create([
                'user_id' => $creator->id,
                'action' => 'task_completed',
                'model_type' => Task::class,
                'model_id' => $task->id,
                'new_values' => [
                    'task_title' => $task->title,
                    'completed_by' => auth()->user()->name,
                    'completed_at' => now()->toDateTimeString()
                ],
                'ip_address' => request()->ip(),
            ]);
        }
    }

    private function notifyTaskBlocked(Task $task)
    {
        // Notify the task creator (manager) that the task has been blocked
        $creator = $task->creator;
        if ($creator && $creator->id !== auth()->id()) {
            ActivityLog::create([
                'user_id' => $creator->id,
                'action' => 'task_blocked',
                'model_type' => Task::class,
                'model_id' => $task->id,
                'new_values' => [
                    'task_title' => $task->title,
                    'blocked_by' => auth()->user()->name,
                    'blocked_at' => now()->toDateTimeString(),
                    'reason' => 'Task marked as blocked - please check comments for details'
                ],
                'ip_address' => request()->ip(),
            ]);
        }
    }

    private function authorizeTaskAccess()
    {
        if (!auth()->user()->canAccessTasks()) {
            abort(403, 'You do not have access to the Task Management system.');
        }
    }
}