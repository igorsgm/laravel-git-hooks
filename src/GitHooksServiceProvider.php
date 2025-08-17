<?php

declare(strict_types=1);

namespace Igorsgm\GitHooks;

use Illuminate\Support\ServiceProvider;

class GitHooksServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot(): void
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
                \Igorsgm\GitHooks\Console\Commands\MakeHook::class,
            ]);
        }
    }

    /**
     * Register the application services.
     */
    public function register(): void
    {
        // Automatically apply the package configuration
        $this->mergeConfigFrom(__DIR__.'/../config/git-hooks.php', 'laravel-git-hooks');

        $this->app->singleton('laravel-git-hooks', fn () => new GitHooks);
    }
}
