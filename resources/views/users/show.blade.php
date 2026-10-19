@extends('layouts.app')

@section('title', 'User Details')
@section('page-title', 'User Details')

@section('content')
<div class="row">
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-user"></i> User Information</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <td><strong>Name:</strong></td>
                        <td>{{ $user->name }}</td>
                    </tr>
                    <tr>
                        <td><strong>Email:</strong></td>
                        <td>{{ $user->email }}</td>
                    </tr>
                    <tr>
                        <td><strong>Role:</strong></td>
                        <td>
                            <span class="badge bg-{{ $user->role->name == 'super_admin' ? 'danger' : ($user->role->name == 'hr_manager' ? 'primary' : ($user->role->name == 'manager' ? 'info' : 'secondary')) }}">
                                {{ $user->role->display_name ?? 'N/A' }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Status:</strong></td>
                        <td>
                            <span class="badge bg-{{ $user->is_active ? 'success' : 'secondary' }}">
                                {{ $user->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Last Login:</strong></td>
                        <td>{{ $user->last_login ? $user->last_login->format('d M Y H:i') : 'Never' }}</td>
                    </tr>
                    <tr>
                        <td><strong>Created At:</strong></td>
                        <td>{{ $user->created_at->format('d M Y') }}</td>
                    </tr>
                </table>
            </div>
            <div class="card-footer">
                <a href="{{ route('users.edit', $user) }}" class="btn btn-warning">
                    <i class="fas fa-edit"></i> Edit
                </a>
                <a href="{{ route('users.index') }}" class="btn btn-secondary">Back to List</a>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-user-tie"></i> Employee Binding</h5>
            </div>
            <div class="card-body">
                @if($user->employee)
                    <table class="table table-borderless">
                        <tr>
                            <td><strong>Employee Number:</strong></td>
                            <td>{{ $user->employee->employee_number }}</td>
                        </tr>
                        <tr>
                            <td><strong>Department:</strong></td>
                            <td>{{ $user->employee->department->name ?? 'Not Assigned' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Joining Date:</strong></td>
                            <td>{{ $user->employee->joining_date->format('d M Y') }}</td>
                        </tr>
                        <tr>
                            <td><strong>Status:</strong></td>
                            <td>
                                <span class="badge bg-{{ $user->employee->employment_status === 'active' ? 'success' : 'secondary' }}">
                                    {{ ucfirst($user->employee->employment_status) }}
                                </span>
                            </td>
                        </tr>
                    </table>
                @else
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i> 
                        This user is not bound to any employee record.
                    </div>
                    <p class="text-muted">To bind this user to an employee, edit the user and select an employee, or create the user with "Employee" role.</p>
                @endif
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-history"></i> Recent Activity</h5>
            </div>
            <div class="card-body">
                @php
                    $activities = \App\Models\ActivityLog::where('user_id', $user->id)
                        ->orderBy('created_at', 'desc')
                        ->take(5)
                        ->get();
                @endphp
                @if($activities->count() > 0)
                    <ul class="list-group list-group-flush">
                        @foreach($activities as $activity)
                        <li class="list-group-item px-0">
                            <small class="text-muted">{{ $activity->created_at->format('d M Y H:i') }}</small>
                            <br>
                            <strong>{{ ucfirst($activity->action) }}</strong> {{ class_basename($activity->model_type) ?? '' }}
                        </li>
                        @endforeach
                    </ul>
                @else
                    <p class="text-muted">No recent activity.</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection