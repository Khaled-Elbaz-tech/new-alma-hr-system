<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Employee extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'employee_number',
        'joining_date',
        'contract_start_date',
        'contract_end_date',
        'department_id',
        'employment_status',
        'annual_leave_entitlement',
    ];

    protected $casts = [
        'joining_date' => 'date',
        'contract_start_date' => 'date',
        'contract_end_date' => 'date',
        'annual_leave_entitlement' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function fieldValues()
    {
        return $this->hasMany(EmployeeFieldValue::class);
    }

    public function leaveBalances()
    {
        return $this->hasMany(LeaveBalance::class);
    }

    public function leaveRequests()
    {
        return $this->hasMany(LeaveRequest::class);
    }

    // Calculate months since joining
    public function getMonthsFromJoiningAttribute(): int
    {
        return $this->joining_date->diffInMonths(Carbon::now());
    }

    // Calculate entitled days based on months worked
    public function calculateEntitledDays(int $year = null): float
    {
        $year = $year ?? Carbon::now()->year;
        $joiningDate = $this->joining_date;
        
        // If joined after the year started
        if ($joiningDate->year == $year) {
            $monthsWorked = $joiningDate->diffInMonths(Carbon::create($year, 12, 31));
            return round(($this->annual_leave_entitlement / 12) * $monthsWorked, 2);
        }
        
        // If joined before this year, full entitlement
        if ($joiningDate->year < $year) {
            return $this->annual_leave_entitlement;
        }
        
        return 0;
    }

    // Get current balance for a specific leave type (now only Annual Leave tracked)
    public function getLeaveBalance(int $leaveTypeId, int $year = null): float
    {
        $year = $year ?? Carbon::now()->year;
        
        // Since leave_balances now only tracks annual leave (no leave_type_id), 
        // we simply fetch the balance for this employee for the given year
        $balance = $this->leaveBalances()
            ->where('year', $year)
            ->first();
        
        return $balance ? $balance->balance : 0;
    }
}
