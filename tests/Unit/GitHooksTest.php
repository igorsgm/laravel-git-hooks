<?php

declare(strict_types=1);

use Igorsgm\GitHooks\GitHooks;
use Illuminate\Support\Facades\App;

beforeEach(function () {
    $this->initializeGitAsTempDirectory();
    $this->gitHooks = new GitHooks();
});

afterEach(function () {
    $this->deleteTempDirectory();
});

test('getGitHooksDir returns .git/hooks path in a regular repo', function () {
    chdir(base_path());

    $result = $this->gitHooks->getGitHooksDir();

    expect($result)->toEndWith(DIRECTORY_SEPARATOR.'hooks');
    expect($result)->toContain('.git');
});

test('getGitHooksDir returns the common hooks dir in a git worktree', function () {
    $mainRepoPath = base_path();
    chdir($mainRepoPath);

    shell_exec('git -C '.escapeshellarg($mainRepoPath).' commit --allow-empty -m "init" 2>/dev/null');

    $worktreePath = str_replace('\\', '/', sys_get_temp_dir()).'/worktree-test-'.uniqid();
    shell_exec('git -C '.escapeshellarg($mainRepoPath).' worktree add '.escapeshellarg($worktreePath).' -b test-worktree 2>/dev/null');

    if (! is_dir($worktreePath)) {
        $this->markTestSkipped('Worktree directory was not created');
    }

    $this->app->setBasePath($worktreePath);

    $resolvedPath = realpath($worktreePath);
    if ($resolvedPath === false) {
        $this->markTestSkipped('Could not resolve worktree path');
    }

    chdir($resolvedPath);

    $result = $this->gitHooks->getGitHooksDir();

    $expectedCommonHooksDir = $mainRepoPath.DIRECTORY_SEPARATOR.'.git'.DIRECTORY_SEPARATOR.'hooks';
    expect($result)->toBe($expectedCommonHooksDir);

    $this->app->setBasePath($mainRepoPath);
    chdir($mainRepoPath);
    shell_exec('git -C '.escapeshellarg($mainRepoPath).' worktree remove '.escapeshellarg($worktreePath).' 2>/dev/null');
});

test('getGitHooksDir falls back to base_path .git/hooks when git command fails', function () {
    $nonGitDir = sys_get_temp_dir().DIRECTORY_SEPARATOR.'non-git-'.uniqid();
    mkdir($nonGitDir, 0755, true);

    $this->app->setBasePath($nonGitDir);

    $result = $this->gitHooks->getGitHooksDir();

    expect($result)->toBe($nonGitDir.DIRECTORY_SEPARATOR.'.git'.DIRECTORY_SEPARATOR.'hooks');

    rmdir($nonGitDir);
    $this->app->setBasePath(base_path());
});

test('install creates hook file with php command when use_sail is false', function () {
    config(['git-hooks.use_sail' => false]);
    config(['git-hooks.artisan_path' => base_path('artisan')]);

    $hookPath = $this->gitHooks->getGitHooksDir().'/pre-commit';

    $this->gitHooks->install('pre-commit');

    expect(file_exists($hookPath))->toBeTrue();
    $content = file_get_contents($hookPath);
    expect($content)->toContain('php');
    expect($content)->not->toContain('vendor/bin/sail');
});

test('install creates hook file with sail command when use_sail is true', function () {
    config(['git-hooks.use_sail' => true]);

    $hookPath = $this->gitHooks->getGitHooksDir().'/pre-commit';

    $this->gitHooks->install('pre-commit');

    expect(file_exists($hookPath))->toBeTrue();
    $content = file_get_contents($hookPath);
    expect($content)->toContain('vendor/bin/sail');
    expect($content)->not->toContain('php '.base_path('artisan'));
});

test('getHookStub returns valid stub content', function () {
    $stub = $this->gitHooks->getHookStub();

    expect($stub)->not->toBeNull();
    expect($stub)->toContain('{command}');
});

test('getAvailableHooks returns filtered supported hooks', function () {
    config([
        'git-hooks.pre-commit' => ['SomeHook::class'],
        'git-hooks.post-commit' => [],
        'git-hooks.pre-push' => ['AnotherHook::class'],
    ]);

    $hooks = $this->gitHooks->getAvailableHooks();

    expect($hooks)->toContain('pre-commit');
    expect($hooks)->toContain('pre-push');
    expect($hooks)->not->toContain('post-commit');
    expect($hooks)->not->toContain('commit-msg');
});

test('getSupportedHooks returns all supported hook types', function () {
    $hooks = $this->gitHooks->getSupportedHooks();

    expect($hooks)->toContain('pre-commit');
    expect($hooks)->toContain('commit-msg');
    expect($hooks)->toContain('post-commit');
    expect($hooks)->toContain('prepare-commit-msg');
    expect($hooks)->toContain('pre-push');
    expect($hooks)->toContain('pre-rebase');
    expect($hooks)->toContain('post-rewrite');
    expect($hooks)->toContain('post-checkout');
    expect($hooks)->toContain('post-merge');
});
