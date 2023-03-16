<?php

namespace Igorsgm\GitHooks\Console\Commands\Hooks;

use Closure;
use Igorsgm\GitHooks\Contracts\CodeAnalyzerPreCommitHook;
use Igorsgm\GitHooks\Git\ChangedFiles;

class BladeFormatterPreCommitHook extends BaseCodeAnalyzerPreCommitHook implements CodeAnalyzerPreCommitHook
{
    /**
     * Get the name of the hook.
     */
    public function getName(): ?string
    {
        return 'Blade Formatter';
    }

    /**
     * Analyze and fix committed blade.php files using blade-formatter npm package
     *
     * @param  ChangedFiles  $files  The files that have been changed in the current commit.
     * @param  Closure  $next  A closure that represents the next middleware in the pipeline.
     * @return mixed|null
     */
    public function handle(ChangedFiles $files, Closure $next)
    {
        return $this->setFileExtensions('/\.blade\.php$/')
            ->setAnalyzerExecutable(config('git-hooks.code_analyzers.blade_formatter.path'), true)
            ->handleCommittedFiles($files, $next);
    }

    /**
     * Returns the command to run Blade Formatter tester
     */
    public function analyzerCommand(): string
    {
        return trim(sprintf('%s -c', $this->getAnalyzerExecutable()));
    }

    /**
     * Returns the command to run Blade Formatter fixer
     */
    public function fixerCommand(): string
    {
        return trim(sprintf('%s --write', $this->getFixerExecutable()));
    }
}
