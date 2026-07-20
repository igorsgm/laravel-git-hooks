<?php

declare(strict_types=1);

namespace Igorsgm\GitHooks\Console\Commands\Hooks;

use Igorsgm\GitHooks\Git\ChangedFile;
use Igorsgm\GitHooks\Support\Config;

class CodeceptionPreCommitHook extends BaseTestRunnerPreCommitHook
{
    protected string $name = 'Codeception';

    protected string $testPath = 'tests';

    protected function getConfigPath(): string
    {
        return 'git-hooks.code_analyzers.codeception';
    }

    protected function getTestCommand(): string
    {
        return 'run';
    }

    protected function getAdditionalParams(): string
    {
        $additionalParams = Config::string('git-hooks.code_analyzers.codeception.additional_params');

        if (!empty($additionalParams)) {
            $additionalParams = (string) preg_replace('/\s*--(filter)\b(=\S*)?\s*/', '', $additionalParams);
        }

        return $additionalParams;
    }

    /**
     * @return array<int, string>
     */
    protected function getTestFilePatterns(string $withoutExtension): array
    {
        return [
            $withoutExtension.'Cest.php',
            $withoutExtension.'Test.php',
            $withoutExtension.'Cept.php',
        ];
    }

    protected function shouldSkipFile(ChangedFile $file): bool
    {
        $filePath = $file->getFilePath();

        if (str_contains($filePath, $this->testPath)) {
            return true;
        }

        return pathinfo($filePath, PATHINFO_EXTENSION) !== 'php';
    }
}
