@extends('layouts.app')

@section('title', 'Edit Leave Request')
@section('page-title', 'Edit Leave Request')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-pencil-alt"></i> Edit Leave Request #{{ $leave->id }}</h5>
            </div>
            <div class="card-body">
                <div class="alert alert-light border mb-4">
                    <p class="mb-1"><strong>Employee:</strong> {{ $leave->employee->user->name }} ({{ $leave->employee->employee_number }})</p>
                    <p class="mb-1"><strong>Department:</strong> {{ $leave->employee->department->name ?? 'N/A' }}</p>
                    <p class="mb-1"><strong>Status:</strong> {{ ucfirst($leave->status) }} <span class="text-muted">(status cannot be changed here — use the approve / reject flow)</span></p>
                    <p class="mb-0"><strong>Currently stored:</strong> {{ $breakdown['calendar_days'] }} calendar days &minus; {{ $breakdown['weekend_days'] }} weekend days ({{ implode(', ', $breakdown['weekend_day_names']) }}) &minus; {{ $breakdown['holiday_days'] }} holiday days = <strong>{{ $breakdown['working_days'] }} working days</strong> (stored total: {{ $leave->total_days }} days)</p>
                    @if(count($breakdown['holidays']) > 0)
                    <p class="mb-0 small text-muted">Holidays excluded: @foreach($breakdown['holidays'] as $h){{ $h['date'] }} ({{ $h['name'] }})@if(!$loop->last), @endif @endforeach</p>
                    @endif
                </div>

                <form method="POST" action="{{ route('leaves.update', $leave) }}" id="editLeaveForm">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label class="form-label">Leave Type <span class="text-danger">*</span></label>
                        <select name="leave_type_id" class="form-select" required id="leaveTypeSelect">
                            @foreach($leaveTypes as $type)
                                <option value="{{ $type->id }}" {{ (int) old('leave_type_id', $leave->leave_type_id) === (int) $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Start Date <span class="text-danger">*</span></label>
                            <input type="date" name="start_date" class="form-control" required id="startDate" value="{{ old('start_date', $leave->start_date ? $leave->start_date->format('Y-m-d') : '') }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">End Date <span class="text-danger">*</span></label>
                            <input type="date" name="end_date" class="form-control" required id="endDate" value="{{ old('end_date', $leave->end_date ? $leave->end_date->format('Y-m-d') : '') }}">
                        </div>
                    </div>

                    <div class="alert alert-info" id="calculationResult" style="display: none;">
                        <h6><i class="fas fa-calculator"></i> New calculation (live)</h6>
                        <p class="mb-1" id="breakdownLine"></p>
                        <p class="mb-0"><strong>Holiday names:</strong> <span id="holidayNames">&mdash;</span></p>
                        <p class="mb-0 small text-muted mt-2">Saving recomputes the working days from the current weekends and holidays. Balances are calculated dynamically, so editing an approved request moves that employee's balance automatically.</p>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Reason <span class="text-danger">*</span></label>
                        <textarea name="reason" class="form-control" rows="3" required>{{ old('reason', $leave->reason) }}</textarea>
                    </div>

                    <p class="text-muted small">The employee and the status are not editable here. To change who the leave belongs to, delete it and create a new one; to change its status, use approve / reject.</p>

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                    <a href="{{ route('leaves.index') }}" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
@php
    $dayMap = ['sunday'=>0,'monday'=>1,'tuesday'=>2,'wednesday'=>3,'thursday'=>4,'friday'=>5,'saturday'=>6];
    $weekendNums = array_values(array_filter(array_map(fn($d) => $dayMap[strtolower($d)] ?? null, $weekendDays ?? [])));
    if (empty($weekendNums)) { $weekendNums = [5, 6]; }
@endphp
<script>
document.addEventListener('DOMContentLoaded', function() {
    const startDate = document.getElementById('startDate');
    const endDate = document.getElementById('endDate');
    const calculationResult = document.getElementById('calculationResult');
    const holidays = @json($holidays->map(fn($h) => ['name' => $h->name, 'start_date' => $h->start_date ? $h->start_date->format('Y-m-d') : null, 'end_date' => $h->end_date ? $h->end_date->format('Y-m-d') : null])->values());
    const weekendDays = @json($weekendNums);

    function calculateDays() {
        if (!startDate.value || !endDate.value) {
            calculationResult.style.display = 'none';
            return;
        }
        const start = new Date(startDate.value);
        const end = new Date(endDate.value);
        if (end < start) {
            calculationResult.style.display = 'none';
            return;
        }
        const calendarDays = Math.floor((end - start) / (1000 * 60 * 60 * 24)) + 1;
        let weekendsExcluded = 0;
        let current = new Date(start);
        while (current <= end) {
            if (weekendDays.includes(current.getDay())) { weekendsExcluded++; }
            current.setDate(current.getDate() + 1);
        }
        let holidaysExcluded = 0;
        const holidayNames = new Set();
        holidays.forEach(holiday => {
            const holidayStart = new Date(holiday.start_date);
            const holidayEnd = holiday.end_date ? new Date(holiday.end_date) : holidayStart;
            current = new Date(start);
            while (current <= end) {
                if (current >= holidayStart && current <= holidayEnd && !weekendDays.includes(current.getDay())) {
                    holidaysExcluded++;
                    holidayNames.add(holiday.name);
                }
                current.setDate(current.getDate() + 1);
            }
        });
        const workingDays = calendarDays - weekendsExcluded - holidaysExcluded;
        document.getElementById('breakdownLine').textContent = calendarDays + ' calendar days \u2212 ' + weekendsExcluded + ' weekend days \u2212 ' + holidaysExcluded + ' holiday days = ' + workingDays + ' working days';
        document.getElementById('holidayNames').textContent = holidayNames.size > 0 ? Array.from(holidayNames).join(', ') : '—';
        calculationResult.style.display = 'block';
    }

    startDate.addEventListener('change', calculateDays);
    endDate.addEventListener('change', calculateDays);
    calculateDays();
});
</script>
@endsection
