<?php

declare(strict_types=1);

namespace Igorsgm\GitHooks\Console\Commands\Hooks;

use Closure;
use Igorsgm\GitHooks\Contracts\CodeAnalyzerPreCommitHook;
use Igorsgm\GitHooks\Git\ChangedFiles;
use Igorsgm\GitHooks\Support\Config;

class LarastanPreCommitHook extends BaseCodeAnalyzerPreCommitHook implements CodeAnalyzerPreCommitHook
{
    /**
     * Name of the hook
     */
    protected string $name = 'Larastan';

    /**
     * Config parameter for the analyzer command.
     */
    protected string $configParam = '';

    /**
     * Analyzes committed files using Larastan
     *
     * @param  ChangedFiles  $files  The list of committed files to analyze.
     * @param  Closure  $next  The next hook in the chain to execute.
     */
    public function handle(ChangedFiles $files, Closure $next): mixed
    {
        $this->configParam = $this->configParam();

        return $this->setFileExtensions(config('git-hooks.code_analyzers.larastan.file_extensions'))
            ->setAnalyzerExecutable(config('git-hooks.code_analyzers.larastan.path'))
            ->setRunInDocker(config('git-hooks.code_analyzers.larastan.run_in_docker'))
            ->setDockerContainer(config('git-hooks.code_analyzers.larastan.docker_container'))
            ->handleCommittedFiles($files, $next);
    }

    /**
     * Returns the command to run Larastan analyzer with the given configuration file.
     */
    public function analyzerCommand(): string
    {
        $additionalParams = $this->additionalParams();

        return mb_trim(
            sprintf('%s analyse %s %s', $this->getAnalyzerExecutable(), $this->configParam, $additionalParams)
        );
    }

    /**
     * Empty fixer command because Larastan doesn't provide any type of auto-fixing.
     */
    public function fixerCommand(): string
    {
        return '';
    }

    /**
     * Gets the command-line parameter for specifying the configuration file for Larastan.
     *
     * @return string The command-line parameter for the configuration file, or an empty string if not set.
     */
    protected function configParam(): string
    {
        $phpStanConfigFile = mb_rtrim(Config::string('git-hooks.code_analyzers.larastan.config'), '/');
        $this->validateConfigPath($phpStanConfigFile);

        return empty($phpStanConfigFile) ? '' : '--configuration='.$phpStanConfigFile;
    }

    /**
     * Retrieves additional parameters for Larastan from the configuration file,
     * filtering out pre-defined parameters to avoid conflicts.
     */
    protected function additionalParams(): string
    {
        $additionalParams = Config::string('git-hooks.code_analyzers.larastan.additional_params');

        if (!empty($additionalParams)) {
            $additionalParams = (string) preg_replace(
                '/\s*--(configuration|c|xdebug)\b(=\S*)?\s*/',
                '',
                $additionalParams
            );
        }

        return $additionalParams;
    }
}
