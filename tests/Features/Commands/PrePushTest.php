<?php

use Igorsgm\GitHooks\Contracts\PostCommitHook;
use Igorsgm\GitHooks\Facades\GitHooks;
use Igorsgm\GitHooks\Git\Log;

test(
    'Git Log is sent through HookPipes', function (string $logText) {
        // This approach is broken in the current version of Mockery
        // @TODO: Update this test once Pest or Mockery versions are updated
        //    $prePushHook1 = mock(PostCommitHook::class)->expect(
        //        handle: fn (Log $log, Closure $closure) => expect($log->getHash())->toBe(mockCommitHash())
        //    );
        $prePushHook1 = Mockery::mock(PostCommitHook::class);
        $prePushHook1->expects('handle')
            ->withArgs(fn (Log $log, Closure $closure) => $log->getHash() === mockCommitHash())
            ->once();

        $prePushHook2 = clone $prePushHook1;

        $this->config->set(
            'git-hooks.pre-push', [
                $prePushHook1,
                $prePushHook2,
            ]
        );

        GitHooks::shouldReceive('getLastCommitFromLog')->andReturn($logText);

        $this->artisan('git-hooks:pre-push')->assertSuccessful();
    }
)->with('lastCommitLogText');
