<?php

declare(strict_types=1);

use Igorsgm\GitHooks\Console\Commands\Hooks\PintPreCommitHook;
use Igorsgm\GitHooks\Facades\GitHooks;

$projectRoot = dirname(__DIR__, 3);
$pintBin = $projectRoot.'/vendor/bin/pint';

beforeEach(function () {
    $this->gitInit();
    $this->initializeTempDirectory(base_path('temp'));
});

test('Pint fails when staged PHP file has style issues', function ($pintConfiguration) use ($projectRoot) {
    $this->config->set('git-hooks.code_analyzers.laravel_pint', $pintConfiguration);
    $this->config->set('git-hooks.pre-commit', [PintPreCommitHook::class]);

    $originalContent = file_get_contents($projectRoot.'/tests/Fixtures/ClassWithFixableIssues.php');
    $filePath = $this->makeTempFile('ClassWithFixableIssues.php', $originalContent);

    GitHooks::shouldReceive('isMergeInProgress')->andReturn(false);
    GitHooks::shouldReceive('getListOfChangedFiles')->andReturn('AM temp/ClassWithFixableIssues.php');

    $this->artisan('git-hooks:pre-commit')
        ->expectsOutputToContain('Pint Failed')
        ->expectsOutputToContain('COMMIT FAILED')
        ->expectsConfirmation('Would you like to attempt to correct files automagically?', 'no')
        ->assertExitCode(1);

    expect(file_get_contents($filePath))->toBe($originalContent);
})->with('pintConfiguration')->skip(!file_exists($pintBin), 'Laravel Pint binary not found');

test('Pint passes when no PHP files are staged', function ($pintConfiguration) use ($projectRoot) {
    $this->config->set('git-hooks.code_analyzers.laravel_pint', $pintConfiguration);
    $this->config->set('git-hooks.pre-commit', [PintPreCommitHook::class]);

    $originalContent = file_get_contents($projectRoot.'/tests/Fixtures/sample.js');
    $jsFilePath = $this->makeTempFile('sample.js', $originalContent);

    GitHooks::shouldReceive('isMergeInProgress')->andReturn(false);
    GitHooks::shouldReceive('getListOfChangedFiles')->andReturn('AM temp/sample.js');

    $this->artisan('git-hooks:pre-commit')
        ->doesntExpectOutputToContain('Pint Failed')
        ->assertSuccessful();

    expect(file_get_contents($jsFilePath))->toBe($originalContent);
})->with('pintConfiguration')->skip(!file_exists($pintBin), 'Laravel Pint binary not found');

test('Pint auto-fixes staged PHP file when automatically_fix_errors is enabled', function ($pintConfiguration) use ($projectRoot) {
    $this->config->set('git-hooks.code_analyzers.laravel_pint', $pintConfiguration);
    $this->config->set('git-hooks.pre-commit', [PintPreCommitHook::class]);
    $this->config->set('git-hooks.automatically_fix_errors', true);

    $originalContent = file_get_contents($projectRoot.'/tests/Fixtures/ClassWithFixableIssues.php');
    $filePath = $this->makeTempFile('ClassWithFixableIssues.php', $originalContent);

    GitHooks::shouldReceive('isMergeInProgress')->andReturn(false);
    GitHooks::shouldReceive('getListOfChangedFiles')->andReturn('AM temp/ClassWithFixableIssues.php');

    $this->artisan('git-hooks:pre-commit')
        ->expectsOutputToContain('Pint Failed')
        ->expectsOutputToContain('COMMIT FAILED')
        ->assertExitCode(0);

    expect(file_get_contents($filePath))->not->toBe($originalContent);
})->with('pintConfiguration')->skip(!file_exists($pintBin), 'Laravel Pint binary not found');

