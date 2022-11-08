<?php

namespace Igorsgm\GitHooks\Tests\Console\Commands;

use Igorsgm\GitHooks\Console\Commands\RegisterHooks;
use Igorsgm\GitHooks\Tests\TestCase;
use Illuminate\Console\OutputStyle;
use Mockery;

class RegisterHooksTest extends TestCase
{
    public function test_get_command_name()
    {
        $command = new RegisterHooks();

        $this->assertEquals('git-hooks:register-hooks', $command->getName());
    }

    public function test_run_laravel_git_hooks()
    {
        $gitHooks = $this->makeGitHooks();

        $gitHooks->expects('install');
        $gitHooks->expects('install');
        $gitHooks->expects('getAvailableHooks')->andReturns(['pre-commit', 'post-commit']);

        $command = new RegisterHooks();

        $command->setOutput($output = Mockery::mock(OutputStyle::class));

        $output->expects('writeLn')
            ->with('<info>Git hooks have been successfully created</info>', 32);

        $command->handle($gitHooks);

        $this->assertTrue(true);
    }
}
