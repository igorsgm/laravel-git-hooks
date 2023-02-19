<?php

namespace Igorsgm\GitHooks\Contracts;

interface HookCommand
{
    /**
     * Get hook name
     */
    public function getHook(): string;

    /**
     * Get array of registered hooks
     */
    public function getRegisteredHooks(): array;
}
