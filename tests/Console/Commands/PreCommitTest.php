<?php

namespace Igorsgm\GitHooks\Tests\Console\Commands;

use Closure;
use Igorsgm\GitHooks\Console\Commands\PreCommit;
use Igorsgm\GitHooks\Contracts\PreCommitHook;
use Igorsgm\GitHooks\Git\ChangedFiles;
use Igorsgm\GitHooks\Git\GitHelper;
use Igorsgm\GitHooks\Tests\TestCase;
use Illuminate\Support\Facades\Config;
use Mockery;

class PreCommitTest extends TestCase
{
    public function test_get_command_name()
    {
        $config = $this->makeConfig();
        $command = new PreCommit($config);

        $this->assertEquals('git-hooks:pre-commit', $command->getName());
    }

    public function test_a_message_should_be_send_through_the_hook_pipes()
    {
        $hook1 = Mockery::mock(PreCommitHook::class);
        $hook1->expects('handle')
            ->andReturnUsing(function (ChangedFiles $files, Closure $closure) {
                $this->assertEquals('AM src/ChangedFiles.php', (string) $files->getFiles()->first());

                return $closure($files);
            });

        $hook2 = Mockery::mock(PreCommitHook::class);
        $hook2->expects('handle')
            ->andReturnUsing(function (ChangedFiles $files, Closure $closure) {
                $this->assertEquals('AM src/ChangedFiles.php', (string) $files->getFiles()->first());

                return $closure($files);
            });

        Config::set('git-hooks', [
            'pre-commit' => [
                $hook1,
                $hook2,
            ],
        ]);

        $app = $this->makeApplication();
        $command = new PreCommit();
        $command->setLaravel($app);

        $gitHelper = Mockery::mock('alias:'.GitHelper::class);
        $gitHelper->expects('getListOfChangedFiles')->andReturns('AM src/ChangedFiles.php');

        $command->handle();

        $this->assertTrue(true);
    }
}
