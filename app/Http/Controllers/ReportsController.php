<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\Department;
use App\Services\LeaveCalculationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * ReportsController — FIXED batch (Reports + Dashboard worker).
 *
 * Fixes applied (see PATCH-NOTEs in hr-fixes-work/reports/):
 * - WF-10: every user-facing balance now comes from visibleBalanceData()
 *   (current-year entitlement EXCLUDED until 1 January) or from
 *   visibleYearRows(), a grouped-query mirror of exactly that policy.
 *   No analyze()-based balance remains in this file.
 * - M3/WF-15: all CSV output goes through fputcsv() (RFC 4180 quoting)
 *   with OWASP formula-injection neutralisation per cell.
 * - M4: Content-Disposition filenames are stripped of CR/LF/quotes/
 *   backslashes/controls and always quoted; no raw name interpolation.
 * - WF-16: bulk paths use grouped queries + chunked streaming instead of
 *   per-employee/per-year analyze() loops and full-table loads.
 * - L5: every orWhere search is wrapped in where(function...) so it cannot
 *   bypass the surrounding department/status filters.
 * - L10: every LIKE search term is escaped for %, _ and backslash.
 * - exportYearByYear: fixed undefined $currentYear in the filename.
 * - exportLeaveHistory/exportYearByYear: null-employee guards + null-safe
 *   relations (previously a missing employee/type could 500).
 *
 * Preserved: filter params of the staged filters version, POST-only
 * mutations (none in this controller), dynamic balances, the visible-balance
 * policy (current-year entitlement hidden until 1 January, negatives
 * shown as-is), and the PhpSpreadsheet fix (which lives in
 * EmployeeController, untouched here).
 */
class ReportsController extends Controller
{
    protected $leaveCalculationService;

    public function __construct(LeaveCalculationService $leaveCalculationService)
    {
        $this->leaveCalculationService = $leaveCalculationService;
    }

    // ------------------------------------------------------------------
    // Shared helpers (WF-10 / WF-16 / M3 / M4 / L10)
    // ------------------------------------------------------------------

