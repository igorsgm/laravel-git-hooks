<?php

namespace Igorsgm\GitHooks\Traits;

use Closure;
use Igorsgm\GitHooks\Contracts\Hook;
use Igorsgm\GitHooks\HooksPipeline;
use Illuminate\Pipeline\Pipeline;
use Throwable;

trait WithPipeline
{
    /**
     * Make pipeline instance
     *
     * @return Pipeline
     */
    protected function makePipeline(): Pipeline
    {
        $pipeline = new HooksPipeline(app(), $this->getHook());

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
        $hooks = collect((array) config('git-hooks.'.$this->getHook()));

        return $hooks->map(function ($hook, $i) {
            return is_int($i) ? $hook : $i;
        })->all();
    }
}
