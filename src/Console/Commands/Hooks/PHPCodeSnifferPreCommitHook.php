<?php

namespace Igorsgm\GitHooks\Console\Commands\Hooks;

use Closure;
use Igorsgm\GitHooks\Contracts\CodeAnalyzerPreCommitHook;
use Igorsgm\GitHooks\Git\ChangedFiles;

class PHPCodeSnifferPreCommitHook extends BaseCodeAnalyzerPreCommitHook implements CodeAnalyzerPreCommitHook
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
    protected $name = 'PHP_CodeSniffer';

    /**
     * Analyze and fix committed PHP files using PHP Code Sniffer and PHP Code Beautifier and Fixer.
     *
     * @param  ChangedFiles  $files  The files that have been changed in the current commit.
     * @param  Closure  $next  A closure that represents the next middleware in the pipeline.
     * @return mixed|null
     */
    public function handle(ChangedFiles $files, Closure $next)
    {
        $this->configParam = $this->configParam();

        return $this->setFileExtensions(config('git-hooks.code_analyzers.php_code_sniffer.file_extensions'))
            ->setAnalyzerExecutable(config('git-hooks.code_analyzers.php_code_sniffer.phpcs_path'))
            ->setFixerExecutable(config('git-hooks.code_analyzers.php_code_sniffer.phpcbf_path'))
            ->handleCommittedFiles($files, $next);
    }

    /**
     * Returns the command to run PHPCS
     */
    public function analyzerCommand(): string
    {
        return trim(sprintf('%s %s', $this->getAnalyzerExecutable(), $this->configParam));
    }

    /**
     * Returns the command to run PHPCS
     */
    public function fixerCommand(): string
    {
        return trim(sprintf('%s %s', $this->getFixerExecutable(), $this->configParam));
    }

    /**
     * Returns the configuration parameter for the analyzer.
     * This method retrieves the PHP CodeSniffer standard from the Git hooks configuration file
     * and returns it as a string in the format '--standard=<standard>'.
     *
     * @return string The configuration parameter for the analyzer.
     */
    public function configParam(): string
    {
        $phpCSStandard = rtrim(config('git-hooks.code_analyzers.php_code_sniffer.standard'), '/');

        return empty($phpCSStandard) ? '' : '--standard='.$phpCSStandard;
    }
}
