<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WeekendSetting extends Model
{
    use HasFactory;

    protected $fillable = ['day', 'is_weekend'];

    protected $casts = [
        'is_weekend' => 'boolean',
    ];

    public static function getWeekendDays(): array
    {
        return self::where('is_weekend', true)->pluck('day')->toArray();
    }
}
