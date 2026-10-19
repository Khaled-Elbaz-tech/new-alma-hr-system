<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'ALMA HR System')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --alma: #8B251E;
            --alma-dark: #5F1812;
            --alma-darker: #47100B;
            --alma-hover: #701C16;
            --alma-soft: #FAF1F0;
            --slate: #64748B;
            --gray-900: #1F2937;
            --gray-600: #4B5563;
            --gray-500: #6B7280;
            --gray-300: #D1D5DB;
            --gray-200: #E5E7EB;
            --gray-100: #F3F4F6;
            --gray-50: #F9FAFB;
            --page-bg: #F4F5F7;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--page-bg);
            color: var(--gray-900);
        }

        /* ---------- Sidebar ---------- */
        .sidebar-fixed {
            width: 210px; flex: 0 0 210px;
        }
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(180deg, var(--alma-darker) 0%, var(--alma-dark) 45%, var(--alma) 100%);
            color: #fff;
        }
        .sidebar .brand {
            padding: 20px 18px 16px;
            border-bottom: 1px solid rgba(255,255,255,.14);
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 8px;
        }
        .sidebar .brand-sub { font-size: 10.5px; color: rgba(255,255,255,.6); letter-spacing: 2.5px; font-weight: 600; }
        .sidebar .brand-logo-wrap {
            background: #fff;
            border-radius: 10px;
            padding: 8px 14px;
            display: inline-block;
            box-shadow: 0 2px 8px rgba(0,0,0,.2);
        }
        .sidebar nav { padding: 8px; }
        .sidebar a { font-size: 13.5px; padding: 9px 12px; }
        .sidebar a {
            color: rgba(255,255,255,.82);
            text-decoration: none;
            padding: 10px 14px;
            display: block;
            border-radius: 8px;
            margin-bottom: 2px;
            font-size: 14px;
            border-left: 3px solid transparent;
            transition: background .15s ease;
        }
        .sidebar a:hover { background: rgba(255,255,255,.10); color: #fff; }
        .sidebar a.active {
            background: rgba(255,255,255,.14);
            color: #fff;
            border-left-color: #fff;
            font-weight: 600;
        }
        .sidebar a i { margin-right: 10px; width: 20px; text-align: center; opacity: .85; }
        .sidebar .nav-label {
            margin: 14px 0 4px;
            padding: 0 14px;
            font-size: 10.5px;
            font-weight: 700;
            letter-spacing: 1.5px;
            color: rgba(255,255,255,.5);
        }
        .sidebar .logout-btn {
            background: none; border: none;
            color: rgba(255,255,255,.82);
            padding: 10px 14px; display: block; width: 100%;
            text-align: left; border-radius: 8px; font-size: 14px;
        }
        .sidebar .logout-btn:hover { background: rgba(255,255,255,.10); color: #fff; }
        .sidebar .logout-btn i { margin-right: 10px; width: 20px; text-align: center; opacity: .85; }

        /* ---------- Main / topbar ---------- */
        .main-content { padding: 24px 28px; }
        .topbar {
            background: #fff;
            border: 1px solid var(--gray-200);
            border-radius: 12px;
            padding: 14px 20px;
            margin-bottom: 22px;
            display: flex; justify-content: space-between; align-items: center;
            box-shadow: 0 1px 2px rgba(0,0,0,.04);
        }
        .topbar h4 { margin: 0; font-weight: 700; font-size: 19px; color: var(--gray-900); }
        .topbar .user-chip {
            display: flex; align-items: center; gap: 10px;
            font-size: 14px; color: var(--gray-600);
        }
        .topbar .avatar {
            width: 36px; height: 36px; border-radius: 50%;
            background: var(--alma); color: #fff;
            display: flex; align-items: center; justify-content: center;
            font-weight: 700; font-size: 15px;
        }
        .role-badge {
            background: var(--gray-100); color: var(--gray-600);
            border: 1px solid var(--gray-200);
            font-size: 11.5px; font-weight: 600;
            padding: 4px 10px; border-radius: 20px;
            letter-spacing: .3px;
        }

        /* ---------- Cards ---------- */
        .card {
            border: 1px solid var(--gray-200);
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,.05);
            overflow: hidden;
        }
        .card-header {
            background: #fff;
            border-bottom: 1px solid var(--gray-200);
            font-weight: 700;
            padding: 14px 20px;
        }
        .card-footer { padding: 15px 20px; border-top: 1px solid var(--gray-200); background: var(--gray-50); }

        /* ---------- Stat cards ---------- */
        .stat-card {
            background: #fff;
            border: 1px solid var(--gray-200);
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,.05);
            display: flex; align-items: center; gap: 16px;
        }
        .stat-card .stat-icon {
            width: 48px; height: 48px; border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 20px; flex-shrink: 0;
        }
        .stat-card .stat-num { font-size: 26px; font-weight: 800; color: var(--gray-900); line-height: 1.1; }
        .stat-card .stat-lbl { font-size: 12.5px; color: var(--gray-500); }
        .stat-icon.alma { background: var(--alma-soft); color: var(--alma); }
        .stat-icon.slate { background: #F1F5F9; color: var(--slate); }
        .stat-icon.gray { background: var(--gray-100); color: var(--gray-600); }

        /* ---------- Tables ---------- */
        .table { margin-bottom: 0; }
        .table thead th {
            background: var(--gray-50);
            color: var(--gray-500);
            font-size: 11.5px; font-weight: 700;
            text-transform: uppercase; letter-spacing: .6px;
            border-bottom: 1px solid var(--gray-200);
            padding: 12px 16px;
            white-space: nowrap;
        }
        .table tbody td { padding: 13px 16px; vertical-align: middle; font-size: 14px; border-bottom: 1px solid var(--gray-100); }
        .table tbody tr:last-child td { border-bottom: none; }
        .table tbody tr:hover { background: var(--gray-50); }

        /* ---------- Buttons ---------- */
        .btn-primary {
            background: var(--alma); border-color: var(--alma);
        }
        .btn-primary:hover, .btn-primary:focus {
            background: var(--alma-hover); border-color: var(--alma-hover);
        }
        .btn-outline-primary {
            color: var(--alma); border-color: var(--alma);
        }
        .btn-outline-primary:hover {
            background: var(--alma); border-color: var(--alma); color: #fff;
        }
        .bg-primary { background: var(--alma) !important; }

        /* ---------- Forms ---------- */
        .form-label { font-weight: 600; font-size: 13.5px; color: var(--gray-700); }
        .form-control, .form-select { border-radius: 8px; border: 1px solid var(--gray-300); padding: 10px 14px; font-size: 14px; }
        .form-control:focus, .form-select:focus {
            border-color: var(--alma);
            box-shadow: 0 0 0 3px rgba(139,37,30,.12);
        }

        /* ---------- Alerts ---------- */
        .alert { border-radius: 10px; border: 1px solid; }
        .alert-success { background: #F0FDF4; border-color: #BBF7D0; color: #15803D; }
        .alert-danger { background: #FEF2F2; border-color: #FECACA; color: #B91C1C; }

        /* ---------- Status badges ---------- */
        .status-badge {
            padding: 5px 12px; border-radius: 20px;
            font-size: 11.5px; font-weight: 700; letter-spacing: .3px;
            display: inline-block; white-space: nowrap;
        }
        .status-pending { background: #FEF3C7; color: #B45309; }
        .status-approved { background: #DCFCE7; color: #15803D; }
        .status-rejected { background: #FEE2E2; color: #B91C1C; }
        .status-hr_approved { background: #E2E8F0; color: #475569; }
        .status-manager_approved { background: #E2E8F0; color: #475569; }
        .status-cancelled { background: var(--gray-100); color: var(--gray-500); }

        /* ---------- Pagination ---------- */
        .pagination { margin: 0; padding: 0; }
        .page-link { color: var(--alma); border-radius: 8px; margin: 0 2px; border: 1px solid var(--gray-200); }
        .page-item.active .page-link { background: var(--alma); border-color: var(--alma); }

        /* ---------- Misc ---------- */
        a { color: var(--alma); }
        a:hover { color: var(--alma-hover); }
        .text-primary { color: var(--alma) !important; }
        .section-title { font-weight: 700; font-size: 16px; margin-bottom: 14px; color: var(--gray-900); }

        /* ---------- Alma stat cards (override Bootstrap colors) ---------- */
        .card.bg-primary { background: linear-gradient(135deg, #8B251E 0%, #5F1812 100%) !important; border: none; }
        .card.bg-success { background: linear-gradient(135deg, #1E7E4C 0%, #14532D 100%) !important; border: none; }
        .card.bg-warning { background: linear-gradient(135deg, #64748B 0%, #475569 100%) !important; border: none; }
        .card.bg-danger { background: linear-gradient(135deg, #C0392B 0%, #922B21 100%) !important; border: none; }
        .card.bg-info { background: linear-gradient(135deg, #475569 0%, #334155 100%) !important; border: none; }
        .card.bg-secondary { background: linear-gradient(135deg, #6B7280 0%, #4B5563 100%) !important; border: none; }
        .card.bg-primary, .card.bg-success, .card.bg-warning,
        .card.bg-danger, .card.bg-info, .card.bg-secondary {
            border-radius: 14px;
            box-shadow: 0 4px 14px rgba(0,0,0,.10);
        }
        .card.bg-primary .card-body, .card.bg-success .card-body, .card.bg-warning .card-body,
        .card.bg-danger .card-body, .card.bg-info .card-body, .card.bg-secondary .card-body {
            padding: 22px;
        }
        .card.bg-primary h4, .card.bg-success h4, .card.bg-warning h4,
        .card.bg-danger h4, .card.bg-info h4, .card.bg-secondary h4 {
            font-weight: 800; font-size: 28px; margin-bottom: 4px;
        }
    </style>
    @yield('styles')
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="sidebar sidebar-fixed p-0">
                <div class="brand">
                    <div class="brand-logo-wrap">
                        @include('partials.alma-logo', ['height' => 34])
                    </div>
                    <div class="brand-sub">HR SYSTEM</div>
                </div>
                <nav>
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

                        <div class="nav-label">REPORTS</div>
                        <a href="{{ route('reports.leave-balance') }}" class="{{ request()->routeIs('reports.leave-balance') ? 'active' : '' }}">
                            <i class="fas fa-chart-bar"></i> Leave Balance
                        </a>
                        <a href="{{ route('reports.leave-history') }}" class="{{ request()->routeIs('reports.leave-history') ? 'active' : '' }}">
                            <i class="fas fa-history"></i> Leave History
                        </a>
                        <a href="{{ route('reports.vacation-history') }}" class="{{ request()->routeIs('reports.vacation-history') ? 'active' : '' }}">
                            <i class="fas fa-book"></i> Vacation History
                        </a>
                        <a href="{{ route('reports.department') }}" class="{{ request()->routeIs('reports.department') ? 'active' : '' }}">
                            <i class="fas fa-chart-pie"></i> Department
                        </a>
                        <a href="{{ route('reports.employee') }}" class="{{ request()->routeIs('reports.employee') ? 'active' : '' }}">
                            <i class="fas fa-id-card"></i> Employee
                        </a>
                        <a href="{{ route('reports.leave-statistics') }}" class="{{ request()->routeIs('reports.leave-statistics') ? 'active' : '' }}">
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
                        <a href="{{ route('leaves.calendar') }}" class="{{ request()->routeIs('leaves.calendar') ? 'active' : '' }}">
                            <i class="fas fa-calendar-alt"></i> Leave Calendar
                        </a>
                    @endif

                    @if(auth()->user()->isSuperAdmin())
                        <div class="nav-label">ADMIN</div>
                        <a href="{{ route('users.index') }}" class="{{ request()->routeIs('users.*') ? 'active' : '' }}">
                            <i class="fas fa-user-cog"></i> Users
                        </a>
                        <a href="{{ route('activity-logs.index') }}" class="{{ request()->routeIs('activity-logs.*') ? 'active' : '' }}">
                            <i class="fas fa-history"></i> Activity Logs
                        </a>
                        <a href="{{ route('dynamic-forms.index') }}" class="{{ request()->routeIs('dynamic-forms.*') ? 'active' : '' }}">
                            <i class="fas fa-list-check"></i> Dynamic Fields
                        </a>
                    @endif

                    <div class="nav-label">ACCOUNT</div>

                    <a href="{{ route('password.change') }}" class="{{ request()->routeIs('password.change') ? 'active' : '' }}">
                        <i class="fas fa-key"></i> Change Password
                    </a>
                    <form action="{{ route('logout') }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="logout-btn">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </button>
                    </form>
                </nav>
            </div>

            <!-- Main Content -->
            <div class="main-content flex-fill">
                <div class="topbar">
                    <h4>@yield('page-title', 'Dashboard')</h4>
                    <div class="user-chip">
                        <span class="role-badge">{{ auth()->user()->role->display_name ?? 'User' }}</span>
                        <div class="avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
                        <span>{{ auth()->user()->name }}</span>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @yield('scripts')
</body>
</html>
