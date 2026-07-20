<?php

declare(strict_types=1);

use Igorsgm\GitHooks\Console\Commands\Hooks\PHPCodeSnifferPreCommitHook;
use Igorsgm\GitHooks\Facades\GitHooks;
use Igorsgm\GitHooks\Tests\Acceptance\ToolSandbox;

$projectRoot = dirname(__DIR__, 3);
$sandbox = ToolSandbox::php('phpcodesniffer', 'squizlabs/php_codesniffer', 'phpcs');

beforeEach(function () use ($sandbox) {
    try {
        $sandbox->install();
    } catch (Throwable $e) {
        $this->markTestSkipped('PHP_CodeSniffer sandbox setup failed: '.$e->getMessage());
    }

    $this->gitInit();
    $this->initializeTempDirectory(base_path('temp'));
});

test('PHP_CodeSniffer fails when staged PHP file violates coding standard', function () use ($projectRoot, $sandbox) {
    $phpcsPath = $sandbox->binaryPath();
    $phpcbfPath = dirname($phpcsPath).DIRECTORY_SEPARATOR.'phpcbf';

    $this->config->set('git-hooks.code_analyzers.php_code_sniffer', [
        'phpcs_path' => $phpcsPath,
        'phpcbf_path' => $phpcbfPath,
        'config' => $projectRoot.'/tests/Fixtures/phpcsFixture.xml',
        'file_extensions' => '/\.php$/',
        'run_in_docker' => false,
        'docker_container' => '',
    ]);
    $this->config->set('git-hooks.pre-commit', [PHPCodeSnifferPreCommitHook::class]);

    $originalContent = file_get_contents($projectRoot.'/tests/Fixtures/ClassWithFixableIssues.php');
    $filePath = $this->makeTempFile('ClassWithFixableIssues.php', $originalContent);

    GitHooks::shouldReceive('isMergeInProgress')->andReturn(false);
    GitHooks::shouldReceive('getListOfChangedFiles')->andReturn('AM temp/ClassWithFixableIssues.php');

    $this->artisan('git-hooks:pre-commit')
        ->expectsOutputToContain('PHP_CodeSniffer Failed')
        ->expectsOutputToContain('COMMIT FAILED')
        ->expectsConfirmation('Would you like to attempt to correct files automagically?', 'no')
        ->assertExitCode(1);

    expect(file_get_contents($filePath))->toBe($originalContent);
});

test('PHP_CodeSniffer passes when no PHP files are staged', function () use ($projectRoot, $sandbox) {
    $phpcsPath = $sandbox->binaryPath();
    $phpcbfPath = dirname($phpcsPath).DIRECTORY_SEPARATOR.'phpcbf';

    $this->config->set('git-hooks.code_analyzers.php_code_sniffer', [
        'phpcs_path' => $phpcsPath,
        'phpcbf_path' => $phpcbfPath,
        'config' => $projectRoot.'/tests/Fixtures/phpcsFixture.xml',
        'file_extensions' => '/\.php$/',
        'run_in_docker' => false,
        'docker_container' => '',
    ]);
    $this->config->set('git-hooks.pre-commit', [PHPCodeSnifferPreCommitHook::class]);

    $originalContent = file_get_contents($projectRoot.'/tests/Fixtures/sample.js');
    $filePath = $this->makeTempFile('sample.js', $originalContent);

    GitHooks::shouldReceive('isMergeInProgress')->andReturn(false);
    GitHooks::shouldReceive('getListOfChangedFiles')->andReturn('AM temp/sample.js');

    $this->artisan('git-hooks:pre-commit')
        ->doesntExpectOutputToContain('PHP_CodeSniffer Failed')
        ->assertSuccessful();

    expect(file_get_contents($filePath))->toBe($originalContent);
});

test('PHP_CodeSniffer skips non-PHP files staged alongside PHP file with issues', function () use ($projectRoot, $sandbox) {
    $phpcsPath = $sandbox->binaryPath();
    $phpcbfPath = dirname($phpcsPath).DIRECTORY_SEPARATOR.'phpcbf';

    $this->config->set('git-hooks.code_analyzers.php_code_sniffer', [
        'phpcs_path' => $phpcsPath,
        'phpcbf_path' => $phpcbfPath,
        'config' => $projectRoot.'/tests/Fixtures/phpcsFixture.xml',
        'file_extensions' => '/\.php$/',
        'run_in_docker' => false,
        'docker_container' => '',
    ]);
    $this->config->set('git-hooks.pre-commit', [PHPCodeSnifferPreCommitHook::class]);

    $phpOriginal = file_get_contents($projectRoot.'/tests/Fixtures/ClassWithFixableIssues.php');
    $phpPath = $this->makeTempFile('ClassWithFixableIssues.php', $phpOriginal);

    $jsOriginal = file_get_contents($projectRoot.'/tests/Fixtures/sample.js');
    $jsPath = $this->makeTempFile('sample.js', $jsOriginal);

    GitHooks::shouldReceive('isMergeInProgress')->andReturn(false);
    GitHooks::shouldReceive('getListOfChangedFiles')
        ->andReturn("AM temp/ClassWithFixableIssues.php\nAM temp/sample.js");

    $this->artisan('git-hooks:pre-commit')
        ->expectsOutputToContain('PHP_CodeSniffer Failed')
        ->doesntExpectOutputToContain('temp/sample.js')
        ->expectsConfirmation('Would you like to attempt to correct files automagically?', 'no')
        ->assertExitCode(1);

    expect(file_get_contents($phpPath))->toBe($phpOriginal);
    expect(file_get_contents($jsPath))->toBe($jsOriginal);
});
