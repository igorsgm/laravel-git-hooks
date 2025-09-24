<?php

declare(strict_types=1);

use Igorsgm\GitHooks\Traits\GitHelper;
use Symfony\Component\Process\Exception\ProcessFailedException;

uses(GitHelper::class);
beforeEach(function () {
    $this->initializeGitAsTempDirectory();
});

test('Gets list of changed files', function () {
    chdir(__DIR__);

    expect($this->getListOfChangedFiles())->toBe(
        shell_exec('git status --short') ?? ''
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

test('isMergeInProgress returns true when a merge is in progress', function () {
    $testFileName = 'test.txt';

    $this->makeTempFile('../'.$testFileName, 'Test merge');

    $gitAddCommand = sprintf('git add %s', $testFileName);

    // Generating a Fake Merge process
    $commandsToGenerateFakeMerge = [
        'git checkout -b main',
        $gitAddCommand,
        'git commit -m "Add test file"',
        'git push --set-upstream main main',
        'git checkout -b test-branch',
        'git checkout main',
        "echo 'Test merge (edit on main)' > $testFileName",
        $gitAddCommand,
        'git commit -m "edited on main"',
        'git push main',
        'git checkout test-branch',
        "echo 'Test merge (edit on test-branch)' > $testFileName",
        $gitAddCommand,
        'git commit -m "edited on test-branch"',
        'git push test-branch',
        'git checkout main',
        'git merge test-branch',
    ];

    chdir(base_path());

    $noOutputSuffix = ' > '.(PHP_OS_FAMILY === 'Windows' ? 'NUL' : '/dev/null 2>&1');
    foreach ($commandsToGenerateFakeMerge as $command) {
        if (str_starts_with($command, 'git')) {
            $command .= $noOutputSuffix;
        }
        shell_exec($command);
    }

    expect($this->isMergeInProgress())->toBeTrue();
});

test('isMergeInProgress returns false when a merge is not in progress', function () {
    chdir(base_path());
    shell_exec('git merge --abort');
    expect($this->isMergeInProgress())->toBeFalse();
});
