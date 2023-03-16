<?php

use Igorsgm\GitHooks\Console\Commands\Hooks\PintPreCommitHook;
use Igorsgm\GitHooks\Facades\GitHooks;
use Igorsgm\GitHooks\Traits\GitHelper;

uses(GitHelper::class);
beforeEach(function () {
    $this->gitInit();
    $this->initializeTempDirectory(base_path('temp'));
});

test('Skips Pint check when there is none php files added to commit', function ($pintConfiguration) {
    $this->config->set('git-hooks.code_analyzers.laravel_pint', $pintConfiguration);
    $this->config->set('git-hooks.pre-commit', [
        PintPreCommitHook::class,
    ]);

    $this->makeTempFile('sample.js',
        file_get_contents(__DIR__.'/../../../Fixtures/sample.js')
    );

    GitHooks::shouldReceive('isMergeInProgress')->andReturn(false);
    GitHooks::shouldReceive('getListOfChangedFiles')->andReturn('AM src/sample.js');

    $this->artisan('git-hooks:pre-commit')->assertSuccessful();
})->with('pintConfigurations');

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
})->with('listOfFixableFiles', 'pintConfigurations');

test('Fails commit when Pint is not passing and user does not autofix the files',
    function ($pintConfiguration, $listOfFixableFiles) {
        $this->config->set('git-hooks.code_analyzers.laravel_pint', $pintConfiguration);
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
    })->with('pintConfigurations', 'listOfFixableFiles');

test('Commit passes when Pint fixes the files', function ($pintConfiguration, $listOfFixableFiles) {
    $this->config->set('git-hooks.code_analyzers.laravel_pint', $pintConfiguration);
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
})->with('pintConfigurations', 'listOfFixableFiles');
