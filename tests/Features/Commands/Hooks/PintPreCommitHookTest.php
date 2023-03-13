<?php

use Igorsgm\GitHooks\Console\Commands\Hooks\PintPreCommitHook;
use Igorsgm\GitHooks\Facades\GitHooks;
use Igorsgm\GitHooks\Git\ChangedFiles;
use Igorsgm\GitHooks\Traits\GitHelper;

uses(GitHelper::class);
beforeEach(function () {
    $this->gitInit();
    $this->initializeTempDirectory(base_path('temp'));
});

test('Skips Pint check if there are no files added to commit', function () {
    $changedFiles = mock(ChangedFiles::class)
        ->shouldReceive('getAddedToCommit')
        ->andReturn(collect())
        ->getMock();

    $next = function ($files) {
        return 'passed';
    };

    $hook = new PintPreCommitHook();
    $result = $hook->handle($changedFiles, $next);
    expect($result)->toBe('passed');
});

test('Skips Pint check during a Merge process', function ($modifiedFilesList) {
    $changedFiles = new ChangedFiles($modifiedFilesList);
    GitHooks::shouldReceive('isMergeInProgress')->andReturn(true);

    $next = function ($files) {
        return 'passed';
    };

    $hook = new PintPreCommitHook();
    $result = $hook->handle($changedFiles, $next);
    expect($result)->toBe('passed');
})->with('modifiedFilesList');

test('Throws HookFailException and notifies when Pint is not installed', function ($listOfFixableFiles) {
    $this->config->set('git-hooks.code_analyzers.laravel_pint', [
        'path' => 'inexistent/path/to/pint',
    ]);

    $this->config->set('git-hooks.pre-commit', [
        PintPreCommitHook::class,
    ]);

    GitHooks::shouldReceive('isMergeInProgress')->andReturn(false);
    GitHooks::shouldReceive('getListOfChangedFiles')->andReturn($listOfFixableFiles);

    $this->artisan('git-hooks:pre-commit')
        ->expectsOutputToContain('Pint is not installed.')
        ->assertExitCode(1);
})->with('listOfFixableFiles');

test('Fails commit when Pint is not passing and user does not autofix the files', function ($listOfFixableFiles) {
    $this->config->set('git-hooks.code_analyzers.laravel_pint', [
        'path' => '../../../bin/pint',
        'preset' => 'psr12',
    ]);
    $this->config->set('git-hooks.pre-commit', [
        PintPreCommitHook::class,
    ]);

    $this->makeTempFile('ClassWithFixableIssues.php',
        file_get_contents(__DIR__.'/../../../Fixtures/ClassWithFixableIssues.php')
    );

    GitHooks::shouldReceive('isMergeInProgress')->andReturn(false);
    GitHooks::shouldReceive('getListOfChangedFiles')->andReturn($listOfFixableFiles);

    $this->artisan('git-hooks:pre-commit')
        ->expectsOutputToContain('Pint Failed')
        ->expectsOutputToContain('COMMIT FAILED')
        ->expectsConfirmation('Would you like to attempt to correct files automagically?', 'no')
        ->assertExitCode(1);
})->with('listOfFixableFiles');

test('Commit passes when Pint fixes fix the files', function ($listOfFixableFiles) {
    $this->config->set('git-hooks.code_analyzers.laravel_pint', [
        'path' => '../../../bin/pint',
        'preset' => 'psr12',
    ]);
    $this->config->set('git-hooks.pre-commit', [
        PintPreCommitHook::class,
    ]);

    $this->makeTempFile('ClassWithFixableIssues.php',
        file_get_contents(__DIR__.'/../../../Fixtures/ClassWithFixableIssues.php')
    );

    GitHooks::shouldReceive('isMergeInProgress')->andReturn(false);
    GitHooks::shouldReceive('getListOfChangedFiles')->andReturn($listOfFixableFiles);

    $this->artisan('git-hooks:pre-commit')
        ->expectsOutputToContain('Pint Failed')
        ->expectsOutputToContain('COMMIT FAILED')
        ->expectsConfirmation('Would you like to attempt to correct files automagically?', 'yes');

    $this->artisan('git-hooks:pre-commit')
        ->doesntExpectOutputToContain('Pint Failed')
        ->assertSuccessful();
})->with('listOfFixableFiles');
