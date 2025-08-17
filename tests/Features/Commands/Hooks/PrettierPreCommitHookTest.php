<?php

use Igorsgm\GitHooks\Console\Commands\Hooks\PrettierPreCommitHook;
use Igorsgm\GitHooks\Facades\GitHooks;

beforeEach(
    function () {
        $this->gitInit();
        $this->initializeTempDirectory(base_path('temp'));

        copy(__DIR__.'/../../../Fixtures/fake-tool', __DIR__.'/../../../Fixtures/fake-Prettier');
        copy(__DIR__.'/../../../Fixtures/fake-tool.bat', __DIR__.'/../../../Fixtures/fake-Prettier.bat');

        // Always start clean
        @unlink(__DIR__.'/../../../Fixtures/.Prettier_mode');

        // Force Terminal::hasSttyAvailable() to return true
        $reflection = new ReflectionClass(\Symfony\Component\Console\Terminal::class);
        $property = $reflection->getProperty('stty');
        $property->setAccessible(true);
        $property->setValue(true); // force stty as available
    }
);

afterEach(
    function () {
        @unlink(__DIR__.'/../../../Fixtures/.Prettier_mode');
        @unlink(__DIR__.'/../../../Fixtures/fake-Prettier');
        @unlink(__DIR__.'/../../../Fixtures/fake-Prettier.bat');
    }
);

test(
    'Skips Prettier check when there is none JS files added to commit', function ($prettierConfiguration, $listOfFixablePHPFiles) {
        $this->config->set('git-hooks.code_analyzers.prettier', $prettierConfiguration);
        $this->config->set(
            'git-hooks.pre-commit', [
                PrettierPreCommitHook::class,
            ]
        );

        $this->makeTempFile(
            'ClassWithFixableIssues.php',
            file_get_contents(__DIR__.'/../../../Fixtures/ClassWithFixableIssues.php')
        );

        GitHooks::shouldReceive('isMergeInProgress')->andReturn(false);
        GitHooks::shouldReceive('getListOfChangedFiles')->andReturn($listOfFixablePHPFiles);

        $this->artisan('git-hooks:pre-commit')->assertSuccessful();
    }
)->with('prettierConfiguration', 'listOfFixablePhpFiles');

test(
    'Fails commit when Prettier is not passing and user does not autofix the files',
    function ($prettierConfiguration, $listOfFixableJSFiles) {
        $this->config->set('git-hooks.code_analyzers.prettier', $prettierConfiguration);
        $this->config->set(
            'git-hooks.pre-commit', [
                PrettierPreCommitHook::class,
            ]
        );

        $this->makeTempFile(
            'fixable-js-file.js',
            file_get_contents(__DIR__.'/../../../Fixtures/fixable-js-file.js')
        );

        GitHooks::shouldReceive('isMergeInProgress')->andReturn(false);
        GitHooks::shouldReceive('getListOfChangedFiles')->andReturn($listOfFixableJSFiles);

        $this->artisan('git-hooks:pre-commit')
            ->expectsOutputToContain('Prettier Failed')
            ->expectsOutputToContain('COMMIT FAILED')
            ->expectsConfirmation('Would you like to attempt to correct files automagically?', 'no')
            ->assertExitCode(1);
    }
)->with('prettierConfiguration', 'listOfFixableJSFiles');

test(
    'Commit passes when Prettier fixes the files with CLI confirmation', function ($prettierConfiguration, $listOfFixableJSFiles) {
        $this->config->set('git-hooks.code_analyzers.prettier', $prettierConfiguration);
        $this->config->set(
            'git-hooks.pre-commit', [
                PrettierPreCommitHook::class,
            ]
        );

        $this->makeTempFile(
            'fixable-js-file.js',
            file_get_contents(__DIR__.'/../../../Fixtures/fixable-js-file.js')
        );

        GitHooks::shouldReceive('isMergeInProgress')->andReturn(false);
        GitHooks::shouldReceive('getListOfChangedFiles')->andReturn($listOfFixableJSFiles);

        $this->artisan('git-hooks:pre-commit')
            ->expectsOutputToContain('Prettier Failed')
            ->expectsOutputToContain('COMMIT FAILED')
            ->expectsConfirmation('Would you like to attempt to correct files automagically?', 'yes');

        // Switch to success
        touch(__DIR__.'/../../../Fixtures/.Prettier_mode');

        $this->artisan('git-hooks:pre-commit')
            ->doesntExpectOutputToContain('Prettier Failed')
            ->assertSuccessful();
    }
)->with('prettierConfiguration', 'listOfFixableJSFiles');

