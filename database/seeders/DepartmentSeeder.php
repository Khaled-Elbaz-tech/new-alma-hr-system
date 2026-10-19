<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Department;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            ['name' => 'IT', 'code' => 'IT', 'is_active' => true],
            ['name' => 'HR', 'code' => 'HR', 'is_active' => true],
            ['name' => 'Finance', 'code' => 'FIN', 'is_active' => true],
            ['name' => 'Operations', 'code' => 'OPS', 'is_active' => true],
            ['name' => 'Marketing', 'code' => 'MKT', 'is_active' => true],
        ];
        foreach ($departments as $dept) {
            Department::firstOrCreate(['code' => $dept['code']], $dept);
        }
    }
}
