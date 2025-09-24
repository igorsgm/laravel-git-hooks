<?php

declare(strict_types=1);

use Igorsgm\GitHooks\Console\Commands\Hooks\PhpInsightsPreCommitHook;
use Igorsgm\GitHooks\Facades\GitHooks;

beforeEach(function () {
    $this->gitInit();
    $this->initializeTempDirectory(base_path('temp'));
});

test('Skips PhpInsights check when there is none php files added to commit', function ($phpInsightsConfiguration) {
    $this->config->set('git-hooks.code_analyzers.phpinsights', $phpInsightsConfiguration);
    $this->config->set('git-hooks.pre-commit', [
        PhpInsightsPreCommitHook::class,
    ]);

    $this->makeTempFile('sample.js',
        file_get_contents(__DIR__.'/../../../Fixtures/sample.js')
    );

    GitHooks::shouldReceive('isMergeInProgress')->andReturn(false);
    GitHooks::shouldReceive('getListOfChangedFiles')->andReturn('AM src/sample.js');

    $this->artisan('git-hooks:pre-commit')->assertSuccessful();
})->with('phpinsightsConfiguration');

test('Fails commit when PhpInsights is not passing and user does not autofix the files',
    function ($phpInsightsConfiguration, $listOfFixablePhpFiles) {
        $this->config->set('git-hooks.code_analyzers.phpinsights', $phpInsightsConfiguration);
        $this->config->set('git-hooks.pre-commit', [
            PhpInsightsPreCommitHook::class,
        ]);

        $this->makeTempFile('ClassWithFixableIssues.php',
            file_get_contents(__DIR__.'/../../../Fixtures/ClassWithFixableIssues.php')
        );

        GitHooks::shouldReceive('isMergeInProgress')->andReturn(false);
        GitHooks::shouldReceive('getListOfChangedFiles')->andReturn($listOfFixablePhpFiles);

        $this->artisan('git-hooks:pre-commit')
            ->expectsOutputToContain('PhpInsights Failed')
            ->expectsOutputToContain('COMMIT FAILED')
            ->expectsConfirmation('Would you like to attempt to correct files automagically?', 'no')
            ->assertExitCode(1);
    })->with('phpinsightsConfiguration', 'listOfFixablePhpFiles');

test('Commit passes when PhpInsights fixes the files', function ($phpInsightsConfiguration, $listOfFixablePhpFiles) {
    $this->config->set('git-hooks.code_analyzers.phpinsights', $phpInsightsConfiguration);
    $this->config->set('git-hooks.pre-commit', [
        PhpInsightsPreCommitHook::class,
    ]);

    $this->makeTempFile('ClassWithFixableIssues.php',
        file_get_contents(__DIR__.'/../../../Fixtures/ClassWithFixableIssues.php')
    );

    GitHooks::shouldReceive('isMergeInProgress')->andReturn(false);
    GitHooks::shouldReceive('getListOfChangedFiles')->andReturn($listOfFixablePhpFiles);

    $this->artisan('git-hooks:pre-commit')
        ->expectsOutputToContain('PhpInsights Failed')
        ->expectsOutputToContain('COMMIT FAILED')
        ->expectsConfirmation('Would you like to attempt to correct files automagically?', 'yes');

    $this->artisan('git-hooks:pre-commit')
        ->doesntExpectOutputToContain('PhpInsights Failed')
        ->assertSuccessful();
})->with('phpinsightsConfiguration', 'listOfFixablePhpFiles');
