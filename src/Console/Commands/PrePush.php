<?php

namespace Igorsgm\GitHooks\Console\Commands;

use Igorsgm\GitHooks\Contracts\HookCommand;
use Igorsgm\GitHooks\Exceptions\HookFailException;
use Igorsgm\GitHooks\Git\GitHelper;
use Igorsgm\GitHooks\Git\Log;
use Igorsgm\GitHooks\Traits\WithPipeline;
use Illuminate\Console\Command;

class PrePush extends Command implements HookCommand
{
    use WithPipeline;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'git-hooks:pre-push';

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
