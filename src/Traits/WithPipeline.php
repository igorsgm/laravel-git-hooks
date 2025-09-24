<?php

declare(strict_types=1);

namespace Igorsgm\GitHooks\Traits;

use Closure;
use Igorsgm\GitHooks\Contracts\Hook;
use Igorsgm\GitHooks\HooksPipeline;
use Illuminate\Pipeline\Pipeline;

trait WithPipeline
{
    /**
     * Hook which is currently running in the Pipeline.
     */
    public ?Hook $hookExecuting;

    /**
     * {@inheritDoc}
     */
    public function getRegisteredHooks(): array
    {
        $hooks = collect((array) config('git-hooks.'.$this->getHook()));

        return $hooks->map(fn ($hook, $i) => is_int($i) ? $hook : $i)->all();
    }

    public function getHookTaskTitle(Hook $hook): string
    {
        $hookName = $hook->getName() ?? class_basename($hook);

        return sprintf('  <bg=blue;fg=white> HOOK </> %s', $hookName);
    }

    /**
     * Make pipeline instance
     */
    protected function makePipeline(): Pipeline
    {
        $pipeline = new HooksPipeline(app(), $this->getHook());

        return $pipeline
            ->through($this->getRegisteredHooks())
            ->withPipeStartCallback($this->startHookConsoleTask())
            ->withPipeEndCallback($this->finishHookConsoleTask());
    }

    /**
     * Show information about Hook which is being executed
     */
    protected function startHookConsoleTask(): Closure
    {
        return function (Hook $hook): void {
            $this->hookExecuting = $hook;

            // Binding the Command instance to the Hook, so it can be used inside the Hook
            $hook->setCommand($this);

            $taskTitle = $this->getHookTaskTitle($hook);
            $loadingText = 'loading...';
            $this->output->write(
                "{$taskTitle}: <comment>{$loadingText}</comment>"
            );
        };
    }

    /**
     * Finish the console task of the Hook which just executed
     */
    protected function finishHookConsoleTask(): Closure
    {
        return function ($success): void {
            if (empty($this->hookExecuting)) {
                return;
            }

            // Check if we can use escape sequences
            if ($this->output->isDecorated()) {
                // Move the cursor to the beginning of the line
                $this->output->write("\x0D");

                // Erase the line
                $this->output->write("\x1B[2K");
            } else {
                // Make sure we first close the previous line
                $this->output->writeln('');
            }

            $taskTitle = $this->getHookTaskTitle($this->hookExecuting);

            $status = $success ? '<info>✔</info>' : '<error>failed</error>';
            $this->output->writeln("{$taskTitle}: {$status}");

            $this->hookExecuting = null;
        };
    }
}
