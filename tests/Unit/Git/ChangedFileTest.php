<?php

use Igorsgm\GitHooks\Git\ChangedFile;

test('Gets file meta', function (string $file, bool $isAdded, bool $isModified, bool $isDeleted, bool $isUntracked, bool $inCommit) {
    $file = new ChangedFile($file);

    $this->assertEquals($isAdded, $file->isAdded());
    $this->assertEquals($isModified, $file->isModified());
    $this->assertEquals($isDeleted, $file->isDeleted());
    $this->assertEquals($isUntracked, $file->isUntracked());
    $this->assertEquals($inCommit, $file->isInCommit());
})->with('modifiedFilesMeta');

test('Gets files', function () {
    $file = new ChangedFile('AM src/ChangedFiles.php');
    $this->assertEquals('src/ChangedFiles.php', $file->getFilePath());

    $file = new ChangedFile('?? LICENSE');
    $this->assertEquals('LICENSE', $file->getFilePath());
});
