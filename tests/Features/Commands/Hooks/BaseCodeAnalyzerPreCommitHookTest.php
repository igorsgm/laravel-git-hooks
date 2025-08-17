<?php

use Igorsgm\GitHooks\Facades\GitHooks;
use Igorsgm\GitHooks\Git\ChangedFiles;
use Igorsgm\GitHooks\Tests\Fixtures\ConcreteBaseCodeAnalyzerFixture;

beforeEach(
    function () {
        $this->gitInit();
        $this->initializeTempDirectory(base_path('temp'));
    }
);

test(
    'Skips check if there are no staged files in commit', function () {
        $changedFiles = Mockery::mock(ChangedFiles::class)
            ->shouldReceive('getStaged')
            ->andReturn(collect())
            ->getMock();

        $next = fn ($files) => 'passed';

        $hook = new ConcreteBaseCodeAnalyzerFixture;
        $result = $hook->handleCommittedFiles($changedFiles, $next);
        expect($result)->toBe('passed');
    }
);

test(
    'Skips check during a Merge process', function ($modifiedFilesList) {
        $changedFiles = new ChangedFiles($modifiedFilesList);
        GitHooks::shouldReceive('isMergeInProgress')->andReturn(true);

        $next = fn ($files) => 'passed';

        $hook = new ConcreteBaseCodeAnalyzerFixture;
        $result = $hook->handleCommittedFiles($changedFiles, $next);
        expect($result)->toBe('passed');
    }
)->with('modifiedFilesList');

test(
    'Throws HookFailException and notifies when Code Analyzer is not installed',
    function ($configName, $nonExistentPathConfig, $preCommitHookClass, $listOfFixablePhpFiles) {
        $this->config->set('git-hooks.code_analyzers.'.$configName, $nonExistentPathConfig);

        $this->config->set(
            'git-hooks.pre-commit', [
                $preCommitHookClass,
            ]
        );

        GitHooks::shouldReceive('isMergeInProgress')->andReturn(false);
        GitHooks::shouldReceive('getListOfChangedFiles')->andReturn($listOfFixablePhpFiles);

        $preCommitHook = new $preCommitHookClass;
        $this->artisan('git-hooks:pre-commit')
            ->expectsOutputToContain($preCommitHook->getName().' is not installed.')
            ->assertExitCode(1);
    }
)->with('codeAnalyzersList', 'listOfFixablePhpFiles');

test(
    'Throws HookFailException and notifies when config path does not exist',
    function ($configName, $nonExistentPathConfig, $preCommitHookClass, $listOfFixablePhpFiles) {
        $nonExistentPathConfig['config'] = 'nonexistent/path';
        $this->config->set('git-hooks.code_analyzers.'.$configName, $nonExistentPathConfig);

        $this->config->set(
            'git-hooks.pre-commit', [
                $preCommitHookClass,
            ]
        );

        GitHooks::shouldReceive('isMergeInProgress')->andReturn(false);
        GitHooks::shouldReceive('getListOfChangedFiles')->andReturn($listOfFixablePhpFiles);

        $preCommitHook = new $preCommitHookClass;
        $this->artisan('git-hooks:pre-commit')
            ->expectsOutputToContain($preCommitHook->getName().' config file does not exist.')
            ->assertExitCode(1);
    }
)->with('codeAnalyzersList', 'listOfFixablePhpFiles');
