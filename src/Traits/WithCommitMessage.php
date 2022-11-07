<?php

namespace Igorsgm\GitHooks\Traits;

use Closure;
use Igorsgm\GitHooks\Contracts\CommitMessageStorage;
use Igorsgm\GitHooks\Exceptions\HookFailException;
use Igorsgm\GitHooks\Git\ChangedFiles;
use Igorsgm\GitHooks\Git\CommitMessage;
use Igorsgm\GitHooks\Git\GetListOfChangedFiles;
use Illuminate\Contracts\Config\Repository;

trait WithCommitMessage
{
    use WithPipeline;

    /**
     * @var Repository
     */
    protected $config;

    /**
     * @var CommitMessageStorage
     */
    protected $messageStorage;

    /**
     * Execute the console command.
     *
     * @param  GetListOfChangedFiles  $command
     */
    public function handle(GetListOfChangedFiles $command)
    {
        $file = $this->argument('file');

        $message = $this->messageStorage->get(
            $this->getLaravel()->basePath($file)
        );

        try {
            $this->sendMessageThroughHooks(
                new CommitMessage(
                    $message,
                    new ChangedFiles(
                        $command->exec()->getOutput()
                    )
                )
            );
        } catch (HookFailException $e) {
            return 1;
        }
    }

    /**
     * Get the git message path (By default .git/COMMIT_MESSAGE)
     *
     * @return string
     */
    private function getMessagePath(): string
    {
        $file = $this->argument('file');

        return $this->getLaravel()->basePath($file);
    }

    /**
     * Send the given message from .git/COMMIT_MESSAGE through the pipes
     *
     * @param  CommitMessage  $message
     */
    protected function sendMessageThroughHooks(CommitMessage $message): void
    {
        $this->makePipeline()
            ->send($message)
            ->then($this->storeMessage());
    }

    /**
     * Store prepared message
     *
     * @return Closure
     */
    protected function storeMessage(): Closure
    {
        return function (CommitMessage $message) {
            $this->messageStorage->update(
                $this->getMessagePath(),
                (string) $message
            );
        };
    }
}
