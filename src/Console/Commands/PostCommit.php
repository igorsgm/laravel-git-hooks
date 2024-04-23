<?php

declare(strict_types=1);

namespace Igorsgm\GitHooks\Console\Commands;

use Igorsgm\GitHooks\Contracts\HookCommand;
use Igorsgm\GitHooks\Traits\WithCommitLog;
use Illuminate\Console\Command;

class PostCommit extends Command implements HookCommand
{
    use WithCommitLog;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'git-hooks:post-commit';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run hook post-commit';

    public function getHook(): string
    {
        return 'post-commit';
    }
}
