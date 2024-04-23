<?php

namespace Igorsgm\GitHooks\Tests\Fixtures;

use Closure;
use Igorsgm\GitHooks\Contracts\PreCommitHook;
use Igorsgm\GitHooks\Git\ChangedFiles;
use Illuminate\Console\Command;

class PreCommitFixtureHook implements PreCommitHook
{
    public function getName(): string
    {
        return 'MyPreCommitHook1';
    }

    public function handle(ChangedFiles $files, Closure $next): mixed
    {
        return $next($files);
    }

    public function setCommand(Command $command): void
    {
        // nothing to do
    }
}
