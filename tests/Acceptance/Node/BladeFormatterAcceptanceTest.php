<?php

declare(strict_types=1);

use Igorsgm\GitHooks\Console\Commands\Hooks\BladeFormatterPreCommitHook;
use Igorsgm\GitHooks\Facades\GitHooks;
use Igorsgm\GitHooks\Tests\Acceptance\ToolSandbox;

$projectRoot = dirname(__DIR__, 3);
$sandbox = ToolSandbox::js('blade-formatter', 'blade-formatter', 'blade-formatter');

beforeEach(function () use ($sandbox) {
    try {
        $sandbox->install();
    } catch (Throwable $e) {
        $this->markTestSkipped('Blade Formatter sandbox setup failed: '.$e->getMessage());
    }

    $this->gitInit();
    $this->initializeTempDirectory(base_path('temp'));
});

test('Blade Formatter fails when staged blade file is not formatted', function () use ($projectRoot, $sandbox) {
    $this->config->set('git-hooks.code_analyzers.blade_formatter', [
        'path' => $sandbox->binaryPath(),
        'config' => '',
        'file_extensions' => '/\.blade\.php$/',
        'run_in_docker' => false,
        'docker_container' => '',
    ]);
    $this->config->set('git-hooks.pre-commit', [BladeFormatterPreCommitHook::class]);

    $originalContent = file_get_contents($projectRoot.'/tests/Fixtures/fixable-blade-file.blade.php');
    $filePath = $this->makeTempFile('fixable-blade-file.blade.php', $originalContent);

    GitHooks::shouldReceive('isMergeInProgress')->andReturn(false);
    GitHooks::shouldReceive('getListOfChangedFiles')->andReturn('AM temp/fixable-blade-file.blade.php');

    $this->artisan('git-hooks:pre-commit')
        ->expectsOutputToContain('Blade Formatter Failed')
        ->expectsOutputToContain('COMMIT FAILED')
        ->expectsConfirmation('Would you like to attempt to correct files automagically?', 'no')
        ->assertExitCode(1);

    expect(file_get_contents($filePath))->toBe($originalContent);
});

test('Blade Formatter passes when no blade files are staged', function () use ($projectRoot, $sandbox) {
    $this->config->set('git-hooks.code_analyzers.blade_formatter', [
        'path' => $sandbox->binaryPath(),
        'config' => '',
        'file_extensions' => '/\.blade\.php$/',
        'run_in_docker' => false,
        'docker_container' => '',
    ]);
    $this->config->set('git-hooks.pre-commit', [BladeFormatterPreCommitHook::class]);

    $originalContent = file_get_contents($projectRoot.'/tests/Fixtures/ClassWithFixableIssues.php');
    $filePath = $this->makeTempFile('ClassWithFixableIssues.php', $originalContent);

    GitHooks::shouldReceive('isMergeInProgress')->andReturn(false);
    GitHooks::shouldReceive('getListOfChangedFiles')->andReturn('AM temp/ClassWithFixableIssues.php');

    $this->artisan('git-hooks:pre-commit')
        ->doesntExpectOutputToContain('Blade Formatter Failed')
        ->assertSuccessful();

    expect(file_get_contents($filePath))->toBe($originalContent);
});

test('Blade Formatter auto-fixes staged blade file when automatically_fix_errors is enabled', function () use ($projectRoot, $sandbox) {
    $this->config->set('git-hooks.code_analyzers.blade_formatter', [
        'path' => $sandbox->binaryPath(),
        'config' => '',
        'file_extensions' => '/\.blade\.php$/',
        'run_in_docker' => false,
        'docker_container' => '',
    ]);
    $this->config->set('git-hooks.pre-commit', [BladeFormatterPreCommitHook::class]);
    $this->config->set('git-hooks.automatically_fix_errors', true);

    $originalContent = file_get_contents($projectRoot.'/tests/Fixtures/fixable-blade-file.blade.php');
    $filePath = $this->makeTempFile('fixable-blade-file.blade.php', $originalContent);

    GitHooks::shouldReceive('isMergeInProgress')->andReturn(false);
    GitHooks::shouldReceive('getListOfChangedFiles')->andReturn('AM temp/fixable-blade-file.blade.php');

    $this->artisan('git-hooks:pre-commit')
        ->expectsOutputToContain('Blade Formatter Failed')
        ->expectsOutputToContain('COMMIT FAILED')
        ->assertExitCode(0);

    expect(file_get_contents($filePath))->not->toBe($originalContent);
});

test('Blade Formatter skips non-blade files staged alongside blade file with issues', function () use ($projectRoot, $sandbox) {
    $this->config->set('git-hooks.code_analyzers.blade_formatter', [
        'path' => $sandbox->binaryPath(),
        'config' => '',
        'file_extensions' => '/\.blade\.php$/',
        'run_in_docker' => false,
        'docker_container' => '',
    ]);
    $this->config->set('git-hooks.pre-commit', [BladeFormatterPreCommitHook::class]);

    $bladeOriginal = file_get_contents($projectRoot.'/tests/Fixtures/fixable-blade-file.blade.php');
    $bladePath = $this->makeTempFile('fixable-blade-file.blade.php', $bladeOriginal);

    $phpOriginal = file_get_contents($projectRoot.'/tests/Fixtures/ClassWithFixableIssues.php');
    $phpPath = $this->makeTempFile('ClassWithFixableIssues.php', $phpOriginal);

    GitHooks::shouldReceive('isMergeInProgress')->andReturn(false);
    GitHooks::shouldReceive('getListOfChangedFiles')
        ->andReturn("AM temp/fixable-blade-file.blade.php\nAM temp/ClassWithFixableIssues.php");

    $this->artisan('git-hooks:pre-commit')
        ->expectsOutputToContain('Blade Formatter Failed')
        ->doesntExpectOutputToContain('ClassWithFixableIssues.php')
        ->expectsConfirmation('Would you like to attempt to correct files automagically?', 'no')
        ->assertExitCode(1);

    expect(file_get_contents($bladePath))->toBe($bladeOriginal);
    expect(file_get_contents($phpPath))->toBe($phpOriginal);
});