test(
    'Commit passes when Prettier fixes the files automatically', function ($prettierConfiguration, $listOfFixableJSFiles) {
        $this->config->set('git-hooks.code_analyzers.prettier', $prettierConfiguration);
        $this->config->set(
            'git-hooks.pre-commit', [
                PrettierPreCommitHook::class,
            ]
        );
        $this->config->set('git-hooks.automatically_fix_errors', true);

        $this->makeTempFile(
            'fixable-js-file.js',
            file_get_contents(__DIR__.'/../../../Fixtures/fixable-js-file.js')
        );

        GitHooks::shouldReceive('isMergeInProgress')->andReturn(false);
        GitHooks::shouldReceive('getListOfChangedFiles')->andReturn($listOfFixableJSFiles);

        // Switch to success
        touch(__DIR__.'/../../../Fixtures/.Prettier_mode');

        $this->artisan('git-hooks:pre-commit')
            ->doesntExpectOutputToContain('Prettier Failed')
            ->assertSuccessful();
    }
)->with('prettierConfiguration', 'listOfFixableJSFiles');

test(
    'Commit passes when Prettier fixes the files automatically with analyzer rerun', function ($prettierConfiguration, $listOfFixableJSFiles) {
        $this->config->set('git-hooks.code_analyzers.prettier', $prettierConfiguration);
        $this->config->set(
            'git-hooks.pre-commit', [
                PrettierPreCommitHook::class,
            ]
        );
        $this->config->set('git-hooks.automatically_fix_errors', true);
        $this->config->set('git-hooks.rerun_analyzer_after_autofix', true);
        $this->config->set('git-hooks.debug_commands', true);

        $this->makeTempFile(
            'fixable-js-file.js',
            file_get_contents(__DIR__.'/../../../Fixtures/fixable-js-file.js')
        );

        GitHooks::shouldReceive('isMergeInProgress')->andReturn(false);
        GitHooks::shouldReceive('getListOfChangedFiles')->andReturn($listOfFixableJSFiles);

        // Switch to success
        touch(__DIR__.'/../../../Fixtures/.Prettier_mode');

        $this->artisan('git-hooks:pre-commit')
            ->doesntExpectOutputToContain('Prettier Failed')
            ->assertSuccessful();

        $this->artisan('git-hooks:pre-commit')
            ->doesntExpectOutputToContain('Prettier Failed')
            ->assertSuccessful();
    }
)->with('prettierConfiguration', 'listOfFixableJSFiles');

test(
    'Commit passes when Prettier fixes the files automatically with debug commands', function ($prettierConfiguration, $listOfFixableJSFiles) {
        $this->config->set('git-hooks.code_analyzers.prettier', $prettierConfiguration);
        $this->config->set(
            'git-hooks.pre-commit', [
                PrettierPreCommitHook::class,
            ]
        );
        $this->config->set('git-hooks.automatically_fix_errors', true);
        $this->config->set('git-hooks.debug_commands', true);

        $this->makeTempFile(
            'fixable-js-file.js',
            file_get_contents(__DIR__.'/../../../Fixtures/fixable-js-file.js')
        );

        GitHooks::shouldReceive('isMergeInProgress')->andReturn(false);
        GitHooks::shouldReceive('getListOfChangedFiles')->andReturn($listOfFixableJSFiles);

        // Switch to success
        touch(__DIR__.'/../../../Fixtures/.Prettier_mode');

        $this->artisan('git-hooks:pre-commit')
            ->doesntExpectOutputToContain('Prettier Failed')
            ->assertSuccessful();

        $this->artisan('git-hooks:pre-commit')
            ->doesntExpectOutputToContain('Prettier Failed')
            ->assertSuccessful();
    }
)->with('prettierConfiguration', 'listOfFixableJSFiles');

test(
    'Commit passes when Prettier fixes the files automatically with output errors', function ($prettierConfiguration, $listOfFixableJSFiles) {
        $this->config->set('git-hooks.code_analyzers.prettier', $prettierConfiguration);
        $this->config->set(
            'git-hooks.pre-commit', [
                PrettierPreCommitHook::class,
            ]
        );
        $this->config->set('git-hooks.automatically_fix_errors', true);
        $this->config->set('git-hooks.output_errors', true);
        $this->config->set('git-hooks.debug_commands', false);

        $this->makeTempFile(
            'fixable-js-file.js',
            file_get_contents(__DIR__.'/../../../Fixtures/fixable-js-file.js')
        );

        GitHooks::shouldReceive('isMergeInProgress')->andReturn(false);
        GitHooks::shouldReceive('getListOfChangedFiles')->andReturn($listOfFixableJSFiles);

        // Switch to success
        touch(__DIR__.'/../../../Fixtures/.Prettier_mode');

        $this->artisan('git-hooks:pre-commit')
            ->doesntExpectOutputToContain('Prettier Failed')
            ->assertSuccessful();

        $this->artisan('git-hooks:pre-commit')
            ->doesntExpectOutputToContain('Prettier Failed')
            ->assertSuccessful();
    }
)->with('prettierConfiguration', 'listOfFixableJSFiles');
