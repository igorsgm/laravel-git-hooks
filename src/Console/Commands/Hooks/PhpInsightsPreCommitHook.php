<?php

declare(strict_types=1);

namespace Igorsgm\GitHooks\Console\Commands\Hooks;

use Closure;
use Igorsgm\GitHooks\Contracts\CodeAnalyzerPreCommitHook;
use Igorsgm\GitHooks\Git\ChangedFiles;

class PhpInsightsPreCommitHook extends BaseCodeAnalyzerPreCommitHook implements CodeAnalyzerPreCommitHook
{
    protected string $configParam;

    /**
     * Name of the hook
     */
    protected string $name = 'PhpInsights';

    /**
     * Analyze committed PHP files using phpinsights
     *
     * @param  ChangedFiles  $files  The files that have been changed in the current commit.
     * @param  Closure  $next  A closure that represents the next middleware in the pipeline.
     */
    public function handle(ChangedFiles $files, Closure $next): mixed
    {
        $this->configParam = $this->configParam();

        return $this->setFileExtensions(config('git-hooks.code_analyzers.phpinsights.file_extensions'))
            ->setAnalyzerExecutable(config('git-hooks.code_analyzers.phpinsights.path'), true)
            ->setRunInDocker(config('git-hooks.code_analyzers.phpinsights.run_in_docker'))
            ->setDockerContainer(config('git-hooks.code_analyzers.phpinsights.docker_container'))
            ->handleCommittedFiles($files, $next);
    }

    /**
     * Returns the command to run phpinsights
     */
    public function analyzerCommand(): string
    {
        return trim(sprintf('%s analyse %s --no-interaction %s', $this->getAnalyzerExecutable(), $this->configParam, $this->additionalParams()));
    }

    /**
     * Fixer command
     */
    public function fixerCommand(): string
    {
        return trim(sprintf('%s analyse %s --no-interaction --fix %s', $this->getAnalyzerExecutable(), $this->configParam, $this->additionalParams()));
    }

    /**
     * Gets the command-line parameter for specifying the configuration file for phpinsights.
     *
     * @return string The command-line parameter for the configuration file, or an empty string if not set.
     */
    protected function configParam(): string
    {
        $phpInsightsConfigFile = (string) config('git-hooks.code_analyzers.phpinsights.config');

        if (! empty($phpInsightsConfigFile)) {
            $this->validateConfigPath($phpInsightsConfigFile);

            return '--config-path='.$phpInsightsConfigFile;
        }

        return '';
    }

    /**
     * Retrieves additional parameters for the phpinsights from the configuration file
     */
    protected function additionalParams(): ?string
    {
        $additionalParams = (string) config('git-hooks.code_analyzers.phpinsights.additional_params');

        return $additionalParams;
    }
}
