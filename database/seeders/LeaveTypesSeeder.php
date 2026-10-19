<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\LeaveType;

class LeaveTypesSeeder extends Seeder
{
    public function run(): void
    {
        $leaveTypes = [
            [
                'name' => 'Annual Leave',
                'code' => 'annual',
                'default_days' => 21,
                'is_paid' => true,
                'requires_attachment' => false,
                'deduct_from_balance' => true,
                'description' => 'Regular annual vacation'
            ],
            [
                'name' => 'Sick Leave',
                'code' => 'sick',
                'default_days' => 30,
                'is_paid' => true,
                'requires_attachment' => true,
                'deduct_from_balance' => false,
                'description' => 'Medical leave with doctor certificate'
            ],
            [
                'name' => 'Business Leave',
                'code' => 'business',
                'default_days' => null,
                'is_paid' => true,
                'requires_attachment' => false,
                'deduct_from_balance' => false,
                'description' => 'Official business travel'
            ],
            [
                'name' => 'Marriage Leave',
                'code' => 'marriage',
                'default_days' => 5,
                'is_paid' => true,
                'requires_attachment' => true,
                'deduct_from_balance' => false,
                'description' => 'Marriage leave - one time only'
            ],
            [
                'name' => 'Bereavement Leave',
                'code' => 'bereavement',
                'default_days' => 5,
                'is_paid' => true,
                'requires_attachment' => true,
                'deduct_from_balance' => false,
                'description' => 'Death of immediate family member'
            ],
            [
                'name' => 'Emergency Leave',
                'code' => 'emergency',
                'default_days' => 3,
                'is_paid' => true,
                'requires_attachment' => false,
                'deduct_from_balance' => true,
                'description' => 'Urgent personal matters'
            ],
        ];

        foreach ($leaveTypes as $type) {
            LeaveType::create($type);
        }
    }
}
