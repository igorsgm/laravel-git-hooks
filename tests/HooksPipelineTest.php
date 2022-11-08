<?php

namespace Igorsgm\GitHooks\Tests;

use Igorsgm\GitHooks\Contracts\Hook;
use Igorsgm\GitHooks\HooksPipeline;
use Illuminate\Container\Container;
use Illuminate\Support\Facades\Config;

class HooksPipelineTest extends TestCase
{
    public function test_pass_data_through_pipes()
    {
        $container = new Container();

        $hooks = [
            HooksPipelineTestHook::class,
            HooksPipelineTestWithArgsHook::class,
        ];

        Config::set('git-hooks', [
            'pre-commit' => [
                HooksPipelineTestHook::class,
                HooksPipelineTestWithArgsHook::class => [
                    'param' => 'Hook 2',
                ],
            ],
        ]);

        $pipeline = new HooksPipeline($container, 'pre-commit');

        $message = $pipeline->through($hooks)
            ->send('message')
            ->thenReturn();

        $this->assertEquals('message Hook 1 Hook 2', $message);
    }
}

class HooksPipelineTestHook implements Hook
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

class HooksPipelineTestWithArgsHook implements Hook
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
     *
     * @return string
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
