<?php

namespace Igorsgm\GitHooks\Console\Commands\Hooks;

use Closure;
use Igorsgm\GitHooks\Contracts\PreCommitHook;
use Igorsgm\GitHooks\Exceptions\HookFailException;
use Igorsgm\GitHooks\Facades\GitHooks;
use Igorsgm\GitHooks\Git\ChangedFiles;
use Igorsgm\GitHooks\Traits\ConsoleHelper;
use Symfony\Component\Console\Terminal;

class PintPreCommitHook implements PreCommitHook
{
    use ConsoleHelper;

    /**
     * @var string
     */
    private $pintExecutable;

    /**
     * @var string
     */
    private $pintConfig;

    /**
     * @var array
     */
    private $filesBadlyFormatted;

    public function getName(): ?string
    {
        return 'Laravel Pint';
    }

    /**
     * Create a new console command instance.
     *
     * @return void
     */
    public function __construct($argInput = '')
    {
        $this->initConsole($argInput);

        $this->pintExecutable = './'.trim(config('git-hooks.code_analyzers.laravel_pint.path'), '/');
        $this->pintConfig = './'.trim(config('git-hooks.code_analyzers.laravel_pint.config'), '/');
        $this->filesBadlyFormatted = [];
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
                sprintf('%s --test --config %s %s', $this->pintExecutable, $this->pintConfig, $stagedFilePath),
                [
                    'cwd' => base_path(),
                ])->isSuccessful();

            if (! $isPintProperlyFormatted) {
                if (empty($this->filesBadlyFormatted)) {
                    $this->newLine();
                }

                $this->output->writeln(
                    sprintf('<fg=red> Pint Failed:</> %s', "$stagedFilePath")
                );
                $this->filesBadlyFormatted[] = $stagedFilePath;
            }
        }

        if (empty($this->filesBadlyFormatted)) {
            return $next($files);
        }

        $this->newLine();
        $this->output->writeln(
            sprintf('<bg=red;fg=white> COMMIT FAILED </> %s',
                'Your commit contains files that should pass Pint but do not. Please fix the Pint errors in the files above and try again.')
        );

        $this->suggestAutoFixOrExit();
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

        $this->newLine(2);
        $this->output->writeln(
            sprintf('<bg=red;fg=white> ERROR </> %s',
                'Pint is not installed. Please run <info>composer require laravel/pint --dev</info> to install it.')
        );
        $this->newLine();
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
            $this->confirm('Would you like to attempt to correct files automagically?', false)
        ) {
            $errorFilesString = implode(' ', $this->filesBadlyFormatted);
            $this->runCommands(
                [
                    sprintf('%s --config %s %s', $this->pintExecutable, $this->pintConfig, $errorFilesString),
                    'git add '.$errorFilesString,
                ],
                ['cwd' => base_path()]
            );
        } else {
            throw new HookFailException();
        }
    }
}
