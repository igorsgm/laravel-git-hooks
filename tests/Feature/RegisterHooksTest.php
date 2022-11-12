<?php

use Igorsgm\GitHooks\Contracts\PostCommitHook;
use Igorsgm\GitHooks\Contracts\PreCommitHook;
use Igorsgm\GitHooks\Contracts\PrePushHook;
use Igorsgm\GitHooks\Tests\Fixtures\CommitMessageFixtureHook1;
use Igorsgm\GitHooks\Tests\Fixtures\PrepareCommitMessageFixtureHook1;

beforeEach(function () {
    $this->initializeGitAsTempDirectory();
});

afterEach(function () {
    $this->deleteTempDirectory();
});

it('installs pre-commit file in .git/hooks folder', function () {
    $preCommitHookClass = mock(PreCommitHook::class);

    $this->config->set('git-hooks.pre-commit', [
        $preCommitHookClass,
    ]);

    $this->artisan('git-hooks:register')->assertSuccessful();

    $hookFile = base_path('.git/hooks/pre-commit');

    expect($hookFile)
        ->toBeFile()
        ->toContainHookArtisanCommand('pre-commit');
});

it('installs prepare-commit-msg file in .git/hooks folder', function () {
    $this->config->set('git-hooks.prepare-commit-msg', [
        PrepareCommitMessageFixtureHook1::class,
    ]);

    $this->artisan('git-hooks:register')->assertSuccessful();

    $hookFile = base_path('.git/hooks/prepare-commit-msg');

    expect($hookFile)
        ->toBeFile()
        ->toContainHookArtisanCommand('prepare-commit-msg');
});

it('installs commit-msg file in .git/hooks folder', function () {
    $this->config->set('git-hooks.commit-msg', [
        CommitMessageFixtureHook1::class,
    ]);

    $this->artisan('git-hooks:register')->assertSuccessful();

    $hookFile = base_path('.git/hooks/commit-msg');

    expect($hookFile)
        ->toBeFile()
        ->toContainHookArtisanCommand('commit-msg');
});

it('installs post-commit file in .git/hooks folder', function () {
    $postCommitHookClass = mock(PostCommitHook::class);

    $this->config->set('git-hooks.post-commit', [
        $postCommitHookClass,
    ]);

    $this->artisan('git-hooks:register')->assertSuccessful();

    $hookFile = base_path('.git/hooks/post-commit');

    expect($hookFile)
        ->toBeFile()
        ->toContainHookArtisanCommand('post-commit');
});

it('installs pre-push file in .git/hooks folder', function () {
    $prePushHookClass = mock(PrePushHook::class);

    $this->config->set('git-hooks.pre-push', [
        $prePushHookClass,
    ]);

    $this->artisan('git-hooks:register')->assertSuccessful();

    $hookFile = base_path('.git/hooks/pre-push');

    expect($hookFile)
        ->toBeFile()
        ->toContainHookArtisanCommand('pre-push');
});
