<?php

namespace Igorsgm\GitHooks\Contracts;

use Illuminate\Console\Command;

/**
 * @property \Illuminate\Console\Command $command
 */
interface Hook
{
    /**
     * Get hook name
     */
    public function getName(): ?string;

    public function setCommand(Command $command): void;
}
