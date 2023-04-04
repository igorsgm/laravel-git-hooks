<?php

namespace Igorsgm\GitHooks\Console\Commands\Hooks;

use Closure;
use Igorsgm\GitHooks\Contracts\CodeAnalyzerPreCommitHook;
use Igorsgm\GitHooks\Git\ChangedFiles;

class PintPreCommitHook extends BaseCodeAnalyzerPreCommitHook implements CodeAnalyzerPreCommitHook
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
    protected $name = 'Laravel Pint';

    /**
     * Analyze and fix committed PHP files using Laravel Pint
     *
     * @param  ChangedFiles  $files  The files that have been changed in the current commit.
     * @param  Closure  $next  A closure that represents the next middleware in the pipeline.
     * @return mixed|null
     */
    public function handle(ChangedFiles $files, Closure $next)
    {
        $this->configParam = $this->configParam();

        return $this->setFileExtensions(config('git-hooks.code_analyzers.laravel_pint.file_extensions'))
            ->setAnalyzerExecutable(config('git-hooks.code_analyzers.laravel_pint.path'), true)
            ->handleCommittedFiles($files, $next);
    }

    /**
     * Returns the command to run Pint tester
     */
    public function analyzerCommand(): string
    {
        return trim(sprintf('%s --test %s', $this->getAnalyzerExecutable(), $this->configParam));
    }

    /**
     * Returns the command to run Pint fixer
     */
    public function fixerCommand(): string
    {
        return trim(sprintf('%s %s', $this->getFixerExecutable(), $this->configParam));
    }

    /**
     * Gets the command-line parameter for specifying the configuration file for Laravel Pint.
     *
     * @return string The command-line parameter for the configuration file, or an empty string if not set.
     */
    protected function configParam(): string
    {
        $pintConfigFile = config('git-hooks.code_analyzers.laravel_pint.config');

        if (! empty($pintConfigFile)) {
            $this->validateConfigPath($pintConfigFile);

            return '--config '.trim($pintConfigFile, '/');
        }

        $pintPreset = config('git-hooks.code_analyzers.laravel_pint.preset');

        return empty($pintPreset) ? '' : '--preset '.trim($pintPreset, '/');
    }
}
