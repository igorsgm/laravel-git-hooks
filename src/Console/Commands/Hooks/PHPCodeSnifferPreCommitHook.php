<?php

namespace Igorsgm\GitHooks\Console\Commands\Hooks;

use Closure;
use Igorsgm\GitHooks\Contracts\PreCommitHook;
use Igorsgm\GitHooks\Exceptions\HookFailException;
use Igorsgm\GitHooks\Facades\GitHooks;
use Igorsgm\GitHooks\Git\ChangedFiles;
use Igorsgm\GitHooks\Traits\ProcessHelper;
use Illuminate\Console\Command;
use Symfony\Component\Console\Terminal;

class PHPCodeSnifferPreCommitHook implements PreCommitHook
{
    use ProcessHelper;

    /**
     * Command instance that is bound automatically by Hooks Pipeline, so it can be used inside the Hook.
     *
     * @var Command
     */
    public $command;

    /**
     * @var string
     */
    private $phpCSExecutable;

    /**
     * @var string
     */
    private $phpCBFExecutable;

    /**
     * @var array
     */
    private $filesBadlyFormattedPaths = [];

    /**
     * Create a new console command instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->setCwd(base_path());
        $this->phpCSExecutable = './'.trim(config('git-hooks.code_analyzers.php_code_sniffer.phpcs_path'), '/');
        $this->phpCBFExecutable = './'.trim(config('git-hooks.code_analyzers.php_code_sniffer.phpcbf_path'), '/');
    }

    public function getName(): ?string
    {
        return 'PHP_CodeSniffer';
    }

    public function handle(ChangedFiles $files, Closure $next)
    {
        $commitFiles = $files->getAddedToCommit();

        if ($commitFiles->isEmpty() || GitHooks::isMergeInProgress()) {
            return $next($files);
        }

        $this->validatePhpCsInstallation();

        foreach ($commitFiles as $file) {
            if ($file->extension() !== 'php') {
                continue;
            }

            $filePath = $file->getFilePath();
            $isPhpCSProperlyFormatted = $this->runCommands(
                implode(' ', [
                    $this->phpCSExecutable,
                    $this->getPhpCSStandardParam(),
                    $filePath,
                ]))->isSuccessful();

            if (! $isPhpCSProperlyFormatted) {
                if (empty($this->filesBadlyFormattedPaths)) {
                    $this->command->newLine();
                }

                $this->command->getOutput()->writeln(
                    sprintf('<fg=red> %s Failed:</> %s', $this->getName(), $filePath)
                );
                $this->filesBadlyFormattedPaths[] = $filePath;
            }
        }

        if (empty($this->filesBadlyFormattedPaths)) {
            return $next($files);
        }

        $this->command->newLine();
        $this->command->getOutput()->writeln(
            '<bg=red;fg=white> COMMIT FAILED </> ' .
                sprintf('Your commit contains files that should pass %s but do not. Please fix the %s errors in the files above and try again.', $this->getName(), $this->getName())
        );

        $this->suggestAutoFixOrExit();
    }

    private function getPhpCSStandardParam(): string
    {
        $phpCSStandard = trim(config('git-hooks.code_analyzers.php_code_sniffer.standard'), '/');
        return empty($phpCSStandard) ? '' : '--standard='.$phpCSStandard;
    }

    /**
     * @return void
     *
     * @throws HookFailException
     */
    private function validatePhpCsInstallation()
    {
        $isPhpCSInstalled = file_exists(base_path(config('git-hooks.code_analyzers.php_code_sniffer.phpcs_path')));

        if ($isPhpCSInstalled) {
            return;
        }

        $this->command->newLine(2);
        $this->command->getOutput()->writeln(
            sprintf('<bg=red;fg=white> ERROR </> %s',
                'Php_CodeSniffer is not installed. Please run <info>composer require squizlabs/php_codesniffer --dev</info> to install it.')
        );
        $this->command->newLine();
        throw new HookFailException();
    }

    /**
     * @return void
     *
     * @throws HookFailException
     */
    private function suggestAutoFixOrExit()
    {
        if (Terminal::hasSttyAvailable() &&
            $this->command->confirm('Would you like to attempt to correct files automagically?', false)
        ) {
            $errorFilesString = implode(' ', $this->filesBadlyFormattedPaths);
            $this->runCommands([
                implode(' ', [
                    $this->phpCBFExecutable,
                    $this->getPhpCSStandardParam(),
                    $errorFilesString,
                ]),
                'git add '.$errorFilesString,
            ]);
        } else {
            throw new HookFailException();
        }
    }
}
