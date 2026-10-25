@extends('layouts.app')

@section('title', 'Holidays')
@section('page-title', 'Holiday Calendar')

@section('content')
<div class="row mb-3">
    <div class="col-md-6">
        <form method="GET" class="d-flex gap-2 align-items-center">
            <label class="mb-0">Year:</label>
            <select name="year" class="form-select" style="width: auto;">
                @for($y = date('Y') - 2; $y <= date('Y') + 2; $y++)
                    <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endfor
            </select>
            <input type="text" name="search" class="form-control" placeholder="Search holiday name..." value="{{ request('search') }}" style="width: auto;">
            <select name="is_recurring" class="form-select" style="width: auto;">
                <option value="">All</option>
                <option value="1" {{ request('is_recurring') === '1' ? 'selected' : '' }}>Recurring</option>
                <option value="0" {{ request('is_recurring') === '0' ? 'selected' : '' }}>One-time</option>
            </select>
            <button type="submit" class="btn btn-secondary">Filter</button>
            <a href="{{ route('holidays.index') }}" class="btn btn-outline-secondary">Reset</a>
        </form>
    </div>
    <div class="col-md-6 text-end">
        <a href="{{ route('holidays.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add Holiday
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-calendar-day"></i> Holidays for {{ $year }}</h5>
            </div>
            <div class="card-body">
                @if($holidays->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Holiday</th>
                                    <th>Dates</th>
                                    <th>Days</th>
                                    <th>Year</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($holidays as $holiday)
                                    <tr>
                                        <td>
                                            <strong>{{ $holiday->name }}</strong>
                                            @if($holiday->is_recurring)
                                                <br><span class="badge bg-info">Recurring</span>
                                            @endif
                                        </td>
                                        <td>
                                            {{ $holiday->start_date->format('d M') }} - {{ $holiday->end_date->format('d M') }}
                                        </td>
                                        <td>
                                            <span class="badge bg-primary">{{ $holiday->start_date->diffInDays($holiday->end_date) + 1 }}</span>
                                        </td>
                                        <td>{{ $holiday->year }}</td>
                                        <td>
                                            <a href="{{ route('holidays.edit', $holiday) }}" class="btn btn-sm btn-warning">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            @if(!auth()->user()->isEmployee())
                                            <form action="{{ route('holidays.destroy', $holiday) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this holiday?')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> No holidays found for {{ $year }}.
                    </div>
                @endif
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-cog"></i> Weekend Settings</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('weekend-settings.update') }}">
                    @csrf
                    <p class="text-muted small">Select days that are considered weekends (excluded from working days):</p>
                    <div class="mb-3">
                        @foreach(['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'] as $day)
                            <div class="form-check">
                                <input type="checkbox" name="weekends[]" value="{{ $day }}" 
                                    class="form-check-input" id="day{{ $day }}"
                                    {{ $weekendSettings->where('day', $day)->first()?->is_weekend ? 'checked' : '' }}>
                                <label class="form-check-label" for="day{{ $day }}">{{ ucfirst($day) }}</label>
                            </div>
                        @endforeach
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Weekend
                    </button>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-info-circle"></i> Holiday Rules</h5>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item px-0">
                        <i class="fas fa-check text-success"></i> Holidays are excluded from leave deduction
                    </li>
                    <li class="list-group-item px-0">
                        <i class="fas fa-check text-success"></i> Recurring holidays repeat yearly
                    </li>
                    <li class="list-group-item px-0">
                        <i class="fas fa-exclamation-triangle text-warning"></i> Cannot delete if used in approved leave
                    </li>
                    <li class="list-group-item px-0">
                        <i class="fas fa-user-shield text-info"></i> Only Admin/HR can manage holidays
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection