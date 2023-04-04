<?php

namespace Igorsgm\GitHooks\Console\Commands\Hooks;

use Closure;
use Igorsgm\GitHooks\Exceptions\HookFailException;
use Igorsgm\GitHooks\Facades\GitHooks;
use Igorsgm\GitHooks\Git\ChangedFile;
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

    /**
     * Name of the hook
     *
     * @var string
     */
    protected $name;

    /*
     * List of files extensions that will be analyzed by the hook.
     * Can also be a regular expression.
     * @var array|string
     */
    public $fileExtensions = [];

    /**
     * The path to the analyzer executable.
     *
     * @var string
     */
    protected $analyzerExecutable;

    /**
     * The path to the fixer executable. In multiple cases it's the same of the analyzer executable.
     *
     * @var string
     */
    protected $fixerExecutable;

    /**
     * The list of paths of files that are badly formatted and should be fixed.
     *
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

        return $next($files);
    }

    /**
     * Analyzes the committed files and checks if they are properly formatted.
     *
     * @param  mixed  $commitFiles  The files to analyze.
     * @return $this
     */
    protected function analizeCommittedFiles($commitFiles)
    {
        /** @var ChangedFile $file */
        foreach ($commitFiles as $file) {
            if (! $this->canFileBeAnalyzed($file)) {
                continue;
            }

            $filePath = $file->getFilePath();
            $command = $this->analyzerCommand().' '.$filePath;

            $isProperlyFormatted = $this->runCommands($command)->isSuccessful();

            if (! $isProperlyFormatted) {
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
     * Checks whether the given ChangedFile can be analyzed based on its file extension and the list of allowed extensions.
     */
    protected function canFileBeAnalyzed(ChangedFile $file): bool
    {
        if (empty($this->fileExtensions) || $this->fileExtensions === 'all') {
            return true;
        }

        return is_string($this->fileExtensions) && preg_match($this->fileExtensions, $file->getFilePath());
    }

    /**
     * Returns the message to display when the commit fails.
     *
     * @return $this
     */
    protected function commitFailMessage()
    {
        $this->command->newLine();

        $message = '<bg=red;fg=white> COMMIT FAILED </> ';
        $message .= sprintf("Your commit contains files that should pass %s but do not. Please fix the errors in the files above and try again.\n", $this->getName());
        $message .= sprintf('You can check which %s errors happened in them by executing: <comment>%s {filePath}</comment>', $this->getName(), $this->analyzerCommand());

        $this->command->getOutput()->writeln($message);

        return $this;
    }

    /**
     * Check if the BaseCodeAnalyzerPreCommitHook is installed.
     *
     * @return $this
     *
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
     *
     * @throws HookFailException
     */
    protected function suggestAutoFixOrExit()
    {
        $hasFixerCommand = ! empty($this->fixerCommand());

        if (Terminal::hasSttyAvailable() && $hasFixerCommand &&
            $this->command->confirm('Would you like to attempt to correct files automagically?')
        ) {
            $errorFilesString = implode(' ', $this->filesBadlyFormattedPaths);

            $this->runCommands([
                $this->fixerCommand().' '.$errorFilesString,
                'git add '.$errorFilesString,
            ]);
        } else {
            throw new HookFailException();
        }
    }

    /**
     * Get the name of the hook.
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param  array|string  $fileExtensions
     * @return BaseCodeAnalyzerPreCommitHook
     */
    public function setFileExtensions($fileExtensions)
    {
        $this->fileExtensions = $fileExtensions;

        return $this;
    }

    /**
     * @return BaseCodeAnalyzerPreCommitHook
     */
    public function setAnalyzerExecutable($executablePath, $isSameAsFixer = false)
    {
        $this->analyzerExecutable = './'.trim($executablePath, '/');

        return $isSameAsFixer ? $this->setFixerExecutable($executablePath) : $this;
    }

    public function getAnalyzerExecutable(): string
    {
        return $this->analyzerExecutable;
    }

    /**
     * @return BaseCodeAnalyzerPreCommitHook
     */
    public function setFixerExecutable($exacutablePath)
    {
        $this->fixerExecutable = './'.trim($exacutablePath, '/');

        return $this;
    }

    public function getFixerExecutable(): string
    {
        return $this->fixerExecutable;
    }
}
