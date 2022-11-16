<?php

namespace Igorsgm\GitHooks\Tests\Fixtures;

use Igorsgm\GitHooks\Contracts\Hook;

class HooksPipelineDefaultFixture1 implements Hook
{
    /**
     * Get hook name
     *
     * @return string
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
}
