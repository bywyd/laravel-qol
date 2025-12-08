<?php

namespace Bywyd\LaravelQol\Console;

use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Input\InputOption;

class DtoMakeCommand extends GeneratorCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'make:dto';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new DTO (Data Transfer Object) class';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'DTO';

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        if ($this->option('request')) {
            return __DIR__.'/stubs/dto.request.stub';
        }

        if ($this->option('array')) {
            return __DIR__.'/stubs/dto.array.stub';
        }

        return __DIR__.'/stubs/dto.stub';
    }

    /**
     * Get the default namespace for the class.
     *
     * @param  string  $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace.'\DTOs';
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['request', 'r', InputOption::VALUE_NONE, 'Generate a DTO with from request method'],
            ['array', 'a', InputOption::VALUE_NONE, 'Generate a DTO with array conversion methods'],
        ];
    }
}
