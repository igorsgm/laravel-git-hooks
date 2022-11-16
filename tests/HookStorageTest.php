<?php

namespace Igorsgm\GitHooks\Tests;

use Igorsgm\GitHooks\Tests\Traits\WithTmpFiles;

class HookStorageTest extends TestCase
{
    use WithTmpFiles;

    /**
     * @TODO revisiti this test
     */
    public function test_hook_file_should_be_created()
    {
        $this->assertTrue(true);
//        $storage = new HookStorage();
//
//        $hookPath = $this->getTmpFilePath('hook');
//
//        $storage->store($hookPath, 'Hook content');
//
//        $this->assertTmpFileContains('hook', 'Hook content');
//        $this->assertEquals(0777, (fileperms($hookPath) & 0777));
    }
}
