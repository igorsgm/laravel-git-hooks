<?php

declare(strict_types=1);

namespace Igorsgm\GitHooks\Console\Commands\Hooks;

use Closure;
use Igorsgm\GitHooks\Contracts\CodeAnalyzerPreCommitHook;
use Igorsgm\GitHooks\Git\ChangedFiles;

class RectorPreCommitHook extends BaseCodeAnalyzerPreCommitHook implements CodeAnalyzerPreCommitHook
{
    protected string $configParam;

    /**
     * Name of the hook
     */
    protected string $name = 'Rector';

    /**
     * Analyze committed PHP files using Rector
     *
     * @param  ChangedFiles  $files  The files that have been changed in the current commit.
     * @param  Closure  $next  A closure that represents the next middleware in the pipeline.
     */
    public function handle(ChangedFiles $files, Closure $next): mixed
    {
        $this->configParam = $this->configParam();

        return $this->setFileExtensions(config('git-hooks.code_analyzers.rector.file_extensions'))
            ->setAnalyzerExecutable(config('git-hooks.code_analyzers.rector.path'), true)
            ->setRunInDocker(config('git-hooks.code_analyzers.rector.run_in_docker'))
            ->setDockerContainer(config('git-hooks.code_analyzers.rector.docker_container'))
            ->handleCommittedFiles($files, $next);
    }

    /**
     * Returns the command to run Rector tester
     */
    public function analyzerCommand(): string
    {
        return trim(sprintf('%s process --dry-run %s %s', $this->getAnalyzerExecutable(), $this->configParam, $this->additionalParams()));
    }

    /**
     * Fixer command
     */
    public function fixerCommand(): string
    {
        return trim(sprintf('%s process %s %s', $this->getAnalyzerExecutable(), $this->configParam, $this->additionalParams()));
    }

    /**
     * Gets the command-line parameter for specifying the configuration file for Rector.
     *
     * @return string The command-line parameter for the configuration file, or an empty string if not set.
     */
    protected function configParam(): string
    {
        $rectorConfigFile = (string) config('git-hooks.code_analyzers.rector.config');

        if (! empty($rectorConfigFile)) {
            $this->validateConfigPath($rectorConfigFile);

            return '--config='.$rectorConfigFile;
        }

        return '';
    }

    /**
     * Retrieves additional parameters for the Rector code analyzer from the configuration file
     */
    protected function additionalParams(): ?string
    {
        $additionalParams = (string) config('git-hooks.code_analyzers.rector.additional_params');

        return $additionalParams;
    }
}
