<?php

declare(strict_types=1);

namespace Igorsgm\GitHooks\Console\Commands\Hooks;

use Closure;
use Igorsgm\GitHooks\Contracts\CodeAnalyzerPreCommitHook;
use Igorsgm\GitHooks\Git\ChangedFile;
use Igorsgm\GitHooks\Git\ChangedFiles;
use Igorsgm\GitHooks\Support\Config;

class VitestPreCommitHook extends BaseTestRunnerPreCommitHook implements CodeAnalyzerPreCommitHook
{
    protected string $name = 'Vitest';

    protected string $testPath = 'tests';

    public function handle(ChangedFiles $files, Closure $next): mixed
    {
        $potentialTestFiles = $this->findTestFilesForChangedFiles($files);

        if (empty($potentialTestFiles)) {
            return $next($files);
        }

        $this->chunkSize = 1;

        $testFiles = new ChangedFiles(implode(PHP_EOL, $potentialTestFiles));

        return $this->setFileExtensions('all')
            ->setAnalyzerExecutable(config('git-hooks.code_analyzers.vitest.path'), true)
            ->setRunInDocker(config('git-hooks.code_analyzers.vitest.run_in_docker'))
            ->setDockerContainer(config('git-hooks.code_analyzers.vitest.docker_container'))
            ->handleCommittedFiles($testFiles, $next);
    }

    public function analyzerCommand(): string
    {
        return mb_trim(sprintf(
            '%s run %s',
            $this->getAnalyzerExecutable(),
            $this->getAdditionalParams()
        ));
    }

    public function fixerCommand(): string
    {
        return mb_trim(sprintf(
            '%s run --update %s',
            $this->getFixerExecutable(),
            $this->getAdditionalParams()
        ));
    }

    protected function getConfigPath(): string
    {
        return 'git-hooks.code_analyzers.vitest';
    }

    protected function getTestCommand(): string
    {
        return 'run';
    }

    protected function getAdditionalParams(): string
    {
        $additionalParams = Config::string('git-hooks.code_analyzers.vitest.additional_params');

        if (!empty($additionalParams)) {
            $additionalParams = (string) preg_replace('/\s*--(filter)\b(=\S*)?\s*/', '', $additionalParams);
        }

        return $additionalParams;
    }

    /**
     * Vitest overrides findTestFiles() directly so this is never called.
     * It satisfies the abstract contract of BaseTestRunnerPreCommitHook.
     *
     * @return array<int, string>
     */
    protected function getTestFilePatterns(string $withoutExtension): array
    {
        return [];
    }

    /**
     * @return array<int, string>
     */
    protected function findTestFiles(string $file): array
    {
        $testFiles = [];
        $baseName = basename($file);
        $withoutExtension = pathinfo($baseName, PATHINFO_FILENAME);

        $patterns = [
            $withoutExtension.'.test.'.$this->getExtension($file),
            $withoutExtension.'.spec.'.$this->getExtension($file),
            $withoutExtension.'.test.ts',
            $withoutExtension.'.spec.ts',
            $withoutExtension.'.test.tsx',
            $withoutExtension.'.spec.tsx',
        ];

        foreach ($patterns as $pattern) {
            $found = $this->findTestPattern($pattern);
            $testFiles = array_merge($testFiles, $found);
        }

        return $testFiles;
    }

    protected function getExtension(string $file): string
    {
        return pathinfo($file, PATHINFO_EXTENSION);
    }

    protected function shouldSkipFile(ChangedFile $file): bool
    {
        $filePath = $file->getFilePath();

        if (str_contains($filePath, $this->testPath)) {
            return true;
        }

        $extension = pathinfo($filePath, PATHINFO_EXTENSION);
        if (!in_array($extension, ['js', 'jsx', 'ts', 'tsx', 'vue'], true)) {
            return true;
        }

        return false;
    }
}
