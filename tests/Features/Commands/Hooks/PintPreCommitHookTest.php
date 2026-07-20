<?php

declare(strict_types=1);

use Igorsgm\GitHooks\Console\Commands\Hooks\PintPreCommitHook;
use Igorsgm\GitHooks\Facades\GitHooks;

beforeEach(function () {
    $this->gitInit();
    $this->initializeTempDirectory(base_path('temp'));
    $this->config->set('git-hooks.validate_paths', false);
});

test('Skips Pint check when there is none php files added to commit', function ($pintConfiguration) {
    $this->config->set('git-hooks.code_analyzers.laravel_pint', $pintConfiguration);
    $this->config->set('git-hooks.code_analyzers.laravel_pint.path', $this->fakePassBin());
    $this->config->set('git-hooks.pre-commit', [PintPreCommitHook::class]);

    $this->makeTempFile('sample.js', file_get_contents(__DIR__.'/../../../Fixtures/sample.js'));

    GitHooks::shouldReceive('isMergeInProgress')->andReturn(false);
    GitHooks::shouldReceive('getListOfChangedFiles')->andReturn('AM src/sample.js');

    $this->artisan('git-hooks:pre-commit')->assertSuccessful();
})->with('pintConfiguration');

test('Commit passes when Pint finds no issues', function ($pintConfiguration) {
    $this->config->set('git-hooks.code_analyzers.laravel_pint', $pintConfiguration);
    $this->config->set('git-hooks.code_analyzers.laravel_pint.path', $this->fakePassBin());
    $this->config->set('git-hooks.pre-commit', [PintPreCommitHook::class]);

    $this->makeTempFile('ClassWithFixableIssues.php',
        file_get_contents(__DIR__.'/../../../Fixtures/ClassWithFixableIssues.php')
    );

    GitHooks::shouldReceive('isMergeInProgress')->andReturn(false);
    GitHooks::shouldReceive('getListOfChangedFiles')->andReturn('AM temp/ClassWithFixableIssues.php');

    $this->artisan('git-hooks:pre-commit')
        ->doesntExpectOutputToContain('Pint Failed')
        ->assertSuccessful();
})->with('pintConfiguration');

test('Fails commit when Pint is not passing and user does not autofix the files',
    function ($pintConfiguration, $listOfFixablePhpFiles) {
        $this->config->set('git-hooks.code_analyzers.laravel_pint', $pintConfiguration);
        $this->config->set('git-hooks.code_analyzers.laravel_pint.path', $this->fakeFixBin());
        $this->config->set('git-hooks.pre-commit', [PintPreCommitHook::class]);

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
    $this->config->set('git-hooks.code_analyzers.laravel_pint.path', $this->fakeFixBin());
    $this->config->set('git-hooks.pre-commit', [PintPreCommitHook::class]);

    $this->makeTempFile('ClassWithFixableIssues.php',
        file_get_contents(__DIR__.'/../../../Fixtures/ClassWithFixableIssues.php')
    );

    GitHooks::shouldReceive('isMergeInProgress')->andReturn(false);
    GitHooks::shouldReceive('getListOfChangedFiles')->andReturn($listOfFixablePhpFiles);

    $this->artisan('git-hooks:pre-commit')
        ->expectsOutputToContain('Pint Failed')
        ->expectsOutputToContain('COMMIT FAILED')
        ->expectsConfirmation('Would you like to attempt to correct files automagically?', 'yes')
        ->assertSuccessful();
})->with('pintConfiguration', 'listOfFixablePhpFiles');

test('Commit passes when Pint fixes the files automatically', function ($pintConfiguration, $listOfFixablePhpFiles) {
    $this->config->set('git-hooks.code_analyzers.laravel_pint', $pintConfiguration);
    $this->config->set('git-hooks.code_analyzers.laravel_pint.path', $this->fakeFixBin());
    $this->config->set('git-hooks.pre-commit', [PintPreCommitHook::class]);
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
})->with('pintConfiguration', 'listOfFixablePhpFiles');

test('Commit fails when rerun analyzer still fails after autofix', function ($pintConfiguration, $listOfFixablePhpFiles) {
    // fake-fix exits 1 for --test (analyze) and 0 for fix mode — but the rerun also uses
    // --test, so it exits 1 again. This exercises the path where the hook correctly rejects
    // the commit when the post-fix rerun does not pass.
    $this->config->set('git-hooks.code_analyzers.laravel_pint', $pintConfiguration);
    $this->config->set('git-hooks.code_analyzers.laravel_pint.path', $this->fakeFixBin());
    $this->config->set('git-hooks.pre-commit', [PintPreCommitHook::class]);
    $this->config->set('git-hooks.automatically_fix_errors', true);
    $this->config->set('git-hooks.rerun_analyzer_after_autofix', true);

    $this->makeTempFile('ClassWithFixableIssues.php',
        file_get_contents(__DIR__.'/../../../Fixtures/ClassWithFixableIssues.php')
    );

    GitHooks::shouldReceive('isMergeInProgress')->andReturn(false);
    GitHooks::shouldReceive('getListOfChangedFiles')->andReturn($listOfFixablePhpFiles);

    $this->artisan('git-hooks:pre-commit')
        ->expectsOutputToContain('Pint Failed')
        ->expectsOutputToContain('COMMIT FAILED')
        ->expectsOutputToContain('AUTOFIX')
        ->assertExitCode(1);
})->with('pintConfiguration', 'listOfFixablePhpFiles');

test('Commit passes when Pint fixes the files automatically with debug commands', function ($pintConfiguration, $listOfFixablePhpFiles) {
    $this->config->set('git-hooks.code_analyzers.laravel_pint', $pintConfiguration);
    $this->config->set('git-hooks.code_analyzers.laravel_pint.path', $this->fakeFixBin());
    $this->config->set('git-hooks.pre-commit', [PintPreCommitHook::class]);
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
        ->assertSuccessful();
})->with('pintConfiguration', 'listOfFixablePhpFiles');

test('Commit passes when Pint fixes the files automatically with output errors', function ($pintConfiguration, $listOfFixablePhpFiles) {
    $this->config->set('git-hooks.code_analyzers.laravel_pint', $pintConfiguration);
    $this->config->set('git-hooks.code_analyzers.laravel_pint.path', $this->fakeFixBin());
    $this->config->set('git-hooks.pre-commit', [PintPreCommitHook::class]);
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
        ->assertSuccessful();
})->with('pintConfiguration', 'listOfFixablePhpFiles');

