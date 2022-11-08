<?php

namespace Igorsgm\GitHooks\Console\Commands;

use Igorsgm\GitHooks\Contracts\HookCommand;
use Igorsgm\GitHooks\Exceptions\HookFailException;
use Igorsgm\GitHooks\Git\GitHelper;
use Igorsgm\GitHooks\Git\Log;
use Igorsgm\GitHooks\Traits\WithPipeline;
use Illuminate\Console\Command;

class PostCommit extends Command implements HookCommand
{
    use WithPipeline;

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

    /**
     * {@inheritDoc}
     */
    public function getHook(): string
    {
        return 'post-commit';
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        try {
            $this->sendLogCommitThroughHooks(
                new Log(
                    GitHelper::getLastCommitFromLog()
                )
            );
        } catch (HookFailException $e) {
            return 1;
        }
    }

    /**
     * Send the log commit through the pipes
     *
     * @param  Log  $log
     */
    protected function sendLogCommitThroughHooks(Log $log): void
    {
        $this->makePipeline()
            ->send($log)
            ->thenReturn();
    }
}
