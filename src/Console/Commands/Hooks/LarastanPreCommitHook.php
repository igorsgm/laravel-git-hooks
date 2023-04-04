<?php

namespace Igorsgm\GitHooks\Console\Commands\Hooks;

use Closure;
use Igorsgm\GitHooks\Contracts\CodeAnalyzerPreCommitHook;
use Igorsgm\GitHooks\Git\ChangedFiles;

class LarastanPreCommitHook extends BaseCodeAnalyzerPreCommitHook implements CodeAnalyzerPreCommitHook
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
    protected $name = 'Larastan';

    /**
     * Analyzes committed files using Larastan
     *
     * @param  ChangedFiles  $files  The list of committed files to analyze.
     * @param  Closure  $next  The next hook in the chain to execute.
     * @return mixed|null
     */
    public function handle(ChangedFiles $files, Closure $next)
    {
        $this->configParam = $this->configParam();

        return $this->setAnalyzerExecutable(config('git-hooks.code_analyzers.larastan.path'))
            ->handleCommittedFiles($files, $next);
    }

    /**
     * Returns the command to run Larastan analyzer with the given configuration file.
     * By default, it turns off XDebug if it’s enabled to achieve better performance.
     */
    public function analyzerCommand(): string
    {
        $additionalParams = config('git-hooks.code_analyzers.larastan.additional_params');

        if (! empty($additionalParams)) {
            // Removing configuration/c/xdebug parameters from additional parameters to avoid conflicts
            // because they are already set in the command by default.
            $additionalParams = preg_replace('/\s*--(configuration|c|xdebug)\b(=\S*)?\s*/', '', $additionalParams);
        }

        return trim(
            sprintf('%s analyse %s --xdebug %s', $this->getAnalyzerExecutable(), $this->configParam, $additionalParams)
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
        $phpStanConfigFile = rtrim(config('git-hooks.code_analyzers.larastan.config'), '/');
        $this->validateConfigPath($phpStanConfigFile);

        return empty($phpStanConfigFile) ? '' : '--configuration='.$phpStanConfigFile;
    }
}
