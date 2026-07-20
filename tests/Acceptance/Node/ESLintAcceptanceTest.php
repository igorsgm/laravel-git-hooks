<?php

declare(strict_types=1);

use Igorsgm\GitHooks\Console\Commands\Hooks\ESLintPreCommitHook;
use Igorsgm\GitHooks\Facades\GitHooks;
use Igorsgm\GitHooks\Tests\Acceptance\ToolSandbox;

$projectRoot = dirname(__DIR__, 3);
$sandbox = ToolSandbox::js('eslint', 'eslint', 'eslint');

beforeEach(function () use ($sandbox) {
    try {
        $sandbox->install();
    } catch (Throwable $e) {
        $this->markTestSkipped('ESLint sandbox setup failed: '.$e->getMessage());
    }

    $this->gitInit();
    $this->initializeTempDirectory(base_path('temp'));
});

test('ESLint fails when staged JS file has linting errors', function () use ($projectRoot, $sandbox) {
    $this->config->set('git-hooks.code_analyzers.eslint', [
        'path' => $sandbox->binaryPath(),
        'config' => $projectRoot.'/tests/Fixtures/.eslintrcFixture.js',
        'file_extensions' => '/\.(js|jsx|ts|tsx)$/',
        'run_in_docker' => false,
        'docker_container' => '',
        'additional_params' => '',
    ]);
    $this->config->set('git-hooks.pre-commit', [ESLintPreCommitHook::class]);

    $originalContent = file_get_contents($projectRoot.'/tests/Fixtures/fixable-js-file.js');
    $filePath = $this->makeTempFile('fixable-js-file.js', $originalContent);

    GitHooks::shouldReceive('isMergeInProgress')->andReturn(false);
    GitHooks::shouldReceive('getListOfChangedFiles')->andReturn('AM temp/fixable-js-file.js');

    $this->artisan('git-hooks:pre-commit')
        ->expectsOutputToContain('ESLint Failed')
        ->expectsOutputToContain('COMMIT FAILED')
        ->expectsConfirmation('Would you like to attempt to correct files automagically?', 'no')
        ->assertExitCode(1);

    expect(file_get_contents($filePath))->toBe($originalContent);
});

test('ESLint passes when staged JS file has no linting errors', function () use ($projectRoot, $sandbox) {
    $this->config->set('git-hooks.code_analyzers.eslint', [
        'path' => $sandbox->binaryPath(),
        'config' => $projectRoot.'/tests/Fixtures/.eslintrcFixture.js',
        'file_extensions' => '/\.(js|jsx|ts|tsx)$/',
        'run_in_docker' => false,
        'docker_container' => '',
        'additional_params' => '',
    ]);
    $this->config->set('git-hooks.pre-commit', [ESLintPreCommitHook::class]);

    $originalContent = file_get_contents($projectRoot.'/tests/Fixtures/ClassWithoutFixableIssues.php');
    $filePath = $this->makeTempFile('ClassWithoutFixableIssues.php', $originalContent);

    GitHooks::shouldReceive('isMergeInProgress')->andReturn(false);
    GitHooks::shouldReceive('getListOfChangedFiles')->andReturn('AM temp/ClassWithoutFixableIssues.php');

    $this->artisan('git-hooks:pre-commit')
        ->doesntExpectOutputToContain('ESLint Failed')
        ->assertSuccessful();

    expect(file_get_contents($filePath))->toBe($originalContent);
});

