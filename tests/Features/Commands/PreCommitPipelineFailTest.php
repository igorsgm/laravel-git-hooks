<?php

declare(strict_types=1);

use Igorsgm\GitHooks\Facades\GitHooks;
use Igorsgm\GitHooks\Tests\Fixtures\MarkFailedPreCommitFixtureHook;

test('PreCommit clearPipelineFailed is called when pipeline is marked failed without exception', function (string $listOfChangedFiles) {
    // Use a hook that marks pipeline failed but doesn't throw HookFailException
    $this->config->set('git-hooks.pre-commit', [
        MarkFailedPreCommitFixtureHook::class,
    ]);

    GitHooks::shouldReceive('getListOfChangedFiles')->andReturn($listOfChangedFiles);

    // The pre-commit command should return exit code 1 because pipeline was marked failed
    $this->artisan('git-hooks:pre-commit')->assertExitCode(1);
})->with('listOfChangedFiles');
