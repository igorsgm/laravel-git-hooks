<?php

namespace Igorsgm\GitHooks\Console\Commands\Hooks;

use Closure;
use Igorsgm\GitHooks\Contracts\PreCommitHook;
use Igorsgm\GitHooks\Exceptions\HookFailException;
use Igorsgm\GitHooks\Facades\GitHooks;
use Igorsgm\GitHooks\Git\ChangedFiles;
use Igorsgm\GitHooks\Traits\ConsoleHelper;

class PintPreCommitHook implements PreCommitHook
{
    use ConsoleHelper;

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

        if (! $this->isPintInstalled()) {
            $this->newLine(2);
            $this->output->writeln(
                sprintf('<bg=red;fg=white> ERROR </> %s',
                    'Pint is not installed. Please run <info>composer require laravel/pint --dev</info> to install it.')
            );
            $this->newLine();
            throw new HookFailException();
        }

        $errorFiles = [];
        foreach ($stagedFilePaths as $stagedFilePath) {
            $isPintProperlyFormatted = $this->runCommands('./vendor/bin/pint --test --config ./pint.json '.$stagedFilePath,
                [
                    'cwd' => base_path(),
                ])->isSuccessful();

            if (! $isPintProperlyFormatted) {
                if (empty($errorFiles)) {
                    $this->newLine();
                }

                $this->output->writeln(
                    sprintf('<fg=red> Pint Failed:</> %s', "$stagedFilePath")
                );
                $errorFiles[] = $stagedFilePath;
            }
        }

        if (empty($errorFiles)) {
            return $next($files);
        }

        $this->newLine();
        $this->output->writeln(
            sprintf('<bg=red;fg=white> COMMIT FAILED </> %s',
                'Your commit contains files that should pass Pint but do not. Please fix the Pint errors in the files above and try again.')
        );

        if ($this->confirm('Would you like to attempt to correct files automagically?', false)) {
            $errorFilesString = implode(' ', $errorFiles);
            $this->runCommands(
                [
                    './vendor/bin/pint --config ./pint.json '.$errorFilesString,
                    'git add '.$errorFilesString,
                ],
                ['cwd' => base_path()]
            );
        } else {
            throw new HookFailException();
        }
    }

    public function isPintInstalled()
    {
        return file_exists(base_path('vendor/bin/pint'));
    }
}
