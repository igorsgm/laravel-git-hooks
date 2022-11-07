<?php

namespace Igorsgm\GitHooks\Tests\Console\Commands;

use Closure;
use Igorsgm\GitHooks\Console\Commands\PostCommit;
use Igorsgm\GitHooks\Contracts\PostCommitHook;
use Igorsgm\GitHooks\Git\GetLasCommitFromLog;
use Igorsgm\GitHooks\Git\Log;
use Igorsgm\GitHooks\Tests\TestCase;
use Illuminate\Config\Repository;
use Mockery;
use Symfony\Component\Process\Process;

class PostCommitTest extends TestCase
{
    public function test_get_command_name()
    {
        $config = $this->makeConfig();
        $command = new PostCommit($config);

        $this->assertEquals('git-hooks:post-commit', $command->getName());
    }

    public function test_a_message_should_be_send_through_the_hook_pipes()
    {
        $hook1 = Mockery::mock(PostCommitHook::class);
        $hook1->expects('handle')
            ->andReturnUsing(function (Log $log, Closure $closure) {
                $this->assertEquals('bfdc6c406626223bf3cbb65b8d269f7b65ca0570', $log->getHash());

                return $closure($log);
            });

        $hook2 = Mockery::mock(PostCommitHook::class);
        $hook2->expects('handle')
            ->andReturnUsing(function (Log $log, Closure $closure) {
                $this->assertEquals('bfdc6c406626223bf3cbb65b8d269f7b65ca0570', $log->getHash());

                return $closure($log);
            });

        $config = new Repository([
            'git-hooks' => [
                'post-commit' => [
                    $hook1,
                    $hook2,
                ],
            ],
        ]);

        $app = $this->makeApplication();
        $command = new PostCommit($config);
        $command->setLaravel($app);

        $gitCommand = Mockery::mock(GetLasCommitFromLog::class);
        $process = Mockery::mock(Process::class);

        $process->expects('getOutput')
            ->andReturns(<<<'EOL'
commit bfdc6c406626223bf3cbb65b8d269f7b65ca0570
Author: Igor Moraes <igor.sgm@gmail.com>
Date:   Tue Feb 18 12:01:15 2020 +0300

    Added PreCommit hooks.

    Added docs for `pre-commit`, `prepare-commit-msg`, `commit-msg`

    fixed #2
EOL
            );
        $gitCommand->expects('exec')->andReturns($process);

        $command->handle($gitCommand);

        $this->assertTrue(true);
    }
}
