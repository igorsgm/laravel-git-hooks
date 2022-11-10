<?php

use Igorsgm\GitHooks\Contracts\PostCommitHook;
use Igorsgm\GitHooks\Facades\GitHooks;
use Igorsgm\GitHooks\Git\Log;

test('Git Log is sent through HookPipes', function () {
    $commitHash = 'b636c88159e121d0c8276c417576d57ebb380dc3';

    $postCommitHook1 = mock(PostCommitHook::class)->expect(
        handle: fn(Log $log, Closure $closure) => expect($log->getHash())->toBe($commitHash)
    );
    $postCommitHook2 = clone $postCommitHook1;

    $this->config->set('git-hooks.post-commit', [
        $postCommitHook1,
        $postCommitHook2,
    ]);

    GitHooks::shouldReceive('getLastCommitFromLog')
        ->andReturn("commit $commitHash
Author: Igor Moraes <igor.sgm@gmail.com>
Date:   Wed Nov 9 04:50:40 2022 -0800

    wip
");

    $this->artisan('git-hooks:post-commit')->assertSuccessful();
});
