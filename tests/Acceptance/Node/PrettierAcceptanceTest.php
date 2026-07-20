<?php

declare(strict_types=1);

use Igorsgm\GitHooks\Console\Commands\Hooks\PrettierPreCommitHook;
use Igorsgm\GitHooks\Facades\GitHooks;
use Igorsgm\GitHooks\Tests\Acceptance\ToolSandbox;

$projectRoot = dirname(__DIR__, 3);
$sandbox = ToolSandbox::js('prettier', 'prettier', 'prettier');

beforeEach(function () use ($sandbox) {
    try {
        $sandbox->install();
    } catch (Throwable $e) {
        $this->markTestSkipped('Prettier sandbox setup failed: '.$e->getMessage());
    }

    $this->gitInit();
    $this->initializeTempDirectory(base_path('temp'));
});

test('Prettier fails when staged JS file is not formatted', function () use ($projectRoot, $sandbox) {
    $this->config->set('git-hooks.code_analyzers.prettier', [
        'path' => $sandbox->binaryPath(),
        'config' => $projectRoot.'/tests/Fixtures/.prettierrcFixture.json',
        'file_extensions' => '/\.(js|jsx|ts|tsx|css|scss|json|vue)$/',
        'run_in_docker' => false,
        'docker_container' => '',
        'additional_params' => '',
    ]);
    $this->config->set('git-hooks.pre-commit', [PrettierPreCommitHook::class]);

    $originalContent = file_get_contents($projectRoot.'/tests/Fixtures/fixable-js-file.js');
    $filePath = $this->makeTempFile('fixable-js-file.js', $originalContent);

    GitHooks::shouldReceive('isMergeInProgress')->andReturn(false);
    GitHooks::shouldReceive('getListOfChangedFiles')->andReturn('AM temp/fixable-js-file.js');

    $this->artisan('git-hooks:pre-commit')
        ->expectsOutputToContain('Prettier Failed')
        ->expectsOutputToContain('COMMIT FAILED')
        ->expectsConfirmation('Would you like to attempt to correct files automagically?', 'no')
        ->assertExitCode(1);

    expect(file_get_contents($filePath))->toBe($originalContent);
});

test('Prettier passes when staged JS file is already formatted', function () use ($projectRoot, $sandbox) {
    $this->config->set('git-hooks.code_analyzers.prettier', [
        'path' => $sandbox->binaryPath(),
        'config' => $projectRoot.'/tests/Fixtures/.prettierrcFixture.json',
        'file_extensions' => '/\.(js|jsx|ts|tsx|css|scss|json|vue)$/',
        'run_in_docker' => false,
        'docker_container' => '',
        'additional_params' => '',
    ]);
    $this->config->set('git-hooks.pre-commit', [PrettierPreCommitHook::class]);

    $originalContent = file_get_contents($projectRoot.'/tests/Fixtures/ClassWithoutFixableIssues.php');
    $filePath = $this->makeTempFile('ClassWithoutFixableIssues.php', $originalContent);

    GitHooks::shouldReceive('isMergeInProgress')->andReturn(false);
    GitHooks::shouldReceive('getListOfChangedFiles')->andReturn('AM temp/ClassWithoutFixableIssues.php');

    $this->artisan('git-hooks:pre-commit')
        ->doesntExpectOutputToContain('Prettier Failed')
        ->assertSuccessful();

    expect(file_get_contents($filePath))->toBe($originalContent);
});

test('Prettier auto-fixes staged JS file when automatically_fix_errors is enabled', function () use ($projectRoot, $sandbox) {
    $this->config->set('git-hooks.code_analyzers.prettier', [
        'path' => $sandbox->binaryPath(),
        'config' => $projectRoot.'/tests/Fixtures/.prettierrcFixture.json',
        'file_extensions' => '/\.(js|jsx|ts|tsx|css|scss|json|vue)$/',
        'run_in_docker' => false,
        'docker_container' => '',
        'additional_params' => '',
    ]);
    $this->config->set('git-hooks.pre-commit', [PrettierPreCommitHook::class]);
    $this->config->set('git-hooks.automatically_fix_errors', true);

    $originalContent = file_get_contents($projectRoot.'/tests/Fixtures/fixable-js-file.js');
    $filePath = $this->makeTempFile('fixable-js-file.js', $originalContent);

    GitHooks::shouldReceive('isMergeInProgress')->andReturn(false);
    GitHooks::shouldReceive('getListOfChangedFiles')->andReturn('AM temp/fixable-js-file.js');

    $this->artisan('git-hooks:pre-commit')
        ->expectsOutputToContain('Prettier Failed')
        ->expectsOutputToContain('COMMIT FAILED')
        ->assertExitCode(0);

    expect(file_get_contents($filePath))->not->toBe($originalContent);
});

test('Prettier skips non-JS files staged alongside JS file with errors', function () use ($projectRoot, $sandbox) {
    $this->config->set('git-hooks.code_analyzers.prettier', [
        'path' => $sandbox->binaryPath(),
        'config' => $projectRoot.'/tests/Fixtures/.prettierrcFixture.json',
        'file_extensions' => '/\.(js|jsx|ts|tsx|css|scss|json|vue)$/',
        'run_in_docker' => false,
        'docker_container' => '',
        'additional_params' => '',
    ]);
    $this->config->set('git-hooks.pre-commit', [PrettierPreCommitHook::class]);

    $jsOriginal = file_get_contents($projectRoot.'/tests/Fixtures/fixable-js-file.js');
    $jsPath = $this->makeTempFile('fixable-js-file.js', $jsOriginal);

    $phpOriginal = file_get_contents($projectRoot.'/tests/Fixtures/ClassWithFixableIssues.php');
    $phpPath = $this->makeTempFile('ClassWithFixableIssues.php', $phpOriginal);

    GitHooks::shouldReceive('isMergeInProgress')->andReturn(false);
    GitHooks::shouldReceive('getListOfChangedFiles')
        ->andReturn("AM temp/fixable-js-file.js\nAM temp/ClassWithFixableIssues.php");

    $this->artisan('git-hooks:pre-commit')
        ->expectsOutputToContain('Prettier Failed')
        ->doesntExpectOutputToContain('ClassWithFixableIssues.php')
        ->expectsConfirmation('Would you like to attempt to correct files automagically?', 'no')
        ->assertExitCode(1);

    expect(file_get_contents($jsPath))->toBe($jsOriginal);
    expect(file_get_contents($phpPath))->toBe($phpOriginal);
});
