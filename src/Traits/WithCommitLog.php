<?php

declare(strict_types=1);

namespace Igorsgm\GitHooks\Traits;

use Igorsgm\GitHooks\Exceptions\HookFailException;
use Igorsgm\GitHooks\Facades\GitHooks;
use Igorsgm\GitHooks\Git\Log;

trait WithCommitLog
{
    use WithPipeline;

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        try {
            $this->sendLogCommitThroughHooks(
                new Log(
                    GitHooks::getLastCommitFromLog()
                )
            );
        } catch (HookFailException) {
            return 1;
        }

        return 0;
    }

    /**
     * Send the log commit through the pipes
     */
    protected function sendLogCommitThroughHooks(Log $log): void
    {
        $this->makePipeline()
            ->send($log)
            ->thenReturn();
    }
}
