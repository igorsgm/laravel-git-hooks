<?php

namespace Igorsgm\GitHooks;

use Closure;
use Illuminate\Contracts\Container\Container;
use Illuminate\Pipeline\Pipeline;
use Throwable;

class HooksPipeline extends Pipeline
{
    /**
     * @var Closure
     */
    protected $pipeStartCallback;

    /**
     * @var Closure
     */
    protected $pipeEndCallback;

    /**
     * @var string
     */
    protected $hook;

    /**
     * @param  \Illuminate\Contracts\Container\Container|null  $container
     * @param  string  $hook
     */
    public function __construct(Container $container, string $hook)
    {
        parent::__construct($container);
        $this->hook = $hook;
    }

    /**
     * @param  Closure  $callback
     * @return $this
     */
    public function withPipeStartCallback(Closure $callback)
    {
        $this->pipeStartCallback = $callback;

        return $this;
    }

    /**
     * @param  Closure  $callback
     * @return $this
     */
    public function withPipeEndCallback(Closure $callback)
    {
        $this->pipeEndCallback = $callback;

        return $this;
    }

    /**
     * Get a Closure that represents a slice of the application onion.
     *
     * @return Closure
     */
    protected function carry()
    {
        return function ($stack, $pipe) {
            return function ($passable) use ($stack, $pipe) {
                try {
                    if (is_callable($pipe)) {
                        // If the pipe is a callable, then we will call it directly, but otherwise we
                        // will resolve the pipes out of the dependency container and call it with
                        // the appropriate method and arguments, returning the results back out.
                        return $pipe($passable, $stack);
                    } elseif (! is_object($pipe)) {
                        $hookParameters = (array) config('git-hooks.'.$this->hook.'.'.$pipe);

                        // If the pipe is a string we will parse the string and resolve the class out
                        // of the dependency injection container. We can then build a callable and
                        // execute the pipe function giving in the parameters that are required.
                        $pipe = $this->getContainer()->make($pipe, ['parameters' => $hookParameters]);

                        $this->handlePipeEnd(true);

                        if ($this->pipeStartCallback) {
                            call_user_func_array($this->pipeStartCallback, [$pipe]);
                        }

                        $parameters = [$passable, $stack];
                    } else {
                        // If the pipe is already an object we'll just make a callable and pass it to
                        // the pipe as-is. There is no need to do any extra parsing and formatting
                        // since the object we're given was already a fully instantiated object.
                        $parameters = [$passable, $stack];
                    }

                    $carry = method_exists($pipe, $this->method)
                                ? $pipe->{$this->method}(...$parameters)
                                : $pipe(...$parameters);

                    return $this->handleCarry($carry);
                } catch (Throwable $e) {
                    return $this->handleException($passable, $e);
                }
            };
        };
    }

    /**
     * Handle the call back call once a specific pipe has finished or errored.
     *
     * @param  bool  $success
     * @return void
     */
    protected function handlePipeEnd($success)
    {
        if ($this->pipeEndCallback) {
            call_user_func_array($this->pipeEndCallback, [$success]);
        }
    }

    /**
     * Handle the value returned from each pipe before passing it to the next.
     *
     * @param  mixed  $carry
     * @return mixed
     */
    protected function handleCarry($carry)
    {
        $this->handlePipeEnd(true);

        return $carry;
    }

    /**
     * Handle the given exception.
     *
     * @param  mixed  $passable
     * @param  \Throwable  $e
     * @return mixed
     *
     * @throws \Throwable
     */
    protected function handleException($passable, Throwable $e)
    {
        $this->handlePipeEnd(false);

        throw $e;
    }
}
