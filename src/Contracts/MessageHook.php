<?php

namespace Igorsgm\GitHooks\Contracts;

use Closure;
use Igorsgm\GitHooks\Git\CommitMessage;

interface MessageHook extends Hook
{
    /**
     * @param  CommitMessage  $message
     * @param  Closure  $next
     */
    public function handle(CommitMessage $message, Closure $next);
}
