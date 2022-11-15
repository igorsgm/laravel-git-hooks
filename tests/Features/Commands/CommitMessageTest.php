<?php

use Igorsgm\GitHooks\Facades\GitHooks;
use Igorsgm\GitHooks\Tests\Fixtures\CommitMessageFixtureHook1;
use Igorsgm\GitHooks\Tests\Fixtures\CommitMessageFixtureHook2;
use Igorsgm\GitHooks\Tests\Fixtures\CommitMessageFixtureHook4;

test('Commit Message is sent through HookPipes', function (string $listOfChangedFiles) {
    $commitMessageHooks = [
        CommitMessageFixtureHook1::class,
        CommitMessageFixtureHook2::class,
    ];

    $this->config->set('git-hooks.commit-msg', $commitMessageHooks);

    $file = 'tmp/COMMIT_MESSAGE';

    GitHooks::shouldReceive('getCommitMessageContentFromFile')
        ->andReturn('Test commit');

    GitHooks::shouldReceive('getListOfChangedFiles')
        ->andReturn($listOfChangedFiles);

    GitHooks::shouldReceive('updateCommitMessageContentInFile')
        ->with(base_path($file), 'Test commit hook1 hook2');

    $command = $this->artisan('git-hooks:commit-msg', ['file' => $file])
        ->assertExitCode(0);

    foreach ($commitMessageHooks as $hook) {
        $command->expectsOutputToContain(sprintf('   HOOK  %s: ✔', resolve($hook)->getName()));
    }
})->with('listOfChangedFiles');

test('Pass parameters into Commit Hook class', function (string $listOfChangedFiles) {
    $commitMessageHooks = [
        CommitMessageFixtureHook4::class => [
            'param1' => 'hello',
            'param2' => 'world',
        ],
    ];

    $this->config->set('git-hooks.commit-msg', $commitMessageHooks);

    $file = 'tmp/COMMIT_MESSAGE';

    GitHooks::shouldReceive('getCommitMessageContentFromFile')
        ->andReturn('Test commit');

    GitHooks::shouldReceive('getListOfChangedFiles')
        ->andReturn($listOfChangedFiles);

    GitHooks::shouldReceive('updateCommitMessageContentInFile')
        ->with(base_path($file), 'Test commit hello world');

    $command = $this->artisan('git-hooks:commit-msg', ['file' => $file])
        ->assertExitCode(0);

    foreach ($commitMessageHooks as $hook => $parameters) {
        $hook = resolve($hook, compact('parameters'));
        $command->expectsOutputToContain(sprintf('   HOOK  %s: ✔', $hook->getName()));
    }
})->with('listOfChangedFiles');