test('Pint fixes staged PHP file when user confirms autofix', function ($pintConfiguration) use ($projectRoot) {
    $this->config->set('git-hooks.code_analyzers.laravel_pint', $pintConfiguration);
    $this->config->set('git-hooks.pre-commit', [PintPreCommitHook::class]);

    $originalContent = file_get_contents($projectRoot.'/tests/Fixtures/ClassWithFixableIssues.php');
    $filePath = $this->makeTempFile('ClassWithFixableIssues.php', $originalContent);

    GitHooks::shouldReceive('isMergeInProgress')->andReturn(false);
    GitHooks::shouldReceive('getListOfChangedFiles')->andReturn('AM temp/ClassWithFixableIssues.php');

    $this->artisan('git-hooks:pre-commit')
        ->expectsOutputToContain('Pint Failed')
        ->expectsOutputToContain('COMMIT FAILED')
        ->expectsConfirmation('Would you like to attempt to correct files automagically?', 'yes')
        ->assertExitCode(0);

    expect(file_get_contents($filePath))->not->toBe($originalContent);
})->with('pintConfiguration')->skip(!file_exists($pintBin), 'Laravel Pint binary not found');

test('Pint skips non-PHP files staged alongside PHP file with issues', function ($pintConfiguration) use ($projectRoot) {
    $this->config->set('git-hooks.code_analyzers.laravel_pint', $pintConfiguration);
    $this->config->set('git-hooks.pre-commit', [PintPreCommitHook::class]);

    $phpOriginal = file_get_contents($projectRoot.'/tests/Fixtures/ClassWithFixableIssues.php');
    $phpPath = $this->makeTempFile('ClassWithFixableIssues.php', $phpOriginal);

    $jsOriginal = file_get_contents($projectRoot.'/tests/Fixtures/sample.js');
    $jsPath = $this->makeTempFile('sample.js', $jsOriginal);

    GitHooks::shouldReceive('isMergeInProgress')->andReturn(false);
    GitHooks::shouldReceive('getListOfChangedFiles')
        ->andReturn("AM temp/ClassWithFixableIssues.php\nAM temp/sample.js");

    $this->artisan('git-hooks:pre-commit')
        ->expectsOutputToContain('Pint Failed')
        ->doesntExpectOutputToContain('temp/sample.js')
        ->expectsConfirmation('Would you like to attempt to correct files automagically?', 'no')
        ->assertExitCode(1);

    expect(file_get_contents($phpPath))->toBe($phpOriginal);
    expect(file_get_contents($jsPath))->toBe($jsOriginal);
})->with('pintConfiguration')->skip(!file_exists($pintBin), 'Laravel Pint binary not found');

test('Pint processes multiple PHP files in a single run', function ($pintConfiguration) use ($projectRoot) {
    $this->config->set('git-hooks.code_analyzers.laravel_pint', $pintConfiguration);
    $this->config->set('git-hooks.pre-commit', [PintPreCommitHook::class]);

    $fixableOriginal = file_get_contents($projectRoot.'/tests/Fixtures/ClassWithFixableIssues.php');
    $fixablePath = $this->makeTempFile('ClassWithFixableIssues.php', $fixableOriginal);

    $cleanOriginal = file_get_contents($projectRoot.'/tests/Fixtures/ClassWithoutFixableIssues.php');
    $cleanPath = $this->makeTempFile('ClassWithoutFixableIssues.php', $cleanOriginal);

    GitHooks::shouldReceive('isMergeInProgress')->andReturn(false);
    GitHooks::shouldReceive('getListOfChangedFiles')
        ->andReturn("AM temp/ClassWithFixableIssues.php\nAM temp/ClassWithoutFixableIssues.php");

    $this->artisan('git-hooks:pre-commit')
        ->expectsOutputToContain('Pint Failed')
        ->expectsOutputToContain('COMMIT FAILED')
        ->expectsConfirmation('Would you like to attempt to correct files automagically?', 'no')
        ->assertExitCode(1);

    expect(file_get_contents($fixablePath))->toBe($fixableOriginal);
    expect(file_get_contents($cleanPath))->toBe($cleanOriginal);
})->with('pintConfiguration')->skip(!file_exists($pintBin), 'Laravel Pint binary not found');
