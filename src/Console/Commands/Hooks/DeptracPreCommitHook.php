<?php

declare(strict_types=1);

namespace Igorsgm\GitHooks\Console\Commands\Hooks;

use Closure;
use Igorsgm\GitHooks\Contracts\CodeAnalyzerPreCommitHook;
use Igorsgm\GitHooks\Git\ChangedFiles;
use Igorsgm\GitHooks\Support\Config;

class DeptracPreCommitHook extends BaseCodeAnalyzerPreCommitHook implements CodeAnalyzerPreCommitHook
{
    /**
     * Name of the hook
     */
    protected string $name = 'Deptrac';

    /**
     * Config parameter for the analyzer command.
     */
    protected string $configParam = '';

    /**
     * Analyze committed PHP files using Deptrac
     *
     * @param  ChangedFiles  $files  The files that have been changed in the current commit.
     * @param  Closure  $next  A closure that represents the next middleware in the pipeline.
     */
    public function handle(ChangedFiles $files, Closure $next): mixed
    {
        $this->configParam = $this->configParam();

        return $this->setFileExtensions(config('git-hooks.code_analyzers.deptrac.file_extensions'))
            ->setAnalyzerExecutable(config('git-hooks.code_analyzers.deptrac.path'))
            ->setRunInDocker(config('git-hooks.code_analyzers.deptrac.run_in_docker'))
            ->setDockerContainer(config('git-hooks.code_analyzers.deptrac.docker_container'))
            ->handleCommittedFiles($files, $next);
    }

    /**
     * Returns the command to run Deptrac analyzer
     */
    public function analyzerCommand(): string
    {
        return mb_trim(sprintf(
            '%s analyse %s --no-progress %s',
            $this->getAnalyzerExecutable(),
            $this->configParam,
            $this->additionalParams()
        ));
    }

    /**
     * Empty fixer command because Deptrac doesn't have auto-fix.
     */
    public function fixerCommand(): string
    {
        return '';
    }

    /**
     * Gets the command-line parameter for specifying the configuration file for Deptrac.
     *
     * @return string The command-line parameter for the configuration file, or an empty string if not set.
     */
    protected function configParam(): string
    {
        $deptracConfig = mb_rtrim(Config::string('git-hooks.code_analyzers.deptrac.config'), '/');

        if (!empty($deptracConfig)) {
            $this->validateConfigPath($deptracConfig);

            return '--config='.$deptracConfig;
        }

        return '';
    }

    /**
     * Retrieves additional parameters for Deptrac from the configuration file,
     * filtering out pre-defined parameters to avoid conflicts.
     */
    protected function additionalParams(): string
    {
        $additionalParams = Config::string('git-hooks.code_analyzers.deptrac.additional_params');

        if (!empty($additionalParams)) {
            $additionalParams = (string) preg_replace(
                '/\s*--(config|c|no-progress)\b(=\S*)?\s*/',
                '',
                $additionalParams
            );
        }

        return $additionalParams;
    }
}
