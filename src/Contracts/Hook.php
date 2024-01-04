<?php

namespace Igorsgm\GitHooks\Contracts;

/**
 * @property \Illuminate\Console\Command $command
 */
interface Hook
{
    /**
     * Get hook name
     */
    public function getName(): ?string;
}
