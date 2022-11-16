<?php

dataset('modifiedFilesMeta', [
    'empty log' => [
        '',
        false,
        false,
        false,
        false,
        false,
    ],
    'added and modified files' => [
        'AM src/ChangedFiles.php',
        true,
        true,
        false,
        false,
        true,
    ],
    'modified file' => [
        ' M src/Console/Commands/CommitMessage.php',
        false,
        true,
        false,
        false,
        false,
    ],
    'deleted file' => [
        ' D LICENSE',
        false,
        false,
        true,
        false,
        false,
    ],
    'untracked file' => [
        '?? LICENSE',
        false,
        false,
        false,
        true,
        false,
    ],
]);

dataset('modifiedFilesList', [
    'modified files list' => implode(PHP_EOL, [
        'M  src/Console/Commands/CommitMessage.php',
        'M src/Console/Commands/PrepareCommitMessage.php',
        'A src/Traits/WithCommitMessage.php',
        'M  src/Contracts/MessageHook.php',
        'AM src/Git/ChangedFile.php',
        'AM src/Git/ChangedFiles.php',
        'A  src/Git/CommitMessage.php',
        'M  tests/Console/Commands/CommitMessageTest.php',
        'M  tests/Console/Commands/PrepareCommitMessageTest.php',
        'AM tests/Git/ChangedFileTest.php',
        'AM tests/Git/ChangedFilesTest.php',
        'D  tests/Git/CommitMessageTest.php',
        '?? tests/Git/UntrackedFile.php',
    ]),
]);
