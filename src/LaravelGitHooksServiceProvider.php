<?php

namespace Igorsgm\LaravelGitHooks;

use Illuminate\Support\ServiceProvider;

class LaravelGitHooksServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        /*
         * Optional methods to load your package assets
         */
        // $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'laravel-git-hooks');
        // $this->loadViewsFrom(__DIR__.'/../resources/views', 'laravel-git-hooks');
        // $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        // $this->loadRoutesFrom(__DIR__.'/routes.php');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/git-hooks.php' => $this->app->configPath('git-hooks.php'),
            ], 'config');

            // Publishing the views.
            /*$this->publishes([
                __DIR__.'/../resources/views' => resource_path('views/vendor/laravel-git-hooks'),
            ], 'views');*/

            // Publishing assets.
            /*$this->publishes([
                __DIR__.'/../resources/assets' => public_path('vendor/laravel-git-hooks'),
            ], 'assets');*/

            // Publishing the translation files.
            /*$this->publishes([
                __DIR__.'/../resources/lang' => resource_path('lang/vendor/laravel-git-hooks'),
            ], 'lang');*/

            // Registering package commands.
            // $this->commands([]);
        }
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        // Automatically apply the package configuration
        $this->mergeConfigFrom(__DIR__.'/../config/git-hooks.php', 'git-hooks');

        if ($this->app->runningInConsole()) {
            $this->app->singleton('laravel-git-hooks', function ($app) {
                $hooks = [
                    'pre-commit',
                    'prepare-commit-msg',
                    'commit-msg',
                    'post-commit',
                    'pre-push',
                    'pre-rebase',
                    'post-rewrite',
                    'post-checkout',
                    'post-merge',
                ];

                $config = $app['config']->get('git-hooks');

                $hooks = array_filter($hooks, function ($hook) use ($config) {
                    return ! empty($config[$hook]);
                });

                $storage = $app[Contracts\HookStorage::class];

                return new LaravelGitHooks($app, $storage, $hooks);
            });

            $this->app->bind(Contracts\HookStorage::class, HookStorage::class);
            $this->app->bind(Contracts\CommitMessageStorage::class, CommitMessageStorage::class);

            $this->commands([
                \Igorsgm\LaravelGitHooks\Console\Commands\RegisterHooks::class,
                \Igorsgm\LaravelGitHooks\Console\Commands\CommitMessage::class,
                \Igorsgm\LaravelGitHooks\Console\Commands\PreCommit::class,
                \Igorsgm\LaravelGitHooks\Console\Commands\PrepareCommitMessage::class,
                \Igorsgm\LaravelGitHooks\Console\Commands\PostCommit::class,
                \Igorsgm\LaravelGitHooks\Console\Commands\PrePush::class,
            ]);
        }
    }
}
