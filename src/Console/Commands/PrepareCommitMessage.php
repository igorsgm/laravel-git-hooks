<?php

namespace Igorsgm\GitHooks\Console\Commands;

use Igorsgm\GitHooks\Contracts\HookCommand;
use Igorsgm\GitHooks\Traits\WithCommitMessage;
use Illuminate\Console\Command;

class PrepareCommitMessage extends Command implements HookCommand
{
    use WithCommitMessage;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'git-hooks:prepare-commit-msg {file} {type?} {sha?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run hook prepare-commit-msg';

    /**
     * Get hook name
     */
    public function getHook(): string
    {
        return 'prepare-commit-msg';
    }
}
