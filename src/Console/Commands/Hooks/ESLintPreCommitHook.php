<?php

namespace Igorsgm\GitHooks\Console\Commands\Hooks;

use Closure;
use Igorsgm\GitHooks\Contracts\CodeAnalyzerPreCommitHook;
use Igorsgm\GitHooks\Git\ChangedFiles;

class ESLintPreCommitHook extends BaseCodeAnalyzerPreCommitHook implements CodeAnalyzerPreCommitHook
{
    /**
     * @var string
     */
    protected $configParam;

    /**
     * Name of the hook
     *
     * @var string
     */
    protected $name = 'ESLint';

    /**
     * Analyze and fix committed JS files using ESLint.
     *
     * @param  ChangedFiles  $files  The files that have been changed in the current commit.
     * @param  Closure  $next  A closure that represents the next middleware in the pipeline.
     * @return mixed|null
     */
    public function handle(ChangedFiles $files, Closure $next)
    {
        $this->configParam = $this->configParam();

        return $this->setFileExtensions('/\.(jsx?|tsx?|vue)$/')
            ->setAnalyzerExecutable(config('git-hooks.code_analyzers.eslint.path'), true)
            ->handleCommittedFiles($files, $next);
    }

    /**
     * Returns the command to run ESLint tester
     */
    public function analyzerCommand(): string
    {
        return trim(implode(' ', [
            $this->getAnalyzerExecutable(),
            $this->configParam,
            $this->additionalParams()
        ]));
    }

    /**
     * Returns the command to run ESLint fixer
     */
    public function fixerCommand(): string
    {
        return trim(
            sprintf('%s --fix %s %s', $this->getFixerExecutable(), $this->configParam, $this->additionalParams())
        );
    }

    /**
     * Gets the command-line parameter for specifying the configuration file for ESLint.
     *
     * @return string The command-line parameter for the configuration file, or an empty string if not set.
     */
    protected function configParam(): string
    {
        $eslintConfig = rtrim(config('git-hooks.code_analyzers.eslint.config'), '/');

        return empty($eslintConfig) ? '' : '--config='.$eslintConfig;
    }

    /**
     * Retrieves additional parameters for the ESLint code analyzer from the configuration file,
     * filters out pre-defined parameters to avoid conflicts, and returns them as a string.
     */
    protected function additionalParams(): ?string
    {
        $additionalParams = config('git-hooks.code_analyzers.eslint.additional_params');

        if (! empty($additionalParams)) {
            $additionalParams = preg_replace('/\s+\.(?:(\s)|$)/', '$1', $additionalParams);
            $additionalParams = preg_replace('/\s*--(config|c)\b(=\S*)?\s*/', '', $additionalParams);
        }

        return $additionalParams;
    }
}
