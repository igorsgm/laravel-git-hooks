<?php

use Igorsgm\GitHooks\Console\Commands\Hooks\PHPCodeSnifferPreCommitHook;
use Igorsgm\GitHooks\Facades\GitHooks;

beforeEach(
    function () {
        $this->gitInit();
        $this->initializeTempDirectory(base_path('temp'));

        copy(__DIR__.'/../../../Fixtures/fake-tool', __DIR__.'/../../../Fixtures/fake-PHP_CodeSniffer');
        copy(__DIR__.'/../../../Fixtures/fake-tool.bat', __DIR__.'/../../../Fixtures/fake-PHP_CodeSniffer.bat');

        // Always start clean
        @unlink(__DIR__.'/../../../Fixtures/.PHP_CodeSniffer_mode');

        // Force Terminal::hasSttyAvailable() to return true
        $reflection = new ReflectionClass(\Symfony\Component\Console\Terminal::class);
        $property = $reflection->getProperty('stty');
        $property->setAccessible(true);
        $property->setValue(true); // force stty as available
    }
);

afterEach(
    function () {
        @unlink(__DIR__.'/../../../Fixtures/.PHP_CodeSniffer_mode');
        @unlink(__DIR__.'/../../../Fixtures/fake-PHP_CodeSniffer');
        @unlink(__DIR__.'/../../../Fixtures/fake-PHP_CodeSniffer.bat');
    }
);

test(
    'Skips PHPCS check when there is none php files added to commit', function ($phpCSConfiguration) {
        $this->config->set('git-hooks.code_analyzers.php_code_sniffer', $phpCSConfiguration);
        $this->config->set(
            'git-hooks.pre-commit', [
                PHPCodeSnifferPreCommitHook::class,
            ]
        );

        $this->makeTempFile(
            'sample.js',
            file_get_contents(__DIR__.'/../../../Fixtures/sample.js')
        );

        GitHooks::shouldReceive('isMergeInProgress')->andReturn(false);
        GitHooks::shouldReceive('getListOfChangedFiles')->andReturn('AM src/sample.js');

        $this->artisan('git-hooks:pre-commit')->assertSuccessful();
    }
)->with('phpcsConfiguration');

test(
    'Fails commit when PHPCS is not passing and user does not autofix the files',
    function ($phpCSConfiguration, $listOfFixablePhpFiles) {
        $this->config->set('git-hooks.code_analyzers.php_code_sniffer', $phpCSConfiguration);
        $this->config->set(
            'git-hooks.pre-commit', [
                PHPCodeSnifferPreCommitHook::class,
            ]
        );

        $this->makeTempFile(
            'ClassWithFixableIssues.php',
            file_get_contents(__DIR__.'/../../../Fixtures/ClassWithFixableIssues.php')
        );

        GitHooks::shouldReceive('isMergeInProgress')->andReturn(false);
        GitHooks::shouldReceive('getListOfChangedFiles')->andReturn($listOfFixablePhpFiles);

        $this->artisan('git-hooks:pre-commit')
            ->expectsOutputToContain('PHP_CodeSniffer Failed')
            ->expectsOutputToContain('COMMIT FAILED')
            ->expectsConfirmation('Would you like to attempt to correct files automagically?', 'no')
            ->assertExitCode(1);
    }
)->with('phpcsConfiguration', 'listOfFixablePhpFiles');

test(
    'Commit passes when PHPCBF fixes the files', function ($phpCSConfiguration, $listOfFixablePhpFiles) {
        $this->config->set('git-hooks.code_analyzers.php_code_sniffer', $phpCSConfiguration);
        $this->config->set(
            'git-hooks.pre-commit', [
                PHPCodeSnifferPreCommitHook::class,
            ]
        );

        $this->makeTempFile(
            'ClassWithFixableIssues.php',
            file_get_contents(__DIR__.'/../../../Fixtures/ClassWithFixableIssues.php')
        );

        GitHooks::shouldReceive('isMergeInProgress')->andReturn(false);
        GitHooks::shouldReceive('getListOfChangedFiles')->andReturn($listOfFixablePhpFiles);

        $this->artisan('git-hooks:pre-commit')
            ->expectsOutputToContain('PHP_CodeSniffer Failed')
            ->expectsOutputToContain('COMMIT FAILED')
            ->expectsConfirmation('Would you like to attempt to correct files automagically?', 'yes');

        // Switch to success
        touch(__DIR__.'/../../../Fixtures/.PHP_CodeSniffer_mode');

        $this->artisan('git-hooks:pre-commit')
            ->doesntExpectOutputToContain('PHP_CodeSniffer Failed')
            ->assertSuccessful();
    }
)->with('phpcsConfiguration', 'listOfFixablePhpFiles');
