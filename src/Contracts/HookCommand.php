<?php

namespace Igorsgm\GitHooks\Contracts;

interface HookCommand
{
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
