<?php

namespace Igorsgm\GitHooks\Tests;

use Igorsgm\GitHooks\Facades\GitHooks;
use Igorsgm\GitHooks\GitHooksServiceProvider;

class TestCase extends \Orchestra\Testbench\TestCase
{
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
}
