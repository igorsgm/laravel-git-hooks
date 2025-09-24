<?php

declare(strict_types=1);

namespace Igorsgm\GitHooks\Traits;

use Igorsgm\GitHooks\Git\ChangedFile;
use Illuminate\Support\Collection;

trait WithFileAnalysis
{
    protected int $chunkSize = 100;

    /**
     * @var array<int, string>
     */
    protected array $filesBadlyFormattedPaths = [];

    /**
     * Get the file extensions that can be analyzed.
     *
     * @return array<int, string>|string
     */
    abstract public function getFileExtensions(): array|string;

    public function analizeCommittedFiles(Collection $commitFiles): self
    {
        /** @var Collection<int, ChangedFile> $chunk */
        foreach ($commitFiles->chunk($this->chunkSize) as $chunk) {
            $filePaths = $this->getAnalyzableFilePaths($chunk);

            if (empty($filePaths)) {
                continue;
            }

            $this->analyzeFiles($filePaths);
        }

        return $this;
    }

    public function canFileBeAnalyzed(ChangedFile $file): bool
    {
        $fileExtensions = $this->getFileExtensions();

        if (empty($fileExtensions) || $fileExtensions === 'all') {
            return true;
        }

        return is_string($fileExtensions) && preg_match($fileExtensions, $file->getFilePath());
    }

    /**
     * @param  Collection<int, ChangedFile>  $files
     * @return array<int, string>
     */
    protected function getAnalyzableFilePaths(Collection $files): array
    {
        return $files->filter(fn ($file) => $this->canFileBeAnalyzed($file))->map(fn ($file) => $file->getFilePath())->toArray();
    }

    /**
     * @param  array<int, string>  $filePaths
     */
    protected function analyzeFiles(array $filePaths): void
    {
        $filePath = implode(' ', $filePaths);
        $command = $this->dockerCommand($this->analyzerCommand().' '.$filePath);

        $params = [
            'show-output' => config('git-hooks.debug_output'),
        ];

        $process = $this->runCommands($command, $params);

        if (config('git-hooks.debug_commands')) {
            $this->outputDebugCommandIfEnabled($process);
        }

        if (!$process->isSuccessful()) {
            $this->handleAnalysisFailure($filePath, $process);
        }
    }

    protected function handleAnalysisFailure(string $filePath, mixed $process): void
    {
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
