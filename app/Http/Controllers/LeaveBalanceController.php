<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LeaveBalanceController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:super_admin,hr_manager']);
    }

    /**
     * Import historical leave requests from CSV.
     * In the dynamic calculation system, historical leave is stored as
     * LeaveRequest records with is_historical flag, NOT as LeaveBalance records.
     *
     * Integrity contract (WF-07, WF-08, WF-09/L6):
     * - Two-phase, WHOLE-FILE atomic: every row is validated first; if ANY row
     *   fails, NOTHING is written and the full error report is returned. Only
     *   when all rows validate are the creates run inside one DB transaction,
     *   so a mid-write failure rolls the whole import back. (No partial imports.)
     * - total_days must be numeric and >= 0 (negative days previously INFLATED
     *   balances) and must not exceed the calendar span of the range
     *   (end_date - start_date + 1 day). calendar_days, when present, must be
     *   numeric and >= 0.
     * - Dates must really parse (Carbon::parse, failure = row error) and
     *   end_date must be >= start_date. The employee must exist AND be active
     *   (employment_status = 'active'); inactive employees are row errors.
     * - Leave-type lookup is ANCHORED (WF-08): exact, case-insensitive, trimmed
     *   match on name OR code. An empty cell, an unknown name, or a name/code
     *   matching more than one type is a row error — never a silent match on
     *   the first row of the table (the old LIKE '%%' bug).
     * - Limits: 2 MB file (validation max:2048) and 1,000 data rows max.
     * - Robustness (WF-09/L6): column-count mismatches and malformed rows are
     *   per-row errors, never a 500; exception text is logged server-side only
     *   and users see generic messages.
     * - Audit: every run writes one ActivityLog row with file name and counts.
     * - Duplicates (same employee + start_date + leave type, whether already in
     *   the DB or repeated inside the file) are SKIPPED, preserving the old
     *   behaviour, and counted — they are not errors.
     */
    public function importHistorical(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        $file = $request->file('file');
        $fileName = $file->getClientOriginalName();

        $handle = fopen($file->getRealPath(), 'r');
        if ($handle === false) {
            $this->logHistoricalImportAudit($request, $fileName, ['imported' => 0, 'skipped' => 0, 'failed' => 0, 'errors' => ['Could not read the uploaded file.']]);
            return back()->with('error', 'Could not read the uploaded file. Please try again.');
        }

        $headerRow = fgetcsv($handle);
        if ($headerRow === false) {
            fclose($handle);
            $this->logHistoricalImportAudit($request, $fileName, ['imported' => 0, 'skipped' => 0, 'failed' => 0, 'errors' => ['Empty file: no header row.']]);
            return back()->with('error', 'The uploaded file is empty.');
        }
        $header = array_map(function ($h) {
            return trim((string) $h);
        }, $headerRow);
        // Strip a UTF-8 BOM from the first header cell.
        if (isset($header[0])) {
            $header[0] = ltrim($header[0], "\xEF\xBB\xBF");
        }
        if (!in_array('Employee Number', $header, true)) {
            fclose($handle);
            $this->logHistoricalImportAudit($request, $fileName, ['imported' => 0, 'skipped' => 0, 'failed' => 0, 'errors' => ['Missing required Employee Number header column.']]);
            return back()->with('error', 'Import failed: the CSV header must include an Employee Number column.');
        }

        // ---- Phase 1: validate every row, write nothing ----
        $errors = [];
        $pending = [];
        $skipped = 0;
        $seenKeys = [];
        $dataRowNumber = 0;

        try {
            while (($row = fgetcsv($handle)) !== false) {
                // Skip blank lines (e.g. a trailing newline) without counting them.
                $nonEmpty = array_filter($row, function ($v) {
                    return trim((string) $v) !== '';
                });
                if (count($nonEmpty) === 0) {
                    continue;
                }
                $dataRowNumber++;

                if ($dataRowNumber > self::IMPORT_MAX_ROWS) {
                    $errors[] = "Row {$dataRowNumber}: Import row limit (" . self::IMPORT_MAX_ROWS . ") exceeded; the whole import was rejected and nothing was imported.";
                    break;
                }

                // L6: column-count mismatch is a per-row error, never a 500.
                if (count($row) !== count($header)) {
                    $errors[] = "Row {$dataRowNumber}: Column count does not match the header.";
                    continue;
                }

                try {
                    $data = array_combine($header, $row);

                    $employeeNumber = trim($data['Employee Number'] ?? '');
                    if ($employeeNumber === '') {
                        $errors[] = "Row {$dataRowNumber}: Missing Employee Number.";
                        continue;
                    }
                    $employee = Employee::where('employee_number', $employeeNumber)->first();
                    if (!$employee) {
                        $errors[] = "Row {$dataRowNumber}: Employee '{$employeeNumber}' not found.";
                        continue;
                    }
                    if ($employee->employment_status !== 'active') {
                        $errors[] = "Row {$dataRowNumber}: Employee '{$employeeNumber}' is not active.";
                        continue;
                    }

                    // WF-08: anchored, exact (case-insensitive, trimmed) match.
                    $leaveTypeRaw = trim($data['Leave Type'] ?? '');
                    if ($leaveTypeRaw === '') {
                        $errors[] = "Row {$dataRowNumber}: Leave Type is required.";
                        continue;
                    }
                    $leaveTypeKey = mb_strtolower($leaveTypeRaw);
                    $matches = LeaveType::where(function ($q) use ($leaveTypeKey) {
                        $q->whereRaw('LOWER(name) = ?', [$leaveTypeKey])
                            ->orWhereRaw('LOWER(code) = ?', [$leaveTypeKey]);
                    })->get();
                    if ($matches->count() === 0) {
                        $errors[] = "Row {$dataRowNumber}: Leave type '{$leaveTypeRaw}' not found.";
                        continue;
                    }
                    if ($matches->count() > 1) {
                        $errors[] = "Row {$dataRowNumber}: Leave type '{$leaveTypeRaw}' is ambiguous; use the exact leave-type code.";
                        continue;
                    }
                    $leaveTypeId = $matches->first()->id;

                    // WF-07: dates must really parse; end >= start.
                    $startDate = $this->parseImportDate(trim($data['Start Date'] ?? ''));
                    if (!$startDate) {
                        $errors[] = "Row {$dataRowNumber}: Invalid start date.";
                        continue;
                    }
                    $endDate = $this->parseImportDate(trim($data['End Date'] ?? ''));
                    if (!$endDate) {
                        $errors[] = "Row {$dataRowNumber}: Invalid end date.";
                        continue;
                    }
                    if ($endDate->lt($startDate)) {
                        $errors[] = "Row {$dataRowNumber}: End date is before the start date.";
                        continue;
                    }

                    // WF-07: total_days numeric, >= 0, <= calendar span.
                    $totalDaysRaw = trim($data['Total Days'] ?? '');
                    if ($totalDaysRaw === '' || !is_numeric($totalDaysRaw) || (float) $totalDaysRaw < 0) {
                        $errors[] = "Row {$dataRowNumber}: Total Days must be a number of 0 or more.";
                        continue;
                    }
                    $totalDays = (float) $totalDaysRaw;
                    $calendarSpan = $startDate->diffInDays($endDate) + 1;
                    if ($totalDays > $calendarSpan) {
                        $errors[] = "Row {$dataRowNumber}: Total Days exceeds the calendar span of the leave ({$calendarSpan} days).";
                        continue;
                    }

                    $calendarDaysRaw = trim($data['Calendar Days'] ?? '');
                    if ($calendarDaysRaw === '') {
                        $calendarDays = 0;
                    } elseif (!is_numeric($calendarDaysRaw) || (int) $calendarDaysRaw < 0 || (float) $calendarDaysRaw != (int) $calendarDaysRaw) {
                        $errors[] = "Row {$dataRowNumber}: Calendar Days must be a whole number of 0 or more.";
                        continue;
                    } else {
                        $calendarDays = (int) $calendarDaysRaw;
                    }

                    $reason = trim($data['Reason'] ?? '');
                    if ($reason === '') {
                        $reason = 'Historical entry';
                    }
                    if (mb_strlen($reason) > 2000) {
                        $errors[] = "Row {$dataRowNumber}: Reason is too long (max 2000 characters).";
                        continue;
                    }

                    // Duplicate check: already in DB, or repeated inside this file.
                    $dedupKey = $employee->id . '|' . $startDate->format('Y-m-d') . '|' . $leaveTypeId;
                    $existing = isset($seenKeys[$dedupKey]) || LeaveRequest::where('employee_id', $employee->id)
                        ->where('start_date', $startDate->format('Y-m-d'))
                        ->where('leave_type_id', $leaveTypeId)
                        ->exists();
                    if ($existing) {
                        $skipped++;
                        continue;
                    }
                    $seenKeys[$dedupKey] = true;

                    $pending[] = [
                        'employee_id' => $employee->id,
                        'leave_type_id' => $leaveTypeId,
                        'start_date' => $startDate->format('Y-m-d'),
                        'end_date' => $endDate->format('Y-m-d'),
                        'total_days' => $totalDays,
                        'calendar_days' => $calendarDays,
                        'reason' => $reason,
                        'flight_ticket_required' => $this->parseImportFlag($data['Flight Ticket'] ?? null),
                        'exit_reentry_required' => $this->parseImportFlag($data['Exit/Re-entry'] ?? null),
                        'status' => 'approved', // Historical requests are pre-approved
                        'is_historical' => true,
                        'created_by' => Auth::id(),
                    ];
                } catch (\Throwable $e) {
                    // WF-09/L6: log server-side; user sees a generic message.
                    Log::warning('Historical import row failed', ['row' => $dataRowNumber, 'exception' => get_class($e)]);
                    $errors[] = "Row {$dataRowNumber}: Could not validate this row. Please check its values and try again.";
                    continue;
                }
            }
        } catch (\Throwable $e) {
            Log::error('Historical import aborted', ['exception' => get_class($e)]);
            fclose($handle);
            return back()->with('error', 'Import failed unexpectedly. Nothing was imported; please try again.');
        }

        fclose($handle);

        // Any validation failure rejects the WHOLE import — nothing is written.
        if (!empty($errors)) {
            $this->logHistoricalImportAudit($request, $fileName, [
                'imported' => 0,
                'skipped' => $skipped,
                'failed' => count($errors),
                'errors' => array_slice($errors, 0, 50),
            ]);
            $summary = "Import rejected: no rows were imported. {$skipped} rows would have been skipped (already exist). Errors: " . implode('; ', array_slice($errors, 0, 20));
            if (count($errors) > 20) {
                $summary .= '; and ' . (count($errors) - 20) . ' more errors (see activity log).';
            }
            return back()->with('error', $summary);
        }

        // ---- Phase 2: all rows valid — create inside one transaction ----
        try {
            DB::transaction(function () use ($pending) {
                foreach ($pending as $attributes) {
                    LeaveRequest::create($attributes);
                }
            });
        } catch (\Throwable $e) {
            // Transaction rolled back: nothing from this file persists.
            Log::error('Historical import transaction failed', ['exception' => get_class($e)]);
            $this->logHistoricalImportAudit($request, $fileName, ['imported' => 0, 'skipped' => $skipped, 'failed' => count($pending), 'errors' => ['Database error during import; the whole import was rolled back.']]);
            return back()->with('error', 'Import failed. Nothing was imported; please try again.');
        }

        $imported = count($pending);
        $this->logHistoricalImportAudit($request, $fileName, ['imported' => $imported, 'skipped' => $skipped, 'failed' => 0, 'errors' => []]);

        return back()->with('success', "Import completed. {$imported} imported, {$skipped} skipped (already exist).");
    }

    // ---- Historical-import helpers ----

    private const IMPORT_MAX_ROWS = 1000;

    /**
     * Strict-enough date parsing for import cells: returns a Carbon date or
     * null when the cell is empty or unparseable. Never throws to the caller.
     */
    private function parseImportDate(string $raw): ?Carbon
    {
        if ($raw === '') {
            return null;
        }
        try {
            return Carbon::parse($raw)->startOfDay();
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Boolean-ish CSV flags: only 1/true/yes/y/on count as true. (The old code
     * cast the raw string with (bool), so even the string 'false' became true.)
     */
    private function parseImportFlag($value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        return in_array(strtolower(trim((string) $value)), ['1', 'true', 'yes', 'y', 'on'], true);
    }

    /**
     * One ActivityLog row per import run. Never receives raw exception text.
     * A logging failure must never fail the import.
     */
    private function logHistoricalImportAudit(Request $request, string $fileName, array $summary): void
    {
        try {
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'import',
                'model_type' => LeaveRequest::class,
                'model_id' => null,
                'new_values' => array_merge(['file_name' => $fileName], $summary),
                'ip_address' => $request->ip(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('Historical import audit log failed', ['exception' => get_class($e)]);
        }
    }
}
