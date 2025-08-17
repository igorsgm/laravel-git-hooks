<?php

namespace Igorsgm\GitHooks\Tests\Git;

use Igorsgm\GitHooks\Git\ChangedFiles;

test(
    'Gets added to commit files', function ($modifiedFiles) {
        $files = new ChangedFiles($modifiedFiles);

        $filesAddedToCommit = $files->getAddedToCommit()->map->__toString()->values()->all();

        expect($filesAddedToCommit)
            ->toBe(
                [
                    'M  src/Console/Commands/CommitMessage.php',
                    'M  src/Contracts/MessageHook.php',
                    'AM src/Git/ChangedFile.php',
                    'AM src/Git/ChangedFiles.php',
                    'A  src/Git/CommitMessage.php',
                    'M  tests/Console/Commands/CommitMessageTest.php',
                    'M  tests/Console/Commands/PrepareCommitMessageTest.php',
                    'AM tests/Git/ChangedFileTest.php',
                    'AM tests/Git/ChangedFilesTest.php',
                    'D  tests/Git/CommitMessageTest.php',
                ]
            );
    }
)->with('modifiedFilesList');

test(
    'Gets staged files', function ($modifiedFiles) {
        $files = new ChangedFiles($modifiedFiles);

        $filesAddedToCommit = $files->getStaged()->map->__toString()->values()->all();

        expect($filesAddedToCommit)
            ->toBe(
                [
                    'M  src/Console/Commands/CommitMessage.php',
                    'M  src/Contracts/MessageHook.php',
                    'AM src/Git/ChangedFile.php',
                    'AM src/Git/ChangedFiles.php',
                    'A  src/Git/CommitMessage.php',
                    'M  tests/Console/Commands/CommitMessageTest.php',
                    'M  tests/Console/Commands/PrepareCommitMessageTest.php',
                    'AM tests/Git/ChangedFileTest.php',
                    'AM tests/Git/ChangedFilesTest.php',
                ]
            );
    }
)->with('modifiedFilesList');

test(
    'Gets deleted to commit files', function ($modifiedFiles) {
        $files = new ChangedFiles($modifiedFiles);

        $deletedFiles = $files->getDeleted()->map->__toString()->values()->all();

        expect($deletedFiles)->toBe(
            [
                'D  tests/Git/CommitMessageTest.php',
            ]
        );
    }
)->with('modifiedFilesList');

test(
    'Gets all files', function ($modifiedFiles) {
        $files = new ChangedFiles($modifiedFiles);

        $allFiles = $files->getFiles()->map->__toString()->values()->all();

        expect($allFiles)->toBe(
            [
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
            ]
        );
    }
)->with('modifiedFilesList');

test(
    'Gets untracked files', function ($modifiedFiles) {
        $files = new ChangedFiles($modifiedFiles);

        $untrackedFiles = $files->getUntracked()->map->__toString()->values()->all();

        expect($untrackedFiles)->toBe(
            [
                '?? tests/Git/UntrackedFile.php',
            ]
        );
    }
)->with('modifiedFilesList');
