<?php

namespace Bywyd\LaravelQol;

use Illuminate\Support\ServiceProvider;

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
    }
}
