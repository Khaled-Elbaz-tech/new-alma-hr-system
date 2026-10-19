<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $superAdminRole = Role::where('name', 'super_admin')->first();
        
        User::create([
            'name' => 'System Administrator',
            'email' => 'admin@company.com',
            'password' => Hash::make('password123'),
            'role_id' => $superAdminRole->id,
            'is_active' => true,
        ]);
    }
}
