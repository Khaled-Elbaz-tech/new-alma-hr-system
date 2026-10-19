<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeField extends Model
{
    use HasFactory;

    protected $fillable = [
        'field_name',
        'field_label',
        'field_type',
        'field_options',
        'field_section',
        'is_required',
        'is_active',
        'employee_can_view',
        'employee_can_edit',
        'hr_can_view',
        'hr_can_edit',
        'field_order',
    ];

    protected $casts = [
        'field_options' => 'array',
        'is_required' => 'boolean',
        'is_active' => 'boolean',
        'employee_can_view' => 'boolean',
        'employee_can_edit' => 'boolean',
        'hr_can_view' => 'boolean',
        'hr_can_edit' => 'boolean',
    ];

    public function values()
    {
        return $this->hasMany(EmployeeFieldValue::class, 'field_id');
    }
}
