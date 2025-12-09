<?php

namespace Bywyd\LaravelQol;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;

class PermissionRegistrar
{
    /**
     * Register permissions as gates.
     *
     * @return void
     */
    public static function registerPermissions(): void
    {
        // Skip if database doesn't have the permissions table yet
        try {
            if (!\Schema::hasTable('permissions')) {
                return;
            }

            $permissions = \Bywyd\LaravelQol\Models\Permission::all();

            foreach ($permissions as $permission) {
                Gate::define($permission->slug, function ($user) use ($permission) {
                    return $user->hasPermission($permission);
                });
            }
        } catch (\Exception $e) {
            // Silently fail if there's any database issue during boot
            // This can happen during migrations or in testing environments
        }
    }

    /**
     * Register Blade directives.
     *
     * @return void
     */
    public static function registerBladeDirectives(): void
    {
        // @role('admin')
        Blade::if('role', function ($role) {
            return auth()->check() && auth()->user()->hasRole($role);
        });

        // @hasrole('admin')
        Blade::if('hasrole', function ($role) {
            return auth()->check() && auth()->user()->hasRole($role);
        });

        // @hasanyrole(['admin', 'editor'])
        Blade::if('hasanyrole', function ($roles) {
            return auth()->check() && auth()->user()->hasAnyRole($roles);
        });

        // @hasallroles(['admin', 'editor'])
        Blade::if('hasallroles', function ($roles) {
            return auth()->check() && auth()->user()->hasAllRoles($roles);
        });

        // @permission('edit-posts')
        Blade::if('permission', function ($permission) {
            return auth()->check() && auth()->user()->hasPermission($permission);
        });

        // @haspermission('edit-posts')
        Blade::if('haspermission', function ($permission) {
            return auth()->check() && auth()->user()->hasPermission($permission);
        });

        // @hasanypermission(['edit-posts', 'delete-posts'])
        Blade::if('hasanypermission', function ($permissions) {
            return auth()->check() && auth()->user()->hasAnyPermission($permissions);
        });

        // @hasallpermissions(['edit-posts', 'delete-posts'])
        Blade::if('hasallpermissions', function ($permissions) {
            return auth()->check() && auth()->user()->hasAllPermissions($permissions);
        });
    }
}
