<?php

use Igorsgm\GitHooks\Console\Commands\Hooks\PrettierPreCommitHook;
use Igorsgm\GitHooks\Facades\GitHooks;

beforeEach(function () {
    $this->gitInit();
    $this->initializeTempDirectory(base_path('temp'));
});

test('Skips Prettier check when there is none JS files added to commit', function ($prettierConfiguration, $listOfFixablePHPFiles) {
    $this->config->set('git-hooks.code_analyzers.prettier', $prettierConfiguration);
    $this->config->set('git-hooks.pre-commit', [
        PrettierPreCommitHook::class,
    ]);

    $this->makeTempFile('ClassWithFixableIssues.php',
        file_get_contents(__DIR__.'/../../../Fixtures/ClassWithFixableIssues.php')
    );

    GitHooks::shouldReceive('isMergeInProgress')->andReturn(false);
    GitHooks::shouldReceive('getListOfChangedFiles')->andReturn($listOfFixablePHPFiles);

    $this->artisan('git-hooks:pre-commit')->assertSuccessful();
})->with('prettierConfiguration', 'listOfFixablePhpFiles');

test('Fails commit when Prettier is not passing and user does not autofix the files',
    function ($prettierConfiguration, $listOfFixableJSFiles) {
        $this->config->set('git-hooks.code_analyzers.prettier', $prettierConfiguration);
        $this->config->set('git-hooks.pre-commit', [
            PrettierPreCommitHook::class,
        ]);

        $this->makeTempFile('fixable-js-file.js',
            file_get_contents(__DIR__.'/../../../Fixtures/fixable-js-file.js')
        );

        GitHooks::shouldReceive('isMergeInProgress')->andReturn(false);
        GitHooks::shouldReceive('getListOfChangedFiles')->andReturn($listOfFixableJSFiles);

        $this->artisan('git-hooks:pre-commit')
            ->expectsOutputToContain('Prettier Failed')
            ->expectsOutputToContain('COMMIT FAILED')
            ->expectsConfirmation('Would you like to attempt to correct files automagically?', 'no')
            ->assertExitCode(1);
    })->with('prettierConfiguration', 'listOfFixableJSFiles');

test('Commit passes when Prettier fixes the files', function ($prettierConfiguration, $listOfFixableJSFiles) {
    $this->config->set('git-hooks.code_analyzers.prettier', $prettierConfiguration);
    $this->config->set('git-hooks.pre-commit', [
        PrettierPreCommitHook::class,
    ]);

    $this->makeTempFile('fixable-js-file.js',
        file_get_contents(__DIR__.'/../../../Fixtures/fixable-js-file.js')
    );

    GitHooks::shouldReceive('isMergeInProgress')->andReturn(false);
    GitHooks::shouldReceive('getListOfChangedFiles')->andReturn($listOfFixableJSFiles);

    $this->artisan('git-hooks:pre-commit')
        ->expectsOutputToContain('Prettier Failed')
        ->expectsOutputToContain('COMMIT FAILED')
        ->expectsConfirmation('Would you like to attempt to correct files automagically?', 'yes');

    $this->artisan('git-hooks:pre-commit')
        ->doesntExpectOutputToContain('Prettier Failed')
        ->assertSuccessful();
})->with('prettierConfiguration', 'listOfFixableJSFiles');
