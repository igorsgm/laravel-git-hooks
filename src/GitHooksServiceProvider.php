<?php

namespace Igorsgm\GitHooks;

use Illuminate\Support\ServiceProvider;

class GitHooksServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/git-hooks.php' => $this->app->configPath('git-hooks.php'),
            ], 'laravel-git-hooks');

            // Registering package commands.
            $this->commands([
                \Igorsgm\GitHooks\Console\Commands\RegisterHooks::class,
                \Igorsgm\GitHooks\Console\Commands\CommitMessage::class,
                \Igorsgm\GitHooks\Console\Commands\PreCommit::class,
                \Igorsgm\GitHooks\Console\Commands\PrepareCommitMessage::class,
                \Igorsgm\GitHooks\Console\Commands\PostCommit::class,
                \Igorsgm\GitHooks\Console\Commands\PrePush::class,
            ]);
        }
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        // Automatically apply the package configuration
        $this->mergeConfigFrom(__DIR__.'/../config/git-hooks.php', 'laravel-git-hooks');

        $this->app->singleton('laravel-git-hooks', function ($app) {
            $config = $app['config']->get('git-hooks');

            $hooks = array_filter(GitHooks::getSupportedHooks(), function ($hook) use ($config) {
                return ! empty($config[$hook]);
            });

            $storage = $app[Contracts\HookStorage::class];

            return new GitHooks($app, $storage, $hooks);
        });

        $this->app->bind(Contracts\HookStorage::class, HookStorage::class);
        $this->app->bind(Contracts\CommitMessageStorage::class, CommitMessageStorage::class);
    }
}
