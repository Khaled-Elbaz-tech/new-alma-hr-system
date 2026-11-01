@extends('layouts.app')

@section('title', 'Expected Balance Report')
@section('page-title', 'Expected Balance Report — 1 January ' . $nextYear)

@section('content')
<div class="card mb-4">
    <div class="card-body">
        <p class="mb-0">
            Expected balance on <strong>1 January {{ $nextYear }}</strong>: the visible balance today plus the {{ $currentYear }} annual entitlement counted in full to 31 December.
            This report is rolling — in {{ $currentYear }} it shows 1 January {{ $nextYear }}; in {{ $nextYear }} the same page will show 1 January {{ $nextYear + 1 }} with no code change.
            The current year's approved leave is already deducted in the visible balance, so it is not deducted again here.
            The summary table below is sorted by expected balance (lowest first), then by employee name. Underneath it, each employee has a year-by-year breakdown in the same order.
        </p>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-calendar-plus"></i> Expected Balances — 1 January {{ $nextYear }}</h5>
        <div class="d-flex gap-2">
            <a href="{{ route('reports.export.expected-balance', ['format' => 'csv']) }}" class="btn btn-primary btn-sm">
                <i class="fas fa-file-csv"></i> Download CSV
            </a>
            <a href="{{ route('reports.export.expected-balance', ['format' => 'xlsx']) }}" class="btn btn-success btn-sm">
                <i class="fas fa-file-excel"></i> Download Excel
            </a>
        </div>
    </div>
    <div class="card-body">
        @if(isset($rows) && count($rows) > 0)
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Employee</th>
                            <th>Department</th>
                            <th>Status</th>
                            <th>Joining Date</th>
                            <th>Visible Balance</th>
                            <th>{{ $currentYear }} Entitlement (Full)</th>
                            <th>{{ $currentYear }} Used (Approved)</th>
                            <th>Pending {{ $currentYear }}</th>
                            <th>Expected 1 Jan {{ $nextYear }}</th>
                            <th>Approved Booked for {{ $nextYear }}</th>
                            <th>Expected if Pending Approved</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rows as $row)
                        <tr>
                            <td>
                                <a href="{{ route('employees.show', $row['employee']) }}">
                                    {{ $row['employee']->user->name ?? 'N/A' }}
                                </a>
                                <br><small class="text-muted">{{ $row['employee']->employee_number }}</small>
                            </td>
                            <td>{{ $row['employee']->department->name ?? 'N/A' }}</td>
                            <td>{{ $row['employee']->employment_status ?? '' }}</td>
                            <td>{{ $row['employee']->joining_date ? $row['employee']->joining_date->format('Y-m-d') : '' }}</td>
                            <td>{{ number_format($row['expected']['visible_balance'], 2) }}</td>
                            <td>{{ number_format($row['expected']['current_year_entitled_full'], 2) }}</td>
                            <td>{{ number_format($row['expected']['current_year_used'], 2) }}</td>
                            <td>{{ number_format($row['expected']['pending_current_year'], 2) }}</td>
                            <td>
                                <strong class="{{ $row['expected']['expected_balance'] < 0 ? 'text-danger' : 'text-success' }}">
                                    {{ number_format($row['expected']['expected_balance'], 2) }}
                                </strong>
                            </td>
                            <td>{{ number_format($row['expected']['approved_next_year_used'], 2) }}</td>
                            <td>{{ number_format($row['expected']['expected_if_pending_approved'], 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="alert alert-info mb-0">
                <i class="fas fa-info-circle"></i>
                No employees found.
            </div>
        @endif
    </div>
</div>

<h5 class="mb-3"><i class="fas fa-history"></i> Year-by-Year Breakdown</h5>
<p class="text-muted">Years run from the joining year through {{ $currentYear }}, oldest first. <strong>Remaining</strong> is the running visible balance (closed-year entitlements only, minus approved annual leave used in all years up to and including that year). The current-year row also shows <strong>Expected Remaining</strong>: the same running figure plus the current year's full-year entitlement, which opens on 1 January {{ $nextYear }}. The two figures are different numbers and are never conflated.</p>

@if(isset($rows) && count($rows) > 0)
    @foreach($rows as $row)
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">
                <a href="{{ route('employees.show', $row['employee']) }}">{{ $row['employee']->user->name ?? 'N/A' }}</a>
                <small class="text-muted">({{ $row['employee']->employee_number }}) — {{ $row['employee']->department->name ?? 'N/A' }} — joined {{ $row['employee']->joining_date ? $row['employee']->joining_date->format('Y-m-d') : '' }}</small>
            </h5>
        </div>
        <div class="card-body">
            <p class="text-muted small mb-3">
                Visible today: <strong>{{ number_format($row['expected']['visible_balance'], 2) }} days</strong>
                &middot; {{ $currentYear }} entitlement (full): <strong>{{ number_format($row['expected']['current_year_entitled_full'], 2) }} days</strong>
                &middot; Expected 1 Jan {{ $nextYear }}: <strong class="{{ $row['expected']['expected_balance'] < 0 ? 'text-danger' : 'text-success' }}">{{ number_format($row['expected']['expected_balance'], 2) }} days</strong>
                &middot; Pending {{ $currentYear }}: {{ number_format($row['expected']['pending_current_year'], 2) }} days
                &middot; Approved booked for {{ $nextYear }}: {{ number_format($row['expected']['approved_next_year_used'], 2) }} days
            </p>
            @if(!empty($row['detail_rows']))
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Year</th>
                                <th>Entitled</th>
                                <th>Used (Approved Annual)</th>
                                <th>Pending</th>
                                <th>Remaining</th>
                                <th>Expected Remaining</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($row['detail_rows'] as $detail)
                            <tr @if($detail['kind'] === 'current') class="table-light" @endif>
                                <td><strong>{{ $detail['year_label'] }}</strong></td>
                                <td>
                                    {{ $detail['entitled'] === null ? '—' : number_format($detail['entitled'], 2) }}
                                    @if(!empty($detail['entitled_note']))
                                        <br><small class="text-muted">{{ $detail['entitled_note'] }}</small>
                                    @endif
                                </td>
                                <td>{{ number_format($detail['used'], 2) }}</td>
                                <td>{{ number_format($detail['pending'], 2) }}</td>
                                <td class="{{ $detail['remaining'] < 0 ? 'text-danger' : 'text-success' }}">
                                    <strong>{{ number_format($detail['remaining'], 2) }}</strong>
                                    @if(!empty($detail['remaining_note']))
                                        <br><small class="text-muted">{{ $detail['remaining_note'] }}</small>
                                    @endif
                                </td>
                                <td>
                                    @if($detail['expected_remaining'] === null)
                                        —
                                    @else
                                        <strong class="{{ $detail['expected_remaining'] < 0 ? 'text-danger' : 'text-success' }}">
                                            {{ number_format($detail['expected_remaining'], 2) }}
                                        </strong>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <p class="text-muted small mb-0">
                    <i class="fas fa-info-circle"></i>
                    Remaining is the running visible balance. On the current-year row, Expected Remaining adds the full-year entitlement that opens on 1 January {{ $nextYear }}. A leave spanning December–January counts in the year it starts. Annual leave only.
                </p>
            @else
                <p class="text-muted mb-0">No leave years yet (joining date is after the current year).</p>
            @endif
        </div>
    </div>
    @endforeach
@endif

<div class="card mt-4">
    <div class="card-header bg-light">
        <h6 class="mb-0"><i class="fas fa-info-circle"></i> How the Expected Balance Is Calculated</h6>
    </div>
    <div class="card-body">
        <ul class="mb-0">
            <li><strong>Expected 1 Jan {{ $nextYear }}</strong> = visible balance today + {{ $currentYear }} entitlement in full (1.75 days per month worked to 31 December, capped at the employee's annual entitlement).</li>
            <li><strong>Year-by-year detail:</strong> closed years show Entitled as counted then and Remaining as the running visible balance (entitlements of closed years only, minus approved annual used up to and including that year). The current-year row shows Entitled in full with "opens 1 January {{ $nextYear }}", Remaining as the visible balance today, and Expected Remaining as the expected balance — two distinct figures, shown side by side.</li>
            <li><strong>Booked-only next-year row</strong> appears only when the employee has approved annual leave starting in {{ $nextYear }}: Entitled is "—", Used is the approved {{ $nextYear }} days, and Remaining is the expected balance minus those booked days.</li>
            <li><strong>Annual leave only.</strong> Other leave types never change these numbers. Used days are attributed by the year a leave starts, so a leave spanning December–January counts in the year it starts.</li>
            <li><strong>Pending</strong> means awaiting approval: status <code>pending</code>, <code>hr_approved</code> or <code>manager_approved</code>. Pending days are info only — the headline expected figure is approved-only. <strong>Expected if Pending Approved</strong> = expected balance minus pending {{ $currentYear }} days.</li>
            <li><strong>Approved Booked for {{ $nextYear }}</strong> is already-approved annual leave starting in {{ $nextYear }}. It is not deducted from the opening expected balance in the summary table; the booked-only detail row shows the balance after it.</li>
            <li>A negative balance is shown as-is and is never floored at zero. All employees except soft-deleted ones are listed, in the same scope as the Employees list.</li>
        </ul>
    </div>
</div>
@endsection
