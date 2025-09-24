<?php

declare(strict_types=1);

use Igorsgm\GitHooks\Git\ChangedFile;

test('Gets file meta', function (string $file, bool $isAdded, bool $isModified, bool $isDeleted, bool $isUntracked, bool $inCommit, bool $isStaged) {
    $file = new ChangedFile($file);

    $this->assertEquals($isAdded, $file->isAdded());
    $this->assertEquals($isModified, $file->isModified());
    $this->assertEquals($isDeleted, $file->isDeleted());
    $this->assertEquals($isUntracked, $file->isUntracked());
    $this->assertEquals($inCommit, $file->isInCommit());
    $this->assertEquals($isStaged, $file->isStaged());
})->with('modifiedFilesMeta');

test('Gets files', function () {
    $file = new ChangedFile('AM src/ChangedFiles.php');
    $this->assertEquals('src/ChangedFiles.php', $file->getFilePath());

    $file = new ChangedFile('?? LICENSE');
    $this->assertEquals('LICENSE', $file->getFilePath());
});
