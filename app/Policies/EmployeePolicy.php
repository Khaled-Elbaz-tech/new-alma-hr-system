<?php

namespace App\Policies;

use Illuminate\Auth\Access\Response;
use App\Models\Employee;
use App\Models\User;

class EmployeePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isHrManager() || $user->isManager() || $user->isDM();
    }

    public function view(User $user, Employee $employee): bool
    {
        if ($user->isSuperAdmin() || $user->isHrManager()) {
            return true;
        }
        
        if ($user->isManager() || $user->isDM()) {
            $managedDepartment = $user->managedDepartment;
            return $managedDepartment && $employee->department_id == $managedDepartment->id;
        }
        
        if ($user->isEmployee()) {
            return $employee->user_id == $user->id;
        }
        
        return false;
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isHrManager();
    }

    public function update(User $user, Employee $employee): bool
    {
        return $user->isSuperAdmin() || $user->isHrManager();
    }

    public function delete(User $user, Employee $employee): bool
    {
        return $user->isSuperAdmin();
    }

    public function restore(User $user, Employee $employee): bool
    {
        return $user->isSuperAdmin();
    }

    public function forceDelete(User $user, Employee $employee): bool
    {
        return $user->isSuperAdmin();
    }
}