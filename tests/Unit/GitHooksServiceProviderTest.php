<?php

declare(strict_types=1);

use Illuminate\Console\Command;
use Igorsgm\GitHooks\Console\Commands\CommitMessage;
use Igorsgm\GitHooks\Console\Commands\MakeHook;
use Igorsgm\GitHooks\Console\Commands\PostCommit;
use Igorsgm\GitHooks\Console\Commands\PreCommit;
use Igorsgm\GitHooks\Console\Commands\PrepareCommitMessage;
use Igorsgm\GitHooks\Console\Commands\PrePush;
use Igorsgm\GitHooks\Console\Commands\RegisterHooks;
use Igorsgm\GitHooks\Facades\GitHooks;
use Igorsgm\GitHooks\GitHooks as GitHooksImpl;

test('GitHooks singleton is bound in the container', function () {
    expect(app('laravel-git-hooks'))->toBeInstanceOf(GitHooksImpl::class);
});

test('GitHooks facade resolves the singleton', function () {
    expect(GitHooks::getFacadeRoot())->toBeInstanceOf(GitHooksImpl::class);
});

test('Config is merged from package default', function () {
    expect(config('git-hooks'))->toBeArray()
        ->and(config('git-hooks.pre-commit'))->toBeArray()
        ->and(config('git-hooks.commit-msg'))->toBeArray();
});

test('All hook commands are registered', function () {
    $artisan = app(\Illuminate\Contracts\Console\Kernel::class);
    $commands = collect($artisan->all());

    $expected = [
        'git-hooks:register',
        'git-hooks:pre-commit',
        'git-hooks:commit-msg',
        'git-hooks:prepare-commit-msg',
        'git-hooks:post-commit',
        'git-hooks:pre-push',
        'git-hooks:make',
    ];

    foreach ($expected as $name) {
        expect($commands->has($name))->toBeTrue("Command '{$name}' is not registered");
    }
});

test('RegisterHooks command is the correct class', function () {
    expect(app(\Illuminate\Contracts\Console\Kernel::class)->all())
        ->toHaveKey('git-hooks:register')
        ->and(app(\Illuminate\Contracts\Console\Kernel::class)->all()['git-hooks:register'])
        ->toBeInstanceOf(Command::class);
});
