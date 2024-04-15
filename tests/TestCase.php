<?php

namespace Igorsgm\GitHooks\Tests;

use Enlightn\Enlightn\EnlightnServiceProvider;
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
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();
        GitHooks::shouldReceive('getSupportedHooks')->andReturn(array_keys($this->config->get('git-hooks')));
    }

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
            'code_analyzers' => [],
            'artisan_path' => base_path('artisan'),
            'output_errors' => false,
            'automatically_fix_errors' => false,
            'rerun_analyzer_after_autofix' => false,
            'stop_at_first_analyzer_failure' => true,
            'debug_commands' => false,
            'debug_output' => false,
            'run_in_docker' => false,
            'docker_command' => '',
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
            EnlightnServiceProvider::class,
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

    public function gitInit()
    {
        chdir(base_path());
        shell_exec('git init --quiet');

        return $this;
    }

    /**
     * @return void
     */
    public function initializeGitAsTempDirectory()
    {
        $this->gitInit()
            ->initializeTempDirectory(base_path('.git'));
    }
}
