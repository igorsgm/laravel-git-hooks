<?php

use Igorsgm\GitHooks\Console\Commands\Hooks\BladeFormatterPreCommitHook;
use Igorsgm\GitHooks\Facades\GitHooks;

beforeEach(
    function () {
        $this->gitInit();
        $this->initializeTempDirectory(base_path('temp'));

        copy(__DIR__.'/../../../Fixtures/fake-tool', __DIR__.'/../../../Fixtures/fake-BladeFormatter');
        copy(__DIR__.'/../../../Fixtures/fake-tool.bat', __DIR__.'/../../../Fixtures/fake-BladeFormatter.bat');

        // Always start clean
        @unlink(__DIR__.'/../../../Fixtures/.BladeFormatter_mode');

        // Force Terminal::hasSttyAvailable() to return true
        $reflection = new ReflectionClass(\Symfony\Component\Console\Terminal::class);
        $property = $reflection->getProperty('stty');
        $property->setAccessible(true);
        $property->setValue(true); // force stty as available
    }
);

afterEach(
    function () {
        @unlink(__DIR__.'/../../../Fixtures/.BladeFormatter_mode');
        @unlink(__DIR__.'/../../../Fixtures/fake-BladeFormatter');
        @unlink(__DIR__.'/../../../Fixtures/fake-BladeFormatter.bat');
    }
);

test(
    'Skips Blade Formatter check when there is none .blade.php files added to commit', function ($bladeFormatterConfiguration) {
        $this->config->set('git-hooks.code_analyzers.blade_formatter', $bladeFormatterConfiguration);
        $this->config->set(
            'git-hooks.pre-commit', [
                BladeFormatterPreCommitHook::class,
            ]
        );

        $this->makeTempFile(
            'CommitMessageFixtureHook1.php',
            file_get_contents(__DIR__.'/../../../Fixtures/CommitMessageFixtureHook1.php')
        );

        GitHooks::shouldReceive('isMergeInProgress')->andReturn(false);
        GitHooks::shouldReceive('getListOfChangedFiles')->andReturn('AM src/CommitMessageFixtureHook1.php');

        $this->artisan('git-hooks:pre-commit')->assertSuccessful();
    }
)->with('bladeFormatterConfiguration');

test(
    'Fails commit when Blade Formatter is not passing and user does not autofix the files',
    function ($bladeFormatterConfiguration, $listOfFixablePhpFiles) {
        $this->config->set('git-hooks.code_analyzers.blade_formatter', $bladeFormatterConfiguration);
        $this->config->set(
            'git-hooks.pre-commit', [
                BladeFormatterPreCommitHook::class,
            ]
        );

        $this->makeTempFile(
            'fixable-blade-file.blade.php',
            file_get_contents(__DIR__.'/../../../Fixtures/fixable-blade-file.blade.php')
        );

        GitHooks::shouldReceive('isMergeInProgress')->andReturn(false);
        GitHooks::shouldReceive('getListOfChangedFiles')->andReturn($listOfFixablePhpFiles);

        $this->artisan('git-hooks:pre-commit')
            ->expectsOutputToContain('Blade Formatter Failed')
            ->expectsOutputToContain('COMMIT FAILED')
            ->expectsConfirmation('Would you like to attempt to correct files automagically?', 'no')
            ->assertExitCode(1);
    }
)->with('bladeFormatterConfiguration', 'listOfFixablePhpFiles');

test(
    'Commit passes when Blade Formatter fixes the files', function ($bladeFormatterConfiguration, $listOfFixablePhpFiles) {
        $this->config->set('git-hooks.code_analyzers.blade_formatter', $bladeFormatterConfiguration);
        $this->config->set(
            'git-hooks.pre-commit', [
                BladeFormatterPreCommitHook::class,
            ]
        );

        $this->makeTempFile(
            'fixable-blade-file.blade.php',
            file_get_contents(__DIR__.'/../../../Fixtures/fixable-blade-file.blade.php')
        );

        GitHooks::shouldReceive('isMergeInProgress')->andReturn(false);
        GitHooks::shouldReceive('getListOfChangedFiles')->andReturn($listOfFixablePhpFiles);

        $this->artisan('git-hooks:pre-commit')
            ->expectsOutputToContain('Blade Formatter Failed')
            ->expectsOutputToContain('COMMIT FAILED')
            ->expectsConfirmation('Would you like to attempt to correct files automagically?', 'yes');

        // Switch to success
        touch(__DIR__.'/../../../Fixtures/.BladeFormatter_mode');

        $this->artisan('git-hooks:pre-commit')
            ->doesntExpectOutputToContain('Blade Formatter Failed')
            ->assertSuccessful();
    }
)->with('bladeFormatterConfiguration', 'listOfFixablePhpFiles');
