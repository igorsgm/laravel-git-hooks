<?php

namespace Igorsgm\GitHooks\Tests\Fixtures;

use Closure;
use Igorsgm\GitHooks\Contracts\PreCommitHook;
use Igorsgm\GitHooks\Git\ChangedFiles;

class PreCommitFixtureHook implements PreCommitHook
{
    public function getName(): string
    {
        return 'MyPreCommitHook1';
    }

    public function handle(ChangedFiles $files, Closure $next)
    {
        return $next($files);
    }
}
