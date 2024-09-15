<?php

namespace Igorsgm\GitHooks\Console\Commands;

use Igorsgm\GitHooks\Contracts\HookCommand;
use Igorsgm\GitHooks\Traits\WithCommitLog;
use Illuminate\Console\Command;

class PrePush extends Command implements HookCommand
{
    use WithCommitLog;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'git-hooks:pre-push {remote?} {url?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run hook pre-push';

    /**
     * {@inheritDoc}
     */
    public function getHook(): string
    {
        return 'pre-push';
    }
}
