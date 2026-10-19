<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DepartmentController extends Controller
{
    public function index()
    {
        $departments = Department::with(['manager', 'parent', 'children', 'employees'])
            ->whereNull('parent_id')
            ->orderBy('name')
            ->get();
        
        return view('departments.index', compact('departments'));
    }

    public function create()
    {
        $departments = Department::where('is_active', true)->get();
        $managers = User::whereHas('role', function ($query) {
            $query->whereIn('name', ['manager', 'dm', 'hr_manager']);
        })->where('is_active', true)->get();
        
        return view('departments.create', compact('departments', 'managers'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:departments,name',
            'code' => 'required|string|max:10|unique:departments,code',
            'manager_id' => 'required|exists:users,id',
            'parent_id' => 'nullable|exists:departments,id',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $department = Department::create($request->all());

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'create',
            'model_type' => Department::class,
            'model_id' => $department->id,
            'new_values' => $request->all(),
            'ip_address' => $request->ip(),
        ]);

        return redirect()->route('departments.index')->with('success', 'Department created successfully');
    }

    public function show(Department $department)
    {
        $department->load(['manager', 'parent', 'children', 'employees.user']);
        
        return view('departments.show', compact('department'));
    }

    public function edit(Department $department)
    {
        $departments = Department::where('is_active', true)->where('id', '!=', $department->id)->get();
        $managers = User::whereHas('role', function ($query) {
            $query->whereIn('name', ['manager', 'dm', 'hr_manager']);
        })->where('is_active', true)->get();
        
        return view('departments.edit', compact('department', 'departments', 'managers'));
    }

    public function update(Request $request, Department $department)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:departments,name,' . $department->id,
            'code' => 'required|string|max:10|unique:departments,code,' . $department->id,
            'manager_id' => 'required|exists:users,id',
            'parent_id' => 'nullable|exists:departments,id',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $oldValues = $department->toArray();
        
        $department->update($request->all());

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'update',
            'model_type' => Department::class,
            'model_id' => $department->id,
            'old_values' => $oldValues,
            'new_values' => $request->all(),
            'ip_address' => $request->ip(),
        ]);

        return redirect()->route('departments.index')->with('success', 'Department updated successfully');
    }

    public function destroy(Request $request, Department $department)
    {
        // Check if department has employees
        if ($department->employees()->count() > 0) {
            return back()->with('error', 'Cannot delete department with employees. Please reassign employees first.');
        }

        $department->update(['is_active' => false]);

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'delete',
            'model_type' => Department::class,
            'model_id' => $department->id,
            'ip_address' => $request->ip(),
        ]);

        return redirect()->route('departments.index')->with('success', 'Department deactivated successfully');
    }
}