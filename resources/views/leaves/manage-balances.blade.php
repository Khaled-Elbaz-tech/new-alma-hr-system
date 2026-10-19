@extends('layouts.app')

@section('title', 'Manage Leave Balances')
@section('page-title', 'Manage Leave Balances')

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-balance-scale"></i> All Leave Balances</h5>
    </div>
    <div class="card-body">
        <!-- Filters -->
        <form method="GET" class="mb-3">
            <div class="row">
                <div class="col-md-4">
                    <input type="text" name="employee" class="form-control" placeholder="Search employee..."
                           value="{{ request('employee') }}">
                </div>
                <div class="col-md-3">
                    <select name="year" class="form-select">
                        <option value="">All Years</option>
                        @foreach($years as $y)
                            <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Filter</button>
                    <a href="{{ route('leaves.manage-balances') }}" class="btn btn-outline-secondary">Reset</a>
                </div>
                <div class="col-md-3 text-end">
                    <a href="{{ route('reports.leave-balance') }}" class="btn btn-success"><i class="fas fa-download"></i> Export</a>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Employee</th>
                        <th>Year</th>
                        <th>Months Worked</th>
                        <th>Entitled</th>
                        <th>Used</th>
                        <th>Balance</th>
                        <th>Tickets</th>
                        <th>Exit/Re-entry</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($balances as $balance)
                        <tr>
                            <td>
                                <strong>{{ $balance->employee->user->name }}</strong><br>
                                <small>{{ $balance->employee->employee_id }}</small>
                            </td>
                            <td>{{ $balance->year }}</td>
                            <td>{{ $balance->months_from_hiring }}</td>
                            <td>{{ number_format($balance->entitled_days, 2) }}</td>
                            <td>{{ number_format($balance->used_days, 2) }}</td>
                            <td>
                                <strong class="{{ $balance->balance >= 0 ? 'text-success' : 'text-danger' }}">
                                    {{ number_format($balance->balance, 2) }}
                                </strong>
                            </td>
                            <td>{{ $balance->ticket_count }}</td>
                            <td>{{ $balance->exit_reentry_count }}</td>
                            <td>
                                <a href="{{ route('leaves.edit-balance', $balance) }}" class="btn btn-sm btn-warning">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted">No leave balances found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $balances->links() }}
    </div>
</div>
@endsection
