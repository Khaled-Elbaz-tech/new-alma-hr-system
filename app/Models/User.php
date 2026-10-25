<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'is_active',
        'last_login',
        'must_change_password',   // H8: forced reset flag for imported accounts
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'last_login' => 'datetime',
            'must_change_password' => 'boolean',   // H8
        ];
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function employee()
    {
        return $this->hasOne(Employee::class);
    }

    public function managedDepartment()
    {
        return $this->hasOne(Department::class, 'manager_id');
    }

    /**
     * Projects the user is associated with
     */
    public function projects()
    {
        return $this->belongsToMany(Project::class, 'project_user');
    }

    // Helper methods for role checking
    public function isSuperAdmin(): bool
    {
        return $this->role && $this->role->name === 'super_admin';
    }

    public function isHrManager(): bool
    {
        return $this->role && $this->role->name === 'hr_manager';
    }

    public function isManager(): bool
    {
        return $this->role && $this->role->name === 'manager';
    }

    public function isDM(): bool
    {
        return $this->role && $this->role->name === 'dm';
    }

    public function isEmployee(): bool
    {
        return $this->role && $this->role->name === 'employee';
    }

    public function hasRole(string $roleName): bool
    {
        return $this->role && $this->role->name === $roleName;
    }

    public function hasAnyRole(array $roleNames): bool
    {
        return $this->role && in_array($this->role->name, $roleNames);
    }

    public function canAccessDashboard(): bool
    {
        return $this->role && in_array($this->role->name, ['super_admin', 'hr_manager', 'manager', 'dm', 'employee']);
    }

}
