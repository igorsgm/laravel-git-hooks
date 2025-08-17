<?php

declare(strict_types=1);

namespace Igorsgm\GitHooks\Console\Commands\Hooks;

use Closure;
use Igorsgm\GitHooks\Contracts\CodeAnalyzerPreCommitHook;
use Igorsgm\GitHooks\Exceptions\HookFailException;
use Igorsgm\GitHooks\Facades\GitHooks;
use Igorsgm\GitHooks\Git\ChangedFile;
use Igorsgm\GitHooks\Git\ChangedFiles;
use Igorsgm\GitHooks\Traits\ProcessHelper;
use Igorsgm\GitHooks\Traits\WithPipelineFailCheck;
use Illuminate\Console\Command;
use Illuminate\Console\OutputStyle;
use Illuminate\Support\Collection;
use Symfony\Component\Console\Terminal;

abstract class BaseCodeAnalyzerPreCommitHook implements CodeAnalyzerPreCommitHook
{
    use ProcessHelper;
    use WithPipelineFailCheck;

    /**
     * Command instance that is bound automatically by Hooks Pipeline, so it can be used inside the Hook.
     */
    public Command $command;

    /**
     * List of files extensions that will be analyzed by the hook.
     * Can also be a regular expression.
     *
     * @var array<int, string>|string
     */
    public array|string $fileExtensions = [];

    /**
     * Name of the hook
     */
    protected string $name;

    /**
     * The path to the analyzer executable.
     */
    protected string $analyzerExecutable;

    /**
     * The path to the fixer executable. In multiple cases it's the same of the analyzer executable.
     */
    protected string $fixerExecutable;

    /**
     * The list of paths of files that are badly formatted and should be fixed.
     *
     * @var array<int, string>
     */
    protected array $filesBadlyFormattedPaths = [];

    /**
     * Run tool in docker
     */
    protected bool $runInDocker = false;

    /**
     * Docker container on which to run
     */
    protected string $dockerContainer = '';

    /**
     * Chunk size used for analyze
     */
    protected int $chunkSize = 100;

    public function __construct()
    {
        $this->setCwd(base_path());

        $this->chunkSize = config('git-hooks.analyzer_chunk_size');
    }

    /**
     * Handles the committed files and checks if they are properly formatted.
     *
     * @throws HookFailException If the hook fails to analyze the committed files.
     */
    public function handleCommittedFiles(ChangedFiles $files, Closure $next): mixed
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
     * Get output method
     */
    public function getOutput(): ?OutputStyle
    {
        if (! config('git-hooks.debug_output')) {
            return null;
        }

        return $this->command->getOutput();
    }

    public function setCommand(Command $command): void
    {
        $this->command = $command;
    }

    /**
     * Get the name of the hook.
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param  array<int, string>|string  $fileExtensions
     */
    public function setFileExtensions(array|string $fileExtensions): self
    {
        $this->fileExtensions = $fileExtensions;

        return $this;
    }

    public function setAnalyzerExecutable(string $executablePath, bool $isSameAsFixer = false): self
    {
        $this->analyzerExecutable = $executablePath;

        return $isSameAsFixer ? $this->setFixerExecutable($executablePath) : $this;
    }

    public function getAnalyzerExecutable(): string
    {
        return $this->analyzerExecutable;
    }

    public function setFixerExecutable(string $executablePath): self
    {
        $this->fixerExecutable = $executablePath;

        return $this;
    }

    public function getFixerExecutable(): string
    {
        return $this->fixerExecutable;
    }

    public function setRunInDocker(bool $runInDocker): self
    {
        $this->runInDocker = (bool) $runInDocker;

        return $this;
    }

    public function getRunInDocker(): bool
    {
        return $this->runInDocker;
    }

    public function setDockerContainer(string $dockerContainer): self
    {
        $this->dockerContainer = $dockerContainer;

        return $this;
    }

    public function getDockerContainer(): string
    {
        return $this->dockerContainer;
    }

