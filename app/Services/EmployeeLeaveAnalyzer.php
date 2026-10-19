<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\LeaveBalance;
use Carbon\Carbon;

class EmployeeLeaveAnalyzer
{
    /**
     * Get complete leave analysis for an employee
     * Replicates the Excel "Analyzer" sheet behavior
     *
     * @param Employee $employee
     * @return array
     */
    public function analyze(Employee $employee): array
    {
        $joiningDate = $employee->joining_date;
        $joiningYear = $joiningDate->year;
        $currentYear = Carbon::now()->year;
        
        $yearlyData = [];
        
        for ($year = $joiningYear; $year <= $currentYear; $year++) {
            // 1. Months from hiring (for this specific year)
            $monthsFromHiring = $this->calculateMonthsAtYearEnd($employee, $year);
            
            // 2. Days entitled (prorated: 21 days / 12 months * months worked)
            $daysEntitled = $this->calculateDaysEntitled($employee, $year);
            
            // 3. Days used (sum of approved leave days in this year)
            $daysUsed = $this->calculateDaysUsed($employee, $year);
            
            // 4. Ticket count (flight tickets in approved leaves this year)
            $ticketCount = $this->calculateTicketCount($employee, $year);
            
            // 5. Exit/Re-entry count
            $exitReentryCount = $this->calculateExitReentryCount($employee, $year);
            
            // 6. Balance (can be negative)
            $balance = $daysEntitled - $daysUsed;
            
            $yearlyData[$year] = [
                'year' => $year,
                'months_from_hiring' => $monthsFromHiring,
                'days_entitled' => round($daysEntitled, 2),
                'days_used' => round($daysUsed, 2),
                'ticket_count' => $ticketCount,
                'exit_reentry_count' => $exitReentryCount,
                'balance' => round($balance, 2),
            ];
        }
        
        return [
            'hiring_date' => $joiningDate->format('Y-m-d'),
            'years' => $yearlyData,
        ];
    }
    
    /**
     * Calculate months from hiring at end of a specific year
     * Matches Excel: Months From Hiring (Year) column
     */
    private function calculateMonthsAtYearEnd(Employee $employee, int $year): int
    {
        $yearEnd = Carbon::create($year, 12, 31);
        $joiningDate = $employee->joining_date;
        
        if ($joiningDate->year > $year) {
            return 0;
        }
        
        return $joiningDate->diffInMonths($yearEnd);
    }
    
    /**
     * Calculate entitled days for a year
     * Excel: Days Entitled (Year) = 21 days * (Months From Hiring / 12)
     * Annual leave = 21 days, Monthly accrual = 1.75 days
     */
    private function calculateDaysEntitled(Employee $employee, int $year): float
    {
        $annualEntitlement = 21; // From Excel spec
        $months = $this->calculateMonthsAtYearEnd($employee, $year);
        
        return ($annualEntitlement / 12) * $months;
    }
    
    /**
     * Calculate total approved leave days for a year
     * Sum of total_days from leave_requests where status='approved' and in that year
     */
    private function calculateDaysUsed(Employee $employee, int $year): float
    {
        $total = LeaveRequest::where('employee_id', $employee->id)
            ->where('status', 'approved')
            ->whereYear('start_date', $year)
            ->sum('total_days');
            
        return (float) $total;
    }
    
    /**
     * Count flight tickets for approved leaves in a year
     * Excel: Ticket (Year) = count of leaves with flight_ticket_required = true
     */
    private function calculateTicketCount(Employee $employee, int $year): int
    {
        return LeaveRequest::where('employee_id', $employee->id)
            ->where('status', 'approved')
            ->where('flight_ticket_required', true)
            ->whereYear('start_date', $year)
            ->count();
    }
    
    /**
     * Count exit/re-entry for approved leaves in a year
     * Excel: Exit/Re-entry (Year) = count of leaves with exit_reentry_required = true
     */
    private function calculateExitReentryCount(Employee $employee, int $year): int
    {
        return LeaveRequest::where('employee_id', $employee->id)
            ->where('status', 'approved')
            ->where('exit_reentry_required', true)
            ->whereYear('start_date', $year)
            ->count();
    }
}
