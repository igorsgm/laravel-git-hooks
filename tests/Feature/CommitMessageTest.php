<?php

use Igorsgm\GitHooks\Facades\GitHooks;
use Igorsgm\GitHooks\Tests\Fixtures\CommitMessageTestHook1;
use Igorsgm\GitHooks\Tests\Fixtures\CommitMessageTestHook2;
use Igorsgm\GitHooks\Tests\Fixtures\CommitMessageTestHook4;

test('Commit Message is sent through HookPipes', function () {
    $commitMessageHooks = [
        CommitMessageTestHook1::class,
        CommitMessageTestHook2::class,
    ];

    $this->config->set('git-hooks.commit-msg', $commitMessageHooks);

    $file = 'tmp/COMMIT_MESSAGE';

    GitHooks::shouldReceive('getCommitMessageContentFromFile')
        ->andReturn('Test commit');

    GitHooks::shouldReceive('getListOfChangedFiles')
        ->andReturn('AM src/ChangedFiles.php');

    GitHooks::shouldReceive('updateCommitMessageContentInFile')
        ->with(base_path($file), 'Test commit hook1 hook2');

    $command = $this->artisan('git-hooks:commit-msg', ['file' => $file])
        ->assertExitCode(0);

    foreach ($commitMessageHooks as $hook) {
        $command->expectsOutputToContain(sprintf('Hook: %s...', resolve($hook)->getName()));
    }
});

test('Pass parameters into Commit Hook class', function () {
    $commitMessageHooks = [
        CommitMessageTestHook4::class => [
            'param1' => 'hello',
            'param2' => 'world',
        ],
    ];

    $this->config->set('git-hooks.commit-msg', $commitMessageHooks);

    $file = 'tmp/COMMIT_MESSAGE';

    GitHooks::shouldReceive('getCommitMessageContentFromFile')
        ->andReturn('Test commit');

    GitHooks::shouldReceive('getListOfChangedFiles')
        ->andReturn('AM src/ChangedFiles.php');

    GitHooks::shouldReceive('updateCommitMessageContentInFile')
        ->with(base_path($file), 'Test commit hello world');

    $command = $this->artisan('git-hooks:commit-msg', ['file' => $file])
        ->assertExitCode(0);

    foreach ($commitMessageHooks as $hook => $parameters) {
        $hook = resolve($hook, compact('parameters'));
        $command->expectsOutputToContain(sprintf('Hook: %s...', $hook->getName()));
    }
});
