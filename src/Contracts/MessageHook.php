<?php

namespace Igorsgm\GitHooks\Contracts;

use Closure;
use Igorsgm\GitHooks\Git\CommitMessage;

interface MessageHook extends Hook
{
    public function handle(CommitMessage $message, Closure $next);
}
