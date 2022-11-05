<?php

namespace Igorsgm\LaravelGitHooks\Traits;

use Closure;
use Igorsgm\LaravelGitHooks\Contracts\Hook;
use Igorsgm\LaravelGitHooks\HooksPipeline;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Collection;

trait WithPipeline
{
    /**
     * {@inheritDoc}
     */
    public function getConfig(): Repository
    {
        return $this->config;
    }

    /**
     * Make pipeline instance
     *
     * @return Pipeline
     */
    protected function makePipeline(): Pipeline
    {
        $pipeline = new HooksPipeline(
            $this->getLaravel(),
            $this->getConfig(),
            $this->getHook()
        );

        return $pipeline
            ->through($this->getRegisteredHooks())
            ->withCallback($this->showInfoAboutHook());
    }

    /**
     * Show information about run hook
     *
     * @return Closure
     */
    protected function showInfoAboutHook(): Closure
    {
        return function (Hook $hook) {
            $this->info(sprintf('Hook: %s...', $hook->getName()));
        };
    }

    /**
     * {@inheritDoc}
     */
    public function getRegisteredHooks(): array
    {
        $hooks = new Collection((array) $this->config->get('git-hooks.'.$this->getHook()));

        return $hooks->map(function ($hook, $i) {
            if (is_int($i)) {
                return $hook;
            }

            return $i;
        })->all();
    }
}
