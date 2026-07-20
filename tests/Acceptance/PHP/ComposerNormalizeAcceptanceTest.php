<?php

declare(strict_types=1);

use Igorsgm\GitHooks\Console\Commands\Hooks\ComposerNormalizePreCommitHook;
use Igorsgm\GitHooks\Facades\GitHooks;

$projectRoot = dirname(__DIR__, 3);
$validateJsonBin = $projectRoot.'/vendor/bin/validate-json';

beforeEach(function () {
    $this->gitInit();
    $this->initializeTempDirectory(base_path('temp'));
});

test('Composer Normalize hook skips gracefully when no composer.json is staged', function () use ($validateJsonBin) {
    $this->config->set('git-hooks.code_analyzers.composer_normalize', [
        'path' => $validateJsonBin,
        'run_in_docker' => false,
        'docker_container' => '',
    ]);
    $this->config->set('git-hooks.pre-commit', [ComposerNormalizePreCommitHook::class]);

    $originalContent = '<?php class NoTestFile {}';
    $filePath = $this->makeTempFile('NoTestFile.php', $originalContent);

    GitHooks::shouldReceive('isMergeInProgress')->andReturn(false);
    GitHooks::shouldReceive('getListOfChangedFiles')->andReturn('AM temp/NoTestFile.php');

    $this->artisan('git-hooks:pre-commit')->assertSuccessful();

    expect(file_get_contents($filePath))->toBe($originalContent);
})->skip(!file_exists($validateJsonBin), 'validate-json binary not found');

test('Composer Normalize hook skips gracefully when only JS files are staged', function () use ($validateJsonBin) {
    $this->config->set('git-hooks.code_analyzers.composer_normalize', [
        'path' => $validateJsonBin,
        'run_in_docker' => false,
        'docker_container' => '',
    ]);
    $this->config->set('git-hooks.pre-commit', [ComposerNormalizePreCommitHook::class]);

    $originalContent = "const foo = 'bar';\n";
    $filePath = $this->makeTempFile('sample.js', $originalContent);

    GitHooks::shouldReceive('isMergeInProgress')->andReturn(false);
    GitHooks::shouldReceive('getListOfChangedFiles')->andReturn('AM temp/sample.js');

    $this->artisan('git-hooks:pre-commit')
        ->doesntExpectOutputToContain('Composer Normalize Failed')
        ->assertSuccessful();

    expect(file_get_contents($filePath))->toBe($originalContent);
})->skip(!file_exists($validateJsonBin), 'validate-json binary not found');
