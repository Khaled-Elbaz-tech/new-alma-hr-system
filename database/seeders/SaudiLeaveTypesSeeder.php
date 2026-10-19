<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\LeaveType;

/**
 * Saudi Labor Law statutory leave types.
 * Idempotent: safe to re-run (matches on code).
 */
class SaudiLeaveTypesSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            [
                'name' => 'Hajj Leave',
                'code' => 'hajj',
                'default_days' => 15,
                'is_paid' => true,
                'requires_attachment' => true,
                'deduct_from_balance' => false,
                'is_active' => true,
                'description' => 'Saudi Labor Law Art. 114: 10-15 days including Eid Al-Adha, once per entire service, requires 2 continuous years with the employer.',
            ],
            [
                'name' => 'Paternity Leave',
                'code' => 'paternity',
                'default_days' => 3,
                'is_paid' => true,
                'requires_attachment' => true,
                'deduct_from_balance' => false,
                'is_active' => true,
                'description' => 'Saudi Labor Law: 3 paid days on the birth of a child (birth certificate required).',
            ],
            [
                'name' => 'Maternity Leave',
                'code' => 'maternity',
                'default_days' => 70,
                'is_paid' => true,
                'requires_attachment' => true,
                'deduct_from_balance' => false,
                'is_active' => true,
                'description' => 'Saudi Labor Law Art. 151: 10 weeks maternity leave, paid.',
            ],
        ];

        foreach ($types as $type) {
            LeaveType::updateOrCreate(['code' => $type['code']], $type);
        }
    }
}
