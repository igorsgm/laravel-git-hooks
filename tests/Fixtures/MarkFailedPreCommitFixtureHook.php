<?php

declare(strict_types=1);

namespace Igorsgm\GitHooks\Tests\Fixtures;

use Closure;
use Igorsgm\GitHooks\Contracts\PreCommitHook;
use Igorsgm\GitHooks\Git\ChangedFiles;
use Igorsgm\GitHooks\Traits\WithPipelineFailCheck;
use Illuminate\Console\Command;

class MarkFailedPreCommitFixtureHook implements PreCommitHook
{
    use WithPipelineFailCheck;

    public function getName(): string
    {
        return 'MarkFailedHook';
    }

    public function handle(ChangedFiles $files, Closure $next): mixed
    {
        $this->markPipelineFailed();

        return $next($files);
    }

    public function setCommand(Command $command): void
    {
        // nothing to do
    }
}
