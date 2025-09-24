<?php

declare(strict_types=1);

namespace Igorsgm\GitHooks\Console\Commands\Hooks;

use Closure;
use Igorsgm\GitHooks\Contracts\CodeAnalyzerPreCommitHook;
use Igorsgm\GitHooks\Git\ChangedFiles;

class PHPCSFixerPreCommitHook extends BaseCodeAnalyzerPreCommitHook implements CodeAnalyzerPreCommitHook
{
    protected string $configParam;

    /**
     * Name of the hook
     */
    protected string $name = 'PHP_CS_Fixer';

    /**
     * Analyze and fix committed PHP files using PHP CS Fixer.
     *
     * @param  ChangedFiles  $files  The files that have been changed in the current commit.
     * @param  Closure  $next  A closure that represents the next middleware in the pipeline.
     */
    public function handle(ChangedFiles $files, Closure $next): mixed
    {
        $this->configParam = $this->configParam();

        return $this->setFileExtensions(config('git-hooks.code_analyzers.php_cs_fixer.file_extensions'))
            ->setAnalyzerExecutable(config('git-hooks.code_analyzers.php_cs_fixer.path'))
            ->setRunInDocker(config('git-hooks.code_analyzers.php_cs_fixer.run_in_docker'))
            ->setDockerContainer(config('git-hooks.code_analyzers.php_cs_fixer.docker_container'))
            ->handleCommittedFiles($files, $next);
    }

    /**
     * Returns the command to run PHPCS
     */
    public function analyzerCommand(): string
    {
        return mb_trim(sprintf('%s check %s', $this->getAnalyzerExecutable(), $this->configParam));
    }

    /**
     * Returns the command to run PHPCS
     */
    public function fixerCommand(): string
    {
        return mb_trim(sprintf('%s fix %s', $this->getAnalyzerExecutable(), $this->configParam));
    }

    /**
     * Gets the command-line parameter for specifying the configuration file for PHP CS Fixer.
     *
     * @return string The configuration parameter for the analyzer.
     */
    public function configParam(): string
    {
        $configFile = (string) config('git-hooks.code_analyzers.php_cs_fixer.config');

        if (!empty($configFile)) {
            $this->validateConfigPath($configFile);

            return '--config='.$configFile;
        }

        return '';
    }
}
