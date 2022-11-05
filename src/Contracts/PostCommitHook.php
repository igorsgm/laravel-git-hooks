<?php

namespace Igorsgm\LaravelGitHooks\Contracts;

use Closure;
use Igorsgm\LaravelGitHooks\Git\Log;

interface PostCommitHook extends Hook
{
    /**
     * @param  Log  $log
     * @param  Closure  $next
     * @return mixed
     */
    public function handle(Log $log, Closure $next);
}
