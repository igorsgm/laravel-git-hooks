<?php

use Igorsgm\GitHooks\Console\Commands\Hooks\PHPCodeSnifferPreCommitHook;
use Igorsgm\GitHooks\Facades\GitHooks;

beforeEach(function () {
    $this->gitInit();
    $this->initializeTempDirectory(base_path('temp'));
});

test('Skips PHPCS check when there is none php files added to commit', function ($phpCSConfiguration) {
    $this->config->set('git-hooks.code_analyzers.php_code_sniffer', $phpCSConfiguration);
    $this->config->set('git-hooks.pre-commit', [
        PHPCodeSnifferPreCommitHook::class,
    ]);

    $this->makeTempFile('sample.js',
        file_get_contents(__DIR__.'/../../../Fixtures/sample.js')
    );

    GitHooks::shouldReceive('isMergeInProgress')->andReturn(false);
    GitHooks::shouldReceive('getListOfChangedFiles')->andReturn('AM src/sample.js');

    $this->artisan('git-hooks:pre-commit')->assertSuccessful();
})->with('phpcsConfiguration');

test('Fails commit when PHPCS is not passing and user does not autofix the files',
    function ($phpCSConfiguration, $listOfFixablePhpFiles) {
        $this->config->set('git-hooks.code_analyzers.php_code_sniffer', $phpCSConfiguration);
        $this->config->set('git-hooks.pre-commit', [
            PHPCodeSnifferPreCommitHook::class,
        ]);

        $this->makeTempFile('ClassWithFixableIssues.php',
            file_get_contents(__DIR__.'/../../../Fixtures/ClassWithFixableIssues.php')
        );

        GitHooks::shouldReceive('isMergeInProgress')->andReturn(false);
        GitHooks::shouldReceive('getListOfChangedFiles')->andReturn($listOfFixablePhpFiles);

        $this->artisan('git-hooks:pre-commit')
            ->expectsOutputToContain('PHP_CodeSniffer Failed')
            ->expectsOutputToContain('COMMIT FAILED')
            ->expectsConfirmation('Would you like to attempt to correct files automagically?', 'no')
            ->assertExitCode(1);
    })->with('phpcsConfiguration', 'listOfFixablePhpFiles');

test('Commit passes when PHPCBF fixes the files', function ($phpCSConfiguration, $listOfFixablePhpFiles) {
    $this->config->set('git-hooks.code_analyzers.php_code_sniffer', $phpCSConfiguration);
    $this->config->set('git-hooks.pre-commit', [
        PHPCodeSnifferPreCommitHook::class,
    ]);

    $this->makeTempFile('ClassWithFixableIssues.php',
        file_get_contents(__DIR__.'/../../../Fixtures/ClassWithFixableIssues.php')
    );

    GitHooks::shouldReceive('isMergeInProgress')->andReturn(false);
    GitHooks::shouldReceive('getListOfChangedFiles')->andReturn($listOfFixablePhpFiles);

    $this->artisan('git-hooks:pre-commit')
        ->expectsOutputToContain('PHP_CodeSniffer Failed')
        ->expectsOutputToContain('COMMIT FAILED')
        ->expectsConfirmation('Would you like to attempt to correct files automagically?', 'yes');

    $this->artisan('git-hooks:pre-commit')
        ->doesntExpectOutputToContain('PHP_CodeSniffer Failed')
        ->assertSuccessful();
})->with('phpcsConfiguration', 'listOfFixablePhpFiles');
