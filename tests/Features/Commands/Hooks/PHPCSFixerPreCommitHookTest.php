<?php

use Igorsgm\GitHooks\Console\Commands\Hooks\PHPCSFixerPreCommitHook;
use Igorsgm\GitHooks\Facades\GitHooks;

beforeEach(function () {
    $this->gitInit();
    $this->initializeTempDirectory(base_path('temp'));
});

test('Skips PHP CS Fixer check when there is none php files added to commit', function ($phpCSFixerConfiguration) {
    $this->config->set('git-hooks.code_analyzers.php_cs_fixer', $phpCSFixerConfiguration);
    $this->config->set('git-hooks.pre-commit', [
        PHPCSFixerPreCommitHook::class,
    ]);

    $this->makeTempFile('sample.js',
        file_get_contents(__DIR__.'/../../../Fixtures/sample.js')
    );

    GitHooks::shouldReceive('isMergeInProgress')->andReturn(false);
    GitHooks::shouldReceive('getListOfChangedFiles')->andReturn('AM src/sample.js');

    $this->artisan('git-hooks:pre-commit')->assertSuccessful();
})->with('phpcsFixerConfiguration');

test('Fails commit when PHP CS Fixer is not passing and user does not autofix the files',
    function ($phpCSFixerConfiguration, $listOfFixablePhpFiles) {
        $this->config->set('git-hooks.code_analyzers.php_cs_fixer', $phpCSFixerConfiguration);
        $this->config->set('git-hooks.pre-commit', [
            PHPCSFixerPreCommitHook::class,
        ]);

        $this->makeTempFile('ClassWithFixableIssues.php',
            file_get_contents(__DIR__.'/../../../Fixtures/ClassWithFixableIssues.php')
        );

        GitHooks::shouldReceive('isMergeInProgress')->andReturn(false);
        GitHooks::shouldReceive('getListOfChangedFiles')->andReturn($listOfFixablePhpFiles);

        $this->artisan('git-hooks:pre-commit')
            ->expectsOutputToContain('PHP_CS_Fixer Failed')
            ->expectsOutputToContain('COMMIT FAILED')
            ->expectsConfirmation('Would you like to attempt to correct files automagically?', 'no')
            ->assertExitCode(1);
    })->with('phpcsFixerConfiguration', 'listOfFixablePhpFiles');

test('Commit passes when PHP CS Fixer fixes the files', function ($phpCSFixerConfiguration, $listOfFixablePhpFiles) {
    $this->config->set('git-hooks.code_analyzers.php_cs_fixer', $phpCSFixerConfiguration);
    $this->config->set('git-hooks.pre-commit', [
        PHPCSFixerPreCommitHook::class,
    ]);

    $this->makeTempFile('ClassWithFixableIssues.php',
        file_get_contents(__DIR__.'/../../../Fixtures/ClassWithFixableIssues.php')
    );

    GitHooks::shouldReceive('isMergeInProgress')->andReturn(false);
    GitHooks::shouldReceive('getListOfChangedFiles')->andReturn($listOfFixablePhpFiles);

    $this->artisan('git-hooks:pre-commit')
        ->expectsOutputToContain('PHP_CS_Fixer Failed')
        ->expectsOutputToContain('COMMIT FAILED')
        ->expectsConfirmation('Would you like to attempt to correct files automagically?', 'yes');

    $this->artisan('git-hooks:pre-commit')
        ->doesntExpectOutputToContain('PHP_CS_Fixer Failed')
        ->assertSuccessful();
})->with('phpcsFixerConfiguration', 'listOfFixablePhpFiles');
