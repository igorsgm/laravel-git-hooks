<?php

declare(strict_types=1);

use Igorsgm\GitHooks\Console\Commands\Hooks\RectorPreCommitHook;
use Igorsgm\GitHooks\Facades\GitHooks;

beforeEach(function () {
    $this->gitInit();
    $this->initializeTempDirectory(base_path('temp'));
});

test('Skips Rector check when there is none php files added to commit', function ($rectorConfiguration) {
    $this->config->set('git-hooks.code_analyzers.rector', $rectorConfiguration);
    $this->config->set('git-hooks.pre-commit', [
        RectorPreCommitHook::class,
    ]);

    $this->makeTempFile('sample.js',
        file_get_contents(__DIR__.'/../../../Fixtures/sample.js')
    );

    GitHooks::shouldReceive('isMergeInProgress')->andReturn(false);
    GitHooks::shouldReceive('getListOfChangedFiles')->andReturn('AM src/sample.js');

    $this->artisan('git-hooks:pre-commit')->assertSuccessful();
})->with('rectorConfiguration');

test('Fails commit when Rector is not passing',
    function ($rectorConfiguration, $listOfRectorPhpFiles) {
        $this->config->set('git-hooks.code_analyzers.rector', $rectorConfiguration);
        $this->config->set('git-hooks.pre-commit', [
            RectorPreCommitHook::class,
        ]);

        $this->makeTempFile('ClassWithRectorIssues.php',
            file_get_contents(__DIR__.'/../../../Fixtures/ClassWithRectorIssues.php')
        );

        GitHooks::shouldReceive('isMergeInProgress')->andReturn(false);
        GitHooks::shouldReceive('getListOfChangedFiles')->andReturn($listOfRectorPhpFiles);

        $this->artisan('git-hooks:pre-commit')
            ->expectsOutputToContain('Rector Failed')
            ->expectsOutputToContain('COMMIT FAILED')
            ->expectsConfirmation('Would you like to attempt to correct files automagically?', 'no')
            ->assertExitCode(1);
    })->with('rectorConfiguration', 'listOfRectorPhpFiles');

test('Commit passes when Rector is passing', function ($rectorConfiguration, $listOfRectorPhpFiles) {
    $this->config->set('git-hooks.code_analyzers.rector', $rectorConfiguration);
    $this->config->set('git-hooks.pre-commit', [
        RectorPreCommitHook::class,
    ]);

    $this->makeTempFile('ClassWithRectorIssues.php',
        file_get_contents(__DIR__.'/../../../Fixtures/ClassWithRectorIssues.php')
    );

    GitHooks::shouldReceive('isMergeInProgress')->andReturn(false);
    GitHooks::shouldReceive('getListOfChangedFiles')->andReturn($listOfRectorPhpFiles);

    $this->artisan('git-hooks:pre-commit')
        ->expectsOutputToContain('Rector Failed')
        ->expectsOutputToContain('COMMIT FAILED')
        ->expectsConfirmation('Would you like to attempt to correct files automagically?', 'yes');

    $this->artisan('git-hooks:pre-commit')
        ->doesntExpectOutputToContain('Rector Failed')
        ->assertSuccessful();
})->with('rectorConfiguration', 'listOfRectorPhpFiles');
