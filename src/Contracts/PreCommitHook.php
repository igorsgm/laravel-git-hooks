<?php

declare(strict_types=1);

namespace Igorsgm\GitHooks\Contracts;

use Closure;
use Igorsgm\GitHooks\Git\ChangedFiles;

interface PreCommitHook extends Hook
{
    public function handle(ChangedFiles $files, Closure $next): mixed;
}
