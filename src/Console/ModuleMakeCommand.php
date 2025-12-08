<?php

namespace Bywyd\LaravelQol\Console;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

class ModuleMakeCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:module {name : The name of the module}
                            {--api : Generate API routes and controllers}
                            {--views : Generate views}
                            {--all : Generate all components}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new module with complete structure';

    /**
     * Filesystem instance.
     *
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected $files;

    /**
     * Create a new command instance.
     *
     * @param  \Illuminate\Filesystem\Filesystem  $files
     * @return void
     */
    public function __construct(Filesystem $files)
    {
        parent::__construct();

        $this->files = $files;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $name = $this->argument('name');
        $studlyName = Str::studly($name);
        $kebabName = Str::kebab($name);

        $basePath = app_path("Modules/{$studlyName}");

        if ($this->files->exists($basePath)) {
            $this->error("Module {$studlyName} already exists!");
            return 1;
        }

        // Create module directory structure
        $this->createDirectoryStructure($basePath);

        // Generate files
        $this->generateModel($studlyName, $basePath);
        $this->generateMigration($studlyName, $kebabName);
        $this->generateController($studlyName, $basePath);
        $this->generateService($studlyName, $basePath);
        $this->generateRequest($studlyName, $basePath);
        $this->generateRoutes($studlyName, $basePath, $kebabName);

        if ($this->option('all') || $this->option('views')) {
            $this->generateViews($studlyName, $basePath, $kebabName);
        }

        $this->info("Module {$studlyName} created successfully!");
        $this->newLine();
        $this->comment("Don't forget to:");
        $this->line("1. Run migrations: php artisan migrate");
        $this->line("2. Register routes in your RouteServiceProvider or routes/web.php");
        $this->line("3. Add module namespace to composer.json autoload if needed");

        return 0;
    }

    /**
     * Create directory structure.
     *
     * @param  string  $basePath
     * @return void
     */
    protected function createDirectoryStructure($basePath)
    {
        $directories = [
            'Controllers',
            'Models',
            'Services',
            'Requests',
            'Routes',
            'Views',
            'DTOs',
            'Actions',
        ];

        foreach ($directories as $directory) {
            $this->files->makeDirectory("{$basePath}/{$directory}", 0755, true);
        }
    }

    /**
     * Generate model.
     *
     * @param  string  $name
     * @param  string  $basePath
     * @return void
     */
    protected function generateModel($name, $basePath)
    {
        $stub = $this->files->get(__DIR__.'/stubs/module/model.stub');
        $stub = str_replace('DummyClass', $name, $stub);
        $stub = str_replace('DummyTable', Str::snake(Str::plural($name)), $stub);

        $this->files->put("{$basePath}/Models/{$name}.php", $stub);
        $this->info("Created Model: {$name}");
    }

    /**
     * Generate migration.
     *
     * @param  string  $name
     * @param  string  $kebabName
     * @return void
     */
    protected function generateMigration($name, $kebabName)
    {
        $table = Str::snake(Str::plural($name));
        $timestamp = date('Y_m_d_His');
        
        $this->call('make:migration', [
            'name' => "create_{$table}_table",
        ]);

        $this->info("Created Migration: create_{$table}_table");
    }

    /**
     * Generate controller.
     *
     * @param  string  $name
     * @param  string  $basePath
     * @return void
     */
    protected function generateController($name, $basePath)
    {
        $isApi = $this->option('api') || $this->option('all');
        $stub = $this->files->get(__DIR__.'/stubs/module/controller.'.($isApi ? 'api' : 'web').'.stub');
        
        $stub = str_replace('DummyClass', $name, $stub);
        $stub = str_replace('DummyVariable', Str::camel($name), $stub);
        $stub = str_replace('DummyVariablePlural', Str::camel(Str::plural($name)), $stub);

        $this->files->put("{$basePath}/Controllers/{$name}Controller.php", $stub);
        $this->info("Created Controller: {$name}Controller");
    }

    /**
     * Generate service.
     *
     * @param  string  $name
     * @param  string  $basePath
     * @return void
     */
    protected function generateService($name, $basePath)
    {
        $stub = $this->files->get(__DIR__.'/stubs/module/service.stub');
        $stub = str_replace('DummyClass', $name, $stub);
        $stub = str_replace('DummyVariable', Str::camel($name), $stub);

        $this->files->put("{$basePath}/Services/{$name}Service.php", $stub);
        $this->info("Created Service: {$name}Service");
    }

    /**
     * Generate request.
     *
     * @param  string  $name
     * @param  string  $basePath
     * @return void
     */
    protected function generateRequest($name, $basePath)
    {
        $stub = $this->files->get(__DIR__.'/stubs/module/request.stub');
        $stub = str_replace('DummyClass', $name, $stub);

        $this->files->put("{$basePath}/Requests/{$name}Request.php", $stub);
        $this->info("Created Request: {$name}Request");
    }

    /**
     * Generate routes.
     *
     * @param  string  $name
     * @param  string  $basePath
     * @param  string  $kebabName
     * @return void
     */
    protected function generateRoutes($name, $basePath, $kebabName)
    {
        $isApi = $this->option('api') || $this->option('all');
        $stub = $this->files->get(__DIR__.'/stubs/module/routes.'.($isApi ? 'api' : 'web').'.stub');
        
        $stub = str_replace('DummyClass', $name, $stub);
        $stub = str_replace('DummyRoute', $kebabName, $stub);

        $fileName = $isApi ? 'api.php' : 'web.php';
        $this->files->put("{$basePath}/Routes/{$fileName}", $stub);
        $this->info("Created Routes: {$fileName}");
    }

    /**
     * Generate views.
     *
     * @param  string  $name
     * @param  string  $basePath
     * @param  string  $kebabName
     * @return void
     */
    protected function generateViews($name, $basePath, $kebabName)
    {
        $viewsPath = "{$basePath}/Views";
        $views = ['index', 'create', 'edit', 'show'];

        foreach ($views as $view) {
            $stub = $this->files->get(__DIR__.'/stubs/module/views/'.$view.'.stub');
            $stub = str_replace('DummyClass', $name, $stub);
            $stub = str_replace('DummyRoute', $kebabName, $stub);
            $stub = str_replace('DummyVariable', Str::camel($name), $stub);
            $stub = str_replace('DummyVariablePlural', Str::camel(Str::plural($name)), $stub);

            $this->files->put("{$viewsPath}/{$view}.blade.php", $stub);
        }

        $this->info("Created Views: index, create, edit, show");
    }
}
