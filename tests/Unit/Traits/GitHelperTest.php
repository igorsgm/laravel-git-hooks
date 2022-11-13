<?php

use Igorsgm\GitHooks\Traits\GitHelper;
use Symfony\Component\Process\Exception\ProcessFailedException;

uses(GitHelper::class);
beforeEach(function () {
    $this->initializeGitAsTempDirectory();
});

test('Gets list of changed files', function () {
    chdir(__DIR__);

    expect($this->getListOfChangedFiles())->toBe(
        shell_exec('git status --short')
    );
});

test('Gets last commit text from git log', function () {
    chdir(__DIR__);

    expect($this->getLastCommitFromLog())->toBe(
        shell_exec('git log -1 HEAD')
    );
});

test('Throws ProcessFailedException on errored git command', function () {
    expect(fn () => $this->runCommandAndGetOutput('git --wrong'))
        ->toThrow(ProcessFailedException::class);
});

test('Gets Commit message content from .git file', function () {
    $gitFileName = 'COMMIT_MESSAGE';
    $messageText = 'test message';

    $this->makeTempFile($gitFileName, $messageText);
    $gitFilePath = $this->getTempFilePath($gitFileName);

    expect($this->getCommitMessageContentFromFile($gitFilePath))
        ->toBe($messageText);
});

test('Updates Commit message content in .git file', function () {
    $gitFileName = 'COMMIT_MESSAGE';
    $messageText = 'test message';

    $this->makeTempFile($gitFileName, $messageText);
    $gitFilePath = $this->getTempFilePath($gitFileName);

    $newMessage = 'new message 1';
    $this->updateCommitMessageContentInFile($gitFilePath, $newMessage);
    expect(file_get_contents($gitFilePath))->toBe($newMessage);
});
