<?php

namespace Igorsgm\GitHooks;

use Igorsgm\GitHooks\Contracts\HookStorage;
use Illuminate\Contracts\Foundation\Application;

class GitHooks
{
    /**
     * @var HookStorage
     */
    protected $storage;

    /**
     * @var array
     */
    protected $hooksMap;

    /**
     * @var Application
     */
    protected $app;

    /**
     * @param  Application  $app
     * @param  HookStorage  $storage
     * @param  array  $hooksMap
     */
    public function __construct(Application $app, HookStorage $storage, array $hooksMap)
    {
        $this->storage = $storage;
        $this->hooksMap = $hooksMap;
        $this->app = $app;
    }

    /**
     * Get all supported git hooks
     */
    public static function getSupportedHooks()
    {
        return [
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
    }

    public function run(): void
    {
        foreach ($this->hooksMap as $hook) {
            $hookStubPath = __DIR__.'/Console/Commands/stubs/hook';
            $command = 'git-hooks:'.$hook;

            $hookPath = $this->app->basePath('.git/hooks/'.$hook);

            $hookScript = str_replace(
                ['{command}', '{path}'],
                [$command, $this->app->basePath()],
                file_get_contents($hookStubPath)
            );

            $this->storage->store(
                $hookPath,
                $hookScript
            );
        }
    }
}
