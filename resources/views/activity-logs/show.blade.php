@extends('layouts.app')

@section('title', 'Activity Log Details')
@section('page-title', 'Activity Log Details')

@section('content')
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-history"></i> Log #{{ $activityLog->id }}</h5>
        <span class="text-muted"><i class="fas fa-lock"></i> Read-only Audit Log</span>
    </div>
    <div class="card-body">
        <table class="table">
            <tbody>
                <tr>
                    <th style="width: 180px;">Date/Time</th>
                    <td>
                        <small>{{ $activityLog->created_at->format('d M Y') }}</small><br>
                        <span class="text-muted">{{ $activityLog->created_at->format('H:i:s') }}</span>
                    </td>
                </tr>
                <tr>
                    <th>User</th>
                    <td>
                        @if($activityLog->user)
                            <strong>{{ $activityLog->user->name }}</strong>
                            <br>
                            <small class="text-muted">{{ $activityLog->user->email }}</small>
                        @else
                            <span class="text-danger">System</span>
                        @endif
                    </td>
                </tr>
                <tr>
                    <th>Action</th>
                    <td>
                        @php
                            $badgeColor = 'secondary';
                            if(str_contains($activityLog->action, 'login') || str_contains($activityLog->action, 'logout')) $badgeColor = 'info';
                            elseif(str_contains($activityLog->action, 'create')) $badgeColor = 'success';
                            elseif(str_contains($activityLog->action, 'update') || str_contains($activityLog->action, 'approve')) $badgeColor = 'primary';
                            elseif(str_contains($activityLog->action, 'delete') || str_contains($activityLog->action, 'reject')) $badgeColor = 'danger';
                            elseif(str_contains($activityLog->action, 'activate')) $badgeColor = 'warning';
                        @endphp
                        <span class="badge bg-{{ $badgeColor }}">{{ $activityLog->action }}</span>
                    </td>
                </tr>
                <tr>
                    <th>Model</th>
                    <td>
                        @if($activityLog->model_type)
                            <span class="badge bg-light text-dark">{{ class_basename($activityLog->model_type) }}</span>
                            @if($activityLog->model_id)
                                <small class="text-muted">#{{ $activityLog->model_id }}</small>
                            @endif
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                </tr>
                <tr>
                    <th>IP Address</th>
                    <td><small class="text-muted">{{ $activityLog->ip_address ?? 'N/A' }}</small></td>
                </tr>
                <tr>
                    <th>User Agent</th>
                    <td><small class="text-muted">{{ $activityLog->user_agent ?? 'N/A' }}</small></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

@if($activityLog->old_values)
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">Old Values</h5>
    </div>
    <div class="card-body">
        <pre class="bg-light p-2">{{ json_encode($activityLog->old_values, JSON_PRETTY_PRINT) }}</pre>
    </div>
</div>
@endif

@if($activityLog->new_values)
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">New Values</h5>
    </div>
    <div class="card-body">
        <pre class="bg-light p-2">{{ json_encode($activityLog->new_values, JSON_PRETTY_PRINT) }}</pre>
    </div>
</div>
@endif

@if(!$activityLog->old_values && !$activityLog->new_values)
<div class="alert alert-info">
    <i class="fas fa-info-circle"></i> No old or new values were recorded for this log entry.
</div>
@endif

<div class="d-flex gap-2">
    <a href="{{ route('activity-logs.index') }}" class="btn btn-secondary">Back to Activity Logs</a>
    @if($activityLog->user)
        <a href="{{ route('activity-logs.user', $activityLog->user->id) }}" class="btn btn-outline-secondary">View This User's Activity</a>
    @endif
</div>
@endsection
