<?php

declare(strict_types=1);

use Igorsgm\GitHooks\Console\Commands\Hooks\LarastanPreCommitHook;
use Igorsgm\GitHooks\Facades\GitHooks;

beforeEach(function () {
    $this->initializeTempDirectory(base_path('temp'));
    $this->config->set('git-hooks.validate_paths', false);
});

test('Fails commit when Larastan is not passing',
    function ($larastanConfiguration) {
        $this->config->set('git-hooks.code_analyzers.larastan', $larastanConfiguration);
        $this->config->set('git-hooks.code_analyzers.larastan.path', $this->fakeFailBin());
        $this->config->set('git-hooks.pre-commit', [LarastanPreCommitHook::class]);

        $this->makeTempFile('ClassWithFixableIssues.php',
            file_get_contents(__DIR__.'/../../../Fixtures/ClassWithFixableIssues.php')
        );

        GitHooks::shouldReceive('isMergeInProgress')->andReturn(false);
        GitHooks::shouldReceive('getListOfChangedFiles')->andReturn('AM temp/ClassWithFixableIssues.php');

        $this->artisan('git-hooks:pre-commit')
            ->expectsOutputToContain('Larastan Failed')
            ->expectsOutputToContain('COMMIT FAILED')
            ->assertExitCode(1);
    })->with('larastanConfiguration');

test('Commit passes when Larastan check is OK', function ($larastanConfiguration) {
    $this->config->set('git-hooks.code_analyzers.larastan', $larastanConfiguration);
    $this->config->set('git-hooks.code_analyzers.larastan.path', $this->fakePassBin());
    $this->config->set('git-hooks.pre-commit', [LarastanPreCommitHook::class]);

    $this->makeTempFile('ClassWithFixableIssues.php',
        file_get_contents(__DIR__.'/../../../Fixtures/ClassWithFixableIssues.php')
    );

    GitHooks::shouldReceive('isMergeInProgress')->andReturn(false);
    GitHooks::shouldReceive('getListOfChangedFiles')->andReturn('AM temp/ClassWithFixableIssues.php');

    $this->artisan('git-hooks:pre-commit')
        ->doesntExpectOutputToContain('Larastan Failed')
        ->assertSuccessful();
})->with('larastanConfiguration');

