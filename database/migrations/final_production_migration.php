<?php
/**
 * FINAL PRODUCTION MIGRATION SCRIPT
 * 
 * Source of truth: New-Vacation-System.xlsx (converted to JSON)
 * 
 * This script:
 * 1. Creates missing employees
 * 2. Updates joining dates
 * 3. Imports leave balances
 * 4. Fixes duplicate records
 * 5. Ensures leave years exist
 * 6. Generates migration report
 */

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\Employee;
use App\Models\User;
use App\Models\LeaveBalance;
use App\Models\LeaveYear;
use App\Models\Department;
use App\Models\Role;
use Carbon\Carbon;

$startTime = microtime(true);
$report = [
    'matched_employees' => [],
    'created_employees' => [],
    'updated_employees' => [],
    'skipped_employees' => [],
    'leave_balances_imported' => 0,
    'leave_balances_updated' => 0,
    'errors' => [],
];

// Load Excel data from JSON
$jsonPath = __DIR__ . '/storage/app/excel_employees.json';
if (!file_exists($jsonPath)) {
    // Try temp location
    $jsonPath = 'C:\\Users\\THISPC~1\\AppData\\Local\\Temp\\kilo\\excel_employees.json';
}

if (!file_exists($jsonPath)) {
    die("ERROR: Excel JSON file not found at $jsonPath\n");
}

$excelEmployees = json_decode(file_get_contents($jsonPath), true);
if (!$excelEmployees) {
    die("ERROR: Failed to parse Excel JSON file\n");
}

echo "=== FINAL PRODUCTION MIGRATION ===\n\n";
echo "Excel employees loaded: " . count($excelEmployees) . "\n\n";

// Get existing system data
$existingEmployees = Employee::with('user')->get()->keyBy('user_id');
$existingUsers = User::pluck('id', 'email')->toArray();
$departments = Department::pluck('id', 'name')->toArray();
$roles = Role::whereIn('name', ['employee', 'manager', 'dm', 'hr_manager'])->pluck('id', 'name')->toArray();
$defaultRoleId = $roles['employee'] ?? 5;

// Ensure default department exists
if (empty($departments)) {
    $dept = Department::create(['name' => 'General', 'is_active' => true]);
    $departments['General'] = $dept->id;
}

// Ensure leave years exist
$requiredYears = [2022, 2023, 2024, 2025, 2026];
foreach ($requiredYears as $year) {
    LeaveYear::firstOrCreate(['year' => $year], ['is_active' => true]);
}
echo "Leave years ensured: " . implode(', ', $requiredYears) . "\n\n";

// Process each Excel employee
foreach ($excelEmployees as $idx => $excelEmp) {
    $name = $excelEmp['name'];
    $hiringDate = $excelEmp['hiring_date'];
    $balances = $excelEmp['balances'];
    
    // Try to find existing user by name
    $existingUser = User::where('name', $name)->first();
    
    if ($existingUser) {
        // Employee exists in system
        $employee = $existingUser->employee;
        
        if ($employee) {
            // Update joining date if different
            if ($employee->joining_date !== $hiringDate) {
                $employee->joining_date = $hiringDate;
                $employee->save();
                $report['updated_employees'][] = [
                    'name' => $name,
                    'employee_number' => $employee->employee_number,
                    'old_joining_date' => $employee->joining_date,
                    'new_joining_date' => $hiringDate,
                ];
            }
            
            $report['matched_employees'][] = [
                'name' => $name,
                'employee_number' => $employee->employee_number,
                'hiring_date' => $hiringDate,
            ];
            
            // Import balances
            foreach ($balances as $balanceData) {
                $year = $balanceData['year'];
                
                // Skip pre-hiring years
                $hireYear = (int)date('Y', strtotime($hiringDate));
                if ($year < $hireYear) {
                    continue;
                }
                
                $balance = LeaveBalance::updateOrCreate(
                    [
                        'employee_id' => $employee->id,
                        'year' => $year,
                    ],
                    [
                        'months_from_hiring' => $balanceData['months_from_hiring'],
                        'entitled_days' => $balanceData['entitled_days'],
                        'used_days' => $balanceData['used_days'],
                        'ticket_count' => $balanceData['ticket_count'],
                        'exit_reentry_count' => $balanceData['exit_reentry_count'],
                        'balance' => $balanceData['balance'],
                    ]
                );
                
                if ($balance->wasRecentlyCreated) {
                    $report['leave_balances_imported']++;
                } else {
                    $report['leave_balances_updated']++;
                }
            }
        } else {
            // User exists but no employee record - create employee
            $employee = Employee::create([
                'user_id' => $existingUser->id,
                'employee_number' => 'EMP' . str_pad($existingUser->id, 3, '0', STR_PAD_LEFT),
                'joining_date' => $hiringDate,
                'employment_status' => 'active',
                'annual_leave_entitlement' => 21,
            ]);
            
            $report['created_employees'][] = [
                'name' => $name,
                'employee_number' => $employee->employee_number,
                'hiring_date' => $hiringDate,
                'note' => 'Created employee record for existing user',
            ];
            
            // Import balances
            foreach ($balances as $balanceData) {
                $year = $balanceData['year'];
                $hireYear = (int)date('Y', strtotime($hiringDate));
                if ($year < $hireYear) continue;
                
                LeaveBalance::updateOrCreate(
                    [
                        'employee_id' => $employee->id,
                        'year' => $year,
                    ],
                    [
                        'months_from_hiring' => $balanceData['months_from_hiring'],
                        'entitled_days' => $balanceData['entitled_days'],
                        'used_days' => $balanceData['used_days'],
                        'ticket_count' => $balanceData['ticket_count'],
                        'exit_reentry_count' => $balanceData['exit_reentry_count'],
                        'balance' => $balanceData['balance'],
                    ]
                );
                $report['leave_balances_imported']++;
            }
        }
    } else {
        // Employee not found - create new user and employee
        $email = strtolower(str_replace(' ', '.', $name)) . '@almatravel.co';
        
        // Ensure unique email
        $baseEmail = $email;
        $counter = 1;
        while (isset($existingUsers[$email])) {
            $email = preg_replace('/@/', $counter . '@', $baseEmail);
            $counter++;
        }
        
        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make('password123'),
            'role_id' => $defaultRoleId,
            'is_active' => true,
        ]);
        
        $employeeNumber = 'EMP' . str_pad($user->id, 3, '0', STR_PAD_LEFT);
        
        $employee = Employee::create([
            'user_id' => $user->id,
            'employee_number' => $employeeNumber,
            'joining_date' => $hiringDate,
            'employment_status' => 'active',
            'annual_leave_entitlement' => 21,
        ]);
        
        $report['created_employees'][] = [
            'name' => $name,
            'employee_number' => $employeeNumber,
            'email' => $email,
            'hiring_date' => $hiringDate,
        ];
        
        // Import balances
        foreach ($balances as $balanceData) {
            $year = $balanceData['year'];
            $hireYear = (int)date('Y', strtotime($hiringDate));
            if ($year < $hireYear) continue;
            
            LeaveBalance::updateOrCreate(
                [
                    'employee_id' => $employee->id,
                    'year' => $year,
                ],
                [
                    'months_from_hiring' => $balanceData['months_from_hiring'],
                    'entitled_days' => $balanceData['entitled_days'],
                    'used_days' => $balanceData['used_days'],
                    'ticket_count' => $balanceData['ticket_count'],
                    'exit_reentry_count' => $balanceData['exit_reentry_count'],
                    'balance' => $balanceData['balance'],
                ]
            );
            $report['leave_balances_imported']++;
        }
    }
}

