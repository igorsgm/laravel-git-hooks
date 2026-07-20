<?php

declare(strict_types=1);

use Igorsgm\GitHooks\Console\Commands\Hooks\DeptracPreCommitHook;
use Igorsgm\GitHooks\Facades\GitHooks;
use Igorsgm\GitHooks\Tests\Acceptance\ToolSandbox;

$projectRoot = dirname(__DIR__, 3);
$sandbox = ToolSandbox::php('deptrac', 'qossmic/deptrac', 'deptrac');

beforeEach(function () use ($sandbox) {
    try {
        $sandbox->install();
    } catch (Throwable $e) {
        $this->markTestSkipped('Deptrac sandbox setup failed: '.$e->getMessage());
    }

    $this->gitInit();
    $this->initializeTempDirectory(base_path('temp'));
});

test('Deptrac hook skips gracefully when no architecture config is provided', function () use ($projectRoot, $sandbox) {
    $this->config->set('git-hooks.code_analyzers.deptrac', [
        'path' => $sandbox->binaryPath(),
        'config' => '',
        'file_extensions' => '/\.php$/',
        'run_in_docker' => false,
        'docker_container' => '',
        'additional_params' => '',
    ]);
    $this->config->set('git-hooks.pre-commit', [DeptracPreCommitHook::class]);

    $this->makeTempFile('sample.js', file_get_contents($projectRoot.'/tests/Fixtures/sample.js'));

    GitHooks::shouldReceive('isMergeInProgress')->andReturn(false);
    GitHooks::shouldReceive('getListOfChangedFiles')->andReturn('AM temp/sample.js');

    // No PHP files staged → deptrac skips and hook passes
    $this->artisan('git-hooks:pre-commit')->assertSuccessful();
});
