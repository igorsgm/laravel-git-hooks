<?php

declare(strict_types=1);

use Igorsgm\GitHooks\Console\Commands\Hooks\LarastanPreCommitHook;
use Igorsgm\GitHooks\Facades\GitHooks;

$projectRoot = dirname(__DIR__, 3);
$larastanBin = $projectRoot.'/vendor/bin/phpstan';

beforeEach(function () {
    $this->gitInit();
    $this->initializeTempDirectory(base_path('temp'));
});

test('Larastan fails when staged PHP file has type errors', function ($larastanConfiguration) use ($projectRoot) {
    $this->config->set('git-hooks.code_analyzers.larastan', $larastanConfiguration);
    $this->config->set('git-hooks.pre-commit', [LarastanPreCommitHook::class]);

    $originalContent = file_get_contents($projectRoot.'/tests/Fixtures/ClassWithFixableIssues.php');
    $filePath = $this->makeTempFile('ClassWithFixableIssues.php', $originalContent);

    GitHooks::shouldReceive('isMergeInProgress')->andReturn(false);
    GitHooks::shouldReceive('getListOfChangedFiles')->andReturn('AM temp/ClassWithFixableIssues.php');

    $this->artisan('git-hooks:pre-commit')
        ->expectsOutputToContain('Larastan Failed')
        ->expectsOutputToContain('COMMIT FAILED')
        ->assertExitCode(1);

    expect(file_get_contents($filePath))->toBe($originalContent);
})->with('larastanConfiguration')->skip(!file_exists($larastanBin), 'PHPStan/Larastan binary not found');

test('Larastan passes when staged PHP file has no type errors', function ($larastanConfiguration) use ($projectRoot) {
    $this->config->set('git-hooks.code_analyzers.larastan', $larastanConfiguration);
    $this->config->set('git-hooks.pre-commit', [LarastanPreCommitHook::class]);

    $originalContent = file_get_contents($projectRoot.'/tests/Fixtures/ClassWithoutFixableIssues.php');
    $filePath = $this->makeTempFile('ClassWithoutFixableIssues.php', $originalContent);

    GitHooks::shouldReceive('isMergeInProgress')->andReturn(false);
    GitHooks::shouldReceive('getListOfChangedFiles')->andReturn('AM temp/ClassWithoutFixableIssues.php');

    $this->artisan('git-hooks:pre-commit')
        ->doesntExpectOutputToContain('Larastan Failed')
        ->assertSuccessful();

    expect(file_get_contents($filePath))->toBe($originalContent);
})->with('larastanConfiguration')->skip(!file_exists($larastanBin), 'PHPStan/Larastan binary not found');

test('Larastan skips non-PHP files staged alongside PHP file with errors', function ($larastanConfiguration) use ($projectRoot) {
    $this->config->set('git-hooks.code_analyzers.larastan', $larastanConfiguration);
    $this->config->set('git-hooks.pre-commit', [LarastanPreCommitHook::class]);

    $phpOriginal = file_get_contents($projectRoot.'/tests/Fixtures/ClassWithFixableIssues.php');
    $phpPath = $this->makeTempFile('ClassWithFixableIssues.php', $phpOriginal);

    $jsOriginal = file_get_contents($projectRoot.'/tests/Fixtures/sample.js');
    $jsPath = $this->makeTempFile('sample.js', $jsOriginal);

    GitHooks::shouldReceive('isMergeInProgress')->andReturn(false);
    GitHooks::shouldReceive('getListOfChangedFiles')
        ->andReturn("AM temp/ClassWithFixableIssues.php\nAM temp/sample.js");

    $this->artisan('git-hooks:pre-commit')
        ->expectsOutputToContain('Larastan Failed')
        ->doesntExpectOutputToContain('temp/sample.js')
        ->expectsOutputToContain('COMMIT FAILED')
        ->assertExitCode(1);

    expect(file_get_contents($phpPath))->toBe($phpOriginal);
    expect(file_get_contents($jsPath))->toBe($jsOriginal);
})->with('larastanConfiguration')->skip(!file_exists($larastanBin), 'PHPStan/Larastan binary not found');

test('Larastan processes multiple PHP files in a single run', function ($larastanConfiguration) use ($projectRoot) {
    $this->config->set('git-hooks.code_analyzers.larastan', $larastanConfiguration);
    $this->config->set('git-hooks.pre-commit', [LarastanPreCommitHook::class]);

    $fixableOriginal = file_get_contents($projectRoot.'/tests/Fixtures/ClassWithFixableIssues.php');
    $fixablePath = $this->makeTempFile('ClassWithFixableIssues.php', $fixableOriginal);

    $cleanOriginal = file_get_contents($projectRoot.'/tests/Fixtures/ClassWithoutFixableIssues.php');
    $cleanPath = $this->makeTempFile('ClassWithoutFixableIssues.php', $cleanOriginal);

    GitHooks::shouldReceive('isMergeInProgress')->andReturn(false);
    GitHooks::shouldReceive('getListOfChangedFiles')
        ->andReturn("AM temp/ClassWithFixableIssues.php\nAM temp/ClassWithoutFixableIssues.php");

    $this->artisan('git-hooks:pre-commit')
        ->expectsOutputToContain('Larastan Failed')
        ->expectsOutputToContain('COMMIT FAILED')
        ->assertExitCode(1);

    expect(file_get_contents($fixablePath))->toBe($fixableOriginal);
    expect(file_get_contents($cleanPath))->toBe($cleanOriginal);
})->with('larastanConfiguration')->skip(!file_exists($larastanBin), 'PHPStan/Larastan binary not found');
