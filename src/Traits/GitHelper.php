<?php

namespace Igorsgm\GitHooks\Traits;

use Symfony\Component\Process\Exception\ProcessFailedException;

trait GitHelper
{
    use ProcessHelper;

    public function getListOfChangedFiles(): string
    {
        return $this->runCommandAndGetOutput('git status --short');
    }

    public function getLastCommitFromLog(): string
    {
        return $this->runCommandAndGetOutput('git log -1 HEAD');
    }

    /**
     * @param  string|array<int, string>  $commands
     */
    private function runCommandAndGetOutput(string|array $commands): string
    {
        $process = $this->runCommands($commands);

        if (! $process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        return $process->getOutput();
    }

    /**
     * Get commit message content form local file
     */
    public function getCommitMessageContentFromFile(string $filePath): string
    {
        return file_get_contents($filePath);
    }

    /**
     * Update commit message in local file
     */
    public function updateCommitMessageContentInFile(string $path, string $message): void
    {
        file_put_contents($path, $message);
    }

    /**
     * @read https://stackoverflow.com/questions/30733415/how-to-determine-if-git-merge-is-in-process#answer-30781568
     */
    public function isMergeInProgress(): bool
    {
        $command = $this->runCommands('git merge HEAD', [
            'silent' => true,
        ]);

        // If a merge is in progress, the process returns code 128
        return $command->getExitCode() === 128;
    }
}
