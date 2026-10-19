<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveYear extends Model
{
    use HasFactory;

    protected $fillable = [
        'year',
        'is_active',
        'description',
    ];

    protected $casts = [
        'year' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Relationship: A leave year can have many leave balances
     */
    public function leaveBalances()
    {
        return $this->hasMany(LeaveBalance::class, 'year', 'year');
    }
}