<?php

use Igorsgm\GitHooks\Facades\GitHooks;
use Igorsgm\GitHooks\Git\ChangedFiles;
use Igorsgm\GitHooks\Tests\Fixtures\ConcreteBaseCodeAnalyzerFixture;
use Igorsgm\GitHooks\Traits\GitHelper;

uses(GitHelper::class);
beforeEach(function () {
    $this->gitInit();
    $this->initializeTempDirectory(base_path('temp'));
});

test('Skips check if there are no files added to commit', function () {
    $changedFiles = mock(ChangedFiles::class)
        ->shouldReceive('getAddedToCommit')
        ->andReturn(collect())
        ->getMock();

    $next = function ($files) {
        return 'passed';
    };

    $hook = new ConcreteBaseCodeAnalyzerFixture();
    $result = $hook->handleCommittedFiles($changedFiles, $next);
    expect($result)->toBe('passed');
});

test('Skips check during a Merge process', function ($modifiedFilesList) {
    $changedFiles = new ChangedFiles($modifiedFilesList);
    GitHooks::shouldReceive('isMergeInProgress')->andReturn(true);

    $next = function ($files) {
        return 'passed';
    };

    $hook = new ConcreteBaseCodeAnalyzerFixture();
    $result = $hook->handleCommittedFiles($changedFiles, $next);
    expect($result)->toBe('passed');
})->with('modifiedFilesList');
