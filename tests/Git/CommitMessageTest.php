<?php

namespace Igorsgm\GitHooks\Tests\Git;

use Igorsgm\GitHooks\Git\ChangedFiles;
use Igorsgm\GitHooks\Git\CommitMessage;
use Igorsgm\GitHooks\Tests\TestCase;

class CommitMessageTest extends TestCase
{
    public function test_gettter_setter_message()
    {
        $commitMessage = new CommitMessage('Test message', new ChangedFiles(''));

        $this->assertEquals('Test message', $commitMessage->getMessage());

        $commitMessage->setMessage('New message');

        $this->assertEquals('New message', $commitMessage->getMessage());

        $this->assertInstanceOf(ChangedFiles::class, $commitMessage->getFiles());
    }
}
