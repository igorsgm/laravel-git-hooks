<?php

declare(strict_types=1);

namespace Igorsgm\GitHooks\Console\Commands\Hooks;

use Igorsgm\GitHooks\Support\Config;

class PestPreCommitHook extends BaseTestRunnerPreCommitHook
{
    protected string $name = 'Pest';

    protected string $testPath = 'tests';

    protected function getConfigPath(): string
    {
        return 'git-hooks.code_analyzers.pest';
    }

    protected function getTestCommand(): string
    {
        return '';
    }

    protected function getAdditionalParams(): string
    {
        $additionalParams = Config::string('git-hooks.code_analyzers.pest.additional_params');

        if (!empty($additionalParams)) {
            $additionalParams = (string) preg_replace('/\s*--(filter|f)\b(=\S*)?\s*/', '', $additionalParams);
        }

        return $additionalParams;
    }

    /**
     * @return array<int, string>
     */
    protected function getTestFilePatterns(string $withoutExtension): array
    {
        return [
            $withoutExtension.'Test.php',
            $withoutExtension.'.Test.php',
        ];
    }
}
