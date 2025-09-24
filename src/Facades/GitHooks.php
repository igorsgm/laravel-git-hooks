<?php

declare(strict_types=1);

namespace Igorsgm\GitHooks\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Igorsgm\GitHooks\GitHooks
 *
 * @method static array<string> getSupportedHooks()
 * @method static array<string> getAvailableHooks()
 * @method static void install()
 * @method static ?string getHookStub()
 * @method static string getGitHooksDir()
 * @method static string getListOfChangedFiles()
 * @method static string getLastCommitFromLog()
 * @method static string getCommitMessageContentFromFile(string $filePath)
 * @method static void updateCommitMessageContentInFile(string $path, string $message)
 * @method static bool isMergeInProgress()
 */
class GitHooks extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'laravel-git-hooks';
    }
}
