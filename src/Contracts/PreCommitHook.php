<?php

namespace Igorsgm\GitHooks\Contracts;

use Closure;
use Igorsgm\GitHooks\Git\ChangedFiles;

interface PreCommitHook extends Hook
{
    /**
     * @param  ChangedFiles  $files
     * @param  Closure  $next
     * @return mixed
     */
    public function handle(ChangedFiles $files, Closure $next);
}
