<?php

namespace Bywyd\LaravelQol\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Permission extends Model
{
    protected $table = 'permissions';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'group',
    ];

    /**
     * Get the roles that have this permission.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            Role::class,
            'role_permission',
            'permission_id',
            'role_id'
        )->withTimestamps();
    }

    /**
     * Get the users that have this permission directly.
     */
    public function users(): BelongsToMany
    {
        $userModel = config('auth.providers.users.model');
        
        return $this->belongsToMany(
            $userModel,
            'user_permission',
            'permission_id',
            'user_id'
        )->withTimestamps();
    }

    /**
     * Check if this is a wildcard permission.
     *
     * @return bool
     */
    public function isWildcard(): bool
    {
        return $this->slug === '*';
    }

    /**
     * Scope to filter by group.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $group
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByGroup($query, string $group)
    {
        return $query->where('group', $group);
    }

    /**
     * Get all permissions grouped by group.
     *
     * @return \Illuminate\Support\Collection
     */
    public static function getAllGrouped()
    {
        return static::all()->groupBy('group');
    }
}
