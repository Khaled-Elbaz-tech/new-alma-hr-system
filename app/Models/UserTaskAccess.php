<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserTaskAccess extends Model
{
    use HasFactory;

    protected $table = 'user_task_access';

    protected $fillable = [
        'user_id',
        'can_access_tasks',
    ];

    protected $casts = [
        'can_access_tasks' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}