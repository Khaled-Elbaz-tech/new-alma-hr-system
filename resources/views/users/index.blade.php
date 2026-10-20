@extends('layouts.app')

@section('title', 'Users')
@section('page-title', 'User Management')

@section('content')
<div class="row mb-3">
    <div class="col-md-12">
        <a href="{{ route('users.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add User
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <form method="GET" class="row g-3">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control" placeholder="Search by name or email" value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <select name="role_id" class="form-select">
                    <option value="">All Roles</option>
                    @foreach($roles as $role)
                        <option value="{{ $role->id }}" {{ request('role_id') == $role->id ? 'selected' : '' }}>{{ $role->display_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="is_active" class="form-select">
                    <option value="">All Status</option>
                    <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>Active</option>
                    <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-secondary">Filter</button>
                <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
    <div class="card-body">
        @if($users->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
<thead class="table-light">
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Employee Binding</th>
                            <th>Status</th>
                            <th>Last Login</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                        <tbody>
                            @foreach($users as $user)
                            <tr>
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
                                        <span class="text-danger">
                                            <i class="fas fa-exclamation-circle"></i> 
                                            Not bound
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-{{ $user->is_active ? 'success' : 'secondary' }}">
                                        {{ $user->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td>{{ $user->last_login ? $user->last_login->format('d M Y H:i') : 'Never' }}</td>
                                <td>
                                    <a href="{{ route('users.show', $user) }}" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('users.edit', $user) }}" class="btn btn-sm btn-warning">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @if($user->is_active && $user->id !== auth()->id())
                                        <form action="{{ route('users.destroy', $user) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Deactivate this user?')">
                                                <i class="fas fa-ban"></i>
                                            </button>
                                        </form>
                                    @elseif(!$user->is_active)
                                        <form action="{{ route('users.activate', $user) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        </form>
                                    @endif
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
        <h6><i class="fas fa-info-circle"></i> User Management Rules</h6>
        <ul class="mb-0">
            <li>Every employee MUST have a user account</li>
            <li>One user = one employee (unique binding)</li>
            <li>Email must be unique across all users</li>
            <li>Super Admin only can manage users</li>
        </ul>
    </div>
@endsection