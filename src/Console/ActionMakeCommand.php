<?php

namespace Bywyd\LaravelQol\Console;

use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Input\InputOption;

class ActionMakeCommand extends GeneratorCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'make:action';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new action class';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Action';

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        if ($this->option('job')) {
            return __DIR__.'/stubs/action.job.stub';
        }

        if ($this->option('invokable')) {
            return __DIR__.'/stubs/action.invokable.stub';
        }

        return __DIR__.'/stubs/action.stub';
    }

    /**
     * Get the default namespace for the class.
     *
     * @param  string  $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace.'\Actions';
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['job', 'j', InputOption::VALUE_NONE, 'Generate an action that can be dispatched as a job'],
            ['invokable', 'i', InputOption::VALUE_NONE, 'Generate an invokable action class'],
        ];
    }
}
