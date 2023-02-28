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

class PintPreCommitHook implements PreCommitHook
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
    private $pintExecutable;

    /**
     * @var array
     */
    private $filesBadlyFormatted = [];

    /**
     * Create a new console command instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->pintExecutable = './'.trim(config('git-hooks.code_analyzers.laravel_pint.path'), '/');
    }

    public function getName(): ?string
    {
        return 'Laravel Pint';
    }

    public function handle(ChangedFiles $files, Closure $next)
    {
        $stagedFilePaths = $files->getStaged()->map(function ($file) {
            return $file->getFilePath();
        })->toArray();

        if (empty($stagedFilePaths) || GitHooks::isMergeInProgress()) {
            return $next($files);
        }

        $this->validatePintInstallation();

        foreach ($stagedFilePaths as $stagedFilePath) {
            $isPintProperlyFormatted = $this->runCommands(
                implode(' ', [
                    $this->pintExecutable,
                    '--test',
                    $this->getPintConfigParam(),
                    $stagedFilePath,
                ]),
                [
                    'cwd' => base_path(),
                ])->isSuccessful();

            if (! $isPintProperlyFormatted) {
                if (empty($this->filesBadlyFormatted)) {
                    $this->command->newLine();
                }

                $this->command->getOutput()->writeln(
                    sprintf('<fg=red> Pint Failed:</> %s', "$stagedFilePath")
                );
                $this->filesBadlyFormatted[] = $stagedFilePath;
            }
        }

        if (empty($this->filesBadlyFormatted)) {
            return $next($files);
        }

        $this->command->newLine();
        $this->command->getOutput()->writeln(
            sprintf('<bg=red;fg=white> COMMIT FAILED </> %s',
                'Your commit contains files that should pass Pint but do not. Please fix the Pint errors in the files above and try again.')
        );

        $this->suggestAutoFixOrExit();
    }

    private function getPintConfigParam(): string
    {
        $pintConfigFile = trim(config('git-hooks.code_analyzers.laravel_pint.config'), '/');
        return empty($pintConfigFile) ? '' : '--config ./'.$pintConfigFile;
    }

    /**
     * @return void
     *
     * @throws HookFailException
     */
    private function validatePintInstallation()
    {
        $isPintInstalled = file_exists(base_path(config('git-hooks.code_analyzers.laravel_pint.path')));

        if ($isPintInstalled) {
            return;
        }

        $this->command->newLine(2);
        $this->command->getOutput()->writeln(
            sprintf('<bg=red;fg=white> ERROR </> %s',
                'Pint is not installed. Please run <info>composer require laravel/pint --dev</info> to install it.')
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
            $errorFilesString = implode(' ', $this->filesBadlyFormatted);
            $this->runCommands(
                [
                    implode(' ', [
                        $this->pintExecutable,
                        $this->getPintConfigParam(),
                        $errorFilesString,
                    ]),
                    'git add '.$errorFilesString,
                ],
                ['cwd' => base_path()]
            );
        } else {
            throw new HookFailException();
        }
    }
}
