<?php

use Igorsgm\GitHooks\Contracts\PostCommitHook;
use Igorsgm\GitHooks\Facades\GitHooks;
use Igorsgm\GitHooks\Git\Log;

test('Git Log is sent through HookPipes', function () {
    $prePushHook1 = mock(PostCommitHook::class)->expect(
        handle: fn (Log $log, Closure $closure) => expect($log->getHash())->toBe(mockCommitHash())
    );
    $prePushHook2 = clone $prePushHook1;

    $this->config->set('git-hooks.pre-push', [
        $prePushHook1,
        $prePushHook2,
    ]);

    GitHooks::shouldReceive('getLastCommitFromLog')->andReturn(mockLastCommitLog());

    $this->artisan('git-hooks:pre-push')->assertSuccessful();
});
