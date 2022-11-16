<?php

namespace Igorsgm\GitHooks\Tests;

use Igorsgm\GitHooks\GitHooksServiceProvider;

class GitHooksServiceProviderTest extends TestCase
{
    public function test_config_file_should_be_published()
    {
        $app = $this->makeApplication();

        $app->expects('runningInConsole')->andReturns(true);
        $app->expects('configPath')
            ->with('git-hooks.php')
            ->andReturns('config/git-hooks.php');

        $provider = new GitHooksServiceProvider($app);

        $provider->boot();

        $configPath = key(GitHooksServiceProvider::$publishGroups['laravel-git-hooks']);

        $this->assertFileExists($configPath);
        $this->assertEquals(
            'config/git-hooks.php',
            GitHooksServiceProvider::$publishGroups['laravel-git-hooks'][$configPath]
        );
    }
}
