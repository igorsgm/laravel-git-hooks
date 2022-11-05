<?php

namespace Igorsgm\LaravelGitHooks\Contracts;

use Closure;
use Igorsgm\LaravelGitHooks\Git\ChangedFiles;

interface PreCommitHook extends Hook
{
    /**
     * @param  ChangedFiles  $files
     * @param  Closure  $next
     * @return mixed
     */
    public function handle(ChangedFiles $files, Closure $next);
}
