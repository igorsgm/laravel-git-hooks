<?php

namespace Igorsgm\GitHooks\Tests\Fixtures;

class HooksPipelineInvokableFixture3
{
    /**
     * Get hook name
     */
    public function getName(): string
    {
        return 'Hook 3';
    }

    public function __invoke($message, $next)
    {
        $message .= ' Hook 3';

        return $next($message);
    }
}
