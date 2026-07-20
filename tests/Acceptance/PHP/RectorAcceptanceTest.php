<?php

declare(strict_types=1);

use Igorsgm\GitHooks\Console\Commands\Hooks\RectorPreCommitHook;
use Igorsgm\GitHooks\Facades\GitHooks;

$projectRoot = dirname(__DIR__, 3);
$rectorBin = $projectRoot.'/vendor/bin/rector';

beforeEach(function () {
    $this->gitInit();
    $this->initializeTempDirectory(base_path('temp'));
});

test('Rector fails when staged PHP file has improvable code', function ($rectorConfiguration) use ($projectRoot) {
    $this->config->set('git-hooks.code_analyzers.rector', $rectorConfiguration);
    $this->config->set('git-hooks.pre-commit', [RectorPreCommitHook::class]);

    $originalContent = file_get_contents($projectRoot.'/tests/Fixtures/ClassWithRectorIssues.php');
    $filePath = $this->makeTempFile('ClassWithRectorIssues.php', $originalContent);

    GitHooks::shouldReceive('isMergeInProgress')->andReturn(false);
    GitHooks::shouldReceive('getListOfChangedFiles')->andReturn('AM temp/ClassWithRectorIssues.php');

    $this->artisan('git-hooks:pre-commit')
        ->expectsOutputToContain('Rector Failed')
        ->expectsOutputToContain('COMMIT FAILED')
        ->expectsConfirmation('Would you like to attempt to correct files automagically?', 'no')
        ->assertExitCode(1);

    expect(file_get_contents($filePath))->toBe($originalContent);
})->with('rectorConfiguration')->skip(!file_exists($rectorBin), 'Rector binary not found');

test('Rector passes when staged PHP file has no improvable code', function ($rectorConfiguration) use ($projectRoot) {
    $this->config->set('git-hooks.code_analyzers.rector', $rectorConfiguration);
    $this->config->set('git-hooks.pre-commit', [RectorPreCommitHook::class]);

    $originalContent = file_get_contents($projectRoot.'/tests/Fixtures/ClassWithoutFixableIssues.php');
    $filePath = $this->makeTempFile('ClassWithoutFixableIssues.php', $originalContent);

    GitHooks::shouldReceive('isMergeInProgress')->andReturn(false);
    GitHooks::shouldReceive('getListOfChangedFiles')->andReturn('AM temp/ClassWithoutFixableIssues.php');

    $this->artisan('git-hooks:pre-commit')
        ->doesntExpectOutputToContain('Rector Failed')
        ->assertSuccessful();

    expect(file_get_contents($filePath))->toBe($originalContent);
})->with('rectorConfiguration')->skip(!file_exists($rectorBin), 'Rector binary not found');

test('Rector auto-fixes staged PHP file when automatically_fix_errors is enabled', function ($rectorConfiguration) use ($projectRoot) {
    $this->config->set('git-hooks.code_analyzers.rector', $rectorConfiguration);
    $this->config->set('git-hooks.pre-commit', [RectorPreCommitHook::class]);
    $this->config->set('git-hooks.automatically_fix_errors', true);

    $originalContent = file_get_contents($projectRoot.'/tests/Fixtures/ClassWithRectorIssues.php');
    $filePath = $this->makeTempFile('ClassWithRectorIssues.php', $originalContent);

    GitHooks::shouldReceive('isMergeInProgress')->andReturn(false);
    GitHooks::shouldReceive('getListOfChangedFiles')->andReturn('AM temp/ClassWithRectorIssues.php');

    $this->artisan('git-hooks:pre-commit')
        ->expectsOutputToContain('Rector Failed')
        ->expectsOutputToContain('COMMIT FAILED')
        ->assertExitCode(0);

    expect(file_get_contents($filePath))->not->toBe($originalContent);
})->with('rectorConfiguration')->skip(!file_exists($rectorBin), 'Rector binary not found');

test('Rector fixes staged PHP file when user confirms autofix', function ($rectorConfiguration) use ($projectRoot) {
    $this->config->set('git-hooks.code_analyzers.rector', $rectorConfiguration);
    $this->config->set('git-hooks.pre-commit', [RectorPreCommitHook::class]);

    $originalContent = file_get_contents($projectRoot.'/tests/Fixtures/ClassWithRectorIssues.php');
    $filePath = $this->makeTempFile('ClassWithRectorIssues.php', $originalContent);

    GitHooks::shouldReceive('isMergeInProgress')->andReturn(false);
    GitHooks::shouldReceive('getListOfChangedFiles')->andReturn('AM temp/ClassWithRectorIssues.php');

    $this->artisan('git-hooks:pre-commit')
        ->expectsOutputToContain('Rector Failed')
        ->expectsOutputToContain('COMMIT FAILED')
        ->expectsConfirmation('Would you like to attempt to correct files automagically?', 'yes')
        ->assertExitCode(0);

    expect(file_get_contents($filePath))->not->toBe($originalContent);
})->with('rectorConfiguration')->skip(!file_exists($rectorBin), 'Rector binary not found');

test('Rector skips non-PHP files staged alongside PHP file with issues', function ($rectorConfiguration) use ($projectRoot) {
    $this->config->set('git-hooks.code_analyzers.rector', $rectorConfiguration);
    $this->config->set('git-hooks.pre-commit', [RectorPreCommitHook::class]);

    $phpOriginal = file_get_contents($projectRoot.'/tests/Fixtures/ClassWithRectorIssues.php');
    $phpPath = $this->makeTempFile('ClassWithRectorIssues.php', $phpOriginal);

    $jsOriginal = file_get_contents($projectRoot.'/tests/Fixtures/sample.js');
    $jsPath = $this->makeTempFile('sample.js', $jsOriginal);

    GitHooks::shouldReceive('isMergeInProgress')->andReturn(false);
    GitHooks::shouldReceive('getListOfChangedFiles')
        ->andReturn("AM temp/ClassWithRectorIssues.php\nAM temp/sample.js");

    $this->artisan('git-hooks:pre-commit')
        ->expectsOutputToContain('Rector Failed')
        ->doesntExpectOutputToContain('temp/sample.js')
        ->expectsConfirmation('Would you like to attempt to correct files automagically?', 'no')
        ->assertExitCode(1);

    expect(file_get_contents($phpPath))->toBe($phpOriginal);
    expect(file_get_contents($jsPath))->toBe($jsOriginal);
})->with('rectorConfiguration')->skip(!file_exists($rectorBin), 'Rector binary not found');
