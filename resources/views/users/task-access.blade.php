@extends('layouts.app')

@section('title', 'Task Management Access')

@section('page-title')
    <i class="fas fa-user-cog"></i> Task Management Access
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Task Management Access Control</h5>
        <p class="text-muted mb-0">Enable or disable Task Management access for users. Super Admins and HR Managers always have access.</p>
    </div>
    <div class="card-body">
        @if($users->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Employee ID</th>
                            <th>Task Access</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $index => $user)
                            @php
                                $taskAccess = $user->taskAccess;
                                $hasAccess = $taskAccess && $taskAccess->can_access_tasks;
                                $isSuperAdminOrHR = $user->isSuperAdmin() || $user->isHrManager();
                            @endphp
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td><strong>{{ $user->name }}</strong></td>
                                <td>{{ $user->email }}</td>
                                <td>
                                    <span class="badge bg-{{ $user->role->name == 'super_admin' ? 'danger' : ($user->role->name == 'hr_manager' ? 'primary' : ($user->role->name == 'manager' ? 'info' : 'secondary')) }}">
                                        {{ $user->role->display_name ?? 'N/A' }}
                                    </span>
                                </td>
                                <td>
                                    @if($user->employee)
                                        <span class="text-success">
                                            <i class="fas fa-check-circle"></i> 
                                            {{ $user->employee->employee_number }}
                                        </span>
                                    @else
                                        <span class="text-muted">Not bound</span>
                                    @endif
                                </td>
                                <td>
                                    @if($isSuperAdminOrHR)
                                        <span class="badge bg-success">
                                            <i class="fas fa-lock"></i> Always Enabled
                                        </span>
                                    @else
                                        <form action="{{ route('users.toggle-task-access', $user) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm {{ $hasAccess ? 'btn-success' : 'btn-outline-secondary' }}">
                                                <i class="fas {{ $hasAccess ? 'fa-toggle-on' : 'fa-toggle-off' }}"></i>
                                                {{ $hasAccess ? 'Enabled' : 'Disabled' }}
                                            </button>
                                        </form>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-{{ $user->is_active ? 'success' : 'secondary' }}">
                                        {{ $user->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            {{ $users->links() }}
        @else
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> No users found.
            </div>
        @endif
    </div>
</div>

<!-- Rules Info -->
<div class="alert alert-light mt-3">
    <h6><i class="fas fa-info-circle"></i> Access Control Rules</h6>
    <ul class="mb-0">
        <li>Super Administrators and HR Managers always have Task Management access (cannot be disabled)</li>
        <li>For other roles, access can be toggled on/off using the switch above</li>
        <li>When access is disabled, users cannot view or access any task management screens</li>
        <li>The "Task Management" sidebar link is automatically hidden for users without access</li>
        <li>Only users with active Task Management access appear in the "Assigned To" dropdown when creating/editing tasks</li>
    </ul>
</div>
@endsection