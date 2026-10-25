@extends('layouts.app')

@section('title', 'Employees')
@section('page-title', 'Employees')

@section('content')
@if (session('import_passwords'))
    <div class="alert alert-warning">
        <strong>One-time passwords — copy these now. They will not be shown again.</strong>
        <ul>
            @foreach (session('import_passwords') as $cred)
                <li>{{ $cred['email'] }} — <code>{{ $cred['password'] }}</code></li>
            @endforeach
        </ul>
        <p>Each imported user must change this password on first login.</p>
    </div>
@endif
<div class="row mb-3">
    <div class="col-md-12 d-flex gap-2">
        @can('create', \App\Models\Employee::class)
        <a href="{{ route('employees.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add Employee
        </a>
        @endcan
        
        <div class="dropdown">
            <button class="btn btn-success dropdown-toggle" type="button" data-bs-toggle="dropdown">
                <i class="fas fa-file-import"></i> Import
            </button>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="{{ route('employees.download-template') }}">
                    <i class="fas fa-download"></i> Download Template
                </a></li>
                <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#importModal">
                    <i class="fas fa-upload"></i> Upload File
                </a></li>
            </ul>
        </div>
        
        <a href="{{ route('employees.export') }}" class="btn btn-info">
            <i class="fas fa-file-csv"></i> Export CSV
        </a>
        <a href="{{ route('employees.export-excel') }}" class="btn btn-success">
            <i class="fas fa-file-excel"></i> Export Excel
        </a>
    </div>
</div>

<!-- Import Modal -->
<div class="modal fade" id="importModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Import Employees</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('employees.import') }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Select File (Excel/CSV)</label>
                        <input type="file" name="file" class="form-control" accept=".xlsx,.xls,.csv" required>
                        <small class="text-muted">Supported formats: .xlsx, .xls, .csv</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Import</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-header">
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <input type="text" name="search" class="form-control" placeholder="Search by name, email or employee number" value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <select name="department_id" class="form-select">
                    <option value="">All Departments</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>
                            {{ $dept->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="resigned" {{ request('status') == 'resigned' ? 'selected' : '' }}>Resigned</option>
                    <option value="terminated" {{ request('status') == 'terminated' ? 'selected' : '' }}>Terminated</option>
                    <option value="on_leave" {{ request('status') == 'on_leave' ? 'selected' : '' }}>On Leave</option>
                </select>
            </div>
            <div class="col-md-3">
                <input type="date" name="joined_from" class="form-control" value="{{ request('joined_from') }}" title="Joined from">
            </div>
            <div class="col-md-3">
                <input type="date" name="joined_to" class="form-control" value="{{ request('joined_to') }}" title="Joined to">
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-secondary">Filter</button>
                <a href="{{ route('employees.index') }}" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<!-- Employee List -->
<div class="card">
    <div class="card-body">
        @if($employees->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Employee ID</th>
                            <th>Full Name</th>
                            <th>Email</th>
                            <th>Department</th>
                            <th>Manager</th>
                            <th>Joining Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($employees as $employee)
                            <tr>
                                <td>{{ $employee->employee_number }}</td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <img src="{{ $employee->avatar_url }}" alt="{{ $employee->user->name }}" class="rounded-circle" width="38" height="38" style="object-fit: cover;">
                                        <strong>{{ $employee->user->name }}</strong>
                                    </div>
                                </td>
                                <td>{{ $employee->user->email }}</td>
                                <td>{{ $employee->department->name ?? 'N/A' }}</td>
                                <td>
                                    @if($employee->department && $employee->department->manager)
                                        {{ $employee->department->manager->name }}
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>{{ $employee->joining_date ? $employee->joining_date->format('d M Y') : 'N/A' }}</td>
                                <td>
                                    <span class="badge bg-{{ $employee->employment_status === 'active' ? 'success' : 'secondary' }}">
                                        {{ ucfirst($employee->employment_status) }}
                                    </span>
                                </td>
                                <td class="text-nowrap">
                                    <div class="d-flex flex-nowrap align-items-center gap-1">
                                    <a href="{{ route('employees.show', $employee) }}" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                    
                                    @can('update', $employee)
                                    <a href="{{ route('employees.edit', $employee) }}" class="btn btn-sm btn-warning">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    @endcan
                                    
                                    @can('delete', $employee)
                                        @if($employee->employment_status === 'active')
                                            <form action="{{ route('employees.destroy', $employee) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Deactivate this employee?')">
                                                    <i class="fas fa-ban"></i> Deactivate
                                                </button>
                                            </form>
                                        @else
                                            <form action="{{ route('employees.activate', $employee) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-success">
                                                    <i class="fas fa-check"></i> Activate
                                                </button>
                                            </form>
                                        @endif
                                    @endcan
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($employees->hasPages())
            <div class="card-footer">
                {{ $employees->links('pagination::bootstrap-5') }}
            </div>
            @endif
        @else
            <p class="text-muted">No employees found.</p>
        @endif
    </div>
</div>
@endsection