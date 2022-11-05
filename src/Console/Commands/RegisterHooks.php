<?php

namespace Igorsgm\LaravelGitHooks\Console\Commands;

use Igorsgm\LaravelGitHooks\Contracts\Configurator;
use Illuminate\Console\Command;

class RegisterHooks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'git-hooks:register-hooks';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Register git hooks for application';

    /**
     * Execute the console command.
     *
     * @param  Configurator  $configurator
     */
    public function handle(Configurator $configurator)
    {
        $configurator->run();

        $this->info('Git hooks have been successfully created');
    }
}
