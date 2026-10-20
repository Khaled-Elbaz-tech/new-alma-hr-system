<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'ALMA HR System')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f6fa;
            font-size: 15.5px;
            line-height: 1.6;
            color: #212529;
            -webkit-font-smoothing: antialiased;
        }
        h1, h2, h3, h4, h5, h6 { font-weight: 700; color: #1a1a2e; }
        .table { font-size: 15px; }
        .btn { font-size: 14.5px; }
        .form-control, .form-select { font-size: 15px; }
        .card-body { font-size: 15px; }
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(180deg, #8B251E 0%, #6B1D18 100%);
            color: white;
            width: 210px;
            min-width: 210px;
            max-width: 210px;
        }
        .sidebar a {
            font-size: 13.5px;
            padding: 10px 12px;
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

        /* ===== ALMA Professional Design System ===== */
        .main-content { background-color: #f8f9fa; min-height: 100vh; }
        .page-header { margin-bottom: 1.5rem; padding-bottom: 1rem; border-bottom: 2px solid #e9ecef; }
        .page-header h1, .page-header h2 { color: #2c3e50; font-weight: 700; margin-bottom: 0.25rem; }
        .card { border: none; border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,0.08); margin-bottom: 1.5rem; transition: box-shadow 0.2s ease; }
        .card:hover { box-shadow: 0 4px 20px rgba(0,0,0,0.12); }
        .card-header { background: white; border-bottom: 1px solid #e9ecef; border-radius: 12px 12px 0 0 !important; padding: 1rem 1.25rem; font-weight: 600; }
        .table { margin-bottom: 0; }
        .table thead th { background-color: #f8f9fa; border-bottom: 2px solid #dee2e6; color: #495057; font-weight: 600; font-size: 0.875rem; text-transform: uppercase; letter-spacing: 0.5px; padding: 0.9rem 0.75rem; }
        .table tbody tr:hover { background-color: #f8f9fa; }
        .table td { padding: 0.85rem 0.75rem; vertical-align: middle; }
        .btn { border-radius: 8px; font-weight: 500; padding: 0.5rem 1.25rem; transition: all 0.2s ease; }
        .btn-primary { background: #8B251E; border-color: #8B251E; }
        .btn-primary:hover { background: #6B1D18; border-color: #6B1D18; }
        .form-control, .form-select { border-radius: 8px; border: 1px solid #dee2e6; padding: 0.6rem 0.9rem; }
        .form-control:focus, .form-select:focus { border-color: #8B251E; box-shadow: 0 0 0 0.2rem rgba(139,37,30,0.15); }
        .form-label { font-weight: 600; color: #495057; margin-bottom: 0.4rem; }
        .badge { padding: 0.4em 0.8em; border-radius: 6px; font-weight: 500; }
        .alert { border-radius: 10px; border: none; }
        .stat-card { border-radius: 12px; overflow: hidden; transition: transform 0.2s ease; }
        .stat-card:hover { transform: translateY(-3px); }
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
                    <div style="background: #fff; border-radius: 10px; padding: 8px 12px; display: inline-block; box-shadow: 0 2px 8px rgba(0,0,0,.2);">
                        @include('partials.alma-logo', ['height' => 34])
                    </div>
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
                            <i class="fas fa-file-alt"></i> Documents
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
                            <i class="fas fa-id-card"></i> Employee
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
                            <i class="fas fa-list-check"></i> Dynamic Fields
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
                        <form action="{{ route('employees.index') }}" method="GET" class="d-flex" style="max-width: 280px;">
                            <div class="input-group input-group-sm">
                                <input type="text" name="search" class="form-control" placeholder="Search employees..." value="{{ request('search') }}">
                                <button class="btn btn-outline-secondary" type="submit"><i class="fas fa-search"></i></button>
                            </div>
                        </form>
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
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    @yield('scripts')
</body>
</html>