test('ESLint auto-fixes staged JS file when automatically_fix_errors is enabled', function () use ($projectRoot, $sandbox) {
    $this->config->set('git-hooks.code_analyzers.eslint', [
        'path' => $sandbox->binaryPath(),
        'config' => $projectRoot.'/tests/Fixtures/.eslintrcFixture.js',
        'file_extensions' => '/\.(js|jsx|ts|tsx)$/',
        'run_in_docker' => false,
        'docker_container' => '',
        'additional_params' => '',
    ]);
    $this->config->set('git-hooks.pre-commit', [ESLintPreCommitHook::class]);
    $this->config->set('git-hooks.automatically_fix_errors', true);

    $originalContent = file_get_contents($projectRoot.'/tests/Fixtures/fixable-js-file.js');
    $filePath = $this->makeTempFile('fixable-js-file.js', $originalContent);

    GitHooks::shouldReceive('isMergeInProgress')->andReturn(false);
    GitHooks::shouldReceive('getListOfChangedFiles')->andReturn('AM temp/fixable-js-file.js');

    $this->artisan('git-hooks:pre-commit')
        ->expectsOutputToContain('ESLint Failed')
        ->expectsOutputToContain('COMMIT FAILED')
        ->assertExitCode(0);

    expect(file_get_contents($filePath))->not->toBe($originalContent);
});

test('ESLint skips non-JS files staged alongside JS file with errors', function () use ($projectRoot, $sandbox) {
    $this->config->set('git-hooks.code_analyzers.eslint', [
        'path' => $sandbox->binaryPath(),
        'config' => $projectRoot.'/tests/Fixtures/.eslintrcFixture.js',
        'file_extensions' => '/\.(js|jsx|ts|tsx)$/',
        'run_in_docker' => false,
        'docker_container' => '',
        'additional_params' => '',
    ]);
    $this->config->set('git-hooks.pre-commit', [ESLintPreCommitHook::class]);

    $jsOriginal = file_get_contents($projectRoot.'/tests/Fixtures/fixable-js-file.js');
    $jsPath = $this->makeTempFile('fixable-js-file.js', $jsOriginal);

    $phpOriginal = file_get_contents($projectRoot.'/tests/Fixtures/ClassWithFixableIssues.php');
    $phpPath = $this->makeTempFile('ClassWithFixableIssues.php', $phpOriginal);

    GitHooks::shouldReceive('isMergeInProgress')->andReturn(false);
    GitHooks::shouldReceive('getListOfChangedFiles')
        ->andReturn("AM temp/fixable-js-file.js\nAM temp/ClassWithFixableIssues.php");

    $this->artisan('git-hooks:pre-commit')
        ->expectsOutputToContain('ESLint Failed')
        ->doesntExpectOutputToContain('ClassWithFixableIssues.php')
        ->expectsConfirmation('Would you like to attempt to correct files automagically?', 'no')
        ->assertExitCode(1);

    expect(file_get_contents($jsPath))->toBe($jsOriginal);
    expect(file_get_contents($phpPath))->toBe($phpOriginal);
});

test('ESLint processes multiple JS files in a single run', function () use ($projectRoot, $sandbox) {
    $this->config->set('git-hooks.code_analyzers.eslint', [
        'path' => $sandbox->binaryPath(),
        'config' => $projectRoot.'/tests/Fixtures/.eslintrcFixture.js',
        'file_extensions' => '/\.(js|jsx|ts|tsx)$/',
        'run_in_docker' => false,
        'docker_container' => '',
        'additional_params' => '',
    ]);
    $this->config->set('git-hooks.pre-commit', [ESLintPreCommitHook::class]);

    $fixableOriginal = file_get_contents($projectRoot.'/tests/Fixtures/fixable-js-file.js');
    $fixablePath = $this->makeTempFile('fixable-js-file.js', $fixableOriginal);

    $cleanOriginal = file_get_contents($projectRoot.'/tests/Fixtures/clean-js-file.js');
    $cleanPath = $this->makeTempFile('clean-js-file.js', $cleanOriginal);

    GitHooks::shouldReceive('isMergeInProgress')->andReturn(false);
    GitHooks::shouldReceive('getListOfChangedFiles')
        ->andReturn("AM temp/fixable-js-file.js\nAM temp/clean-js-file.js");

    $this->artisan('git-hooks:pre-commit')
        ->expectsOutputToContain('ESLint Failed')
        ->expectsOutputToContain('COMMIT FAILED')
        ->expectsConfirmation('Would you like to attempt to correct files automagically?', 'no')
        ->assertExitCode(1);

    expect(file_get_contents($fixablePath))->toBe($fixableOriginal);
    expect(file_get_contents($cleanPath))->toBe($cleanOriginal);
});
