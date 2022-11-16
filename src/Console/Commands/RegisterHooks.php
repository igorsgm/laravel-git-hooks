<?php

namespace Igorsgm\GitHooks\Console\Commands;

use Exception;
use Igorsgm\GitHooks\GitHooks;
use Illuminate\Console\Command;

class RegisterHooks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'git-hooks:register';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Register or re-register git hooks for application';

    /**
     * Execute the console command.
     *
     * @param  GitHooks  $gitHooks
     * @return void
     *
     * @throws Exception
     */
    public function handle(GitHooks $gitHooks)
    {
        $availableHooks = $gitHooks->getAvailableHooks();

        foreach ($availableHooks as $hook) {
            $gitHooks->install($hook);
        }

        $this->info('Git hooks have been successfully installed.');
    }
}
