<?php

namespace Igorsgm\LaravelGitHooks\Tests;

use Igorsgm\LaravelGitHooks\LaravelGitHooksServiceProvider;

class LaravelGitHooksServiceProviderTest extends TestCase
{
    public function test_config_file_should_be_published()
    {
        $app = $this->makeApplication();

        $app->expects('runningInConsole')->andReturns(true);
        $app->expects('configPath')
            ->with('git-hooks.php')
            ->andReturns('config/git-hooks.php');

        $provider = new LaravelGitHooksServiceProvider($app);

        $provider->boot();

        $configPath = key(LaravelGitHooksServiceProvider::$publishGroups['laravel-git-hooks']);

        $this->assertFileExists($configPath);
        $this->assertEquals(
            'config/git-hooks.php',
            LaravelGitHooksServiceProvider::$publishGroups['laravel-git-hooks'][$configPath]
        );
    }
}