    /**
     * L10: escape LIKE wildcards in a user-supplied search term.
     * Backslash first, otherwise the escapes themselves would be escaped.
     */
    protected function escapeLike(?string $value): string
    {
        return str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], (string) $value);
    }

    /**
     * M3/WF-15 (OWASP CSV Injection): neutralise a cell that Excel would
     * interpret as a formula. A value whose first non-blank character is
     * =, +, -, @, tab or CR is prefixed with a single quote (Excel treats
     * the leading quote as a "force text" marker and does not display it).
     * Non-string values (numbers, null) pass through untouched, so numeric
     * balances are never turned into text.
     */
    protected function sanitizeCsvCell($value)
    {
        if (!is_string($value) || $value === '') {
            return $value;
        }
        if (in_array($value[0], ["\t", "\r"], true)) {
            return "'" . $value;
        }
        $trimmed = ltrim($value, " \t\r\n");
        if ($trimmed !== '' && in_array($trimmed[0], ['=', '+', '-', '@'], true)) {
            return "'" . $value;
        }
        return $value;
    }

    /**
     * M4: build a safe download filename. Strips CR/LF (header injection),
     * double quotes and backslashes (quoted-string breakout), and control
     * characters. The result is always emitted inside double quotes by
     * csvResponseHeaders(). Non-ASCII characters (e.g. Arabic names) are
     * kept — they are harmless inside a quoted string once CR/LF/quote/
     * backslash are removed — so filenames stay recognisable.
     */
    protected function safeFilename(string $filename, string $extension = 'csv'): string
    {
        $clean = str_replace(["\r", "\n", '"', "'", '\\', '/'], '', $filename);
        $clean = preg_replace('/[\x00-\x1F\x7F]+/', '', $clean);
        $clean = trim($clean, " .");
        if ($clean === '') {
            $clean = 'export.' . $extension;
        }
        if (!str_ends_with(strtolower($clean), '.' . $extension)) {
            $clean .= '.' . $extension;
        }
        return $clean;
    }

    protected function csvResponseHeaders(string $filename): array
    {
        return [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $this->safeFilename($filename) . '"',
        ];
    }

    /**
     * Driver-aware YEAR() / MONTH() expressions for grouped queries.
     *
     * Production runs MySQL, where YEAR(col) / MONTH(col) are native.
     * Local development may run SQLite, which has no YEAR() function
     * (it 500s with "no such function: YEAR"), so SQLite uses
     * strftime() cast back to integer. PostgreSQL uses EXTRACT()
     * (standard SQL, not exercised locally). SQL Server also has
     * YEAR() / MONTH(), so it takes the default branch. The returned
     * values are integers on every driver, and the MySQL SQL is
     * unchanged, so MySQL results do not change.
     *
     * $column is a bare or table-qualified column name, e.g.
     * 'start_date' or 'leave_requests.start_date'.
     */
    protected function yearExpression(string $column): string
    {
        return match (DB::connection()->getDriverName()) {
            'sqlite' => "CAST(strftime('%Y', {$column}) AS INTEGER)",
            'pgsql' => "EXTRACT(YEAR FROM {$column})",
            default => "YEAR({$column})",
        };
    }

    protected function monthExpression(string $column): string
    {
        return match (DB::connection()->getDriverName()) {
            'sqlite' => "CAST(strftime('%m', {$column}) AS INTEGER)",
            'pgsql' => "EXTRACT(MONTH FROM {$column})",
            default => "MONTH({$column})",
        };
    }

    /**
     * WF-16: one grouped query for ALL approved-annual usage of a set of
     * employees, instead of one query per employee per year inside
     * analyze()/calculateYearData(). Returns
     * [employee_id][year] => ['used' => float, 'tickets' => int, 'exits' => int].
     * The year expression is driver-aware (see yearExpression()):
     * YEAR() on MySQL, strftime() on SQLite, EXTRACT() on PostgreSQL.
     */
    protected function groupedAnnualStats(array $employeeIds): array
    {
        if (empty($employeeIds)) {
            return [];
        }
        $rows = LeaveRequest::selectRaw(
                'employee_id, ' . $this->yearExpression('start_date') . ' as y, SUM(total_days) as used, '
                . 'SUM(CASE WHEN flight_ticket_required THEN 1 ELSE 0 END) as tickets, '
                . 'SUM(CASE WHEN exit_reentry_required THEN 1 ELSE 0 END) as exits'
            )
            ->whereIn('employee_id', $employeeIds)
            ->where('status', 'approved')
            ->whereHas('leaveType', function ($q) {
                $q->where('code', 'annual');
            })
            ->groupBy('employee_id', DB::raw($this->yearExpression('start_date')))
            ->get();

        $stats = [];
        foreach ($rows as $row) {
            $stats[$row->employee_id][(int) $row->y] = [
                'used' => (float) $row->used,
                'tickets' => (int) $row->tickets,
                'exits' => (int) $row->exits,
            ];
        }
        return $stats;
    }

    /**
     * WF-16/WF-10: months worked in a year. Pure date arithmetic mirroring
     * LeaveCalculationService::calculateMonthsAtYearEnd() exactly (no
     * queries), so the grouped bulk path agrees with the service.
     */
    protected function monthsFromHiring(?Employee $employee, int $year): int
    {
        if (!$employee || !$employee->joining_date) {
            return 0;
        }
        $joiningDate = Carbon::parse($employee->joining_date);
        if ($joiningDate->year > $year) {
            return 0;
        }
        $yearStart = Carbon::create($year, 1, 1)->startOfDay();
        $yearEnd = Carbon::create($year, 12, 31)->endOfDay();
        $workStart = $joiningDate->lt($yearStart) ? $yearStart : $joiningDate;
        $effectiveEnd = min($yearEnd, Carbon::now());
        if ($workStart->gt($effectiveEnd)) {
            return 0;
        }
        $months = ($effectiveEnd->year - $workStart->year) * 12
            + ($effectiveEnd->month - $workStart->month) + 1;
        return max(0, min(12, $months));
    }

    /**
     * Mirrors LeaveCalculationService::calculateDaysEntitled(): 1.75 days
     * per month worked, capped at the employee's annual entitlement.
     */
    protected function entitledForYear(?Employee $employee, int $year): float
    {
        $months = $this->monthsFromHiring($employee, $year);
        $entitled = round(1.75 * $months, 2);
        return min($entitled, $employee->annual_leave_entitlement ?? 21);
    }

    /**
     * WF-10/WF-16 bulk path: visible-policy year rows for one employee,
     * built from grouped stats (no per-year queries). Shape matches the
     * old analyze() rows so views need no change, except:
     *  - days_entitled is NULL for the current year (hidden until
     *    1 January, per the visible-balance policy — views should render
     *    '—'; a null here is never a zero),
     *  - balance is the RUNNING VISIBLE balance (closed-year entitlements
     *    only, minus used days of ALL years, current year included),
     *    i.e. exactly visibleBalanceData()['visible_balance'] at the
     *    current-year row. The running total is rounded each year, as the
     *    service does, so the two paths agree to the cent.
     * Single-employee detail paths use visibleBalanceData() directly via
     * singleEmployeeVisibleRows(); this bulk mirror is cross-checkable
     * against it on any one employee.
     */
    protected function visibleYearRows(Employee $employee, array $statsForEmployee): array
    {
        $currentYear = Carbon::now()->year;
        $joiningYear = $employee->joining_date
            ? Carbon::parse($employee->joining_date)->year
            : $currentYear;
        $rows = [];
        $running = 0.0;
        for ($y = $joiningYear; $y <= $currentYear; $y++) {
            $isCurrent = ($y === $currentYear);
            $entitled = $isCurrent ? null : $this->entitledForYear($employee, $y);
            $used = $statsForEmployee[$y]['used'] ?? 0;
            $running = round($running + ($entitled ?? 0) - $used, 2);
            $rows[$y] = [
                'year' => $y,
                'months_from_hiring' => $this->monthsFromHiring($employee, $y),
                'days_entitled' => $entitled,
                'days_used' => round((float) $used, 2),
                'ticket_count' => $statsForEmployee[$y]['tickets'] ?? 0,
                'exit_reentry_count' => $statsForEmployee[$y]['exits'] ?? 0,
                'balance' => $running,
            ];
        }
        return $rows;
    }

    /**
     * WF-10 detail path: visibleBalanceData() is the balance source here
     * (single employee, so the per-employee service call is bounded).
     * Grouped stats supply only NON-balance fields (months, tickets,
     * exits) that visibleBalanceData() does not return. Output shape is
     * the same as visibleYearRows() for view compatibility.
     */
    protected function singleEmployeeVisibleRows(Employee $employee): array
    {
        $visible = $this->leaveCalculationService->visibleBalanceData($employee);
        $stats = $this->groupedAnnualStats([$employee->id])[$employee->id] ?? [];
        $rows = [];
        foreach (($visible['rows'] ?? []) as $row) {
            $y = (int) $row['year'];
            $rows[$y] = [
                'year' => $y,
                'months_from_hiring' => $this->monthsFromHiring($employee, $y),
                'days_entitled' => $row['entitled'],
                'days_used' => $row['used'],
                'ticket_count' => $stats[$y]['tickets'] ?? 0,
                'exit_reentry_count' => $stats[$y]['exits'] ?? 0,
                'balance' => $row['remaining'],
            ];
        }
        return $rows;
    }

    // Leave Balance Report - Year by year breakdown per employee
    public function leaveBalance(Request $request)
    {
        $employeeId = $request->employee_id;
        $year = $request->year ?? Carbon::now()->year;

        $employees = Employee::with(['user', 'department'])
            ->where('employment_status', 'active')
            ->join('users', 'employees.user_id', '=', 'users.id')
            ->when($request->filled('department_id') && is_numeric($request->department_id), function ($q) use ($request) {
                $q->where('employees.department_id', (int) $request->department_id);
            })
            ->when($request->filled('search'), function ($q) use ($request) {
                // L5: search grouped so it cannot bypass the department filter above.
                // L10: LIKE wildcards escaped.
                $search = $this->escapeLike($request->search);
                $q->where(function ($q2) use ($search) {
                    $q2->where('users.name', 'like', "%{$search}%")
                        ->orWhere('employees.employee_number', 'like', "%{$search}%");
                });
            })
            ->orderBy('users.name')
            ->get(['employees.*']);
        $departments = Department::where('is_active', true)->orderBy('name')->get();

        $leaveTypes = LeaveType::where('is_active', true)->get();

        // If specific employee selected, show year-by-year breakdown.
        // WF-10: balances from visibleBalanceData() (via singleEmployeeVisibleRows).
        $selectedEmployee = null;
        $yearByYearData = [];

        if ($employeeId) {
            $selectedEmployee = Employee::with(['user', 'department'])->find($employeeId);

            if ($selectedEmployee) {
                foreach ($this->singleEmployeeVisibleRows($selectedEmployee) as $y => $data) {
                    $yearByYearData[] = [
                        'year' => $y,
                        'months_from_hiring' => $data['months_from_hiring'] ?? 0,
                        'entitled_days' => $data['days_entitled'],
                        'used_days' => $data['days_used'] ?? 0,
                        'exit_reentry_count' => $data['exit_reentry_count'] ?? 0,
                        'ticket_count' => $data['ticket_count'] ?? 0,
                        'balance' => $data['balance'] ?? 0,
                    ];
                }
            }
        }

        // WF-16: ONE grouped query for all listed employees replaces the
        // old per-employee analyze() loop (which ran several queries per
        // employee per year). Visible policy applied per row.
        $groupedStats = $this->groupedAnnualStats($employees->pluck('id')->all());
        $employeeBalances = [];
        foreach ($employees as $emp) {
            $employeeBalances[$emp->id] = $this->visibleYearRows($emp, $groupedStats[$emp->id] ?? []);
        }

        // Summary for all employees for the selected year. For the current
        // year, entitled_days is null (hidden until 1 January) and balance
        // is the visible running balance; for past years the row is
        // identical to the old figure.
        $summaryData = $employees->map(function ($emp) use ($year, $employeeBalances) {
            $yearData = $employeeBalances[$emp->id][$year] ?? null;

            return [
                'employee' => $emp,
                // NOTE: do NOT use ?? here for entitled_days — a null value
                // is meaningful (current-year entitlement hidden), and ??
                // would silently turn it into 0. Missing year => 0.
                'entitled_days' => $yearData && array_key_exists('days_entitled', $yearData)
                    ? $yearData['days_entitled']
                    : 0,
                'used_days' => $yearData['days_used'] ?? 0,
                'balance' => $yearData['balance'] ?? 0,
            ];
        });

        return view('reports.leave-balance', compact(
            'employees',
            'departments',
            'leaveTypes',
            'year',
            'employeeId',
            'selectedEmployee',
            'yearByYearData',
            'summaryData'
        ));
    }

    // Expected Balance Report — balance each employee is expected to
    // hold on 1 January of the next year (rolling; see
    // LeaveCalculationService::expectedBalanceData()). One service call
    // per employee, the same per-employee path the employee show page
    // uses. Scope copies EmployeeController::index()'s base query:
    // all employees except soft-deleted ones (SoftDeletes global
    // scope); no employment_status filter is invented here. Default
    // sort: expected balance ascending, then employee name. Export is
    // exportExpectedBalance() below (same rows, ?format=csv|xlsx).
    // No filters.
    //
    // Each employee also gets year-by-year detail rows (joining year
    // through the current year, from visibleBalanceData()): closed
    // years show their entitled figure and the running visible
    // balance; the current-year row shows the FULL-year entitlement
    // (opens 1 January next year) with BOTH the visible running
    // figure (Remaining) and the expected figure (Expected
    // Remaining) kept distinct. A booked-only next-year row is added
    // only when the employee has approved annual leave starting in
    // the next year. Pending figures per year come from
    // awaitingApprovalAnnualByYear() (same awaiting-approval status
    // set as expectedBalanceData()).
    public function expectedBalance(Request $request)
    {
        [$rows, $currentYear, $nextYear] = $this->buildExpectedBalanceRows();

        return view('reports.expected-balance', compact('rows', 'currentYear', 'nextYear'));
    }

    /**
     * Shared row builder for the Expected Balance page AND its CSV/XLSX
     * exports, so the two can never drift. Returns
     * [$rows, $currentYear, $nextYear] in exactly the order and shape
     * the page has always received.
     */
    protected function buildExpectedBalanceRows(): array
    {
        $currentYear = Carbon::now()->year;
        $nextYear = $currentYear + 1;

        $employees = Employee::with(['user', 'department'])->get();

        $rows = [];
        foreach ($employees as $emp) {
            $expected = $this->leaveCalculationService->expectedBalanceData($emp);
            $visible = $this->leaveCalculationService->visibleBalanceData($emp);
            $pendingByYear = $this->leaveCalculationService->awaitingApprovalAnnualByYear($emp);

            $detailRows = [];
            foreach (($visible['rows'] ?? []) as $vRow) {
                $year = (int) $vRow['year'];
                $isCurrent = (bool) ($vRow['is_current'] ?? false);
                $detailRows[] = [
                    'kind' => $isCurrent ? 'current' : 'closed',
                    'year_label' => (string) $year . ($isCurrent ? ' (current year)' : ''),
                    'entitled' => $isCurrent ? (float) $expected['current_year_entitled_full'] : $vRow['entitled'],
                    'entitled_note' => $isCurrent ? ('opens 1 January ' . $nextYear) : null,
                    'used' => (float) $vRow['used'],
                    'pending' => (float) ($pendingByYear[$year] ?? 0.0),
                    'remaining' => (float) $vRow['remaining'],
                    'remaining_note' => $isCurrent ? 'visible today' : null,
                    'expected_remaining' => $isCurrent ? (float) $expected['expected_balance'] : null,
                ];
            }

            if ((float) $expected['approved_next_year_used'] > 0) {
                $detailRows[] = [
                    'kind' => 'next_booked',
                    'year_label' => (string) $nextYear . ' (booked only)',
                    'entitled' => null,
                    'entitled_note' => null,
                    'used' => (float) $expected['approved_next_year_used'],
                    'pending' => (float) ($pendingByYear[$nextYear] ?? 0.0),
                    'remaining' => (float) $expected['expected_after_next_year_approved'],
                    'remaining_note' => 'expected after booked leave',
                    'expected_remaining' => null,
                ];
            }

            $rows[] = [
                'employee' => $emp,
                'expected' => $expected,
                'detail_rows' => $detailRows,
            ];
        }

        usort($rows, function ($a, $b) {
            $cmp = $a['expected']['expected_balance'] <=> $b['expected']['expected_balance'];
            if ($cmp !== 0) {
                return $cmp;
            }
            return strcmp($a['employee']->user->name ?? '', $b['employee']->user->name ?? '');
        });

        return [$rows, $currentYear, $nextYear];
    }

    // Export Expected Balance — CSV + XLSX, same rows as the page.
    // One route with ?format=csv|xlsx (default csv); any other format
    // redirects back to the report instead of 500ing.
    public function exportExpectedBalance(Request $request)
    {
        $format = $request->query('format', 'csv');
        if (!in_array($format, ['csv', 'xlsx'], true)) {
            return redirect()->route('reports.expected-balance');
        }

        [$rows, $currentYear, $nextYear] = $this->buildExpectedBalanceRows();
        $headers = $this->expectedExportHeaders($currentYear, $nextYear);
        $flat = $this->expectedExportRows($rows);

        if ($format === 'xlsx') {
            return $this->exportExpectedBalanceXlsx($headers, $flat, $nextYear);
        }

        return response()->stream(function () use ($headers, $flat) {
            $out = fopen('php://output', 'w');
            fputcsv($out, array_map([$this, 'sanitizeCsvCell'], $headers), ',', '"', '');
            foreach ($flat as $row) {
                fputcsv($out, array_map([$this, 'sanitizeCsvCell'], array_values($row)), ',', '"', '');
            }
            fclose($out);
        }, 200, $this->csvResponseHeaders("expected-balance-{$nextYear}.csv"));
    }

    /**
     * The single column list shared by the CSV and XLSX exports, so
     * both files always have identical columns in identical order.
     */
    protected function expectedExportHeaders(int $currentYear, int $nextYear): array
    {
        return [
            'Employee',
            'Department',
            'Status',
            'Joining Date',
            'Visible Balance',
            "{$currentYear} Entitlement (Full)",
            "{$currentYear} Used (Approved)",
            "Pending {$currentYear}",
            "Expected 1 Jan {$nextYear}",
            "Approved Booked for {$nextYear}",
            'Expected if Pending Approved',
            'Year',
            'Year Entitled',
            'Year Used',
            'Year Pending',
            'Year Remaining',
            'Year Expected Remaining',
        ];
    }

    /**
     * Flatten the page rows: one row per employee per year-detail row
     * (including the booked-only next-year row when present), repeating
     * the employee summary fields on each row. Employees with no
     * detail rows (e.g. joining date in a future year) get one row
     * with empty year columns so they are not silently dropped.
     */
    protected function expectedExportRows(array $rows): array
    {
        $flat = [];
        foreach ($rows as $row) {
            $emp = $row['employee'];
            $expected = $row['expected'];
            $summary = [
                $emp->user->name ?? 'N/A',
                $emp->department->name ?? 'N/A',
                $emp->employment_status ?? '',
                $emp->joining_date ? $emp->joining_date->format('Y-m-d') : '',
                (float) $expected['visible_balance'],
                (float) $expected['current_year_entitled_full'],
                (float) $expected['current_year_used'],
                (float) $expected['pending_current_year'],
                (float) $expected['expected_balance'],
                (float) $expected['approved_next_year_used'],
                (float) $expected['expected_if_pending_approved'],
            ];
            $details = $row['detail_rows'] ?? [];
            if ($details === []) {
                $flat[] = array_merge($summary, ['', null, null, null, null, null]);
                continue;
            }
            foreach ($details as $d) {
                $flat[] = array_merge($summary, [
                    $d['year_label'],
                    $d['entitled'],
                    (float) $d['used'],
                    (float) $d['pending'],
                    (float) $d['remaining'],
                    $d['expected_remaining'],
                ]);
            }
        }
        return $flat;
    }

    /**
     * Real .xlsx via PhpSpreadsheet (declared dependency, ^5.9).
     * Same pattern as EmployeeController::exportExcel(): array-coordinate
     * getCell()/setCellValueExplicit() (the 2026-10-22 fix), explicit
     * string cells for text (sanitised), numbers written as numbers so
     * negative balances are never quote-prefixed. Header bold, frozen
     * top row, one sheet named "Expected Balance".
     */
    protected function exportExpectedBalanceXlsx(array $headers, array $flat, int $nextYear)
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Expected Balance');

        foreach ($headers as $col => $header) {
            $cell = $sheet->getCell([$col + 1, 1]);
            $cell->setValue($header);
            $cell->getStyle()->getFont()->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFF'));
            $cell->getStyle()->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setRGB('8B251E');
        }

        $rowNum = 2;
        foreach ($flat as $row) {
            foreach (array_values($row) as $col => $value) {
                if (is_int($value) || is_float($value)) {
                    $sheet->setCellValue([$col + 1, $rowNum], $value);
                } else {
                    $sheet->setCellValueExplicit([$col + 1, $rowNum], $this->sanitizeCsvCell((string) ($value ?? '')), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                }
            }
            $rowNum++;
        }

        $sheet->freezePane('A2');
        foreach (range('A', 'Q') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = $this->safeFilename("expected-balance-{$nextYear}.xlsx", 'xlsx');
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    // Leave History Report
    public function leaveHistory(Request $request)
    {
        $employeeId = $request->employee_id;
        $year = $request->year ?? Carbon::now()->year;
        $leaveTypeId = $request->leave_type_id;
        $status = $request->status;

        $employees = Employee::with('user')->where('employment_status', 'active')
            ->when($request->filled('department_id') && is_numeric($request->department_id), function ($q) use ($request) {
                $q->where('department_id', (int) $request->department_id);
            })
            ->get();
        $departments = Department::where('is_active', true)->orderBy('name')->get();

        $leaveRequests = collect([]);
        $selectedEmployee = null;

        if ($employeeId) {
            $selectedEmployee = Employee::with('user')->find($employeeId);

            $query = LeaveRequest::where('employee_id', $employeeId)
                ->whereYear('start_date', $year)
                ->with(['leaveType', 'hrApprover', 'managerApprover', 'creator']);

            if ($leaveTypeId) {
                $query->where('leave_type_id', $leaveTypeId);
            }

            if ($status) {
                $query->where('status', $status);
            }

            if ($request->filled('from_date') && strtotime($request->from_date)) {
                $query->whereDate('start_date', '>=', date('Y-m-d', strtotime($request->from_date)));
            }
            if ($request->filled('to_date') && strtotime($request->to_date)) {
                $query->whereDate('end_date', '<=', date('Y-m-d', strtotime($request->to_date)));
            }

            $leaveRequests = $query->orderBy('start_date', 'desc')->get();
        }

        return view('reports.leave-history', compact('employees', 'departments', 'leaveRequests', 'selectedEmployee', 'year'));
    }

    // Department Report
    public function department(Request $request)
    {
        $departmentId = $request->department_id;
        $startDate = $request->start_date;
        $endDate = $request->end_date;
        $leaveTypeId = $request->leave_type_id;

        // L5/L10: search is a grouped, LIKE-escaped SQL filter (the base
        // version filtered an already-loaded collection in PHP).
        $departments = Department::with(['manager', 'parent'])
            ->withCount('employees')
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $this->escapeLike($request->search);
                $q->where(function ($q2) use ($search) {
                    $q2->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%");
                });
            })
            ->get();

        $leaveTypes = LeaveType::where('is_active', true)->get();

        // WF-16: the base version ran, PER DEPARTMENT, a count + a sum +
        // an upcoming-leaves query + 12 monthly count queries (and lazy-
        // loaded $dept->employees per department). All four are now
        // single grouped queries over all departments, grouped in PHP.
        $allEmployeeIds = Employee::select('id')->pluck('id')->all();

        // Leaves taken / days taken (same date-range + type filters as base).
        $takenRows = LeaveRequest::join('employees', 'leave_requests.employee_id', '=', 'employees.id')
            ->selectRaw('employees.department_id as dept_id, COUNT(*) as cnt, SUM(leave_requests.total_days) as days')
            ->whereIn('leave_requests.employee_id', $allEmployeeIds)
            ->where('leave_requests.status', 'approved')
            ->when($startDate, function ($q) use ($startDate) {
                $q->where('leave_requests.end_date', '>=', $startDate);
            })
            ->when($endDate, function ($q) use ($endDate) {
                $q->where('leave_requests.start_date', '<=', $endDate);
            })
            ->when($leaveTypeId, function ($q) use ($leaveTypeId) {
                $q->where('leave_requests.leave_type_id', $leaveTypeId);
            })
            ->groupBy('employees.department_id')
            ->get()
            ->keyBy('dept_id');

        // Upcoming leaves (approved, starting within the next 30 days).
        $upcomingByDept = LeaveRequest::whereIn('employee_id', $allEmployeeIds)
            ->where('status', 'approved')
            ->where('start_date', '>=', Carbon::now()->format('Y-m-d'))
            ->where('start_date', '<=', Carbon::now()->addDays(30)->format('Y-m-d'))
            ->with(['employee.user', 'leaveType'])
            ->orderBy('start_date')
            ->get()
            ->groupBy(function ($leave) {
                return $leave->employee->department_id ?? null;
            });

        // Monthly breakdown for the current year (absence trend).
        $monthlyRows = LeaveRequest::join('employees', 'leave_requests.employee_id', '=', 'employees.id')
            ->selectRaw('employees.department_id as dept_id, ' . $this->monthExpression('leave_requests.start_date') . ' as m, COUNT(*) as cnt')
            ->whereIn('leave_requests.employee_id', $allEmployeeIds)
            ->whereYear('leave_requests.start_date', Carbon::now()->year)
            ->where('leave_requests.status', 'approved')
            ->groupBy('employees.department_id', DB::raw($this->monthExpression('leave_requests.start_date')))
            ->get();

        $monthlyByDept = [];
        foreach ($monthlyRows as $row) {
            $monthlyByDept[$row->dept_id][(int) $row->m] = (int) $row->cnt;
        }

        $departmentData = [];
        foreach ($departments as $dept) {
            $taken = $takenRows->get($dept->id);
            $monthlyData = [];
            for ($month = 1; $month <= 12; $month++) {
                $monthlyData[$month] = $monthlyByDept[$dept->id][$month] ?? 0;
            }

            $departmentData[$dept->id] = [
                'leaves_taken' => $taken ? (int) $taken->cnt : 0,
                'days_taken' => $taken ? (float) $taken->days : 0,
                'upcoming_leaves' => $upcomingByDept->get($dept->id, collect()),
                'monthly_trend' => $monthlyData,
            ];
        }

        return view('reports.department', compact(
            'departments',
            'leaveTypes',
            'departmentId',
            'startDate',
            'endDate',
            'leaveTypeId',
            'departmentData'
        ));
    }

    // Employee Report
    public function employee(Request $request)
    {
        $employeeId = $request->employee_id;
        $departmentId = $request->department_id;
        $status = $request->status;

        $query = Employee::with(['user', 'department'])
            ->join('users', 'employees.user_id', '=', 'users.id');

        if ($request->filled('search')) {
            // L5: already grouped in the base version; L10 escaping added.
            $search = $this->escapeLike($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('users.name', 'like', "%{$search}%")
                    ->orWhere('employees.employee_number', 'like', "%{$search}%")
                    ->orWhere('users.email', 'like', "%{$search}%");
            });
        }

        if ($departmentId) {
            $query->where('employees.department_id', $departmentId);
        }

        if ($status) {
            $query->where('employees.employment_status', $status);
        }

        $employees = $query->orderBy('users.name')->get(['employees.*']);
        $departments = Department::where('is_active', true)->get();

        // If specific employee selected, show detailed report
        $selectedEmployee = null;
        $yearByYearBalances = [];
        $leaveHistory = [];
        $attachments = [];

        if ($employeeId) {
            $selectedEmployee = Employee::with(['user', 'department', 'user.role', 'fieldValues.field'])
                ->find($employeeId);

            if ($selectedEmployee) {
                // WF-10: balances from visibleBalanceData(); entitled is
                // null for the current year (hidden until 1 January).
                foreach ($this->singleEmployeeVisibleRows($selectedEmployee) as $y => $data) {
                    $yearByYearBalances[] = [
                        'year' => $y,
                        'entitled' => $data['days_entitled'],
                        'used' => $data['days_used'] ?? 0,
                        'balance' => $data['balance'] ?? 0,
                    ];
                }

                // Leave history (last 3 years)
                $leaveHistory = LeaveRequest::where('employee_id', $employeeId)
                    ->whereYear('start_date', '>=', Carbon::now()->subYears(3)->year)
                    ->with('leaveType')
                    ->orderBy('start_date', 'desc')
                    ->get();

                // Attachments from dynamic fields
                $attachments = $selectedEmployee->fieldValues()
                    ->whereHas('field', function ($q) {
                        $q->where('field_type', 'attachment');
                    })
                    ->with('field')
                    ->get();
            }
        }

        return view('reports.employee', compact(
            'employees',
            'departments',
            'employeeId',
            'departmentId',
            'status',
            'selectedEmployee',
            'yearByYearBalances',
            'leaveHistory',
            'attachments'
        ));
    }

    // Vacation History Report
    public function vacationHistory(Request $request)
    {
        $employeeId = $request->employee_id;
        $departmentId = $request->department_id;
        $year = $request->year;

        $query = Employee::with(['user', 'department'])
            ->join('users', 'users.id', '=', 'employees.user_id')
            ->select('employees.*')
            ->where('employment_status', 'active');

        if ($request->filled('search')) {
            // L5: already grouped in the base version; L10 escaping added.
            $search = $this->escapeLike($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('users.name', 'like', "%{$search}%")
                    ->orWhere('employees.employee_number', 'like', "%{$search}%");
            });
        }

        if ($departmentId) {
            $query->where('department_id', $departmentId);
        }

        $employees = $query->orderBy('users.name')->get();
        $departments = Department::where('is_active', true)->get();

        $reportData = collect();
        $selectedEmployee = null;

        if ($employeeId) {
            $selectedEmployee = Employee::with('user', 'department')->find($employeeId);

            if ($selectedEmployee) {
                // WF-10: visible-policy rows. carry_forward for a year is
                // the visible running balance at the end of the previous
                // year (0 for the joining year); the current year's
                // entitled_days is null (hidden until 1 January). Negative
                // carry-forward is shown as-is, never hidden.
                $cumulativeBalance = 0;
                foreach ($this->singleEmployeeVisibleRows($selectedEmployee) as $y => $data) {
                    if ($year && (int) $year < $y) {
                        break;
                    }
                    $reportData->push([
                        'year' => $y,
                        'entitled_days' => $data['days_entitled'],
                        'used_days' => $data['days_used'] ?? 0,
                        'carry_forward' => $cumulativeBalance,
                        'balance' => $data['balance'] ?? 0,
                    ]);
                    $cumulativeBalance = $data['balance'] ?? 0;
                }
            }
        }

        return view('reports.vacation-history', compact(
            'employees',
            'departments',
            'employeeId',
            'departmentId',
            'year',
            'selectedEmployee',
            'reportData'
        ));
    }

    // Export Vacation History to Excel
    public function exportVacationHistory(Request $request)
    {
        $employeeId = $request->employee_id;
        $year = $request->year;

        $employee = Employee::with('user', 'department')->find($employeeId);

        if (!$employee) {
            return back()->with('error', 'Employee not found.');
        }

        // WF-10: visible-policy rows (current-year entitled = null).
        $rows = [];
        $cumulativeBalance = 0;
        foreach ($this->singleEmployeeVisibleRows($employee) as $y => $data) {
            if ($year && (int) Carbon::parse($year)->year < $y) {
                break;
            }
            $rows[] = [
                $y,
                $data['days_entitled'],
                $data['days_used'] ?? 0,
                $cumulativeBalance,
                $data['balance'] ?? 0,
            ];
            $cumulativeBalance = $data['balance'] ?? 0;
        }

        $headers = ['Year', 'Entitled Days', 'Used Days', 'Carry Forward', 'Balance'];

        // M4: filename is sanitised + quoted inside exportToExcel().
        $currentYear = $year ? Carbon::parse($year)->year : Carbon::now()->year;
        return $this->exportToExcel($headers, $rows, "vacation_history_{$employee->user->name}_{$currentYear}.csv");
    }

    // Leave Statistics
    public function leaveStatistics(Request $request)
    {
        $year = $request->year ?? Carbon::now()->year;
        $departmentId = $request->department_id;
        $departments = Department::where('is_active', true)->orderBy('name')->get();
        // Optional department scope applied to every stats query below.
        $deptScope = function ($q) use ($departmentId) {
            if ($departmentId && is_numeric($departmentId)) {
                $q->whereHas('employee', function ($e) use ($departmentId) {
                    $e->where('department_id', (int) $departmentId);
                });
            }
        };

        // WF-16: the base version ran 36 monthly queries (12 months x 3
        // statuses) + 2 queries per leave type + 2 service-count queries.
        // Monthly statistics: ONE grouped query by month + status.
        $monthlyRows = LeaveRequest::selectRaw($this->monthExpression('start_date') . ' as m, status, COUNT(*) as cnt')
            ->whereYear('start_date', $year)
            ->whereIn('status', ['approved', 'rejected', 'pending'])
            ->when($departmentId, $deptScope)
            ->groupBy(DB::raw($this->monthExpression('start_date')), 'status')
            ->get();

        $monthlyStats = [];
        for ($month = 1; $month <= 12; $month++) {
            $monthlyStats[$month] = ['approved' => 0, 'rejected' => 0, 'pending' => 0, 'total' => 0];
        }
        foreach ($monthlyRows as $row) {
            $m = (int) $row->m;
            if (isset($monthlyStats[$m][$row->status])) {
                $monthlyStats[$m][$row->status] = (int) $row->cnt;
                $monthlyStats[$m]['total'] += (int) $row->cnt;
            }
        }

        // Leave type breakdown: ONE grouped query (count + days) instead
        // of two queries per type.
        $typeRows = LeaveRequest::selectRaw('leave_type_id, COUNT(*) as cnt, SUM(total_days) as days')
            ->whereYear('start_date', $year)
            ->where('status', 'approved')
            ->when($departmentId, $deptScope)
            ->groupBy('leave_type_id')
            ->get()
            ->keyBy('leave_type_id');

        $leaveTypeStats = LeaveType::where('is_active', true)->get()->map(function ($type) use ($typeRows) {
            $row = $typeRows->get($type->id);
            return [
                'name' => $type->name,
                'count' => $row ? (int) $row->cnt : 0,
                'days' => $row ? (float) $row->days : 0,
            ];
        });

        // Exit reentry and ticket stats: ONE query with conditional sums.
        $serviceRow = LeaveRequest::selectRaw(
                'SUM(CASE WHEN exit_reentry_required THEN 1 ELSE 0 END) as exits, '
                . 'SUM(CASE WHEN flight_ticket_required THEN 1 ELSE 0 END) as tickets'
            )
            ->whereYear('start_date', $year)
            ->where('status', 'approved')
            ->when($departmentId, $deptScope)
            ->first();

        $exitReentryCount = $serviceRow ? (int) $serviceRow->exits : 0;
        $ticketCount = $serviceRow ? (int) $serviceRow->tickets : 0;

        return view('reports.leave-statistics', compact('year', 'monthlyStats', 'leaveTypeStats', 'exitReentryCount', 'ticketCount', 'departments', 'departmentId'));
    }

    // Export Leave Balance (WF-10 visible balances, WF-16 chunked stream)
    public function exportLeaveBalance(Request $request)
    {
        $year = $request->year ?? Carbon::now()->year;

        // M3/M4/WF-16: streamed CSV — employees are read in 500-row
        // chunks (never a full-table ->get()), each chunk gets ONE grouped
        // stats query, and rows are written with fputcsv() as they are
        // produced, so memory stays bounded on large rosters (QA R-10).
        return response()->stream(function () use ($year) {
            $out = fopen('php://output', 'w');
            fputcsv($out, array_map([$this, 'sanitizeCsvCell'], ['Employee', 'Department', 'Entitled', 'Used', 'Balance']), ',', '"', '');

            Employee::with(['user', 'department'])
                ->where('employment_status', 'active')
                ->chunkById(500, function ($chunk) use ($out, $year) {
                    $groupedStats = $this->groupedAnnualStats($chunk->pluck('id')->all());
                    foreach ($chunk as $emp) {
                        $rows = $this->visibleYearRows($emp, $groupedStats[$emp->id] ?? []);
                        $yearData = $rows[$year] ?? null;
                        fputcsv($out, array_map([$this, 'sanitizeCsvCell'], [
                            $emp->user->name ?? 'N/A',
                            $emp->department->name ?? 'N/A',
                            // Current-year entitled is null (hidden) -> empty cell, never 0.
                            $yearData && array_key_exists('days_entitled', $yearData) ? $yearData['days_entitled'] : 0,
                            $yearData['days_used'] ?? 0,
                            $yearData['balance'] ?? 0,
                        ]), ',', '"', '');
                    }
                });

            fclose($out);
        }, 200, $this->csvResponseHeaders("leave_balance_{$year}.csv"));
    }

    // Export Leave History to CSV
    public function exportLeaveHistory(Request $request)
    {
        $employeeId = $request->employee_id;
        $year = $request->year ?? Carbon::now()->year;

        $employee = Employee::with('user', 'department')->find($employeeId);

        if (!$employee) {
            return back()->with('error', 'Employee not found.');
        }

        $leaveRequests = LeaveRequest::where('employee_id', $employeeId)
            ->whereYear('start_date', $year)
            ->with(['leaveType', 'hrApprover', 'managerApprover', 'creator'])
            ->orderBy('start_date', 'desc')
            ->get();

        // Export as CSV
        $headers = ['ID', 'Leave Type', 'Start Date', 'End Date', 'Working Days', 'Calendar Days', 'Status', 'HR Approver', 'Manager Approver', 'Created By', 'Created At'];
        $rows = [];

        foreach ($leaveRequests as $leave) {
            $rows[] = [
                $leave->id,
                $leave->leaveType->name ?? 'N/A',
                $leave->start_date ? $leave->start_date->format('Y-m-d') : '',
                $leave->end_date ? $leave->end_date->format('Y-m-d') : '',
                $leave->total_days,
                $leave->calendar_days,
                $leave->status,
                $leave->hrApprover->name ?? '-',
                $leave->managerApprover->name ?? '-',
                $leave->creator->name ?? 'Self',
                $leave->created_at ? $leave->created_at->format('Y-m-d H:i') : '',
            ];
        }

        return $this->exportToExcel($headers, $rows, "leave_history_{$employee->user->name}_{$year}.csv");
    }

    // Export Year-by-Year Report
    public function exportYearByYear(Request $request)
    {
        $employeeId = $request->employee_id;

        $employee = Employee::with('user', 'department')->find($employeeId);

        if (!$employee) {
            return back()->with('error', 'Employee not found.');
        }

        // WF-10: visible-policy rows (current-year entitled = null).
        $yearByYearData = [];
        foreach ($this->singleEmployeeVisibleRows($employee) as $y => $data) {
            $yearByYearData[] = [
                'year' => $y,
                'months_from_hiring' => $data['months_from_hiring'] ?? 0,
                'entitled_days' => $data['days_entitled'],
                'used_days' => $data['days_used'] ?? 0,
                'exit_reentry_count' => $data['exit_reentry_count'] ?? 0,
                'ticket_count' => $data['ticket_count'] ?? 0,
                'balance' => $data['balance'] ?? 0,
            ];
        }

        // Export as CSV
        $headers = ['Year', 'Months from Hiring', 'Entitled', 'Used', 'Exit/Re-entry', 'Tickets', 'Balance'];
        $rows = [];

        foreach ($yearByYearData as $data) {
            $rows[] = [
                $data['year'],
                $data['months_from_hiring'],
                $data['entitled_days'],
                $data['used_days'],
                $data['exit_reentry_count'],
                $data['ticket_count'],
                $data['balance'],
            ];
        }

        // Fixed: the base version interpolated an undefined $currentYear
        // here (empty filename segment). Use the current year.
        return $this->exportToExcel($headers, $rows, "leave_history_{$employee->user->name}_" . Carbon::now()->year . ".csv");
    }

    /**
     * M3/WF-15 + M4: the ONLY CSV writer in this controller. Fields are
     * written with fputcsv() (RFC 4180: commas/quotes/newlines inside a
     * value are quoted, embedded quotes doubled — the '' escape argument
     * selects doubling on PHP >= 8.1; the app runs PHP 8.2) and every
     * cell passes through sanitizeCsvCell() first, so a leading
     * =, +, -, @, tab or CR can never become a live spreadsheet formula.
     * The filename is sanitised and quoted (see safeFilename()).
     */
    private function exportToExcel($headers, $rows, $filename)
    {
        $stream = fopen('php://temp', 'r+');
        fputcsv($stream, array_map([$this, 'sanitizeCsvCell'], $headers), ',', '"', '');
        foreach ($rows as $row) {
            fputcsv($stream, array_map([$this, 'sanitizeCsvCell'], array_values((array) $row)), ',', '"', '');
        }
        rewind($stream);
        $csv = stream_get_contents($stream);
        fclose($stream);

        return response($csv, 200, $this->csvResponseHeaders($filename));
    }
}
