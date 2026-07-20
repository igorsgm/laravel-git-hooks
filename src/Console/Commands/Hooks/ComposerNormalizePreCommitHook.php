<?php

declare(strict_types=1);

namespace Igorsgm\GitHooks\Console\Commands\Hooks;

use Closure;
use Igorsgm\GitHooks\Contracts\CodeAnalyzerPreCommitHook;
use Igorsgm\GitHooks\Git\ChangedFiles;

class ComposerNormalizePreCommitHook extends BaseCodeAnalyzerPreCommitHook implements CodeAnalyzerPreCommitHook
{
    protected string $name = 'Composer Normalize';

    public function handle(ChangedFiles $files, Closure $next): mixed
    {
        return $this->setFileExtensions('/composer\.json$/')
            ->setAnalyzerExecutable(config('git-hooks.code_analyzers.composer_normalize.path'), true)
            ->setRunInDocker(config('git-hooks.code_analyzers.composer_normalize.run_in_docker'))
            ->setDockerContainer(config('git-hooks.code_analyzers.composer_normalize.docker_container'))
            ->handleCommittedFiles($files, $next);
    }

    public function analyzerCommand(): string
    {
        return sprintf('%s normalize --no-interaction', $this->getAnalyzerExecutable());
    }

    public function fixerCommand(): string
    {
        return sprintf('%s normalize --no-interaction', $this->getAnalyzerExecutable());
    }
}
