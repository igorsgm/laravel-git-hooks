<?php

namespace Igorsgm\GitHooks\Console\Commands\Hooks;

use Closure;
use Igorsgm\GitHooks\Contracts\CodeAnalyzerPreCommitHook;
use Igorsgm\GitHooks\Git\ChangedFiles;

class PrettierPreCommitHook extends BaseCodeAnalyzerPreCommitHook implements CodeAnalyzerPreCommitHook
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
    protected $name = 'Prettier';

    /**
     * Analyze and fix committed JS files using Prettier.
     *
     * @param  ChangedFiles  $files  The files that have been changed in the current commit.
     * @param  Closure  $next  A closure that represents the next middleware in the pipeline.
     * @return mixed|null
     */
    public function handle(ChangedFiles $files, Closure $next)
    {
        $this->configParam = $this->configParam();

        return $this->setFileExtensions(config('git-hooks.code_analyzers.prettier.file_extensions'))
            ->setAnalyzerExecutable(config('git-hooks.code_analyzers.prettier.path'), true)
            ->handleCommittedFiles($files, $next);
    }

    /**
     * Returns the command to run Prettier tester
     */
    public function analyzerCommand(): string
    {
        return trim(
            sprintf('%s --check %s %s', $this->getAnalyzerExecutable(), $this->configParam, $this->additionalParams())
        );
    }

    /**
     * Returns the command to run Prettier fixer
     */
    public function fixerCommand(): string
    {
        return trim(
            sprintf('%s --write %s %s', $this->getFixerExecutable(), $this->configParam, $this->additionalParams())
        );
    }

    /**
     * Gets the command-line parameter for specifying the configuration file for Prettier.
     *
     * @return string The command-line parameter for the configuration file, or an empty string if not set.
     */
    protected function configParam(): string
    {
        $prettierConfig = rtrim(config('git-hooks.code_analyzers.prettier.config'), '/');

        return empty($prettierConfig) ? '' : '--config='.$prettierConfig;
    }

    /**
     * Retrieves additional parameters for the Prettier code analyzer from the configuration file,
     * filters out pre-defined parameters to avoid conflicts, and returns them as a string.
     */
    protected function additionalParams(): ?string
    {
        $additionalParams = config('git-hooks.code_analyzers.prettier.additional_params');

        if (! empty($additionalParams)) {
            $additionalParams = preg_replace('/\s+\.(?:(\s)|$)/', '$1', $additionalParams);
            $additionalParams = preg_replace('/\s*--(config|find-config-path|write|check)\b(=\S*)?\s*/', '', $additionalParams);
        }

        return $additionalParams;
    }
}
