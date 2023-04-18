<?php

namespace Igorsgm\GitHooks\Console\Commands;

use Igorsgm\GitHooks\Facades\GitHooks;
use Illuminate\Console\Concerns\CreatesMatchingTest;
use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class MakeHook extends GeneratorCommand
{
    use CreatesMatchingTest;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'git-hooks:make';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new Hook';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Git Hook';

    /**
     * Execute the console command.
     *
     * @return bool|null
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function handle()
    {
        $supportedHooks = GitHooks::getSupportedHooks();

        if (! in_array($this->argument('hookType'), $supportedHooks)) {
            $this->getOutput()->writeln(sprintf(
                '<bg=red;fg=white> ERROR </> Invalid hook type. Valid types are: <comment>%s</comment>',
                implode(', ', $supportedHooks)
            ));

            return 1;
        }

        return parent::handle();
    }

    /**
     * Replace the class name for the given stub.
     *
     * @param  string  $stub
     * @param  string  $name
     * @return string
     */
    protected function replaceClass($stub, $name)
    {
        $stub = parent::replaceClass($stub, $name);

        $hookName = Str::of($name)->classBasename()->snake()->replace('_', ' ')->title()->value();

        return str_replace(['{{ hookName }}'], $hookName, $stub);
    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        $relativePath = '/stubs/'.$this->argument('hookType').'-console.stub';

        return file_exists($customPath = $this->laravel->basePath(trim($relativePath, '/')))
            ? $customPath
            : __DIR__.$relativePath;
    }

    /**
     * Get the default namespace for the class.
     *
     * @param  string  $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace.'\Console\GitHooks';
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['hookType', InputArgument::REQUIRED, 'The type of the Git Hook', null, GitHooks::getSupportedHooks()],
            ['name', InputArgument::REQUIRED, 'The name of the Git Hook'],
        ];
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['force', 'f', InputOption::VALUE_NONE, 'Create the class even if the Git Hook already exists'],
        ];
    }

    /**
     * Prompt for missing input arguments using the returned questions.
     *
     * @return array
     */
    protected function promptForMissingArgumentsUsing()
    {
        $supportedHooks = implode(', ', GitHooks::getSupportedHooks());

        return [
            'name' => 'What should the '.strtolower($this->type).' be named?',
            'hookType' => 'What type of the '.strtolower($this->type)."? Possible values: ($supportedHooks)",
        ];
    }
}
