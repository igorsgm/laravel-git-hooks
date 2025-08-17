<?php

use Igorsgm\GitHooks\Contracts\PostCommitHook;
use Igorsgm\GitHooks\Contracts\PreCommitHook;
use Igorsgm\GitHooks\Contracts\PrePushHook;
use Igorsgm\GitHooks\Tests\Fixtures\CommitMessageFixtureHook1;
use Igorsgm\GitHooks\Tests\Fixtures\PrepareCommitMessageFixtureHook1;

dataset(
    'registrableHookTypes', [
        'pre-commit' => [
            Mockery::mock(PreCommitHook::class),
            'pre-commit',
        ],
        'prepare-commit-msg' => [
            PrepareCommitMessageFixtureHook1::class,
            'prepare-commit-msg',
        ],
        'commit-msg' => [
            CommitMessageFixtureHook1::class,
            'commit-msg',
        ],
        'post-commit' => [
            Mockery::mock(PostCommitHook::class),
            'post-commit',
        ],
        'pre-push' => [
            Mockery::mock(PrePushHook::class),
            'pre-push',
        ],
    ]
);
