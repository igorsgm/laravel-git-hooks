<?php

namespace Igorsgm\GitHooks\Console\Commands;

use Igorsgm\GitHooks\GitHooks;
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
     * @param  GitHooks  $gitHooks
     */
    public function handle(GitHooks $gitHooks)
    {
        $gitHooks->run();

        $this->info('Git hooks have been successfully created');
    }
}
