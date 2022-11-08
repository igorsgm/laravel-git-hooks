<?php

namespace Igorsgm\GitHooks\Console\Commands;

use Igorsgm\GitHooks\Contracts\HookCommand;
use Igorsgm\GitHooks\Traits\WithCommitMessage;
use Illuminate\Console\Command;

class CommitMessage extends Command implements HookCommand
{
    use WithCommitMessage;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'git-hooks:commit-msg {file}';

    /**
     * The console command description.
     */
    protected $description = 'Run hook commit-msg';

    /**
     * {@inheritDoc}
     */
    public function getHook(): string
    {
        return 'commit-msg';
    }
}
