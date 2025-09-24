<?php

declare(strict_types=1);

namespace Igorsgm\GitHooks\Contracts;

use Illuminate\Console\Command;

/**
 * @property Command $command
 */
interface Hook
{
    /**
     * Get hook name
     */
    public function getName(): ?string;

    public function setCommand(Command $command): void;
}
