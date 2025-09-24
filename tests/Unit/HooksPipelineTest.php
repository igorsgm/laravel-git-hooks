<?php

declare(strict_types=1);

use Igorsgm\GitHooks\HooksPipeline;
use Illuminate\Container\Container;

test('Data is sent through Pipes', function ($hook, ?array $parameters = null) {
    $parameters ??= [];
    $container = new Container;

    $hookConfig = !empty($parameters) ? [$hook => $parameters] : [$hook];
    $this->config->set('git-hooks.pre-commit', $hookConfig);

    $pipeline = new HooksPipeline($container, 'pre-commit');

    $message = $pipeline->through($hook)
        ->send('message')
        ->thenReturn();

    $hookName = resolve($hook, compact('parameters'))->getName();
    $this->assertEquals('message '.$hookName, $message);
})->with('pipelineHooks');

test('Data is sent through Pipes with Closure', function () {
    $container = new Container;

    $closureHook = function ($message, $next) {
        $message .= ' Hook 4';

        return $next($message);
    };
    $this->config->set('git-hooks.pre-commit', [
        $closureHook,
    ]);

    $pipeline = new HooksPipeline($container, 'pre-commit');

    $message = $pipeline->through($closureHook)
        ->send('message')
        ->thenReturn();

    $this->assertEquals('message Hook 4', $message);
});
