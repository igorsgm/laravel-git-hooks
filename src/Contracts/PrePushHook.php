<?php

namespace Igorsgm\GitHooks\Contracts;

use Closure;
use Igorsgm\GitHooks\Git\Log;

interface PrePushHook extends Hook
{
    /**
     * @param  Log  $log
     * @param  Closure  $next
     * @return mixed
     */
    public function handle(Log $log, Closure $next);
}
