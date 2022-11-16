<?php

use Igorsgm\GitHooks\Git\ChangedFiles;
use Igorsgm\GitHooks\Git\CommitMessage;

test('Gets commit message', function () {
    $messageText = 'Test message';
    $commitMessage = new CommitMessage($messageText, new ChangedFiles(''));

    expect($commitMessage->getMessage())->toBe($messageText);
});

test('Sets commit message', function () {
    $messageText = 'Test message';
    $commitMessage = new CommitMessage($messageText, new ChangedFiles(''));

    $newMessageText = 'New message';
    $commitMessage->setMessage($newMessageText);

    expect($commitMessage->getMessage())->toBe($newMessageText);
});

test('Gets files as ChangedFiles::class', function () {
    $commitMessage = new CommitMessage('Test message', new ChangedFiles(''));

    expect($commitMessage->getFiles())->toBeInstanceOf(ChangedFiles::class);
});
