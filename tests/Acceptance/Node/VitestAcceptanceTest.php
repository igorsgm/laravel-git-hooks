<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;
use Igorsgm\GitHooks\Console\Commands\Hooks\VitestPreCommitHook;
use Igorsgm\GitHooks\Facades\GitHooks;
use Igorsgm\GitHooks\Tests\Acceptance\ToolSandbox;

$projectRoot = dirname(__DIR__, 3);
$sandbox = ToolSandbox::js('vitest', 'vitest', 'vitest');

beforeEach(function () use ($sandbox) {
    try {
        $sandbox->install();
    } catch (Throwable $e) {
        $this->markTestSkipped('Vitest sandbox setup failed: '.$e->getMessage());
    }

    $this->gitInit();
    $this->initializeTempDirectory(base_path('temp'));
    File::deleteDirectory(base_path('tests'));
});

test('Vitest hook skips gracefully when no test files found for staged JS file', function () use ($projectRoot, $sandbox) {
    $this->config->set('git-hooks.code_analyzers.vitest', [
        'path' => $sandbox->binaryPath(),
        'run_in_docker' => false,
        'docker_container' => '',
        'additional_params' => '',
    ]);
    $this->config->set('git-hooks.pre-commit', [VitestPreCommitHook::class]);

    $this->makeTempFile('sample.js', file_get_contents($projectRoot.'/tests/Fixtures/sample.js'));

    GitHooks::shouldReceive('isMergeInProgress')->andReturn(false);
    GitHooks::shouldReceive('getListOfChangedFiles')->andReturn('AM temp/sample.js');

    // No *.test.js counterpart exists → hook finds no test files and calls $next
    $this->artisan('git-hooks:pre-commit')->assertSuccessful();
});

test('Vitest runner passes when found test file has passing tests', function () use ($projectRoot, $sandbox) {
    $vitestConfig = $projectRoot.'/tests/Fixtures/vitestFixture.config.mjs';

    $this->config->set('git-hooks.code_analyzers.vitest', [
        'path' => $sandbox->binaryPath(),
        'run_in_docker' => false,
        'docker_container' => '',
        'additional_params' => '--config='.$vitestConfig,
    ]);
    $this->config->set('git-hooks.pre-commit', [VitestPreCommitHook::class]);

    $this->makeTempFile('someModule.js', "export const add = (a, b) => a + b;\n");

    File::makeDirectory(base_path('tests'), 0755, true, true);
    file_put_contents(
        base_path('tests/someModule.test.js'),
        "test('someModule adds correctly', () => { expect(1 + 1).toBe(2); });\n"
    );

    GitHooks::shouldReceive('isMergeInProgress')->andReturn(false);
    GitHooks::shouldReceive('getListOfChangedFiles')->andReturn('AM temp/someModule.js');

    $this->artisan('git-hooks:pre-commit')
        ->doesntExpectOutputToContain('Vitest Failed')
        ->assertSuccessful();

    File::deleteDirectory(base_path('tests'));
});

test('Vitest runner fails when found test file has failing tests', function () use ($projectRoot, $sandbox) {
    $vitestConfig = $projectRoot.'/tests/Fixtures/vitestFixture.config.mjs';

    $this->config->set('git-hooks.code_analyzers.vitest', [
        'path' => $sandbox->binaryPath(),
        'run_in_docker' => false,
        'docker_container' => '',
        'additional_params' => '--config='.$vitestConfig,
    ]);
    $this->config->set('git-hooks.pre-commit', [VitestPreCommitHook::class]);

    $this->makeTempFile('someModule.js', "export const add = (a, b) => a + b;\n");

    File::makeDirectory(base_path('tests'), 0755, true, true);
    file_put_contents(
        base_path('tests/someModule.test.js'),
        "test('someModule is broken', () => { expect(1 + 1).toBe(3); });\n"
    );

    GitHooks::shouldReceive('isMergeInProgress')->andReturn(false);
    GitHooks::shouldReceive('getListOfChangedFiles')->andReturn('AM temp/someModule.js');

    $this->artisan('git-hooks:pre-commit')
        ->expectsConfirmation('Would you like to attempt to correct files automagically?', 'no')
        ->expectsOutputToContain('Vitest Failed')
        ->expectsOutputToContain('COMMIT FAILED')
        ->assertExitCode(1);

    File::deleteDirectory(base_path('tests'));
});

