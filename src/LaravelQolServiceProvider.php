<?php

namespace Bywyd\LaravelQol;

use Illuminate\Support\ServiceProvider;
use Illuminate\Routing\Router;
use Bywyd\LaravelQol\Middleware\RoleMiddleware;
use Bywyd\LaravelQol\Middleware\PermissionMiddleware;
use Bywyd\LaravelQol\Middleware\RoleOrPermissionMiddleware;

class LaravelQolServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/laravel-qol.php',
            'laravel-qol'
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Publish configuration
        $this->publishes([
            __DIR__.'/../config/laravel-qol.php' => config_path('laravel-qol.php'),
        ], 'laravel-qol-config');

        // Publish migrations
        $this->publishes([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ], 'laravel-qol-migrations');

        // Load migrations automatically if running in console
        if ($this->app->runningInConsole()) {
            $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        }

        // Register middleware
        $router = $this->app->make(Router::class);
        $router->aliasMiddleware('role', RoleMiddleware::class);
        $router->aliasMiddleware('permission', PermissionMiddleware::class);
        $router->aliasMiddleware('role_or_permission', RoleOrPermissionMiddleware::class);

        // Register permissions and blade directives
        if (config('laravel-qol.permissions.enable_blade_directives', true)) {
            PermissionRegistrar::registerBladeDirectives();
        }

        if (config('laravel-qol.permissions.auto_register_permissions_as_gates', true)) {
            PermissionRegistrar::registerPermissions();
        }
    }
}
