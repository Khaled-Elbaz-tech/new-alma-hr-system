<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class LeaveBalance extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'year',
        'months_from_hiring',
        'entitled_days',
        'used_days',
        'ticket_count',
        'exit_reentry_count',
        'balance',
    ];

    protected $casts = [
        'months_from_hiring' => 'integer',
        'entitled_days' => 'decimal:2',
        'used_days' => 'decimal:2',
        'ticket_count' => 'integer',
        'exit_reentry_count' => 'integer',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Relationship to LeaveYear (by year number, not ID)
     */
    public function leaveYear()
    {
        return $this->belongsTo(LeaveYear::class, 'year', 'year');
    }

    /**
     * Calculate remaining balance on-the-fly
     * Includes carry-forward from all previous years
     * Excel balance = sum of (entitled - used) for current and all prior years
     */
    public function getBalanceAttribute(): float
    {
        $current = round($this->entitled_days - $this->used_days, 2);
        $previousSum = LeaveBalance::where('employee_id', $this->employee_id)
            ->where('year', '<', $this->year)
            ->sum(DB::raw('entitled_days - used_days'));
        return round($current + ($previousSum ?? 0), 2);
    }
}