// Fix duplicate employees
echo "=== FIXING DUPLICATE EMPLOYEES ===\n";

$duplicatePairs = [
    ['original_emp_num' => 'EMP108', 'duplicate_emp_num' => 'EMP0151'],
    ['original_emp_num' => 'EMP144', 'duplicate_emp_num' => 'EMP0153'],
    ['original_emp_num' => 'EMP150', 'duplicate_emp_num' => 'EMP0156'],
    ['original_emp_num' => 'EMP149', 'duplicate_emp_num' => 'EMP0159'],
];

foreach ($duplicatePairs as $pair) {
    $original = Employee::where('employee_number', $pair['original_emp_num'])->first();
    $duplicate = Employee::where('employee_number', $pair['duplicate_emp_num'])->first();
    
    if ($original && $duplicate) {
        // Transfer any data from duplicate to original (if needed)
        // Delete duplicate
        $duplicate->delete();
        
        // Delete duplicate user
        if ($duplicate->user) {
            $duplicate->user->delete();
        }
        
        echo "  Deleted duplicate: {$pair['duplicate_emp_num']} (kept {$pair['original_emp_num']})\n";
        $report['skipped_employees'][] = [
            'name' => $duplicate->user->name ?? 'Unknown',
            'employee_number' => $pair['duplicate_emp_num'],
            'action' => 'Deleted duplicate',
        ];
    }
}

// Calculate final counts
$finalEmployeeCount = Employee::count();
$finalUserCount = User::count();
$finalBalanceCount = LeaveBalance::count();

$endTime = microtime(true);
$duration = round($endTime - $startTime, 2);

// Generate report
echo "\n=== MIGRATION REPORT ===\n\n";
echo "Matched Employees: " . count($report['matched_employees']) . "\n";
echo "Created Employees: " . count($report['created_employees']) . "\n";
echo "Updated Employees: " . count($report['updated_employees']) . "\n";
echo "Skipped/Duplicates: " . count($report['skipped_employees']) . "\n";
echo "Leave Balances Imported: " . $report['leave_balances_imported'] . "\n";
echo "Leave Balances Updated: " . $report['leave_balances_updated'] . "\n";
echo "Errors: " . count($report['errors']) . "\n";
echo "\nFinal Counts:\n";
echo "  Employees: $finalEmployeeCount\n";
echo "  Users: $finalUserCount\n";
echo "  Leave Balances: $finalBalanceCount\n";
echo "  Duration: {$duration}s\n";

if (count($report['errors']) > 0) {
    echo "\n=== ERRORS ===\n";
    foreach ($report['errors'] as $error) {
        echo "  - $error\n";
    }
}

// Save report to file
$reportPath = __DIR__ . '/storage/app/migration_report.json';
file_put_contents($reportPath, json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
echo "\nReport saved to: $reportPath\n";

echo "\n=== MIGRATION COMPLETE ===\n";
