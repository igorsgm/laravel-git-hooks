<?php

namespace Igorsgm\GitHooks\Tests\Fixtures;

use Igorsgm\GitHooks\Console\Commands\Hooks\BaseCodeAnalyzerPreCommitHook;

class ConcreteBaseCodeAnalyzerFixture extends BaseCodeAnalyzerPreCommitHook
{
    public function analyzerCommand(): string {}

    public function fixerCommand(): string {}

    public function handle(\Igorsgm\GitHooks\Git\ChangedFiles $files, \Closure $next): mixed {}
}
