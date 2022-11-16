<?php

namespace Igorsgm\GitHooks;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Igorsgm\GitHooks\GitHooks
 */
class GitHooksFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'laravel-git-hooks';
    }
}
