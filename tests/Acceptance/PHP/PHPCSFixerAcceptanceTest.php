<?php

declare(strict_types=1);

use Igorsgm\GitHooks\Console\Commands\Hooks\PHPCSFixerPreCommitHook;
use Igorsgm\GitHooks\Facades\GitHooks;

$projectRoot = dirname(__DIR__, 3);
$phpCsFixerBin = $projectRoot.'/vendor/bin/php-cs-fixer';

beforeEach(function () {
    $this->gitInit();
    $this->initializeTempDirectory(base_path('temp'));
});

test('PHP CS Fixer fails when staged PHP file has style issues', function ($phpCSFixerConfiguration) use ($projectRoot) {
    $this->config->set('git-hooks.code_analyzers.php_cs_fixer', $phpCSFixerConfiguration);
    $this->config->set('git-hooks.pre-commit', [PHPCSFixerPreCommitHook::class]);

    $originalContent = file_get_contents($projectRoot.'/tests/Fixtures/ClassWithFixableIssues.php');
    $filePath = $this->makeTempFile('ClassWithFixableIssues.php', $originalContent);

    GitHooks::shouldReceive('isMergeInProgress')->andReturn(false);
    GitHooks::shouldReceive('getListOfChangedFiles')->andReturn('AM temp/ClassWithFixableIssues.php');

    $this->artisan('git-hooks:pre-commit')
        ->expectsOutputToContain('PHP_CS_Fixer Failed')
        ->expectsOutputToContain('COMMIT FAILED')
        ->expectsConfirmation('Would you like to attempt to correct files automagically?', 'no')
        ->assertExitCode(1);

    expect(file_get_contents($filePath))->toBe($originalContent);
})->with('phpcsFixerConfiguration')->skip(!file_exists($phpCsFixerBin), 'PHP CS Fixer binary not found');

test('PHP CS Fixer passes when no PHP files are staged', function ($phpCSFixerConfiguration) use ($projectRoot) {
    $this->config->set('git-hooks.code_analyzers.php_cs_fixer', $phpCSFixerConfiguration);
    $this->config->set('git-hooks.pre-commit', [PHPCSFixerPreCommitHook::class]);

    $originalContent = file_get_contents($projectRoot.'/tests/Fixtures/sample.js');
    $filePath = $this->makeTempFile('sample.js', $originalContent);

    GitHooks::shouldReceive('isMergeInProgress')->andReturn(false);
    GitHooks::shouldReceive('getListOfChangedFiles')->andReturn('AM temp/sample.js');

    $this->artisan('git-hooks:pre-commit')
        ->doesntExpectOutputToContain('PHP_CS_Fixer Failed')
        ->assertSuccessful();

    expect(file_get_contents($filePath))->toBe($originalContent);
})->with('phpcsFixerConfiguration')->skip(!file_exists($phpCsFixerBin), 'PHP CS Fixer binary not found');

test('PHP CS Fixer auto-fixes staged PHP file when automatically_fix_errors is enabled', function ($phpCSFixerConfiguration) use ($projectRoot) {
    $this->config->set('git-hooks.code_analyzers.php_cs_fixer', $phpCSFixerConfiguration);
    $this->config->set('git-hooks.pre-commit', [PHPCSFixerPreCommitHook::class]);
    $this->config->set('git-hooks.automatically_fix_errors', true);

    $originalContent = file_get_contents($projectRoot.'/tests/Fixtures/ClassWithFixableIssues.php');
    $filePath = $this->makeTempFile('ClassWithFixableIssues.php', $originalContent);

    GitHooks::shouldReceive('isMergeInProgress')->andReturn(false);
    GitHooks::shouldReceive('getListOfChangedFiles')->andReturn('AM temp/ClassWithFixableIssues.php');

    $this->artisan('git-hooks:pre-commit')
        ->expectsOutputToContain('PHP_CS_Fixer Failed')
        ->expectsOutputToContain('COMMIT FAILED')
        ->assertExitCode(0);

    expect(file_get_contents($filePath))->not->toBe($originalContent);
})->with('phpcsFixerConfiguration')->skip(!file_exists($phpCsFixerBin), 'PHP CS Fixer binary not found');

test('PHP CS Fixer fixes staged PHP file when user confirms autofix', function ($phpCSFixerConfiguration) use ($projectRoot) {
    $this->config->set('git-hooks.code_analyzers.php_cs_fixer', $phpCSFixerConfiguration);
    $this->config->set('git-hooks.pre-commit', [PHPCSFixerPreCommitHook::class]);

    $originalContent = file_get_contents($projectRoot.'/tests/Fixtures/ClassWithFixableIssues.php');
    $filePath = $this->makeTempFile('ClassWithFixableIssues.php', $originalContent);

    GitHooks::shouldReceive('isMergeInProgress')->andReturn(false);
    GitHooks::shouldReceive('getListOfChangedFiles')->andReturn('AM temp/ClassWithFixableIssues.php');

    $this->artisan('git-hooks:pre-commit')
        ->expectsOutputToContain('PHP_CS_Fixer Failed')
        ->expectsOutputToContain('COMMIT FAILED')
        ->expectsConfirmation('Would you like to attempt to correct files automagically?', 'yes')
        ->assertExitCode(0);

    expect(file_get_contents($filePath))->not->toBe($originalContent);
})->with('phpcsFixerConfiguration')->skip(!file_exists($phpCsFixerBin), 'PHP CS Fixer binary not found');

test('PHP CS Fixer skips non-PHP files staged alongside PHP file with issues', function ($phpCSFixerConfiguration) use ($projectRoot) {
    $this->config->set('git-hooks.code_analyzers.php_cs_fixer', $phpCSFixerConfiguration);
    $this->config->set('git-hooks.pre-commit', [PHPCSFixerPreCommitHook::class]);

    $phpOriginal = file_get_contents($projectRoot.'/tests/Fixtures/ClassWithFixableIssues.php');
    $phpPath = $this->makeTempFile('ClassWithFixableIssues.php', $phpOriginal);

    $jsOriginal = file_get_contents($projectRoot.'/tests/Fixtures/sample.js');
    $jsPath = $this->makeTempFile('sample.js', $jsOriginal);

    GitHooks::shouldReceive('isMergeInProgress')->andReturn(false);
    GitHooks::shouldReceive('getListOfChangedFiles')
        ->andReturn("AM temp/ClassWithFixableIssues.php\nAM temp/sample.js");

    $this->artisan('git-hooks:pre-commit')
        ->expectsOutputToContain('PHP_CS_Fixer Failed')
        ->doesntExpectOutputToContain('temp/sample.js')
        ->expectsConfirmation('Would you like to attempt to correct files automagically?', 'no')
        ->assertExitCode(1);

    expect(file_get_contents($phpPath))->toBe($phpOriginal);
    expect(file_get_contents($jsPath))->toBe($jsOriginal);
})->with('phpcsFixerConfiguration')->skip(!file_exists($phpCsFixerBin), 'PHP CS Fixer binary not found');
