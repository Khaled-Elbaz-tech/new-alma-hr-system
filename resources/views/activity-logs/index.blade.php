@extends('layouts.app')

@section('title', 'Activity Logs')
@section('page-title', 'System Activity Logs')

@section('content')
<!-- Filters -->
<div class="card mb-4">
    <div class="card-header">
        <form method="GET" class="row g-3">
            <div class="col-md-2">
                <select name="user_id" class="form-select">
                    <option value="">All Users</option>
                    @foreach($users as $u)
                        <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="model_type" class="form-select">
                    <option value="">All Models</option>
                    @foreach($modelTypes as $modelType)
                        <option value="{{ $modelType }}" {{ request('model_type') == $modelType ? 'selected' : '' }}>{{ class_basename($modelType) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="action" class="form-select">
                    <option value="">All Actions</option>
                    @foreach($actions as $action)
                        <option value="{{ $action }}" {{ request('action') == $action ? 'selected' : '' }}>{{ $action }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}" placeholder="Start Date">
            </div>
            <div class="col-md-2">
                <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}" placeholder="End Date">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-secondary">Filter</button>
                <a href="{{ route('activity-logs.index') }}" class="btn btn-outline-secondary">Reset</a>
            </div>
            <div class="col-md-4 text-end">
                <span class="text-muted"><i class="fas fa-lock"></i> Read-only Audit Log</span>
            </div>
        </form>
    </div>
</div>

<!-- Log Categories Info -->
<div class="row mb-3">
    <div class="col-md-12">
        <div class="alert alert-light">
            <h6><i class="fas fa-info-circle"></i> Logged Actions</h6>
            <div class="row">
                <div class="col-md-3">
                    <strong>Authentication:</strong> login, logout
                </div>
                <div class="col-md-3">
                    <strong>CRUD:</strong> create, update, delete, activate, restore
                </div>
                <div class="col-md-3">
                    <strong>Leave:</strong> leave_request_create, leave_approve_hr, leave_approve_manager, leave_reject, leave_cancel
                </div>
                <div class="col-md-3">
                    <strong>System:</strong> update_weekend_settings, create_historical, etc.
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Activity Logs Table -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-history"></i> All Activity Logs</h5>
    </div>
    <div class="card-body">
        @if($logs->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Date/Time</th>
                            <th>User</th>
                            <th>Action</th>
                            <th>Model</th>
                            <th>IP Address</th>
                            <th>Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($logs as $log)
                        <tr>
                            <td>
                                <small>{{ $log->created_at->format('d M Y') }}</small><br>
                                <span class="text-muted">{{ $log->created_at->format('H:i:s') }}</span>
                            </td>
                            <td>
                                @if($log->user)
                                    <strong>{{ $log->user->name }}</strong>
                                    <br>
                                    <small class="text-muted">{{ $log->user->email }}</small>
                                @else
                                    <span class="text-danger">System</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $badgeColor = 'secondary';
                                    if(str_contains($log->action, 'login') || str_contains($log->action, 'logout')) $badgeColor = 'info';
                                    elseif(str_contains($log->action, 'create')) $badgeColor = 'success';
                                    elseif(str_contains($log->action, 'update') || str_contains($log->action, 'approve')) $badgeColor = 'primary';
                                    elseif(str_contains($log->action, 'delete') || str_contains($log->action, 'reject')) $badgeColor = 'danger';
                                    elseif(str_contains($log->action, 'activate')) $badgeColor = 'warning';
                                @endphp
                                <span class="badge bg-{{ $badgeColor }}">{{ $log->action }}</span>
                            </td>
                            <td>
                                @if($log->model_type)
                                    <span class="badge bg-light text-dark">{{ class_basename($log->model_type) }}</span>
                                    @if($log->model_id)
                                        <small class="text-muted">#{{ $log->model_id }}</small>
                                    @endif
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <small class="text-muted">{{ $log->ip_address ?? 'N/A' }}</small>
                            </td>
                            <td>
                                @if($log->new_values || $log->old_values)
                                    <button class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#logModal{{ $log->id }}">
                                        <i class="fas fa-eye"></i> View
                                    </button>
                                    
                                    <div class="modal fade" id="logModal{{ $log->id }}" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Activity Details</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <p><strong>User:</strong> {{ $log->user->name ?? 'System' }}</p>
                                                    <p><strong>Action:</strong> {{ $log->action }}</p>
                                                    <p><strong>IP:</strong> {{ $log->ip_address ?? 'N/A' }}</p>
                                                    <p><strong>Time:</strong> {{ $log->created_at }}</p>
                                                    @if($log->old_values)
                                                    <p><strong>Old Values:</strong></p>
                                                    <pre class="bg-light p-2">{{ json_encode($log->old_values, JSON_PRETTY_PRINT) }}</pre>
                                                    @endif
                                                    @if($log->new_values)
                                                    <p><strong>New Values:</strong></p>
                                                    <pre class="bg-light p-2">{{ json_encode($log->new_values, JSON_PRETTY_PRINT) }}</pre>
                                                    @endif
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            {{ $logs->links() }}
        @else
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> No activity logs found.
            </div>
        @endif
    </div>
</div>
@endsection