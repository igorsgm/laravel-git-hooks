<?php

use Igorsgm\GitHooks\Contracts\PostCommitHook;
use Igorsgm\GitHooks\Exceptions\HookFailException;
use Igorsgm\GitHooks\Facades\GitHooks;
use Igorsgm\GitHooks\Git\Log;

test('Git Log is sent through HookPipes', function (string $logText) {
    // This approach is broken in the current version of Mockery
    // @TODO: Update this test once Pest or Mockery versions are updated
    //    $postCommitHook1 = mock(PostCommitHook::class)->expect(
    //        handle: fn (Log $log, Closure $closure) => expect($log->getHash())->toBe(mockCommitHash())
    //    );

    $postCommitHook1 = Mockery::mock(PostCommitHook::class);
    $postCommitHook1->expects('handle')
        ->withArgs(fn ($log, $closure) => $log->getHash() === mockCommitHash());

    $postCommitHook2 = clone $postCommitHook1;

    $this->config->set('git-hooks.post-commit', [
        $postCommitHook1,
        $postCommitHook2,
    ]);

    GitHooks::shouldReceive('getLastCommitFromLog')->andReturn($logText);

    $this->artisan('git-hooks:post-commit')->assertSuccessful();
})->with('lastCommitLogText');

it('Returns 1 on HookFailException', function ($logText) {
    // This approach is broken in the current version of Mockery
    // @TODO: Update this test once Pest or Mockery versions are updated
    //    $postCommitHook1 = mock(PostCommitHook::class)->expect(
    //        handle: function (Log $log, Closure $closure) {
    //            throw new HookFailException();
    //        }
    //    );

    $postCommitHook1 = Mockery::mock(PostCommitHook::class);
    $postCommitHook1->expects('handle')
        ->andReturnUsing(function (Log $log, Closure $closure): never {
            throw new HookFailException();
        });

    $this->config->set('git-hooks.post-commit', [
        $postCommitHook1,
    ]);

    GitHooks::shouldReceive('getLastCommitFromLog')->andReturn($logText);
    $this->artisan('git-hooks:post-commit')->assertExitCode(1);
})->with('lastCommitLogText');
