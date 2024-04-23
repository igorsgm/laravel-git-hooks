<?php

use Igorsgm\GitHooks\Console\Commands\Hooks\PintPreCommitHook;
use Igorsgm\GitHooks\Facades\GitHooks;

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
})->with('pintConfiguration');

test('Fails commit when Pint is not passing and user does not autofix the files',
    function ($pintConfiguration, $listOfFixablePhpFiles) {
        $this->config->set('git-hooks.code_analyzers.laravel_pint', $pintConfiguration);
        $this->config->set('git-hooks.pre-commit', [
            PintPreCommitHook::class,
        ]);

        $this->makeTempFile('ClassWithFixableIssues.php',
            file_get_contents(__DIR__.'/../../../Fixtures/ClassWithFixableIssues.php')
        );

        GitHooks::shouldReceive('isMergeInProgress')->andReturn(false);
        GitHooks::shouldReceive('getListOfChangedFiles')->andReturn($listOfFixablePhpFiles);

        $this->artisan('git-hooks:pre-commit')
            ->expectsOutputToContain('Pint Failed')
            ->expectsOutputToContain('COMMIT FAILED')
            ->expectsConfirmation('Would you like to attempt to correct files automagically?', 'no')
            ->assertExitCode(1);
    })->with('pintConfiguration', 'listOfFixablePhpFiles');

test('Commit passes when Pint fixes the files', function ($pintConfiguration, $listOfFixablePhpFiles) {
    $this->config->set('git-hooks.code_analyzers.laravel_pint', $pintConfiguration);
    $this->config->set('git-hooks.pre-commit', [
        PintPreCommitHook::class,
    ]);

    $this->makeTempFile('ClassWithFixableIssues.php',
        file_get_contents(__DIR__.'/../../../Fixtures/ClassWithFixableIssues.php')
    );

    GitHooks::shouldReceive('isMergeInProgress')->andReturn(false);
    GitHooks::shouldReceive('getListOfChangedFiles')->andReturn($listOfFixablePhpFiles);

    $this->artisan('git-hooks:pre-commit')
        ->expectsOutputToContain('Pint Failed')
        ->expectsOutputToContain('COMMIT FAILED')
        ->expectsConfirmation('Would you like to attempt to correct files automagically?', 'yes');

    $this->artisan('git-hooks:pre-commit')
        ->doesntExpectOutputToContain('Pint Failed')
        ->assertSuccessful();
})->with('pintConfiguration', 'listOfFixablePhpFiles');

test('Commit passes when Pint fixes the files automatically', function ($pintConfiguration, $listOfFixablePhpFiles) {
    $this->config->set('git-hooks.code_analyzers.laravel_pint', $pintConfiguration);
    $this->config->set('git-hooks.pre-commit', [
        PintPreCommitHook::class,
    ]);
    $this->config->set('git-hooks.automatically_fix_errors', true);

    $this->makeTempFile('ClassWithFixableIssues.php',
        file_get_contents(__DIR__.'/../../../Fixtures/ClassWithFixableIssues.php')
    );

    GitHooks::shouldReceive('isMergeInProgress')->andReturn(false);
    GitHooks::shouldReceive('getListOfChangedFiles')->andReturn($listOfFixablePhpFiles);

    $this->artisan('git-hooks:pre-commit')
        ->expectsOutputToContain('Pint Failed')
        ->expectsOutputToContain('COMMIT FAILED')
        ->expectsOutputToContain('AUTOFIX')
        ->assertSuccessful();

    $this->artisan('git-hooks:pre-commit')
        ->doesntExpectOutputToContain('Pint Failed')
        ->assertSuccessful();
})->with('pintConfiguration', 'listOfFixablePhpFiles');

