<?php

use Igorsgm\GitHooks\Console\Commands\Hooks\ESLintPreCommitHook;
use Igorsgm\GitHooks\Facades\GitHooks;

beforeEach(function () {
    $this->gitInit();
    $this->initializeTempDirectory(base_path('temp'));
});

test('Skips ESLint check when there is none JS files added to commit', function ($eslintConfiguration, $listOfFixablePHPFiles) {
    $this->config->set('git-hooks.code_analyzers.eslint', $eslintConfiguration);
    $this->config->set('git-hooks.pre-commit', [
        ESLintPreCommitHook::class,
    ]);

    $this->makeTempFile('ClassWithFixableIssues.php',
        file_get_contents(__DIR__.'/../../../Fixtures/ClassWithFixableIssues.php')
    );

    GitHooks::shouldReceive('isMergeInProgress')->andReturn(false);
    GitHooks::shouldReceive('getListOfChangedFiles')->andReturn($listOfFixablePHPFiles);

    $this->artisan('git-hooks:pre-commit')->assertSuccessful();
})->with('eslintConfiguration', 'listOfFixablePhpFiles');

test('Fails commit when ESLint is not passing and user does not autofix the files',
    function ($eslintConfiguration, $listOfFixableJSFiles) {
        $this->config->set('git-hooks.code_analyzers.eslint', $eslintConfiguration);
        $this->config->set('git-hooks.pre-commit', [
            ESLintPreCommitHook::class,
        ]);

        $this->makeTempFile('fixable-js-file.js',
            file_get_contents(__DIR__.'/../../../Fixtures/fixable-js-file.js')
        );

        GitHooks::shouldReceive('isMergeInProgress')->andReturn(false);
        GitHooks::shouldReceive('getListOfChangedFiles')->andReturn($listOfFixableJSFiles);

        $this->artisan('git-hooks:pre-commit')
            ->expectsOutputToContain('ESLint Failed')
            ->expectsOutputToContain('COMMIT FAILED')
            ->expectsConfirmation('Would you like to attempt to correct files automagically?', 'no')
            ->assertExitCode(1);
    })->with('eslintConfiguration', 'listOfFixableJSFiles');

test('Fails commit when ESLint autofixer does not fix the files completely',
    function ($eslintConfiguration, $listOfFixableNonJSFiles) {
        $this->config->set('git-hooks.code_analyzers.eslint', $eslintConfiguration);
        $this->config->set('git-hooks.pre-commit', [
            ESLintPreCommitHook::class,
        ]);

        $this->makeTempFile('not-fully-fixable-js-file.js',
            file_get_contents(__DIR__.'/../../../Fixtures/not-fully-fixable-js-file.js')
        );

        GitHooks::shouldReceive('isMergeInProgress')->andReturn(false);
        GitHooks::shouldReceive('getListOfChangedFiles')->andReturn($listOfFixableNonJSFiles);

        $this->artisan('git-hooks:pre-commit')
            ->expectsOutputToContain('ESLint Failed')
            ->expectsOutputToContain('COMMIT FAILED')
            ->expectsConfirmation('Would you like to attempt to correct files automagically?', 'yes')
            ->expectsOutputToContain('ESLint Autofix Failed');
    })->with('eslintConfiguration', 'listOfNonFixableJSFiles');

test('Commit passes when ESLint fixes the files', function ($eslintConfiguration, $listOfFixableJSFiles) {
    $this->config->set('git-hooks.code_analyzers.eslint', $eslintConfiguration);
    $this->config->set('git-hooks.pre-commit', [
        ESLintPreCommitHook::class,
    ]);

    $this->makeTempFile('fixable-js-file.js',
        file_get_contents(__DIR__.'/../../../Fixtures/fixable-js-file.js')
    );

    GitHooks::shouldReceive('isMergeInProgress')->andReturn(false);
    GitHooks::shouldReceive('getListOfChangedFiles')->andReturn($listOfFixableJSFiles);

    $this->artisan('git-hooks:pre-commit')
        ->expectsOutputToContain('ESLint Failed')
        ->expectsOutputToContain('COMMIT FAILED')
        ->expectsConfirmation('Would you like to attempt to correct files automagically?', 'yes');

    $this->artisan('git-hooks:pre-commit')
        ->doesntExpectOutputToContain('ESLint Failed')
        ->assertSuccessful();
})->with('eslintConfiguration', 'listOfFixableJSFiles');
