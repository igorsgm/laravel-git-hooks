<?php

namespace Igorsgm\LaravelGitHooks;

use Igorsgm\LaravelGitHooks\Contracts\HookStorage;
use Illuminate\Contracts\Foundation\Application;

class LaravelGitHooks
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
     * {@inheritDoc}
     */
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
