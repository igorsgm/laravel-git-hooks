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
use Igorsgm\GitHooks\Traits\WithAutoFix;
use Igorsgm\GitHooks\Traits\WithDockerSupport;
use Igorsgm\GitHooks\Traits\WithFileAnalysis;
use Igorsgm\GitHooks\Traits\WithPipelineFailCheck;
use Illuminate\Console\Command;
use Illuminate\Console\OutputStyle;
use Illuminate\Support\Collection;

abstract class BaseCodeAnalyzerPreCommitHook implements CodeAnalyzerPreCommitHook
{
    use ProcessHelper;
    use WithAutoFix;
    use WithDockerSupport;
    use WithFileAnalysis;
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
     * Chunk size used for analyze
     */
    protected int $chunkSize = 100;

    public function __construct()
    {
        $this->setCwd(base_path());

        $raw = config('git-hooks.analyzer_chunk_size', $this->chunkSize);
        $this->chunkSize = is_int($raw) ? $raw : $this->chunkSize;
    }

    /**
     * Get the analyzer command to be executed.
     */
    abstract public function analyzerCommand(): string;

    /**
     * Get the fixer command to be executed.
     */
    abstract public function fixerCommand(): string;

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
            ->analyzeCommittedFiles($commitFiles);

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
        if (!config('git-hooks.debug_output')) {
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
     * @param  array<int, string>|string|mixed  $fileExtensions
     */
    public function setFileExtensions(mixed $fileExtensions): self
    {
        if (is_array($fileExtensions)) {
            $this->fileExtensions = array_values(array_map(fn ($v): string => is_string($v) ? $v : '', $fileExtensions));
        } else {
            $this->fileExtensions = is_string($fileExtensions) ? $fileExtensions : '';
        }

        return $this;
    }

    public function setAnalyzerExecutable(mixed $executablePath, bool $isSameAsFixer = false): self
    {
        $this->analyzerExecutable = is_string($executablePath) ? $executablePath : '';

        return $isSameAsFixer ? $this->setFixerExecutable($executablePath) : $this;
    }

    public function getAnalyzerExecutable(): string
    {
        return $this->analyzerExecutable;
    }

    public function setFixerExecutable(mixed $executablePath): self
    {
        $this->fixerExecutable = is_string($executablePath) ? $executablePath : '';

        return $this;
    }

    public function getFixerExecutable(): string
    {
        return $this->fixerExecutable;
    }

    /**
     * Get the file extensions that can be analyzed.
     *
     * @return array<int, string>|string
     */
    public function getFileExtensions(): array|string
    {
        return $this->fileExtensions;
    }

    /**
     * Analyzes an array of ChangedFile objects and checks whether each file can be analyzed,
     * whether it is properly formatted according to the configured analyzer, and collects
     * paths of any files that are not properly formatted.
     *
     * @param  Collection<int, ChangedFile>  $commitFiles  The files to analyze.
     * @return $this
     */
    protected function analyzeCommittedFiles(Collection $commitFiles): self
    {
        /** @var Collection<int, ChangedFile> $chunk */
        foreach ($commitFiles->chunk($this->chunkSize) as $chunk) {
            $filePaths = [];

            /** @var ChangedFile $file */
            foreach ($chunk as $file) {
                if (!$this->canFileBeAnalyzed($file)) {
                    continue;
                }

                $filePaths[] = $file->getFilePath();
            }

            if (empty($filePaths)) {
                continue;
            }

            $escapedFilePaths = implode(' ', array_map(escapeshellarg(...), $filePaths));
            $filePath = implode(' ', $filePaths);
            $command = $this->dockerCommand($this->analyzerCommand().' '.$escapedFilePaths);

            $params = [
                'show-output' => config('git-hooks.debug_output'),
            ];

            $process = $this->runCommands($command, $params);

            if (config('git-hooks.debug_commands')) {
                $this->command->newLine();
                $this->command->getOutput()->write(PHP_EOL.' <bg=yellow;fg=white> DEBUG </> Executed command: '.$process->getCommandLine().PHP_EOL);
            }

            $isProperlyFormatted = $process->isSuccessful();

            if (!$isProperlyFormatted) {
                if (empty($this->filesBadlyFormattedPaths)) {
                    $this->command->newLine();
                }

                $this->command->getOutput()->writeln(
                    sprintf('<fg=red> %s Failed:</> %s', $this->getName(), $filePath)
                );
                $this->filesBadlyFormattedPaths[] = $filePath;

                if (config('git-hooks.output_errors') && !config('git-hooks.debug_output')) {
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
        if (!config('git-hooks.validate_paths') || file_exists($this->analyzerExecutable)) {
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
    protected function validateConfigPath(mixed $path): self
    {
        $path = is_string($path) ? $path : '';

        if (!config('git-hooks.validate_paths') || file_exists($path)) {
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
}
