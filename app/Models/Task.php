<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'priority',
        'status',
        'due_date',
        'start_date',
        'progress',
        'created_by',
        'assigned_to',
        'department_id',
        'project_id',
        'category',
    ];

    protected $casts = [
        'due_date' => 'date',
        'start_date' => 'date',
        'progress' => 'integer',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function subtasks()
    {
        return $this->hasMany(TaskSubtask::class)->orderBy('order');
    }

    public function comments()
    {
        return $this->hasMany(TaskComment::class)->latest();
    }

    public function isOverdue(): bool
    {
        return $this->due_date && $this->due_date->isPast() && $this->status !== 'completed';
    }

    public function getPriorityBadgeClass(): string
    {
        return [
            'low' => 'bg-secondary',
            'medium' => 'bg-info',
            'high' => 'bg-warning',
            'critical' => 'bg-danger',
        ][$this->priority] ?? 'bg-secondary';
    }

    public function getStatusBadgeClass(): string
    {
        return [
            'todo' => 'bg-secondary',
            'in_progress' => 'bg-primary',
            'blocked' => 'bg-danger',
            'completed' => 'bg-success',
        ][$this->status] ?? 'bg-secondary';
    }

    // Scope for accessible tasks based on user permissions
    public function scopeAccessibleBy($query, $user)
    {
        // Super admins and HR managers can see all tasks
        if ($user->isSuperAdmin() || $user->isHrManager()) {
            return $query;
        }

        // Others can only see tasks they created or are assigned to
        return $query->where('created_by', $user->id)
                    ->orWhere('assigned_to', $user->id);
    }
}