<?php

namespace Igorsgm\GitHooks\Tests\Git;

use Igorsgm\GitHooks\Git\ChangedFile;
use Igorsgm\GitHooks\Tests\TestCase;

class ChangedFileTest extends TestCase
{
    /**
     * @dataProvider getModifiedFiles
     *
     * @param  string  $file
     * @param  bool  $isAdded
     * @param  bool  $isModified
     * @param  bool  $isDeleted
     * @param  bool  $isUntracked
     */
    public function test_gets_file_meta(string $file, bool $isAdded, bool $isModified, bool $isDeleted, bool $isUntracked, bool $inCommit)
    {
        $file = new ChangedFile($file);

        $this->assertEquals($isAdded, $file->isAdded());
        $this->assertEquals($isModified, $file->isModified());
        $this->assertEquals($isDeleted, $file->isDeleted());
        $this->assertEquals($isUntracked, $file->isUntracked());
        $this->assertEquals($inCommit, $file->isInCommit());
    }

    public function test_gets_file()
    {
        $file = new ChangedFile('AM src/ChangedFiles.php');
        $this->assertEquals('src/ChangedFiles.php', $file->getFilePath());

        $file = new ChangedFile('?? LICENSE');
        $this->assertEquals('LICENSE', $file->getFilePath());
    }

    public function getModifiedFiles()
    {
        return [
            [
                '',
                false,
                false,
                false,
                false,
                false,
            ],
            [
                'AM src/ChangedFiles.php',
                true,
                true,
                false,
                false,
                true,
            ],
            [
                ' M src/Console/Commands/CommitMessage.php',
                false,
                true,
                false,
                false,
                false,
            ],
            [
                ' D LICENSE',
                false,
                false,
                true,
                false,
                false,
            ],
            [
                '?? LICENSE',
                false,
                false,
                false,
                true,
                false,
            ],
        ];
    }
}
