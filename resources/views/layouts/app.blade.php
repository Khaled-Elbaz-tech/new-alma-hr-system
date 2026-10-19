<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'ALMA HR System')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f6fa;
        }
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(180deg, #8B251E 0%, #6B1D18 100%);
            color: white;
        }
        .sidebar a {
            color: #ecf0f1;
            text-decoration: none;
            padding: 12px 15px;
            display: block;
            border-radius: 4px;
            margin-bottom: 2px;
        }
        .sidebar a:hover, .sidebar a.active {
            background: rgba(255,255,255,0.15);
        }
        .sidebar a i {
            margin-right: 10px;
            width: 20px;
        }
        .main-content {
            padding: 20px;
        }
        .card {
            border: none;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .card-footer {
            padding: 15px;
            border-top: 1px solid #eee;
        }
        .pagination {
            margin: 0;
            padding: 0;
        }
        .btn-primary {
            background: #3498db;
            border: none;
        }
        .btn-primary:hover {
            background: #2980b9;
        }
        .status-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
        }
        .status-pending { background: #f39c12; color: white; }
        .status-approved { background: #27ae60; color: white; }
        .status-rejected { background: #e74c3c; color: white; }
        .status-hr_approved { background: #9b59b6; color: white; }
    </style>
    @yield('styles')
</head>
<body>
    @auth
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 sidebar p-0">
                <div class="p-3 text-center border-bottom">
                    @include('partials.alma-logo', ['height' => 36])
                    <div class="mt-2 small fw-bold" style="letter-spacing: 2px; opacity: 0.8;">HR SYSTEM</div>
                </div>
                <div class="p-2">
                    <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <i class="fas fa-home"></i> Dashboard
                    </a>
                    
                    @if(auth()->user()->isEmployee() && !auth()->user()->isHrManager() && !auth()->user()->isManager() && !auth()->user()->isSuperAdmin())
                        <a href="{{ route('leaves.my') }}" class="{{ request()->routeIs('leaves.my') ? 'active' : '' }}">
                            <i class="fas fa-calendar-minus"></i> My Leaves
                        </a>
                        <a href="{{ route('employees.my-profile') }}" class="{{ request()->routeIs('employees.my-profile') ? 'active' : '' }}">
                            <i class="fas fa-user"></i> My Profile
                        </a>
                    @endif
                    
                    @if(auth()->user()->isHrManager() || auth()->user()->isSuperAdmin())
                        <a href="{{ route('employees.index') }}" class="{{ request()->routeIs('employees.*') ? 'active' : '' }}">
                            <i class="fas fa-users"></i> Employees
                        </a>
                        <a href="{{ route('employees.documents') }}" class="{{ request()->routeIs('employees.documents*') ? 'active' : '' }}">
                            <i class="fas fa-file-alt"></i> Employees Documents
                        </a>
                        <a href="{{ route('departments.index') }}" class="{{ request()->routeIs('departments.*') ? 'active' : '' }}">
                            <i class="fas fa-building"></i> Departments
                        </a>
                        <a href="{{ route('leaves.index') }}" class="{{ request()->routeIs('leaves.index') ? 'active' : '' }}">
                            <i class="fas fa-calendar"></i> Leave Requests
                        </a>
                        <a href="{{ route('leaves.calendar') }}" class="{{ request()->routeIs('leaves.calendar') ? 'active' : '' }}">
                            <i class="fas fa-calendar-alt"></i> Leave Calendar
                        </a>
                        <a href="{{ route('holidays.index') }}" class="{{ request()->routeIs('holidays.*') ? 'active' : '' }}">
                            <i class="fas fa-calendar-week"></i> Holidays
                        </a>
                        
                        <div class="mt-3 px-3 small fw-bold text-info">REPORTS</div>
                        <a href="{{ route('reports.leave-balance') }}">
                            <i class="fas fa-chart-bar"></i> Leave Balance
                        </a>
                        <a href="{{ route('reports.leave-history') }}">
                            <i class="fas fa-history"></i> Leave History
                        </a>
                        <a href="{{ route('reports.vacation-history') }}">
                            <i class="fas fa-book"></i> Vacation History
                        </a>
                        <a href="{{ route('reports.department') }}">
                            <i class="fas fa-chart-pie"></i> Department
                        </a>
                        <a href="{{ route('reports.employee') }}">
                            <i class="fas fa-user-chart"></i> Employee
                        </a>
                        <a href="{{ route('reports.leave-statistics') }}">
                            <i class="fas fa-chart-line"></i> Statistics
                        </a>
                    @endif
                    
                    @if(auth()->user()->isManager() || auth()->user()->isDM())
                        <a href="{{ route('team.employees') }}" class="{{ request()->routeIs('team.employees*') ? 'active' : '' }}">
                            <i class="fas fa-users"></i> My Team
                        </a>
                        <a href="{{ route('leaves.manager.index') }}" class="{{ request()->routeIs('leaves.manager*') ? 'active' : '' }}">
                            <i class="fas fa-calendar"></i> Leave Requests
                        </a>
                    @endif
                    
                    @if(auth()->user()->isSuperAdmin())
                        <div class="mt-3 px-3 small fw-bold text-warning">ADMIN</div>
                        <a href="{{ route('users.index') }}" class="{{ request()->routeIs('users.*') ? 'active' : '' }}">
                            <i class="fas fa-user-cog"></i> Users
                        </a>
                        <a href="{{ route('users.task-access') }}" class="{{ request()->routeIs('users.task-access') ? 'active' : '' }}">
                            <i class="fas fa-user-cog"></i> Task Access
                        </a>
                        <a href="{{ route('activity-logs.index') }}" class="{{ request()->routeIs('activity-logs.*') ? 'active' : '' }}">
                            <i class="fas fa-history"></i> Activity Logs
                        </a>
                        <a href="{{ route('dynamic-forms.index') }}" class="{{ request()->routeIs('dynamic-forms.*') ? 'active' : '' }}">
                            <i class="fas fa-forms"></i> Dynamic Fields
                        </a>
                    @endif
                    
                    <div class="mt-3 px-3 small fw-bold text-danger">ACCOUNT</div>
                    
                    @auth
                        @if(auth()->user()->canAccessTasks())
                            <a href="{{ route('tasks.workspace') }}" class="{{ request()->routeIs('tasks.*') ? 'active' : '' }}">
                                <i class="fas fa-tasks"></i> Workspace
                            </a>
                        @endif
                    @endauth
                    
                    <a href="{{ route('password.change') }}">
                        <i class="fas fa-key"></i> Change Password
                    </a>
                    <form action="{{ route('logout') }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-link text-start text-white" style="text-decoration: none;">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </button>
                    </form>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-10 main-content">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4>@yield('page-title', 'Dashboard')</h4>
                    <div class="d-flex align-items-center gap-3">
                        <span>Welcome, {{ auth()->user()->name }}</span>
                        <span class="badge bg-primary">{{ auth()->user()->role->display_name ?? 'User' }}</span>
                    </div>
                </div>
                
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                
                @if(session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif
                
                @yield('content')
            </div>
        </div>
    </div>
    @else
        @yield('content')
    @endauth
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @yield('scripts')
</body>
</html>