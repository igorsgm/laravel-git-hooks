<?php

declare(strict_types=1);

namespace Igorsgm\GitHooks\Contracts;

use Closure;
use Igorsgm\GitHooks\Git\Log;

interface PrePushHook extends Hook
{
    public function handle(Log $log, Closure $next): mixed;
}
