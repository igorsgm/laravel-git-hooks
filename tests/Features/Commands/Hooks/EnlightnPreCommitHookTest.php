<?php

use Igorsgm\GitHooks\Console\Commands\Hooks\EnlightnPreCommitHook;
use Igorsgm\GitHooks\Facades\GitHooks;
use Igorsgm\GitHooks\Git\ChangedFiles;
use Igorsgm\GitHooks\Traits\GitHelper;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

uses(GitHelper::class);
beforeEach(function () {
    $this->gitInit();
    $this->initializeTempDirectory(base_path('temp'));
});

test('Skips Enlightn check if there are no files added to commit', function () {
    $changedFiles = Mockery::mock(ChangedFiles::class)
        ->shouldReceive('getAddedToCommit')
        ->andReturn(collect())
        ->getMock();

    $next = function ($files) {
        return 'passed';
    };

    $hook = new EnlightnPreCommitHook();
    $result = $hook->handle($changedFiles, $next);
    expect($result)->toBe('passed');
});

test('Fails commit when Enlightn is not passing', function ($listOfChangedFiles) {
    $this->config->set('git-hooks.pre-commit', [
        EnlightnPreCommitHook::class,
    ]);

    GitHooks::shouldReceive('isMergeInProgress')->andReturn(false);
    GitHooks::shouldReceive('getListOfChangedFiles')->andReturn($listOfChangedFiles);

    Artisan::command('enlightn', function () {
        return 1;
    });

    // Get all registered commands
    $commands = Artisan::all();

    // Access the 'git-hooks:pre-commit' command instance
    $command = $commands['git-hooks:pre-commit'];

    $input = new ArrayInput([]);
    $output = new BufferedOutput();

    $exitCode = $command->run($input, $output);
    $outputText = $output->fetch();

    $this->assertStringContainsString('COMMIT FAILED', $outputText);
    $this->assertStringContainsString('php artisan enlightn', $outputText);
    $this->assertEquals(1, $exitCode);
})->with('listOfChangedFiles');

test('Commit passes when Enlightn check is OK', function ($listOfChangedFiles) {
    $this->config->set('git-hooks.pre-commit', [
        EnlightnPreCommitHook::class,
    ]);

    GitHooks::shouldReceive('isMergeInProgress')->andReturn(false);
    GitHooks::shouldReceive('getListOfChangedFiles')->andReturn($listOfChangedFiles);

    Artisan::command('enlightn', function () {
        return 0;
    });

    $this->artisan('git-hooks:pre-commit')->assertSuccessful();
})->with('listOfChangedFiles');
