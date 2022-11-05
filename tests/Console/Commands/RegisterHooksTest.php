<?php

namespace Igorsgm\LaravelGitHooks\Tests\Console\Commands;

use Igorsgm\LaravelGitHooks\Console\Commands\RegisterHooks;
use Igorsgm\LaravelGitHooks\Tests\TestCase;
use Illuminate\Console\OutputStyle;
use Mockery;

class RegisterHooksTest extends TestCase
{
    public function test_get_command_name()
    {
        $command = new RegisterHooks();

        $this->assertEquals('git-hooks:register-hooks', $command->getName());
    }

    public function test_run_configurator()
    {
        $configurator = $this->makeConfigurator();

        $configurator
            ->expects('run');

        $command = new RegisterHooks();

        $command->setOutput($output = Mockery::mock(OutputStyle::class));

        $output->expects('writeLn')
            ->with('<info>Git hooks have been successfully created</info>', 32);

        $command->handle($configurator);

        $this->assertTrue(true);
    }
}
