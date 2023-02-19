<?php

namespace Igorsgm\GitHooks\Contracts;

use Closure;
use Igorsgm\GitHooks\Git\ChangedFiles;

interface PreCommitHook extends Hook
{
    /**
     * @return mixed
     */
    public function handle(ChangedFiles $files, Closure $next);
}
