<?php

namespace Igorsgm\GitHooks\Tests;

use Igorsgm\GitHooks\Facades\GitHooks;
use Igorsgm\GitHooks\GitHooksServiceProvider;
use Igorsgm\GitHooks\Tests\Traits\WithTmpFiles;

class TestCase extends \Orchestra\Testbench\TestCase
{
    use WithTmpFiles;

    /**
     * @var \Illuminate\Config\Repository
     */
    public $config;

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    public function defineEnvironment($app)
    {
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        $app['config']->set('git-hooks', [
            'pre-commit' => [],
            'prepare-commit-msg' => [],
            'commit-msg' => [],
            'post-commit' => [],
            'pre-rebase' => [],
            'post-rewrite' => [],
            'post-checkout' => [],
            'post-merge' => [],
            'pre-push' => [],
        ]);

        $this->config = $app['config'];
    }

    /**
     * Get package providers.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            GitHooksServiceProvider::class,
        ];
    }

    /**
     * Override application aliases.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return array
     */
    protected function getPackageAliases($app)
    {
        return [
            'GitHooks' => GitHooks::class,
        ];
    }

    /**
     * @return void
     */
    public function initializeGitAsTempDirectory()
    {
        chdir(base_path());
        shell_exec('git init --quiet');
        $this->initializeTempDirectory(base_path('.git'));
    }
}
