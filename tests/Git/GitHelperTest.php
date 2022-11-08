<?php

namespace Igorsgm\GitHooks\Tests\Git;

use Igorsgm\GitHooks\Git\GitHelper;
use Igorsgm\GitHooks\Tests\TestCase;
use Igorsgm\GitHooks\Tests\Traits\WithTmpFiles;

class GitHelperTest extends TestCase
{
    use WithTmpFiles;

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_gets_file_contents()
    {
        $this->makeTmpFile('COMMIT_MESSAGE', 'test message');

        $this->assertEquals(
            'test message',
            GitHelper::getCommitMessageContentFromFile($this->getTmpFilePath('COMMIT_MESSAGE'))
        );
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_updates_message_content()
    {
        $messagePath = $this->getTmpFilePath('COMMIT_MESSAGE');

        GitHelper::updateCommitMessageContentInFile($messagePath, 'test message 1');

        $this->assertTmpFileContains('COMMIT_MESSAGE', 'test message 1');
    }
}
