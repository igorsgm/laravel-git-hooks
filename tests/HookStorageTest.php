<?php

namespace Igorsgm\LaravelGitHooks\Tests;

use Igorsgm\LaravelGitHooks\HookStorage;
use Igorsgm\LaravelGitHooks\Tests\Concerns\WithTmpFiles;

class HookStorageTest extends TestCase
{
    use WithTmpFiles;

    public function test_hook_file_should_be_created()
    {
        $storage = new HookStorage();

        $hookPath = $this->getTmpFilePath('hook');

        $storage->store($hookPath, 'Hook content');

        $this->assertTmpFileContains('hook', 'Hook content');
        $this->assertEquals(0777, (fileperms($hookPath) & 0777));
    }
}
