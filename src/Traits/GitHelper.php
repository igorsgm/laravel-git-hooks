<?php

namespace Igorsgm\GitHooks\Traits;

use Symfony\Component\Process\Exception\ProcessFailedException;

trait GitHelper
{
    use ProcessHelper;

    /**
     * @return string
     */
    public function getListOfChangedFiles()
    {
        return $this->runCommandAndGetOutput('git status --short');
    }

    /**
     * @return string
     */
    public function getLastCommitFromLog()
    {
        return $this->runCommandAndGetOutput('git log -1 HEAD');
    }

    /**
     * @param  string|array  $commands
     * @return string
     */
    private function runCommandAndGetOutput($commands)
    {
        $process = $this->runCommands($commands);

        if (! $process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        return $process->getOutput();
    }

    /**
     * Get commit message content form local file
     *
     * @param  string  $filePath
     * @return string
     */
    public function getCommitMessageContentFromFile(string $filePath): string
    {
        return file_get_contents($filePath);
    }

    /**
     * Update commit message in local file
     *
     * @param  string  $path
     * @param  string  $message
     */
    public function updateCommitMessageContentInFile(string $path, string $message): void
    {
        file_put_contents($path, $message);
    }
}
