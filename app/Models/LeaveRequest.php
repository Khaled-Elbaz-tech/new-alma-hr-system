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
        // DM approves after HR for Manager/DM leave requests.
        // Null-safe (B4): a missing employee/user/role chain means the
        // DM split cannot apply, never a 500.
        return $this->status === 'hr_approved' && in_array($this->employee?->user?->role?->name, ['manager', 'dm']);
    }

    public function needsManagerApproval(): bool
    {
        return $this->status === 'hr_approved' && in_array($this->employee?->user?->role?->name, ['employee']);
    }

    public function needsDMApproval(): bool
    {
        return $this->status === 'hr_approved' && in_array($this->employee?->user?->role?->name, ['manager', 'dm']);
    }

    /**
     * HR-step waiver (sole-HR deadlock fix): the HR step is waived for
     * leaves whose requester is HR staff (role 'hr_manager' or
     * 'super_admin'). Such leaves go from 'pending' straight to the
     * manager-approve step — the HR step must never be required for
     * them, and no actor may perform it for them (see hrApprove()).
     * Role-based only; applies to ALL HR/super_admin requesters, not
     * only when there is a single HR user. Null-safe: a missing
     * employee/user/role chain means the waiver cannot apply.
     */
    public function skipsHRStep(): bool
    {
        return in_array($this->employee?->user?->role?->name, ['hr_manager', 'super_admin'], true);
    }

    /**
     * Role-only DM check: whether the requester's role requires the DM
     * final step, regardless of the current status. Unlike
     * needsDMApproval() (which also requires status 'hr_approved'),
     * this is usable on the pending-HR path, where the row has not
     * been HR-approved (and never will be — the HR step is waived).
     * With one role per user an HR-staff requester can never also be
     * a manager/dm, so this is false for every skipsHRStep() row
     * today; the check exists so the manager-approve path never
     * assumes that.
     */
    public function needsDMApprovalByRole(): bool
    {
        return in_array($this->employee?->user?->role?->name, ['manager', 'dm'], true);
    }
}
