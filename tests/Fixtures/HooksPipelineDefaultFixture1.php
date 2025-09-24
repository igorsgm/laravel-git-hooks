<?php

declare(strict_types=1);

namespace Igorsgm\GitHooks\Tests\Fixtures;

use Igorsgm\GitHooks\Contracts\Hook;
use Illuminate\Console\Command;

class HooksPipelineDefaultFixture1 implements Hook
{
    /**
     * Get hook name
     */
    public function getName(): string
    {
        return 'Hook 1';
    }

    public function handle(string $message, $next)
    {
        $message .= ' '.$this->getName();

        return $next($message);
    }

    public function setCommand(Command $command): void
    {
        // nothing to do
    }
}
