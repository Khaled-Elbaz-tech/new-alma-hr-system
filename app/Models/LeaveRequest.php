<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LeaveRequest extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'employee_id', 'leave_type_id', 'start_date', 'end_date',
        'total_days', 'calendar_days', 'reason', 'exit_reentry_required',
        'flight_ticket_required', 'hotel_booking_required', 'transfer_required',
        'status', 'hr_approved_by', 'hr_approved_at', 'hr_comments',
        'manager_approved_by', 'manager_approved_at', 'manager_comments',
        'rejected_by', 'rejected_at', 'rejection_reason', 'created_by', 'is_historical',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'exit_reentry_required' => 'boolean',
        'flight_ticket_required' => 'boolean',
        'hr_approved_at' => 'datetime',
        'manager_approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'is_historical' => 'boolean',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function leaveType()
    {
        return $this->belongsTo(LeaveType::class);
    }

    public function hrApprover()
    {
        return $this->belongsTo(User::class, 'hr_approved_by');
    }

    public function managerApprover()
    {
        return $this->belongsTo(User::class, 'manager_approved_by');
    }

    public function rejectedByUser()
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Status helpers
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function canBeApprovedByHR(): bool
    {
        return $this->status === 'pending';
    }

    public function canBeApprovedByManager(): bool
    {
        // Employee: needs hr_approved first, then manager
        // Manager: needs hr_approved first, then DM (handled separately)
        return $this->status === 'hr_approved';
    }

    public function canBeApprovedByDM(): bool
    {
        // DM approves after HR for Manager/DM leave requests
        return $this->status === 'hr_approved' && in_array($this->employee->user->role->name, ['manager', 'dm']);
    }

    public function needsManagerApproval(): bool
    {
        return $this->status === 'hr_approved' && in_array($this->employee->user->role->name, ['employee']);
    }

    public function needsDMApproval(): bool
    {
        return $this->status === 'hr_approved' && in_array($this->employee->user->role->name, ['manager', 'dm']);
    }
}
