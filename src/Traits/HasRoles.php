<?php

namespace Bywyd\LaravelQol\Traits;

use Bywyd\LaravelQol\Models\Role;
use Bywyd\LaravelQol\Models\Permission;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;

trait HasRoles
{
    /**
     * Get the roles for this user.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            Role::class,
            'user_role',
            'user_id',
            'role_id'
        )->withTimestamps();
    }

    /**
     * Get the permissions directly assigned to this user.
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(
            Permission::class,
            'user_permission',
            'user_id',
            'permission_id'
        )->withTimestamps();
    }

    /**
     * Check if user has a role.
     *
     * @param string|array|Role $role
     * @return bool
     */
    public function hasRole($role): bool
    {
        if (is_array($role)) {
            foreach ($role as $r) {
                if ($this->hasRole($r)) {
                    return true;
                }
            }
            return false;
        }

        if (is_string($role)) {
            return $this->roles()->where('slug', $role)->exists();
        }

        return $this->roles()->where('id', $role->id)->exists();
    }

    /**
     * Check if user has all roles.
     *
     * @param array $roles
     * @return bool
     */
    public function hasAllRoles(array $roles): bool
    {
        foreach ($roles as $role) {
            if (!$this->hasRole($role)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if user has any of the given roles.
     *
     * @param array $roles
     * @return bool
     */
    public function hasAnyRole(array $roles): bool
    {
        return $this->hasRole($roles);
    }

    /**
     * Assign role to user.
     *
     * @param string|array|Role $role
     * @return $this
     */
    public function assignRole($role): self
    {
        $roles = is_array($role) ? $role : [$role];

        foreach ($roles as $r) {
            if (is_string($r)) {
                $r = Role::where('slug', $r)->first();
            }

            if ($r && !$this->hasRole($r)) {
                $this->roles()->attach($r->id);
            }
        }

        return $this;
    }

    /**
     * Remove role from user.
     *
     * @param string|array|Role $role
     * @return $this
     */
    public function removeRole($role): self
    {
        $roles = is_array($role) ? $role : [$role];

        foreach ($roles as $r) {
            if (is_string($r)) {
                $r = Role::where('slug', $r)->first();
            }

            if ($r) {
                $this->roles()->detach($r->id);
            }
        }

        return $this;
    }

    /**
     * Sync roles for user.
     *
     * @param array $roles
     * @return $this
     */
    public function syncRoles(array $roles): self
    {
        $roleIds = [];

        foreach ($roles as $role) {
            if (is_string($role)) {
                $r = Role::where('slug', $role)->first();
                if ($r) {
                    $roleIds[] = $r->id;
                }
            } elseif ($role instanceof Role) {
                $roleIds[] = $role->id;
            } else {
                $roleIds[] = $role;
            }
        }

        $this->roles()->sync($roleIds);

        return $this;
    }

    /**
     * Check if user has a permission.
     *
     * @param string|array|Permission $permission
     * @return bool
     */
    public function hasPermission($permission): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        if (is_array($permission)) {
            foreach ($permission as $p) {
                if ($this->hasPermission($p)) {
                    return true;
                }
            }
            return false;
        }

        // Check direct permissions
        if (is_string($permission)) {
            if ($this->permissions()->where('slug', $permission)->exists()) {
                return true;
            }

            // Check wildcard
            if ($this->permissions()->where('slug', '*')->exists()) {
                return true;
            }

            // Check permissions from roles
            return $this->hasPermissionViaRole($permission);
        }

        if ($this->permissions()->where('id', $permission->id)->exists()) {
            return true;
        }

        return $this->hasPermissionViaRole($permission);
    }

    /**
     * Check if user has permission via any of their roles.
     *
     * @param string|Permission $permission
     * @return bool
     */
    protected function hasPermissionViaRole($permission): bool
    {
        foreach ($this->roles as $role) {
            if ($role->hasPermission($permission)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if user has all permissions.
     *
     * @param array $permissions
     * @return bool
     */
    public function hasAllPermissions(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if (!$this->hasPermission($permission)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if user has any of the given permissions.
     *
     * @param array $permissions
     * @return bool
     */
    public function hasAnyPermission(array $permissions): bool
    {
        return $this->hasPermission($permissions);
    }

    /**
     * Give permission directly to user.
     *
     * @param string|array|Permission $permission
     * @return $this
     */
    public function givePermission($permission): self
    {
        $permissions = is_array($permission) ? $permission : [$permission];

        foreach ($permissions as $perm) {
            if (is_string($perm)) {
                $perm = Permission::where('slug', $perm)->first();
            }

            if ($perm && !$this->permissions()->where('id', $perm->id)->exists()) {
                $this->permissions()->attach($perm->id);
            }
        }

        return $this;
    }

    /**
     * Revoke permission from user.
     *
     * @param string|array|Permission $permission
     * @return $this
     */
    public function revokePermission($permission): self
    {
        $permissions = is_array($permission) ? $permission : [$permission];

        foreach ($permissions as $perm) {
            if (is_string($perm)) {
                $perm = Permission::where('slug', $perm)->first();
            }

            if ($perm) {
                $this->permissions()->detach($perm->id);
            }
        }

        return $this;
    }

    /**
     * Sync permissions for user.
     *
     * @param array $permissions
     * @return $this
     */
    public function syncPermissions(array $permissions): self
    {
        $permissionIds = [];

        foreach ($permissions as $permission) {
            if (is_string($permission)) {
                $perm = Permission::where('slug', $permission)->first();
                if ($perm) {
                    $permissionIds[] = $perm->id;
                }
            } elseif ($permission instanceof Permission) {
                $permissionIds[] = $permission->id;
            } else {
                $permissionIds[] = $permission;
            }
        }

        $this->permissions()->sync($permissionIds);

        return $this;
    }

    /**
     * Get all permissions (direct and from roles).
     *
     * @return Collection
     */
    public function getAllPermissions(): Collection
    {
        $permissions = $this->permissions;

        foreach ($this->roles as $role) {
            $permissions = $permissions->merge($role->permissions);
        }

        return $permissions->unique('id');
    }

    /**
     * Check if user is super admin.
     *
     * @return bool
     */
    public function isSuperAdmin(): bool
    {
        return $this->hasRole('super-admin') || 
               $this->permissions()->where('slug', '*')->exists() ||
               $this->roles()->whereHas('permissions', function($query) {
                   $query->where('slug', '*');
               })->exists();
    }

    /**
     * Scope to filter users by role.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string|array|Role $role
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeRole($query, $role)
    {
        if (is_array($role)) {
            return $query->whereHas('roles', function($q) use ($role) {
                $q->whereIn('slug', $role);
            });
        }

        if (is_string($role)) {
            return $query->whereHas('roles', function($q) use ($role) {
                $q->where('slug', $role);
            });
        }

        return $query->whereHas('roles', function($q) use ($role) {
            $q->where('id', $role->id);
        });
    }

    /**
     * Scope to filter users by permission.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string|array|Permission $permission
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePermission($query, $permission)
    {
        if (is_array($permission)) {
            return $query->where(function($q) use ($permission) {
                $q->whereHas('permissions', function($query) use ($permission) {
                    $query->whereIn('slug', $permission);
                })->orWhereHas('roles.permissions', function($query) use ($permission) {
                    $query->whereIn('slug', $permission);
                });
            });
        }

        if (is_string($permission)) {
            return $query->where(function($q) use ($permission) {
                $q->whereHas('permissions', function($query) use ($permission) {
                    $query->where('slug', $permission);
                })->orWhereHas('roles.permissions', function($query) use ($permission) {
                    $query->where('slug', $permission);
                });
            });
        }

        return $query->where(function($q) use ($permission) {
            $q->whereHas('permissions', function($query) use ($permission) {
                $query->where('id', $permission->id);
            })->orWhereHas('roles.permissions', function($query) use ($permission) {
                $query->where('id', $permission->id);
            });
        });
    }
}
