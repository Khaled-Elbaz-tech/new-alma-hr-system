<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeFieldValue extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'field_id',
        'value',
        'attachment_path',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function field()
    {
        return $this->belongsTo(EmployeeField::class, 'field_id');
    }
}
