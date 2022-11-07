<?php

namespace Igorsgm\GitHooks\Tests;

use Igorsgm\GitHooks\CommitMessageStorage;
use Igorsgm\GitHooks\Tests\Concerns\WithTmpFiles;

class CommitMessageStorageTest extends TestCase
{
    use WithTmpFiles;

    public function test_gets_file_contents()
    {
        $storage = new CommitMessageStorage();

        $this->makeTmpFile('COMMIT_MESSAGE', 'test message');

        $this->assertEquals(
            'test message',
            $storage->get($this->getTmpFilePath('COMMIT_MESSAGE'))
        );
    }

    public function test_updates_message_content()
    {
        $storage = new CommitMessageStorage();

        $messagePath = $this->getTmpFilePath('COMMIT_MESSAGE');

        $storage->update($messagePath, 'test message 1');

        $this->assertTmpFileContains('COMMIT_MESSAGE', 'test message 1');
    }
}
