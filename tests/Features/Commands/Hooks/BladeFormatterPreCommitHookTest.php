<?php

use Igorsgm\GitHooks\Console\Commands\Hooks\BladeFormatterPreCommitHook;
use Igorsgm\GitHooks\Facades\GitHooks;
use Igorsgm\GitHooks\Traits\GitHelper;

uses(GitHelper::class);
beforeEach(function () {
    $this->gitInit();
    $this->initializeTempDirectory(base_path('temp'));
});

test('Skips Blade Formatter check when there is none .blade.php files added to commit', function ($phpCSConfiguration) {
    $this->config->set('git-hooks.code_analyzers.php_code_sniffer', $phpCSConfiguration);
    $this->config->set('git-hooks.pre-commit', [
        BladeFormatterPreCommitHook::class,
    ]);

    $this->makeTempFile('CommitMessageFixtureHook1.php',
        file_get_contents(__DIR__.'/../../../Fixtures/CommitMessageFixtureHook1.php')
    );

    GitHooks::shouldReceive('isMergeInProgress')->andReturn(false);
    GitHooks::shouldReceive('getListOfChangedFiles')->andReturn('AM src/CommitMessageFixtureHook1.php');

    $this->artisan('git-hooks:pre-commit')->assertSuccessful();
})->with('phpcsConfiguration');

test('Fails commit when Blade Formatter is not passing and user does not autofix the files',
    function ($bladeFormatterConfiguration, $listOfFixableFiles) {
        $this->config->set('git-hooks.code_analyzers.blade_formatter', $bladeFormatterConfiguration);
        $this->config->set('git-hooks.pre-commit', [
            BladeFormatterPreCommitHook::class,
        ]);

        $this->makeTempFile('fixable-blade-file.blade.php',
            file_get_contents(__DIR__.'/../../../Fixtures/fixable-blade-file.blade.php')
        );

        GitHooks::shouldReceive('isMergeInProgress')->andReturn(false);
        GitHooks::shouldReceive('getListOfChangedFiles')->andReturn($listOfFixableFiles);

        $this->artisan('git-hooks:pre-commit')
            ->expectsOutputToContain('Blade Formatter Failed')
            ->expectsOutputToContain('COMMIT FAILED')
            ->expectsConfirmation('Would you like to attempt to correct files automagically?', 'no')
            ->assertExitCode(1);
    })->with('bladeFormatterConfiguration', 'listOfFixableFiles');

test('Commit passes when Blade Formatter fixes the files', function ($bladeFormatterConfiguration, $listOfFixableFiles) {
    $this->config->set('git-hooks.code_analyzers.blade_formatter', $bladeFormatterConfiguration);
    $this->config->set('git-hooks.pre-commit', [
        BladeFormatterPreCommitHook::class,
    ]);

    $this->makeTempFile('fixable-blade-file.blade.php',
        file_get_contents(__DIR__.'/../../../Fixtures/fixable-blade-file.blade.php')
    );

    GitHooks::shouldReceive('isMergeInProgress')->andReturn(false);
    GitHooks::shouldReceive('getListOfChangedFiles')->andReturn($listOfFixableFiles);

    $this->artisan('git-hooks:pre-commit')
        ->expectsOutputToContain('Blade Formatter Failed')
        ->expectsOutputToContain('COMMIT FAILED')
        ->expectsConfirmation('Would you like to attempt to correct files automagically?', 'yes');

    $this->artisan('git-hooks:pre-commit')
        ->doesntExpectOutputToContain('Blade Formatter Failed')
        ->assertSuccessful();
})->with('bladeFormatterConfiguration', 'listOfFixableFiles');
