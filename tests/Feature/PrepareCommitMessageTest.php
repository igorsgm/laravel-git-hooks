<?php

use Igorsgm\GitHooks\Facades\GitHooks;
use Igorsgm\GitHooks\Tests\Fixtures\PrepareCommitMessageFixtureHook1;
use Igorsgm\GitHooks\Tests\Fixtures\PrepareCommitMessageFixtureHook2;

test('Commit Message is sent through HookPipes', function () {
    $prepareCommitMessageHooks = [
        PrepareCommitMessageFixtureHook1::class,
        PrepareCommitMessageFixtureHook2::class,
    ];

    $this->config->set('git-hooks.prepare-commit-msg', $prepareCommitMessageHooks);

    $file = 'tmp/COMMIT_MESSAGE';

    GitHooks::shouldReceive('getCommitMessageContentFromFile')
        ->andReturn('Test commit');

    GitHooks::shouldReceive('getListOfChangedFiles')
        ->andReturn('AM src/ChangedFiles.php');

    GitHooks::shouldReceive('updateCommitMessageContentInFile')
        ->with(base_path($file), 'Test commit hook1 hook2');

    $command = $this->artisan('git-hooks:prepare-commit-msg', ['file' => $file])
        ->assertExitCode(0);

    foreach ($prepareCommitMessageHooks as $hook) {
        $command->expectsOutputToContain(sprintf('Hook: %s...', resolve($hook)->getName()));
    }
});
