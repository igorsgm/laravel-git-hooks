<?php

declare(strict_types=1);

namespace Igorsgm\GitHooks\Tests\Fixtures;

class HooksPipelineInvokableFixture3
{
    public function __invoke($message, $next)
    {
        $message .= ' Hook 3';

        return $next($message);
    }

    /**
     * Get hook name
     */
    public function getName(): string
    {
        return 'Hook 3';
    }
}
