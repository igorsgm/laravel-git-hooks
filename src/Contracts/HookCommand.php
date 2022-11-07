<?php

namespace Igorsgm\GitHooks\Contracts;

use Illuminate\Contracts\Config\Repository;

interface HookCommand
{
    /**
     * Get config repository
     *
     * @return Repository
     */
    public function getConfig(): Repository;

    /**
     * Get hook name
     *
     * @return string
     */
    public function getHook(): string;

    /**
     * Get array of registered hooks
     *
     * @return array
     */
    public function getRegisteredHooks(): array;
}
