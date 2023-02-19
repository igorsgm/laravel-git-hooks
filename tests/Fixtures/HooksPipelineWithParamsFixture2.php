<?php

namespace Igorsgm\GitHooks\Tests\Fixtures;

use Igorsgm\GitHooks\Contracts\Hook;

class HooksPipelineWithParamsFixture2 implements Hook
{
    /**
     * @var array
     */
    private $parameters;

    public function __construct(array $parameters)
    {
        $this->parameters = $parameters;
    }

    /**
     * Get hook name
     */
    public function getName(): string
    {
        return 'Hook 2';
    }

    public function handle(string $message, $next)
    {
        $message .= ' '.$this->parameters['param'];

        return $next($message);
    }
}
