<?php

namespace Igorsgm\LaravelGitHooks\Console\Commands;

use Igorsgm\LaravelGitHooks\LaravelGitHooks;
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
     * @param  LaravelGitHooks  $laravelGitHooks
     */
    public function handle(LaravelGitHooks $laravelGitHooks)
    {
        $laravelGitHooks->run();

        $this->info('Git hooks have been successfully created');
    }
}
