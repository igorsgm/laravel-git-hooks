<?php

namespace Igorsgm\GitHooks\Traits;

use Igorsgm\GitHooks\Exceptions\HookFailException;
use Igorsgm\GitHooks\Git\GitHelper;
use Igorsgm\GitHooks\Git\Log;

trait WithCommitLog
{
    use WithPipeline;

    /**
     * Execute the console command.
     *
     * @return int|void
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
