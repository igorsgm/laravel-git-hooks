<?php

namespace Igorsgm\GitHooks\Tests\Console\Commands;

use Closure;
use Igorsgm\GitHooks\Console\Commands\PreCommit;
use Igorsgm\GitHooks\Contracts\PreCommitHook;
use Igorsgm\GitHooks\Git\ChangedFiles;
use Igorsgm\GitHooks\Git\GetListOfChangedFiles;
use Igorsgm\GitHooks\Tests\TestCase;
use Illuminate\Config\Repository;
use Mockery;
use Symfony\Component\Process\Process;

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

        $config = new Repository([
            'git-hooks' => [
                'pre-commit' => [
                    $hook1,
                    $hook2,
                ],
            ],
        ]);

        $app = $this->makeApplication();
        $command = new PreCommit($config);
        $command->setLaravel($app);

        $process = Mockery::mock(Process::class);
        $process->expects('getOutput')->andReturns('AM src/ChangedFiles.php');

        $gitCommand = Mockery::mock(GetListOfChangedFiles::class);
        $gitCommand->expects('exec')->andReturns($process);

        $command->handle($gitCommand);

        $this->assertTrue(true);
    }
}
