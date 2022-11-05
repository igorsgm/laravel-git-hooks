<?php

namespace Igorsgm\LaravelGitHooks;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Igorsgm\LaravelGitHooks\Skeleton\SkeletonClass
 */
class LaravelGitHooksFacade extends Facade
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
