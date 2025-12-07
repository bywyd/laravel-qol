<?php

namespace Bywyd\LaravelQol\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    protected $table = 'roles';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'level',
        'is_default',
    ];

    protected $casts = [
        'level' => 'integer',
        'is_default' => 'boolean',
    ];

    /**
     * Get the permissions for this role.
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(
            Permission::class,
            'role_permission',
            'role_id',
            'permission_id'
        )->withTimestamps();
    }

    /**
     * Get the users with this role.
     */
    public function users(): BelongsToMany
    {
        $userModel = config('auth.providers.users.model');
        
        return $this->belongsToMany(
            $userModel,
            'user_role',
            'role_id',
            'user_id'
        )->withTimestamps();
    }

    /**
     * Check if role has a permission.
     *
     * @param string|Permission $permission
     * @return bool
     */
    public function hasPermission($permission): bool
    {
        if (is_string($permission)) {
            return $this->permissions()->where('slug', $permission)->exists();
        }

        return $this->permissions()->where('id', $permission->id)->exists();
    }

    /**
     * Give permission to role.
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

            if ($perm && !$this->hasPermission($perm)) {
                $this->permissions()->attach($perm->id);
            }
        }

        return $this;
    }

    /**
     * Revoke permission from role.
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
     * Sync permissions for role.
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
     * Check if this is a super admin role.
     *
     * @return bool
     */
    public function isSuperAdmin(): bool
    {
        return $this->slug === 'super-admin' || $this->hasPermission('*');
    }

    /**
     * Scope to get default role.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    /**
     * Scope to order by level.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $direction
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByLevel($query, string $direction = 'asc')
    {
        return $query->orderBy('level', $direction);
    }
}