    /**
     * Analyzes an array of ChangedFile objects and checks whether each file can be analyzed,
     * whether it is properly formatted according to the configured analyzer, and collects
     * paths of any files that are not properly formatted.
     *
     * @param  Collection<int, ChangedFile>  $commitFiles  The files to analyze.
     * @return $this
     */
    protected function analizeCommittedFiles(Collection $commitFiles): self
    {
        /** @var Collection<int, ChangedFile> $chunk */
        foreach ($commitFiles->chunk($this->chunkSize) as $chunk) {
            $filePaths = [];

            /** @var ChangedFile $file */
            foreach ($chunk as $file) {
                if (! $this->canFileBeAnalyzed($file)) {
                    continue;
                }

                $filePaths[] = $file->getFilePath();
            }

            if (empty($filePaths)) {
                continue;
            }

            $filePath = implode(' ', $filePaths);
            $command = $this->dockerCommand($this->analyzerCommand().' '.$filePath);

            $params = [
                'show-output' => config('git-hooks.debug_output'),
            ];

            $process = $this->runCommands($command, $params);

            if (config('git-hooks.debug_commands')) {
                $this->command->newLine();
                $this->command->getOutput()->write(PHP_EOL.' <bg=yellow;fg=white> DEBUG </> Executed command: '.$process->getCommandLine().PHP_EOL);
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

                if (config('git-hooks.output_errors') && ! config('git-hooks.debug_output')) {
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
     */
    protected function commitFailMessage(): self
    {
        $this->command->newLine();

        $message = '<bg=red;fg=white> COMMIT FAILED </> ';
        $message .= sprintf(
            "Your commit contains files that should pass %s but do not. Please fix the errors in the files above and try again.\n",
            $this->getName()
        );
        $message .= sprintf(
            'You can check which %s errors happened in them by executing: <comment>%s {filePath}</comment>',
            $this->getName(),
            $this->analyzerCommand()
        );

        $this->command->getOutput()->writeln($message);

        return $this;
    }

    /**
     * Check if the BaseCodeAnalyzerPreCommitHook is installed.
     *
     * @throws HookFailException
     */
    protected function validateAnalyzerInstallation(): self
    {
        if (! config('git-hooks.validate_paths') || file_exists($this->analyzerExecutable)) {
            return $this;
        }

        $this->command->newLine(2);
        $this->command->getOutput()->writeln(
            sprintf(
                '<bg=red;fg=white> ERROR </> %s is not installed. Please install it and try again.',
                $this->getName()
            )
        );
        $this->command->newLine();

        throw new HookFailException;
    }

    /**
     * Validates the given configuration path.
     *
     * @throws HookFailException If the configuration file does not exist.
     */
    protected function validateConfigPath(string $path): self
    {
        if (! config('git-hooks.validate_paths') || file_exists($path)) {
            return $this;
        }

        $this->command->newLine(2);
        $this->command->getOutput()->writeln(
            sprintf(
                '<bg=red;fg=white> ERROR </> %s config file does not exist. Please check the path and try again.',
                $this->getName()
            )
        );
        $this->command->newLine();

        throw new HookFailException;
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

        $this->markPipelineFailed();

        if (config('git-hooks.stop_at_first_analyzer_failure')) {
            throw new HookFailException;
        }

        return false;
    }

    /**
     * Automatically fixes any files in the `$filesBadlyFormattedPaths` array using the
     * configured fixer command. For each fixed file, adds it to Git and removes its path
     * from the `$filesBadlyFormattedPaths` array.
     *
     * @throws HookFailException if any files cannot be fixed.
     */
    private function autoFixFiles(): bool
    {
        $params = [
            'show-output' => config('git-hooks.debug_output'),
        ];

        foreach ($this->filesBadlyFormattedPaths as $key => $filePath) {
            $fixerCommand = $this->dockerCommand($this->fixerCommand().' '.$filePath);
            $process = $this->runCommands($fixerCommand, $params);

            if (config('git-hooks.debug_commands')) {
                $this->command->newLine();
                $this->command->getOutput()->write(PHP_EOL.' <bg=yellow;fg=white> DEBUG </> Executed command: '.$process->getCommandLine().PHP_EOL);
            }

            if (config('git-hooks.rerun_analyzer_after_autofix')) {
                $command = $this->dockerCommand($this->analyzerCommand().' '.$filePath);
                $process = $this->runCommands($command, $params);

                if (config('git-hooks.debug_commands')) {
                    $this->command->newLine();
                    $this->command->getOutput()->write(PHP_EOL.' <bg=yellow;fg=white> DEBUG </> Executed command: '.$process->getCommandLine().PHP_EOL);
                }
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

            if (config('git-hooks.output_errors') && ! config('git-hooks.debug_output')) {
                $this->command->newLine();
                $this->command->getOutput()->write($process->getOutput());
            }
        }

        return empty($this->filesBadlyFormattedPaths);
    }

    private function dockerCommand(string $command): string
    {
        if (! $this->runInDocker || empty($this->dockerContainer)) {
            return $command;
        }

        return 'docker exec '.escapeshellarg($this->dockerContainer).' sh -c '.escapeshellarg($command);
    }
}
