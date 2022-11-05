<?php

namespace Igorsgm\LaravelGitHooks\Contracts;

use Closure;
use Igorsgm\LaravelGitHooks\Git\CommitMessage;

interface MessageHook extends Hook
{
    /**
     * @param  CommitMessage  $message
     * @param  Closure  $next
     */
    public function handle(CommitMessage $message, Closure $next);
}
