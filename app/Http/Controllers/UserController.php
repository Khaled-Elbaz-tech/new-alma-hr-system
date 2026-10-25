<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use App\Models\Employee;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class UserController extends Controller
{
    use AuthorizesRequests;
    public function index(Request $request)
    {
        $query = User::with(['role', 'employee']);
        
        if ($request->has('role_id') && $request->role_id) {
            $query->where('role_id', $request->role_id);
        }
        
        if ($request->has('is_active') && $request->is_active !== '') {
            $query->where('is_active', $request->is_active);
        }
        
        if ($request->filled('search')) {
            // Grouped so the search never escapes the role/status filters.
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();
        $roles = Role::all();
        
        return view('users.index', compact('users', 'roles'));
    }

    public function create()
    {
        $roles = Role::all();
        
        return view('users.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role_id' => 'required|exists:roles,id',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role_id' => $request->role_id,
            'is_active' => $request->is_active ?? true,
        ]);

        // Create employee profile for employee role
        if ($request->role_id == Role::where('name', 'employee')->first()?->id) {
            Employee::create([
                'user_id' => $user->id,
                'employee_number' => 'EMP' . time(),
                'joining_date' => now(),
                'employment_status' => 'active',
                'annual_leave_entitlement' => 21,
            ]);
        }

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'create',
            'model_type' => User::class,
            'model_id' => $user->id,
            'new_values' => $request->except(['password', 'password_confirmation']),
            'ip_address' => $request->ip(),
        ]);

        return redirect()->route('users.index')->with('success', 'User created successfully');
    }

    public function show(User $user)
    {
        $user->load('role', 'employee');
        
        return view('users.show', compact('user'));
    }

    public function edit(User $user)
    {
        $roles = Role::all();
        
        return view('users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $user)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role_id' => 'required|exists:roles,id',
            'is_active' => 'boolean',
            // L1: same min:8 + confirmation policy as creation (no stricter rule invented).
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // H4: last-super-admin protection + no self role-change / self-deactivation.
        if ($user->id === auth()->id() && (int) $request->role_id !== (int) $user->role_id) {
            return back()->with('error', 'You cannot change your own role.')->withInput();
        }
        if ($user->id === auth()->id() && $request->has('is_active') && !(bool) $request->is_active) {
            return back()->with('error', 'You cannot deactivate your own account.')->withInput();
        }
        if ($user->isSuperAdmin()
            && ((int) $request->role_id !== (int) $user->role_id
                || ($request->has('is_active') && !(bool) $request->is_active))
            && $this->activeSuperAdminCount() <= 1) {
            return back()->with('error', 'Cannot demote or deactivate the last remaining super admin.')->withInput();
        }

        $oldValues = $user->toArray();
        
        $updateData = [
            'name' => $request->name,
            'email' => $request->email,
            'role_id' => $request->role_id,
            'is_active' => $request->is_active ?? $user->is_active,
        ];

        // Update password if provided
        if ($request->password) {
            $updateData['password'] = Hash::make($request->password);
        }

        $user->update($updateData);

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'update',
            'model_type' => User::class,
            'model_id' => $user->id,
            'old_values' => $oldValues,
            'new_values' => $request->except(['password', 'password_confirmation']),
            'ip_address' => $request->ip(),
        ]);

        return redirect()->route('users.index')->with('success', 'User updated successfully');
    }

    public function destroy(Request $request, User $user)
    {
        // Cannot delete self
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account');
        }

        // H4: destroy() deactivates the user, so it must not remove the last super admin.
        if ($user->isSuperAdmin() && $this->activeSuperAdminCount() <= 1) {
            return back()->with('error', 'Cannot deactivate the last remaining super admin.');
        }

        $user->update(['is_active' => false]);

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'delete',
            'model_type' => User::class,
            'model_id' => $user->id,
            'ip_address' => $request->ip(),
        ]);

        return redirect()->route('users.index')->with('success', 'User deactivated successfully');
    }

    public function activate(Request $request, User $user)
    {
        $user->update(['is_active' => true]);

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'activate',
            'model_type' => User::class,
            'model_id' => $user->id,
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', 'User activated successfully');
    }
    /**
     * Number of currently active super_admin users (H4 guard).
     */
    private function activeSuperAdminCount(): int
    {
        return User::whereHas('role', function ($query) {
            $query->where('name', 'super_admin');
        })->where('is_active', true)->count();
    }
}
