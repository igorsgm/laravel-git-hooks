<?php

dataset('modifiedFilesMeta', [
    'Empty Log' => [
        '',
        false,
        false,
        false,
        false,
        false,
        false,
    ],
    'Added and Modified Files' => [
        'AM src/ChangedFiles.php',
        true,
        true,
        false,
        false,
        true,
        true,
    ],
    'Modified File' => [
        ' M src/Console/Commands/CommitMessage.php',
        false,
        true,
        false,
        false,
        false,
        true,
    ],
    'Deleted File' => [
        ' D LICENSE',
        false,
        false,
        true,
        false,
        false,
        false,
    ],
    'Untracked File' => [
        '?? LICENSE',
        false,
        false,
        false,
        true,
        false,
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
