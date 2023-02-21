<?php

namespace Igorsgm\GitHooks\Contracts;

/**
 * @property \Illuminate\Console\Command $command
 */
interface Hook
{
    /**
     * Get hook name
     *
     * @return string
     */
    public function getName(): ?string;
}
