<?php

namespace Igorsgm\GitHooks\Tests\Git;

use Igorsgm\GitHooks\Git\ChangedFiles;
use Igorsgm\GitHooks\Tests\TestCase;

class ChangedFilesTest extends TestCase
{
    public function test_gets_added_to_commit_files()
    {
        $files = new ChangedFiles(<<<'EOL'
M  src/Console/Commands/CommitMessage.php
 M src/Console/Commands/PrepareCommitMessage.php
 A src/Traits/WithCommitMessage.php
M  src/Contracts/MessageHook.php
AM src/Git/ChangedFile.php
AM src/Git/ChangedFiles.php
A  src/Git/CommitMessage.php
A  src/Git/GetListOfChangedFiles.php
M  tests/Console/Commands/CommitMessageTest.php
M  tests/Console/Commands/PrepareCommitMessageTest.php
AM tests/Git/ChangedFileTest.php
AM tests/Git/ChangedFilesTest.php
D  tests/Git/CommitMessageTest.php
EOL
        );

        $this->assertEquals([
            'M  src/Console/Commands/CommitMessage.php',
            'M  src/Contracts/MessageHook.php',
            'AM src/Git/ChangedFile.php',
            'AM src/Git/ChangedFiles.php',
            'A  src/Git/CommitMessage.php',
            'A  src/Git/GetListOfChangedFiles.php',
            'M  tests/Console/Commands/CommitMessageTest.php',
            'M  tests/Console/Commands/PrepareCommitMessageTest.php',
            'AM tests/Git/ChangedFileTest.php',
            'AM tests/Git/ChangedFilesTest.php',
            'D  tests/Git/CommitMessageTest.php',
        ], $files->getAddedToCommit()->map->__toString()->values()->all());
    }

    public function test_gets_deleted_to_commit_files()
    {
        $files = new ChangedFiles(<<<'EOL'
M  src/Console/Commands/CommitMessage.php
 M src/Console/Commands/PrepareCommitMessage.php
 A src/Traits/WithCommitMessage.php
 D src/Git/GetListOfChangedFiles.php
M  src/Contracts/MessageHook.php
AM src/Git/ChangedFile.php
AM src/Git/ChangedFiles.php
A  src/Git/CommitMessage.php
A  src/Git/GetListOfChangedFiles.php
M  tests/Console/Commands/CommitMessageTest.php
M  tests/Console/Commands/PrepareCommitMessageTest.php
AM tests/Git/ChangedFileTest.php
AM tests/Git/ChangedFilesTest.php
D  tests/Git/CommitMessageTest.php
EOL
        );

        $this->assertEquals([
            ' D src/Git/GetListOfChangedFiles.php',
            'D  tests/Git/CommitMessageTest.php',
        ], $files->getDeleted()->map->__toString()->values()->all());
    }

    public function test_gets_all_files()
    {
        $files = new ChangedFiles(<<<'EOL'
M  src/Console/Commands/CommitMessage.php
 M src/Console/Commands/PrepareCommitMessage.php
 A src/Traits/WithCommitMessage.php
 D src/Git/GetListOfChangedFiles.php
M  src/Contracts/MessageHook.php
AM src/Git/ChangedFile.php
AM src/Git/ChangedFiles.php
A  src/Git/CommitMessage.php
A  src/Git/GetListOfChangedFiles.php
M  tests/Console/Commands/CommitMessageTest.php
M  tests/Console/Commands/PrepareCommitMessageTest.php
AM tests/Git/ChangedFileTest.php
AM tests/Git/ChangedFilesTest.php
D  tests/Git/CommitMessageTest.php
EOL
        );

        $this->assertEquals([
            'M  src/Console/Commands/CommitMessage.php',
            ' M src/Console/Commands/PrepareCommitMessage.php',
            ' A src/Traits/WithCommitMessage.php',
            ' D src/Git/GetListOfChangedFiles.php',
            'M  src/Contracts/MessageHook.php',
            'AM src/Git/ChangedFile.php',
            'AM src/Git/ChangedFiles.php',
            'A  src/Git/CommitMessage.php',
            'A  src/Git/GetListOfChangedFiles.php',
            'M  tests/Console/Commands/CommitMessageTest.php',
            'M  tests/Console/Commands/PrepareCommitMessageTest.php',
            'AM tests/Git/ChangedFileTest.php',
            'AM tests/Git/ChangedFilesTest.php',
            'D  tests/Git/CommitMessageTest.php',
        ], $files->getFiles()->map->__toString()->values()->all());
    }

    public function test_gets_untracked_files()
    {
        $files = new ChangedFiles(<<<'EOL'
M  src/Console/Commands/CommitMessage.php
 M src/Console/Commands/PrepareCommitMessage.php
 A src/Traits/WithCommitMessage.php
 D src/Git/GetListOfChangedFiles.php
?? src/Contracts/MessageHook.php
AM src/Git/ChangedFile.php
AM src/Git/ChangedFiles.php
?? src/Git/CommitMessage.php
A  src/Git/GetListOfChangedFiles.php
M  tests/Console/Commands/CommitMessageTest.php
M  tests/Console/Commands/PrepareCommitMessageTest.php
AM tests/Git/ChangedFileTest.php
AM tests/Git/ChangedFilesTest.php
?? tests/Git/CommitMessageTest.php
EOL
        );

        $this->assertEquals([
            '?? src/Contracts/MessageHook.php',
            '?? src/Git/CommitMessage.php',
            '?? tests/Git/CommitMessageTest.php',
        ], $files->getUntracked()->map->__toString()->values()->all());
    }
}
