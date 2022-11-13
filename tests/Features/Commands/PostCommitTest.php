<?php

use Igorsgm\GitHooks\Contracts\PostCommitHook;
use Igorsgm\GitHooks\Exceptions\HookFailException;
use Igorsgm\GitHooks\Facades\GitHooks;
use Igorsgm\GitHooks\Git\Log;

test('Git Log is sent through HookPipes', function (string $logText) {
    $postCommitHook1 = mock(PostCommitHook::class)->expect(
        handle: fn (Log $log, Closure $closure) => expect($log->getHash())->toBe(mockCommitHash())
    );
    $postCommitHook2 = clone $postCommitHook1;

    $this->config->set('git-hooks.post-commit', [
        $postCommitHook1,
        $postCommitHook2,
    ]);

    GitHooks::shouldReceive('getLastCommitFromLog')->andReturn($logText);

    $this->artisan('git-hooks:post-commit')->assertSuccessful();
})->with('lastCommitLogText');

it('Returns 1 on HookFailException', function ($logText) {
    $postCommitHook1 = mock(PostCommitHook::class)->expect(
        handle: function (Log $log, Closure $closure) {
            throw new HookFailException();
        }
    );

    $this->config->set('git-hooks.post-commit', [
        $postCommitHook1,
    ]);

    GitHooks::shouldReceive('getLastCommitFromLog')->andReturn($logText);
    $this->artisan('git-hooks:post-commit')->assertExitCode(1);
})->with('lastCommitLogText');
