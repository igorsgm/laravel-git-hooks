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
     *
     * @return array<int, callable>
     */
    public function getRegisteredHooks(): array;
}
