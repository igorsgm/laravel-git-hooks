<?php

namespace Igorsgm\GitHooks\Tests\Console\Commands;

use Closure;
use Igorsgm\GitHooks\Console\Commands\PrePush;
use Igorsgm\GitHooks\Contracts\PrePushHook;
use Igorsgm\GitHooks\Git\GitHelper;
use Igorsgm\GitHooks\Git\Log;
use Igorsgm\GitHooks\Tests\TestCase;
use Illuminate\Support\Facades\Config;
use Mockery;

class PrePushTest extends TestCase
{
    public function test_get_command_name()
    {
        $command = new PrePush();

        $this->assertEquals('git-hooks:pre-push', $command->getName());
    }

    public function test_a_message_should_be_send_through_the_hook_pipes()
    {
        $hook1 = Mockery::mock(PrePushHook::class);
        $hook1->expects('handle')
            ->andReturnUsing(function (Log $log, Closure $closure) {
                $this->assertEquals('bfdc6c406626223bf3cbb65b8d269f7b65ca0570', $log->getHash());

                return $closure($log);
            });

        $hook2 = Mockery::mock(PrePushHook::class);
        $hook2->expects('handle')
            ->andReturnUsing(function (Log $log, Closure $closure) {
                $this->assertEquals('bfdc6c406626223bf3cbb65b8d269f7b65ca0570', $log->getHash());

                return $closure($log);
            });

        Config::set('git-hooks', [
            'pre-push' => [
                $hook1,
                $hook2,
            ],
        ]);

        $command = new PrePush();

        $gitHelper = Mockery::mock('alias:'.GitHelper::class);
        $gitHelper->expects('getLastCommitFromLog')
            ->andReturns(<<<'EOL'
commit bfdc6c406626223bf3cbb65b8d269f7b65ca0570
Author: Igor Moraes <igor.sgm@gmail.com>
Date:   Tue Feb 18 12:01:15 2020 +0300

    Added PreCommit hooks.

    Added docs for `pre-commit`, `prepare-commit-msg`, `commit-msg`

    fixed #2
EOL
            );

        $command->handle();

        $this->assertTrue(true);
    }
}
