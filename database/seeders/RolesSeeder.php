<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class RolesSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            [
                'name' => 'super_admin',
                'display_name' => 'Super Admin (IT)',
                'description' => 'Full system access including logs and configurations'
            ],
            [
                'name' => 'hr_manager',
                'display_name' => 'HR Manager',
                'description' => 'Manage employees, approve leaves, set holidays'
            ],
            [
                'name' => 'manager',
                'display_name' => 'Department Manager',
                'description' => 'Approve team leave requests, view team data'
            ],
            [
                'name' => 'dm',
                'display_name' => 'Director Manager',
                'description' => 'Approve manager leave requests'
            ],
            [
                'name' => 'employee',
                'display_name' => 'Employee',
                'description' => 'View own data, apply for leave'
            ],
        ];

        foreach ($roles as $role) {
            Role::create($role);
        }
    }
}
