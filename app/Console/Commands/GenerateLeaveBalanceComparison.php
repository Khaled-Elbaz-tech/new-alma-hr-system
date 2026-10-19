<?php

namespace App\Console\Commands;

use App\Models\Employee;
use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use App\Services\LeaveCalculationService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class GenerateLeaveBalanceComparison extends Command
{
    protected $signature = 'leave:compare-balances';
    protected $description = 'Generate comparison report of old Excel balances vs new dynamically calculated balances';

    public function handle(LeaveCalculationService $service)
    {
        $this->info('=== Leave Balance Comparison Report ===');
        $this->info('Comparing old (Excel-imported) vs new (dynamic calculation)');
        $this->info('Formula: Balance = Previous Balance + (Months Worked × 1.75) - Annual Leave Used');
        $this->info('');

        // Get all active employees
        $employees = Employee::with('user')->orderBy('employee_number')->get();

        $comparisonData = [];
        $totalOld = 0;
        $totalNew = 0;

        foreach ($employees as $employee) {
            $currentYear = Carbon::now()->year;
            $previousYear = $currentYear - 1;

            // Old balance (from LeaveBalance table)
            $oldCurrentBalance = LeaveBalance::where('employee_id', $employee->id)
                ->where('year', $currentYear)
                ->first();
            $oldPreviousBalance = LeaveBalance::where('employee_id', $employee->id)
                ->where('year', $previousYear)
                ->first();

            $oldBalance = 0;
            if ($oldCurrentBalance) {
                $oldBalance = $oldCurrentBalance->balance;
            } elseif ($oldPreviousBalance) {
                $oldBalance = $oldPreviousBalance->balance;
            }

            // New balance (from calculation service)
            $analysis = $service->analyze($employee);
            $yearlyData = $analysis['years'] ?? [];

            $newCurrentYearData = $yearlyData[$currentYear] ?? null;
            $newPreviousYearData = $yearlyData[$previousYear] ?? null;
            $newYearsBefore = [];

            // Get all previous years with data
            for ($year = $employee->joining_date->year; $year < $currentYear; $year++) {
                if (isset($yearlyData[$year])) {
                    $newYearsBefore[$year] = $yearlyData[$year];
                }
            }

            // New available balance
            // For current year: use calculated balance
            // If current year has no data yet, carry forward previous year's balance
            $newBalance = 0;
            if ($newCurrentYearData) {
                $newBalance = $newCurrentYearData['balance'];
            } elseif ($newPreviousYearData) {
                $newBalance = $newPreviousYearData['balance'];
            }

            $difference = $newBalance - $oldBalance;

            $comparisonData[] = [
                'employee_id' => $employee->id,
                'employee_number' => $employee->employee_number,
                'name' => $employee->user->name ?? 'N/A',
                'department' => $employee->department->name ?? 'N/A',
                'joining_date' => $employee->joining_date->format('Y-m-d'),
                'old_balance' => round($oldBalance, 2),
                'new_balance' => round($newBalance, 2),
                'difference' => round($difference, 2),
                'old_current_entitled' => $oldCurrentBalance ? $oldCurrentBalance->entitled_days : 0,
                'old_current_used' => $oldCurrentBalance ? $oldCurrentBalance->used_days : 0,
                'new_current_entitled' => $newCurrentYearData ? $newCurrentYearData['days_entitled'] : 0,
                'new_current_used' => $newCurrentYearData ? $newCurrentYearData['days_used'] : 0,
                'new_current_carry_forward' => ($newCurrentYearData && $newPreviousYearData)
                    ? $newPreviousYearData['balance']
                    : ($newPreviousYearData ? $newPreviousYearData['balance'] : 0),
            ];

            $totalOld += $oldBalance;
            $totalNew += $newBalance;
        }

        // Output comparison table
        $headers = ['Employee', 'Number', 'Joining', 'Old Balance', 'New Balance', 'Difference'];
        $rows = [];
        foreach ($comparisonData as $item) {
            $rows[] = [
                $item['name'],
                $item['employee_number'],
                $item['joining_date'],
                number_format($item['old_balance'], 2),
                number_format($item['new_balance'], 2),
                $item['difference'] >= 0
                    ? '<fg=green>' . number_format($item['difference'], 2) . '</>'
                    : '<fg=red>' . number_format($item['difference'], 2) . '</>',
            ];
        }

        $this->table($headers, $rows);

        // Summary
        $this->info('');
        $this->info('=== SUMMARY ===');
        $this->info('Total Employees: ' . count($comparisonData));
        $this->info('Total Old Balance: ' . number_format($totalOld, 2));
        $this->info('Total New Balance: ' . number_format($totalNew, 2));
        $this->info('Total Difference: ' . number_format($totalNew - $totalOld, 2));

        // Save detailed report to file
        $reportPath = storage_path('app/leave_balance_comparison_' . now()->format('Y-m-d_H-i-s') . '.csv');
        $this->saveCsvReport($comparisonData, $reportPath);
        $this->info('');
        $this->info('Detailed report saved to: ' . $reportPath);

        return 0;
    }

    private function saveCsvReport(array $data, string $path): void
    {
        $fh = fopen($path, 'w');

        // Write header
        fputcsv($fh, [
            'Employee Number',
            'Name',
            'Department',
            'Joining Date',
            'Old Balance (Excel)',
            'Old Current Year Entitled',
            'Old Current Year Used',
            'New Balance (Calculated)',
            'New Current Year Entitled',
            'New Current Year Used',
            'New Carry Forward',
            'Difference'
        ]);

        foreach ($data as $item) {
            fputcsv($fh, [
                $item['employee_number'],
                $item['name'],
                $item['department'],
                $item['joining_date'],
                $item['old_balance'],
                $item['old_current_entitled'],
                $item['old_current_used'],
                $item['new_balance'],
                $item['new_current_entitled'],
                $item['new_current_used'],
                $item['new_current_carry_forward'],
                $item['difference'],
            ]);
        }

        fclose($fh);
    }
}
