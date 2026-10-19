<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            RolesSeeder::class,
            WeekendSettingsSeeder::class,
            LeaveTypesSeeder::class,
            DepartmentSeeder::class,
            AdminUserSeeder::class,
            DynamicFieldsSeeder::class,
        ]);
    }
}
