<?php

namespace Igorsgm\GitHooks;

use Closure;
use Exception;
use Illuminate\Contracts\Container\Container;
use Illuminate\Pipeline\Pipeline;
use Throwable;

class HooksPipeline extends Pipeline
{
    /**
     * @var Closure
     */
    protected $callback;

    /**
     * @var Closure
     */
    protected $exceptionCallback;

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
    public function withCallback(Closure $callback)
    {
        $this->callback = $callback;

        return $this;
    }

    /**
     * @param  Closure  $callback
     * @return $this
     */
    public function withExceptionCallback(Closure $callback)
    {
        $this->exceptionCallback = $callback;

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

                        if ($this->callback) {
                            call_user_func_array($this->callback, [$pipe]);
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
                } catch (Exception $e) {
                    $this->handleException($passable, $e);
                } catch (Throwable $e) {
                    $this->handleException($passable, $e);
                }
            };
        };
    }

    /**
     * {@inheritDoc}
     */
    protected function handleException($passable, $e)
    {
        if ($this->exceptionCallback) {
            return call_user_func_array($this->exceptionCallback, [$e]);
        }

        throw $e;
    }
}
