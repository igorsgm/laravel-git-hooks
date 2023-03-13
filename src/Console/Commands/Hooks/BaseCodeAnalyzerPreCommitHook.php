<?php

namespace Igorsgm\GitHooks\Console\Commands\Hooks;

use Closure;
use Igorsgm\GitHooks\Exceptions\HookFailException;
use Igorsgm\GitHooks\Facades\GitHooks;
use Igorsgm\GitHooks\Git\ChangedFiles;
use Igorsgm\GitHooks\Traits\ProcessHelper;
use Illuminate\Console\Command;
use Symfony\Component\Console\Terminal;

abstract class BaseCodeAnalyzerPreCommitHook
{
    use ProcessHelper;

    /**
     * Command instance that is bound automatically by Hooks Pipeline, so it can be used inside the Hook.
     *
     * @var Command
     */
    public $command;

    /*
     * List of files extensions that will be analyzed by the hook
     * @var array
     */
    public $fileExtensions = [];

    /**
     * The path to the analyzer executable.
     * @var string
     */
    protected $analyzerExecutable;

    /**
     * The path to the fixer executable. In multiple cases it's the same of the analyzer executable.
     * @var string
     */
    protected $fixerExecutable;

    /**
     * The list of paths of files that are badly formatted and should be fixed.
     * @var array
     */
    protected $filesBadlyFormattedPaths = [];

    public function __construct()
    {
        $this->setCwd(base_path());
    }

    /**
     * Handles the committed files and checks if they are properly formatted.
     *
     * @param  ChangedFiles  $files  The instance of the changed files.
     * @param  Closure  $next  The closure to be executed after the files are handled.
     * @return mixed|void
     *
     * @throws HookFailException If the hook fails to analyze the committed files.
     */
    public function handleCommittedFiles(ChangedFiles $files, Closure $next)
    {
        $commitFiles = $files->getAddedToCommit();

        if ($commitFiles->isEmpty() || GitHooks::isMergeInProgress()) {
            return $next($files);
        }

        $this->checkAnalyzerInstallation()
            ->analizeCommittedFiles($commitFiles);

        if (empty($this->filesBadlyFormattedPaths)) {
            return $next($files);
        }

        $this->commitFailMessage()
            ->suggestAutoFixOrExit();
    }

    /**
     * Analyzes the committed files and checks if they are properly formatted.
     *
     * @param  mixed  $commitFiles  The files to analyze.
     * @return $this
     */
    protected function analizeCommittedFiles($commitFiles)
    {
        foreach ($commitFiles as $file) {
            if (!in_array($file->extension(), $this->fileExtensions)) {
                continue;
            }

            $filePath = $file->getFilePath();
            $command = $this->analyzerCommand().' '.$filePath;

            $isProperlyFormatted = $this->runCommands($command)->isSuccessful();

            if (!$isProperlyFormatted) {
                if (empty($this->filesBadlyFormattedPaths)) {
                    $this->command->newLine();
                }

                $this->command->getOutput()->writeln(
                    sprintf('<fg=red> %s Failed:</> %s', $this->getName(), $filePath)
                );
                $this->filesBadlyFormattedPaths[] = $filePath;
            }
        }

        return $this;
    }

    /**
     * Returns the message to display when the commit fails.
     * @return $this
     */
    protected function commitFailMessage()
    {
        $this->command->newLine();
        $this->command->getOutput()->writeln(
            '<bg=red;fg=white> COMMIT FAILED </> '.
            sprintf('Your commit contains files that should pass %s but do not. Please fix the errors in the files above and try again.',
                $this->getName())
        );

        return $this;
    }

    /**
     * Check if the BaseCodeAnalyzerPreCommitHook is installed.
     *
     * @return $this
     * @throws HookFailException
     */
    protected function checkAnalyzerInstallation()
    {
        if (file_exists($this->analyzerExecutable)) {
            return $this;
        }

        $this->command->newLine(2);
        $this->command->getOutput()->writeln(
            sprintf('<bg=red;fg=white> ERROR </> %s is not installed. Please install it and try again.',
                $this->getName())
        );
        $this->command->newLine();

        throw new HookFailException();
    }

    /**
     * Suggests attempting to automatically fix the incorrectly formatted files or exit.
     *
     * @return void
     * @throws HookFailException
     */
    protected function suggestAutoFixOrExit()
    {
        if (Terminal::hasSttyAvailable() &&
            $this->command->confirm('Would you like to attempt to correct files automagically?')
        ) {
            $errorFilesString = implode(' ', $this->filesBadlyFormattedPaths);

            $this->runCommands([
                $this->fixerCommand().' '.$errorFilesString,
                'git add '.$errorFilesString
            ]);
        } else {
            throw new HookFailException();
        }
    }

    /**
     * @param  array|string  $fileExtensions
     * @return BaseCodeAnalyzerPreCommitHook
     */
    public function setFileExtensions($fileExtensions)
    {
        $this->fileExtensions = (array) $fileExtensions;
        return $this;
    }

    /**
     * @param $executablePath
     * @return BaseCodeAnalyzerPreCommitHook
     */
    public function setAnalyzerExecutable($executablePath, $isSameAsFixer = false)
    {
        $this->analyzerExecutable = './'.trim($executablePath, '/');

        return $isSameAsFixer ? $this->setFixerExecutable($executablePath) : $this;
    }

    /**
     * @return string
     */
    public function getAnalyzerExecutable(): string
    {
        return $this->analyzerExecutable;
    }

    /**
     * @param $exacutablePath
     * @return BaseCodeAnalyzerPreCommitHook
     */
    public function setFixerExecutable($exacutablePath)
    {
        $this->fixerExecutable = './'.trim($exacutablePath, '/');
        return $this;
    }

    /**
     * @return string
     */
    public function getFixerExecutable(): string
    {
        return $this->fixerExecutable;
    }
}
