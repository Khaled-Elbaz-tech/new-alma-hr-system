<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\LeaveRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LeaveBalanceController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:super_admin,hr_manager']);
    }

    /**
     * Import historical leave requests from CSV
     * In the dynamic calculation system, historical leave is stored as
     * LeaveRequest records with is_historical flag, NOT as LeaveBalance records.
     */
    public function importHistorical(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt',
        ]);

        $file = $request->file('file');
        $handle = fopen($file->getRealPath(), 'r');
        $header = fgetcsv($handle);
        $rowCount = 0;
        $imported = 0;
        $skipped = 0;
        $errors = [];

        while (($row = fgetcsv($handle)) !== false) {
            $data = array_combine($header, $row);
            $rowCount++;

            try {
                $employeeNumber = trim($data['Employee Number'] ?? '');
                if (!$employeeNumber) {
                    $errors[] = "Row {$rowCount}: Missing Employee Number";
                    continue;
                }

                $employee = Employee::where('employee_number', $employeeNumber)->first();
                if (!$employee) {
                    $errors[] = "Row {$rowCount}: Employee '{$employeeNumber}' not found";
                    continue;
                }

                // Look up leave type by name or code
                $leaveTypeName = trim($data['Leave Type'] ?? '');
                $leaveTypeId = \App\Models\LeaveType::where('name', 'like', "%{$leaveTypeName}%")
                    ->orWhere('code', 'like', "%{$leaveTypeName}%")
                    ->first()
                    ->id ?? null;

                if (!$leaveTypeId) {
                    $errors[] = "Row {$rowCount}: Leave type '{$leaveTypeName}' not found";
                    continue;
                }

                // Check if this historical request already exists for this employee/date
                $existing = LeaveRequest::where('employee_id', $employee->id)
                    ->where('start_date', $data['Start Date'] ?? null)
                    ->where('leave_type_id', $leaveTypeId)
                    ->first();

                if ($existing) {
                    $skipped++;
                    continue;
                }

                // Create historical leave request
                // Balance will be calculated dynamically from this request
                LeaveRequest::create([
                    'employee_id' => $employee->id,
                    'leave_type_id' => $leaveTypeId,
                    'start_date' => $data['Start Date'] ?? null,
                    'end_date' => $data['End Date'] ?? null,
                    'total_days' => (float) ($data['Total Days'] ?? 0),
                    'calendar_days' => (int) ($data['Calendar Days'] ?? 0),
                    'reason' => $data['Reason'] ?? 'Historical entry',
                    'flight_ticket_required' => (bool) ($data['Flight Ticket'] ?? false),
                    'exit_reentry_required' => (bool) ($data['Exit/Re-entry'] ?? false),
                    'status' => 'approved', // Historical requests are pre-approved
                    'is_historical' => true,
                    'created_by' => Auth::id(),
                ]);

                $imported++;
            } catch (\Exception $e) {
                $errors[] = "Row {$rowCount}: " . $e->getMessage();
            }
        }

        fclose($handle);

        $summary = "Import completed. Row {$rowCount}: {$imported} imported, {$skipped} skipped (already exist).";
        if (!empty($errors)) {
            $summary .= " Errors: " . implode('; ', $errors);
        }

        return back()->with($errors ? 'error' : 'success', $summary);
    }
}
