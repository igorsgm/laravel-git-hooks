<?php

declare(strict_types=1);

use Igorsgm\GitHooks\Console\Commands\Hooks\PhpInsightsPreCommitHook;
use Igorsgm\GitHooks\Facades\GitHooks;
use Igorsgm\GitHooks\Tests\Acceptance\ToolSandbox;

$projectRoot = dirname(__DIR__, 3);
$sandbox = ToolSandbox::php('phpinsights', 'nunomaduro/phpinsights', 'phpinsights');

beforeEach(function () use ($sandbox) {
    try {
        $sandbox->install();
    } catch (Throwable $e) {
        $this->markTestSkipped('PHP Insights sandbox setup failed: '.$e->getMessage());
    }

    $this->gitInit();
    $this->initializeTempDirectory(base_path('temp'));
});

test('PHP Insights fails when staged PHP file has quality issues', function () use ($projectRoot, $sandbox) {
    $this->config->set('git-hooks.code_analyzers.phpinsights', [
        'path' => $sandbox->binaryPath(),
        'config' => $projectRoot.'/tests/Fixtures/phpinsightsFixture.php',
        'file_extensions' => '/\.php$/',
        'run_in_docker' => false,
        'docker_container' => '',
        'additional_params' => '--disable-security-check',
    ]);
    $this->config->set('git-hooks.pre-commit', [PhpInsightsPreCommitHook::class]);

    $originalContent = file_get_contents($projectRoot.'/tests/Fixtures/ClassWithFixableIssues.php');
    $filePath = $this->makeTempFile('ClassWithFixableIssues.php', $originalContent);

    GitHooks::shouldReceive('isMergeInProgress')->andReturn(false);
    GitHooks::shouldReceive('getListOfChangedFiles')->andReturn('AM temp/ClassWithFixableIssues.php');

    $this->artisan('git-hooks:pre-commit')
        ->expectsOutputToContain('PhpInsights Failed')
        ->expectsOutputToContain('COMMIT FAILED')
        ->expectsConfirmation('Would you like to attempt to correct files automagically?', 'no')
        ->assertExitCode(1);

    expect(file_get_contents($filePath))->toBe($originalContent);
});

test('PHP Insights passes when no PHP files are staged', function () use ($projectRoot, $sandbox) {
    $this->config->set('git-hooks.code_analyzers.phpinsights', [
        'path' => $sandbox->binaryPath(),
        'config' => $projectRoot.'/tests/Fixtures/phpinsightsFixture.php',
        'file_extensions' => '/\.php$/',
        'run_in_docker' => false,
        'docker_container' => '',
        'additional_params' => '--disable-security-check',
    ]);
    $this->config->set('git-hooks.pre-commit', [PhpInsightsPreCommitHook::class]);

    $originalContent = file_get_contents($projectRoot.'/tests/Fixtures/sample.js');
    $filePath = $this->makeTempFile('sample.js', $originalContent);

    GitHooks::shouldReceive('isMergeInProgress')->andReturn(false);
    GitHooks::shouldReceive('getListOfChangedFiles')->andReturn('AM temp/sample.js');

    $this->artisan('git-hooks:pre-commit')
        ->doesntExpectOutputToContain('PhpInsights Failed')
        ->assertSuccessful();

    expect(file_get_contents($filePath))->toBe($originalContent);
});

test('PHP Insights skips non-PHP files staged alongside PHP files with quality issues', function () use ($projectRoot, $sandbox) {
    $this->config->set('git-hooks.code_analyzers.phpinsights', [
        'path' => $sandbox->binaryPath(),
        'config' => $projectRoot.'/tests/Fixtures/phpinsightsFixture.php',
        'file_extensions' => '/\.php$/',
        'run_in_docker' => false,
        'docker_container' => '',
        'additional_params' => '--disable-security-check',
    ]);
    $this->config->set('git-hooks.pre-commit', [PhpInsightsPreCommitHook::class]);

    $phpOriginal = file_get_contents($projectRoot.'/tests/Fixtures/ClassWithFixableIssues.php');
    $phpPath = $this->makeTempFile('ClassWithFixableIssues.php', $phpOriginal);

    $jsOriginal = file_get_contents($projectRoot.'/tests/Fixtures/sample.js');
    $jsPath = $this->makeTempFile('sample.js', $jsOriginal);

    GitHooks::shouldReceive('isMergeInProgress')->andReturn(false);
    GitHooks::shouldReceive('getListOfChangedFiles')
        ->andReturn("AM temp/ClassWithFixableIssues.php\nAM temp/sample.js");

    $this->artisan('git-hooks:pre-commit')
        ->expectsOutputToContain('PhpInsights Failed')
        ->expectsOutputToContain('COMMIT FAILED')
        ->expectsConfirmation('Would you like to attempt to correct files automagically?', 'no')
        ->assertExitCode(1);

    expect(file_get_contents($phpPath))->toBe($phpOriginal);
    expect(file_get_contents($jsPath))->toBe($jsOriginal);
});
