<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DepartmentController extends Controller
{
    public function index(Request $request)
    {
        $departments = Department::with(['manager', 'parent', 'children', 'employees'])
            ->whereNull('parent_id')
            ->when($request->filled('search'), function ($q) use ($request) {
                // Matches a top-level department by name/code, or any of
                // its sub-departments by name/code.
                $search = $request->search;
                $q->where(function ($q2) use ($search) {
                    $q2->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhereHas('children', function ($q3) use ($search) {
                            $q3->where('name', 'like', "%{$search}%")
                                ->orWhere('code', 'like', "%{$search}%");
                        });
                });
            })
            ->when($request->filled('is_active') && in_array($request->is_active, ['0', '1'], true), function ($q) use ($request) {
                $q->where('is_active', (bool) $request->is_active);
            })
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

    /**
     * M8 — manager_id must belong to a user holding a manager/dm/hr_manager
     * role. The create/edit dropdowns already filter to those roles, but the
     * server previously accepted ANY existing user id, which would grant the
     * chosen user managedDepartment access to the whole department.
     */
    protected function managerRoleRule()
    {
        return function ($attribute, $value, $fail) {
            $manager = User::with('role')->find($value);

            if (!$manager || !in_array($manager->role?->name, ['manager', 'dm', 'hr_manager'], true)) {
                $fail('The selected manager must be a user with a manager, dm, or hr_manager role.');
            }
        };
    }

    /**
     * M8 — parent_id cycle rule for update(): rejects the department itself
     * and any of its descendants as the new parent. Walks the ancestor chain
     * of the proposed parent; reaching this department means the proposed
     * parent sits below it, so accepting it would create a cycle.
     * (Schema HAS parent_id — Department model: parent() belongsTo,
     * children() hasMany on parent_id — so this check is applicable, not
     * not-applicable. A brand-new department has no descendants, so store()
     * needs no descendant check; the exists rule is sufficient there.)
     */
    protected function parentCycleRule(Department $department)
    {
        return function ($attribute, $value, $fail) use ($department) {
            if ($value === null || $value === '') {
                return;
            }

            if ((int) $value === (int) $department->id) {
                $fail('A department cannot be its own parent department.');
                return;
            }

            $seen = [];
            $currentId = (int) $value;
            while ($currentId) {
                if ($currentId === (int) $department->id) {
                    $fail('A department cannot be moved under one of its own sub-departments (this would create a hierarchy cycle).');
                    return;
                }
                if (in_array($currentId, $seen, true)) {
                    break; // Pre-existing cycle in stored data: stop walking.
                }
                $seen[] = $currentId;
                $current = Department::find($currentId);
                if (!$current || !$current->parent_id) {
                    break;
                }
                $currentId = (int) $current->parent_id;
            }
        };
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:departments,name',
            'code' => 'required|string|max:10|unique:departments,code',
            'manager_id' => ['required', 'exists:users,id', $this->managerRoleRule()],
            'parent_id' => 'nullable|exists:departments,id',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Explicit validated data only (previously $request->all()): this also
        // closes the unvalidated is_active pass-through noted in the pentest
        // (Department::$fillable includes is_active; the forms never send it).
        $department = Department::create($validator->validated());

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'create',
            'model_type' => Department::class,
            'model_id' => $department->id,
            'new_values' => $validator->validated(),
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
            'manager_id' => ['required', 'exists:users,id', $this->managerRoleRule()],
            'parent_id' => ['nullable', 'exists:departments,id', $this->parentCycleRule($department)],
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $oldValues = $department->toArray();

        $department->update($validator->validated());

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'update',
            'model_type' => Department::class,
            'model_id' => $department->id,
            'old_values' => $oldValues,
            'new_values' => $validator->validated(),
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

    /**
     * M9 — force-delete behaviour: BLOCK while child departments exist.
     * Chosen over reparenting because silently moving children to the
     * deleted department's parent (or to top level) would change the
     * hierarchy without the operator's knowledge, and reparenting to null
     * would promote sub-departments to top level. Blocking matches the
     * existing employees guard in this same method. Children are counted
     * regardless of is_active: an inactive child still points at this row,
     * so deleting would still orphan it. destroy() (deactivate) is
     * unaffected: the row still exists, so children are not orphaned.
     */
    public function forceDelete(Request $request, Department $department)
    {
        if ($department->employees()->count() > 0) {
            return back()->with('error', 'Cannot permanently delete department with employees. Please reassign employees first.');
        }

        $childCount = $department->children()->count();
        if ($childCount > 0) {
            return back()->with('error', "Cannot permanently delete department with {$childCount} sub-department(s). Please reassign or delete the sub-departments first.");
        }

        $departmentName = $department->name;
        $department->delete();

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'force_delete',
            'model_type' => Department::class,
            'model_id' => $department->id,
            'ip_address' => $request->ip(),
        ]);

        return redirect()->route('departments.index')->with('success', "Department '{$departmentName}' permanently deleted successfully");
    }
}
