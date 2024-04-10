<?php

namespace Igorsgm\GitHooks\Console\Commands\Hooks;

use Closure;
use Igorsgm\GitHooks\Exceptions\HookFailException;
use Igorsgm\GitHooks\Facades\GitHooks;
use Igorsgm\GitHooks\Git\ChangedFile;
use Igorsgm\GitHooks\Git\ChangedFiles;
use Igorsgm\GitHooks\Traits\ProcessHelper;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
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
    
    /**
     * Run tool in docker
     *
     * @var bool
     */
    protected $runInDocker = false;
    
    /**
     * Docker container on which to run
     *
     * @var string
     */
    protected $dockerContainer = '';

    /**
     * Run tool in docker
     *
     * @var bool
     */
    protected $runInDocker = false;

    /**
     * Docker container on which to run
     *
     * @var string
     */
    protected $dockerContainer = '';

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
        $commitFiles = $files->getStaged();

        if ($commitFiles->isEmpty() || GitHooks::isMergeInProgress()) {
            return $next($files);
        }

        $this->validateAnalyzerInstallation()
            ->analizeCommittedFiles($commitFiles);

        if (empty($this->filesBadlyFormattedPaths)) {
            return $next($files);
        }

        $this->commitFailMessage()
            ->suggestAutoFixOrExit();

        return $next($files);
    }

    /**
     * Analyzes an array of ChangedFile objects and checks whether each file can be analyzed,
     * whether it is properly formatted according to the configured analyzer, and collects
     * paths of any files that are not properly formatted.
     *
     * @param  ChangedFile[]|Collection  $commitFiles  The files to analyze.
     * @return $this
     */
    protected function analizeCommittedFiles($commitFiles)
    {
        foreach ($commitFiles as $file) {
            if (! $this->canFileBeAnalyzed($file)) {
                continue;
            }

            $filePath = $file->getFilePath();
            $command = $this->dockerCommand($this->analyzerCommand().' '.$filePath);

            $process = $this->runCommands($command);

            if (config('git-hooks.debug_commands')) {
                $this->command->getOutput()->write(PHP_EOL . ' <bg=yellow;fg=white> DEBUG </> Executed command: ' . $process->getCommandLine() . PHP_EOL);
            }

            $isProperlyFormatted = $process->isSuccessful();

            if (! $isProperlyFormatted) {
                if (empty($this->filesBadlyFormattedPaths)) {
                    $this->command->newLine();
                }

                $this->command->getOutput()->writeln(
                    sprintf('<fg=red> %s Failed:</> %s', $this->getName(), $filePath)
                );
                $this->filesBadlyFormattedPaths[] = $filePath;

                if (config('git-hooks.output_errors')) {
                    $this->command->newLine();
                    $this->command->getOutput()->write($process->getOutput());
                }
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
        $message .= sprintf("Your commit contains files that should pass %s but do not. Please fix the errors in the files above and try again.\n",
            $this->getName());
        $message .= sprintf('You can check which %s errors happened in them by executing: <comment>%s {filePath}</comment>',
            $this->getName(), $this->analyzerCommand());

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
    protected function validateAnalyzerInstallation()
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
     * Validates the given configuration path.
     *
     * @param  string  $path  The path to the configuration file.
     * @return $this This instance for method chaining.
     *
     * @throws HookFailException If the configuration file does not exist.
     */
    protected function validateConfigPath($path)
    {
        if (file_exists($path)) {
            return $this;
        }

        $this->command->newLine(2);
        $this->command->getOutput()->writeln(
            sprintf('<bg=red;fg=white> ERROR </> %s config file does not exist. Please check the path and try again.',
                $this->getName())
        );
        $this->command->newLine();

        throw new HookFailException();
    }

    /**
     * Suggests attempting to automatically fix the incorrectly formatted files or exit.
     * If any files cannot be fixed, throws a HookFailException to cancel the commit.
     *
     * @throws HookFailException
     */
    protected function suggestAutoFixOrExit(): bool
    {
        $hasFixerCommand = ! empty($this->fixerCommand());

        if ($hasFixerCommand) {
            if (config('git-hooks.automatically_fix_errors')) {
                $this->command->getOutput()->writeln(
                    sprintf('<bg=green;fg=white> AUTOFIX </> <fg=green> %s Running Autofix</>', $this->getName())
                );
                if ($this->autoFixFiles()) {
                    return true;
                }
            } else {
                if (Terminal::hasSttyAvailable() &&
                    $this->command->confirm('Would you like to attempt to correct files automagically?') &&
                    $this->autoFixFiles()
                ) {
                    return true;
                }
            }
        }

        if (config('git-hooks.stop_at_first_analyzer_failure')) {
            throw new HookFailException();
        }

        return false;
    }

    /**
     * Automatically fixes any files in the `$filesBadlyFormattedPaths` array using the
     * configured fixer command. For each fixed file, adds it to Git and removes its path
     * from the `$filesBadlyFormattedPaths` array.
     *
     *
     * @throws HookFailException if any files cannot be fixed.
     */
    private function autoFixFiles(): bool
    {
        foreach ($this->filesBadlyFormattedPaths as $key => $filePath) {
            $fixerCommand = $this->dockerCommand($this->fixerCommand().' '.$filePath);
            $process = $this->runCommands($fixerCommand);

            if (config('git-hooks.rerun_analyzer_after_autofix')) {
                $command = $this->dockerCommand($this->analyzerCommand().' '.$filePath);
                $process = $this->runCommands($command);
            }

            $wasProperlyFixed = $process->isSuccessful();

            if ($wasProperlyFixed) {
                $this->runCommands('git add '.$filePath);
                unset($this->filesBadlyFormattedPaths[$key]);

                continue;
            }

            $this->command->getOutput()->writeln(
                sprintf('<fg=red> %s Autofix Failed:</> %s', $this->getName(), $filePath)
            );

            if (config('git-hooks.output_errors')) {
                $this->command->newLine();
                $this->command->getOutput()->write($process->getOutput());
            }
        }

        return empty($this->filesBadlyFormattedPaths);
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

    /**
     * @return BaseCodeAnalyzerPreCommitHook
     */
    public function setRunInDocker($runInDocker)
    {
        $this->runInDocker = (bool) $runInDocker;

        return $this;
    }

    public function getRunInDocker(): bool
    {
        return $this->runInDocker;
    }

    /**
     * @param  string  $dockerContainer
     * @return BaseCodeAnalyzerPreCommitHook
     */
    public function setDockerContainer($dockerContainer)
    {
        $this->dockerContainer = $dockerContainer;

        return $this;
    }

    public function getDockerContainer(): string
    {
        return $this->dockerContainer;
    }

    private function dockerCommand(string $command): string
    {
        if (!$this->runInDocker || empty($this->dockerContainer)) {
            return $command;
        }

        return 'docker exec ' . escapeshellarg($this->dockerContainer) . ' sh -c ' . escapeshellarg($command);
    }
}
