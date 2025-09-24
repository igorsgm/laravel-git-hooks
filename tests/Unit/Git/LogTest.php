<?php

declare(strict_types=1);

use Igorsgm\GitHooks\Git\Log;

test('Get Log', function (string $logText) {
    $log = new Log($logText);

    expect($log->getLog())->toBe($logText);
})->with('lastCommitLogText');

test('Parse default Git log from Console', function (string $logText) {
    $log = new Log($logText);

    expect($log->getAuthor())->toBe('Igor Moraes <igor.sgm@gmail.com>');
    expect($log->getDate()->toDateTimeString())->toBe('2022-11-09 04:50:40');
    expect($log->getHash())->toBe(mockCommitHash());
    expect($log->getMessage())->toBe("wip\n");
})->with('lastCommitLogText');

test('Parse Merge Git log from Console', function (string $logText) {
    $log = new Log($logText);

    expect($log->getMerge())->toBe(['123abc', '456def']);
})->with('mergeLogText');

test('Returns Hash when parsed to string', function (string $logText) {
    $log = new Log($logText);

    expect((string) $log)->toBe($log->getHash());
})->with('lastCommitLogText');
