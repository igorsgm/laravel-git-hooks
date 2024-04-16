<?php

use Igorsgm\GitHooks\Contracts\MessageHook;
use Igorsgm\GitHooks\Exceptions\HookFailException;
use Igorsgm\GitHooks\Facades\GitHooks;
use Igorsgm\GitHooks\Git\CommitMessage;
use Igorsgm\GitHooks\Tests\Fixtures\PrepareCommitMessageFixtureHook1;
use Igorsgm\GitHooks\Tests\Fixtures\PrepareCommitMessageFixtureHook2;

test('Commit Message is sent through HookPipes', function (string $listOfChangedFiles) {
    $prepareCommitMessageHooks = [
        PrepareCommitMessageFixtureHook1::class,
        PrepareCommitMessageFixtureHook2::class,
    ];

    $this->config->set('git-hooks.prepare-commit-msg', $prepareCommitMessageHooks);

    $file = 'tmp/COMMIT_MESSAGE';

    GitHooks::shouldReceive('getCommitMessageContentFromFile')
        ->andReturn('Test commit');

    GitHooks::shouldReceive('getListOfChangedFiles')
        ->andReturn($listOfChangedFiles);

    GitHooks::shouldReceive('updateCommitMessageContentInFile')
        ->with(base_path($file), 'Test commit hook1 hook2');

    $command = $this->artisan('git-hooks:prepare-commit-msg', ['file' => $file])
        ->assertExitCode(0);

    foreach ($prepareCommitMessageHooks as $hook) {
        $command->expectsOutputToContain(sprintf('   HOOK  %s: ✔', resolve($hook)->getName()));
    }
})->with('listOfChangedFiles');

it('Returns 1 on HookFailException', function ($listOfChangedFiles) {
    // This approach is broken in the current version of Mockery
    // @TODO: Update this test once Pest or Mockery versions are updated
    //    $postCommitHook1 = mock(MessageHook::class)->expect(
    //        handle: function (CommitMessage $commitMessage, Closure $closure) {
    //            throw new HookFailException();
    //        }
    //    );
    $postCommitHook1 = Mockery::mock(MessageHook::class);
    $postCommitHook1->expects('handle')
        ->andReturnUsing(function (CommitMessage $commitMessage, Closure $closure): never {
            throw new HookFailException();
        });

    $this->config->set('git-hooks.prepare-commit-msg', [
        $postCommitHook1,
    ]);

    $file = 'tmp/COMMIT_MESSAGE';

    GitHooks::shouldReceive('getCommitMessageContentFromFile')
        ->andReturn('Test commit');

    GitHooks::shouldReceive('getListOfChangedFiles')
        ->andReturn($listOfChangedFiles);

    GitHooks::shouldReceive('updateCommitMessageContentInFile')
        ->with(base_path($file), 'Test commit hook1 hook2');

    $this->artisan('git-hooks:prepare-commit-msg', ['file' => $file])->assertExitCode(1);
})->with('listOfChangedFiles');
