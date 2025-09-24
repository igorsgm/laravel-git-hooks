<?php

declare(strict_types=1);

namespace Igorsgm\GitHooks\Console\Commands\Hooks;

use Closure;
use Igorsgm\GitHooks\Contracts\CodeAnalyzerPreCommitHook;
use Igorsgm\GitHooks\Git\ChangedFiles;

class BladeFormatterPreCommitHook extends BaseCodeAnalyzerPreCommitHook implements CodeAnalyzerPreCommitHook
{
    protected string $configParam;

    protected string $name = 'Blade Formatter';

    /**
     * Analyze and fix committed blade.php files using blade-formatter npm package
     *
     * @param  ChangedFiles  $files  The files that have been changed in the current commit.
     * @param  Closure  $next  A closure that represents the next middleware in the pipeline.
     */
    public function handle(ChangedFiles $files, Closure $next): mixed
    {
        $this->configParam = $this->configParam();

        return $this->setFileExtensions(config('git-hooks.code_analyzers.blade_formatter.file_extensions'))
            ->setAnalyzerExecutable(config('git-hooks.code_analyzers.blade_formatter.path'), true)
            ->setRunInDocker(config('git-hooks.code_analyzers.blade_formatter.run_in_docker'))
            ->setDockerContainer(config('git-hooks.code_analyzers.blade_formatter.docker_container'))
            ->handleCommittedFiles($files, $next);
    }

    /**
     * Returns the command to run Blade Formatter tester
     */
    public function analyzerCommand(): string
    {
        return mb_trim(sprintf('%s -c %s', $this->getAnalyzerExecutable(), $this->configParam));
    }

    /**
     * Returns the command to run Blade Formatter fixer
     */
    public function fixerCommand(): string
    {
        return mb_trim(sprintf('%s --write %s', $this->getFixerExecutable(), $this->configParam));
    }

    /**
     * Returns the configuration parameter for the analyzer.
     * This method retrieves the Blade Formatter configuration path (usually .bladeformatterrc.json or .bladeformatterrc)
     * from the Git hooks configuration file and returns it as a string in the format '--config=<configFile>'.
     *
     * @return string The configuration parameter for the analyzer.
     */
    public function configParam(): string
    {
        $bladeFormatterConfig = mb_rtrim((string) config('git-hooks.code_analyzers.blade_formatter.config'), '/');
        $this->validateConfigPath($bladeFormatterConfig);

        return empty($bladeFormatterConfig) ? '' : '--config='.$bladeFormatterConfig;
    }
}
