<?php

namespace Bywyd\LaravelQol;

use Illuminate\Support\ServiceProvider;
use Illuminate\Routing\Router;
use Bywyd\LaravelQol\Middleware\RoleMiddleware;
use Bywyd\LaravelQol\Middleware\PermissionMiddleware;
use Bywyd\LaravelQol\Middleware\RoleOrPermissionMiddleware;
use Bywyd\LaravelQol\Middleware\SetLocale;
use Bywyd\LaravelQol\Middleware\RestrictAccess;
use Bywyd\LaravelQol\Middleware\ForceJsonResponse;
use Bywyd\LaravelQol\Middleware\LogRequestResponse;
use Bywyd\LaravelQol\Middleware\SecurityHeaders;
use Bywyd\LaravelQol\Middleware\RateLimitByUser;
use Bywyd\LaravelQol\Middleware\ConvertEmptyStringsToNull;
use Bywyd\LaravelQol\Middleware\TrimStrings;
use Bywyd\LaravelQol\Middleware\ApiVersioning;
use Bywyd\LaravelQol\Middleware\CorsMiddleware;
use Bywyd\LaravelQol\Console\ServiceMakeCommand;
use Bywyd\LaravelQol\Console\DtoMakeCommand;
use Bywyd\LaravelQol\Console\ActionMakeCommand;
use Bywyd\LaravelQol\Console\ModuleMakeCommand;

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

        // Register Settings Manager
        $this->app->singleton('laravel-qol.settings', function ($app) {
            return new \Bywyd\LaravelQol\Services\SettingsManager();
        });
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

        // Publish views
        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/laravel-qol'),
        ], 'laravel-qol-views');

        // Load views
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'laravel-qol');

        // Load migrations automatically if running in console
        if ($this->app->runningInConsole()) {
            $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        }

        // Register middleware
        $router = $this->app->make(Router::class);
        $router->aliasMiddleware('role', RoleMiddleware::class);
        $router->aliasMiddleware('permission', PermissionMiddleware::class);
        $router->aliasMiddleware('role_or_permission', RoleOrPermissionMiddleware::class);
        $router->aliasMiddleware('locale', SetLocale::class);
        $router->aliasMiddleware('restrict.access', RestrictAccess::class);
        $router->aliasMiddleware('force.json', ForceJsonResponse::class);
        $router->aliasMiddleware('log.request', LogRequestResponse::class);
        $router->aliasMiddleware('security.headers', SecurityHeaders::class);
        $router->aliasMiddleware('rate.limit.user', RateLimitByUser::class);
        $router->aliasMiddleware('convert.empty.strings', ConvertEmptyStringsToNull::class);
        $router->aliasMiddleware('trim.strings', TrimStrings::class);
        $router->aliasMiddleware('api.version', ApiVersioning::class);
        $router->aliasMiddleware('cors', CorsMiddleware::class);

        // Register permissions and blade directives
        if (config('laravel-qol.permissions.enable_blade_directives', true)) {
            PermissionRegistrar::registerBladeDirectives();
        }

        if (config('laravel-qol.permissions.auto_register_permissions_as_gates', true)) {
            PermissionRegistrar::registerPermissions();
        }

        // Register Artisan commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                ServiceMakeCommand::class,
                DtoMakeCommand::class,
                ActionMakeCommand::class,
                ModuleMakeCommand::class,
            ]);
        }
    }
}
