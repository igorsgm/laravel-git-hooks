<?php

namespace Igorsgm\GitHooks\Git;

use Igorsgm\GitHooks\Traits\ProcessHelper;
use Symfony\Component\Process\Exception\ProcessFailedException;

class GitHelper
{
    use ProcessHelper;

    /**
     * @return string
     */
    public static function getListOfChangedFiles()
    {
        return self::execAndGetCommandOutput('git status --short');
    }

    /**
     * @return string
     */
    public static function getLastCommitFromLog()
    {
        return self::execAndGetCommandOutput('git log -1 HEAD');
    }

    /**
     * @param  string|array  $commands
     * @return string
     */
    private static function execAndGetCommandOutput($commands)
    {
        $process = self::execCommands($commands);

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
    public static function getCommitMessageContentFromFile(string $filePath): string
    {
        return file_get_contents($filePath);
    }

    /**
     * Update commit message in local file
     *
     * @param  string  $path
     * @param  string  $message
     */
    public static function updateCommitMessageContentInFile(string $path, string $message): void
    {
        file_put_contents($path, $message);
    }
}
