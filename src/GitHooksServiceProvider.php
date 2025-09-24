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
                Console\Commands\RegisterHooks::class,
                Console\Commands\CommitMessage::class,
                Console\Commands\PreCommit::class,
                Console\Commands\PrepareCommitMessage::class,
                Console\Commands\PostCommit::class,
                Console\Commands\PrePush::class,
                Console\Commands\MakeHook::class,
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

        $this->app->singleton('laravel-git-hooks', fn () => new GitHooks());
    }
}
