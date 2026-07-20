<?php

declare(strict_types=1);

namespace Igorsgm\GitHooks\Console\Commands\Hooks;

use Closure;
use Igorsgm\GitHooks\Contracts\CodeAnalyzerPreCommitHook;
use Igorsgm\GitHooks\Git\ChangedFile;
use Igorsgm\GitHooks\Git\ChangedFiles;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

abstract class BaseTestRunnerPreCommitHook extends BaseCodeAnalyzerPreCommitHook implements CodeAnalyzerPreCommitHook
{
    protected string $testPath;

    abstract protected function getConfigPath(): string;

    abstract protected function getTestCommand(): string;

    abstract protected function getAdditionalParams(): string;

    /**
     * @return array<int, string>
     */
    abstract protected function getTestFilePatterns(string $withoutExtension): array;

    public function handle(ChangedFiles $files, Closure $next): mixed
    {
        $potentialTestFiles = $this->findTestFilesForChangedFiles($files);

        if (empty($potentialTestFiles)) {
            return $next($files);
        }

        $this->chunkSize = 1;

        $testFiles = new ChangedFiles(implode(PHP_EOL, $potentialTestFiles));

        return $this->setFileExtensions('all')
            ->setAnalyzerExecutable(config($this->getConfigPath().'.path'), true)
            ->setRunInDocker(config($this->getConfigPath().'.run_in_docker'))
            ->setDockerContainer(config($this->getConfigPath().'.docker_container'))
            ->handleCommittedFiles($testFiles, $next);
    }

    public function analyzerCommand(): string
    {
        return mb_trim(sprintf(
            '%s %s %s',
            $this->getAnalyzerExecutable(),
            $this->getTestCommand(),
            $this->getAdditionalParams()
        ));
    }

    public function fixerCommand(): string
    {
        return '';
    }

    /**
     * @return array<int, string>
     */
    protected function findTestFilesForChangedFiles(ChangedFiles $files): array
    {
        $testFiles = [];
        $commitFiles = $files->getStaged();

        /** @var ChangedFile $file */
        foreach ($commitFiles as $file) {
            if ($this->shouldSkipFile($file)) {
                continue;
            }

            $found = $this->findTestFiles($file->getFilePath());
            $testFiles = array_merge($testFiles, $found);
        }

        return array_unique($testFiles);
    }

    protected function shouldSkipFile(ChangedFile $file): bool
    {
        $filePath = $file->getFilePath();

        if (str_contains($filePath, $this->testPath)) {
            return true;
        }

        $extension = pathinfo($filePath, PATHINFO_EXTENSION);
        if (!in_array($extension, ['php', 'js', 'jsx', 'ts', 'tsx', 'vue'], true)) {
            return true;
        }

        return false;
    }

    /**
     * @return array<int, string>
     */
    protected function findTestFiles(string $file): array
    {
        $testFiles = [];
        $baseName = basename($file);
        $withoutExtension = pathinfo($baseName, PATHINFO_FILENAME);

        $patterns = $this->getTestFilePatterns($withoutExtension);

        foreach ($patterns as $pattern) {
            $found = $this->findTestPattern($pattern);
            $testFiles = array_merge($testFiles, $found);
        }

        return $testFiles;
    }

    /**
     * @return array<int, string>
     */
    protected function findTestPattern(string $pattern): array
    {
        $testFiles = [];
        $testsBasePath = base_path($this->testPath);

        if (!is_dir($testsBasePath)) {
            return $testFiles;
        }

        $directory = new RecursiveDirectoryIterator($testsBasePath);
        $iterator = new RecursiveIteratorIterator($directory);

        /** @var SplFileInfo $info */
        foreach ($iterator as $info) {
            if (!$info->isFile()) {
                continue;
            }

            if ($info->getFilename() === $pattern) {
                $relativePath = $info->getPathname();

                if (str_starts_with($relativePath, base_path())) {
                    $relativePath = mb_substr($relativePath, mb_strlen(base_path()) + 1);
                }

                $testFiles[] = 'AM '.$relativePath;
            }
        }

        return $testFiles;
    }
}
