<?php

declare(strict_types=1);

namespace Igorsgm\GitHooks\Traits;

use Closure;
use Igorsgm\GitHooks\Exceptions\HookFailException;
use Igorsgm\GitHooks\Facades\GitHooks;
use Igorsgm\GitHooks\Git\ChangedFiles;
use Igorsgm\GitHooks\Git\CommitMessage;

trait WithCommitMessage
{
    use WithPipeline;

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        try {
            $message = GitHooks::getCommitMessageContentFromFile(
                $this->getMessagePath()
            );

            $this->sendMessageThroughHooks(
                new CommitMessage(
                    $message,
                    new ChangedFiles(
                        GitHooks::getListOfChangedFiles()
                    )
                )
            );
        } catch (HookFailException) {
            return 1;
        }

        return 0;
    }

    /**
     * Send the given message from .git/COMMIT_MESSAGE through the pipes
     */
    protected function sendMessageThroughHooks(CommitMessage $message): void
    {
        $this->makePipeline()
            ->send($message)
            ->then($this->storeMessage());
    }

    /**
     * Store prepared message
     */
    protected function storeMessage(): Closure
    {
        return function (CommitMessage $message): void {
            GitHooks::updateCommitMessageContentInFile(
                $this->getMessagePath(),
                (string) $message
            );
        };
    }

    /**
     * Get the git message path (By default .git/COMMIT_MESSAGE)
     */
    private function getMessagePath(): string
    {
        $file = $this->argument('file');

        if (!is_string($file)) {
            throw new HookFailException('Invalid file argument provided');
        }

        return base_path($file);
    }
}