test('Commit passes when Pint fixes the files automatically with analyzer rerun', function ($pintConfiguration, $listOfFixablePhpFiles) {
    $this->config->set('git-hooks.code_analyzers.laravel_pint', $pintConfiguration);
    $this->config->set('git-hooks.pre-commit', [
        PintPreCommitHook::class,
    ]);
    $this->config->set('git-hooks.automatically_fix_errors', true);
    $this->config->set('git-hooks.rerun_analyzer_after_autofix', true);
    $this->config->set('git-hooks.debug_commands', true);

    $this->makeTempFile('ClassWithFixableIssues.php',
        file_get_contents(__DIR__.'/../../../Fixtures/ClassWithFixableIssues.php')
    );

    GitHooks::shouldReceive('isMergeInProgress')->andReturn(false);
    GitHooks::shouldReceive('getListOfChangedFiles')->andReturn($listOfFixablePhpFiles);

    $this->artisan('git-hooks:pre-commit')
        ->expectsOutputToContain('Pint Failed')
        ->expectsOutputToContain('COMMIT FAILED')
        ->expectsOutputToContain('AUTOFIX')
        ->doesntExpectOutputToContain('Pint Failed')
        ->assertSuccessful();

    $this->artisan('git-hooks:pre-commit')
        ->doesntExpectOutputToContain('Pint Failed')
        ->assertSuccessful();
})->with('pintConfiguration', 'listOfFixablePhpFiles');

test('Commit passes when Pint fixes the files automatically with debug commands', function ($pintConfiguration, $listOfFixablePhpFiles) {
    $this->config->set('git-hooks.code_analyzers.laravel_pint', $pintConfiguration);
    $this->config->set('git-hooks.pre-commit', [
        PintPreCommitHook::class,
    ]);
    $this->config->set('git-hooks.automatically_fix_errors', true);
    $this->config->set('git-hooks.debug_commands', true);

    $this->makeTempFile('ClassWithFixableIssues.php',
        file_get_contents(__DIR__.'/../../../Fixtures/ClassWithFixableIssues.php')
    );

    GitHooks::shouldReceive('isMergeInProgress')->andReturn(false);
    GitHooks::shouldReceive('getListOfChangedFiles')->andReturn($listOfFixablePhpFiles);

    $this->artisan('git-hooks:pre-commit')
        ->expectsOutputToContain('Pint Failed')
        ->expectsOutputToContain('COMMIT FAILED')
        ->expectsOutputToContain('AUTOFIX')
        ->doesntExpectOutputToContain('Pint Failed')
        ->assertSuccessful();

    $this->artisan('git-hooks:pre-commit')
        ->doesntExpectOutputToContain('Pint Failed')
        ->assertSuccessful();
})->with('pintConfiguration', 'listOfFixablePhpFiles');

test('Commit passes when Pint fixes the files automatically with output errors', function ($pintConfiguration, $listOfFixablePhpFiles) {
    $this->config->set('git-hooks.code_analyzers.laravel_pint', $pintConfiguration);
    $this->config->set('git-hooks.pre-commit', [
        PintPreCommitHook::class,
    ]);
    $this->config->set('git-hooks.automatically_fix_errors', true);
    $this->config->set('git-hooks.output_errors', true);
    $this->config->set('git-hooks.debug_commands', false);

    $this->makeTempFile('ClassWithFixableIssues.php',
        file_get_contents(__DIR__.'/../../../Fixtures/ClassWithFixableIssues.php')
    );

    GitHooks::shouldReceive('isMergeInProgress')->andReturn(false);
    GitHooks::shouldReceive('getListOfChangedFiles')->andReturn($listOfFixablePhpFiles);

    $this->artisan('git-hooks:pre-commit')
        ->expectsOutputToContain('Pint Failed')
        ->expectsOutputToContain('COMMIT FAILED')
        ->expectsOutputToContain('AUTOFIX')
        ->doesntExpectOutputToContain('Pint Failed')
        ->assertSuccessful();

    $this->artisan('git-hooks:pre-commit')
        ->doesntExpectOutputToContain('Pint Failed')
        ->assertSuccessful();
})->with('pintConfiguration', 'listOfFixablePhpFiles');
