<?php

declare(strict_types=1);

namespace Igorsgm\GitHooks;

use BadMethodCallException;
use Closure;
use Illuminate\Contracts\Container\Container;
use Illuminate\Pipeline\Pipeline;
use InvalidArgumentException;
use RuntimeException;
use Throwable;

class HooksPipeline extends Pipeline
{
    protected ?Closure $pipeStartCallback = null;

    protected ?Closure $pipeEndCallback = null;

    public function __construct(?Container $container, protected string $hook)
    {
        parent::__construct($container);
    }

    public function withPipeStartCallback(Closure $callback): self
    {
        $this->pipeStartCallback = $callback;

        return $this;
    }

    public function withPipeEndCallback(Closure $callback): self
    {
        $this->pipeEndCallback = $callback;

        return $this;
    }

    /**
     * Get a Closure that represents a slice of the application onion.
     */
    protected function carry(): Closure
    {
        return fn ($stack, $pipe) => function ($passable) use ($stack, $pipe) {
            try {
                if (is_callable($pipe)) {
                    // If the pipe is a callable, then we will call it directly, but otherwise we
                    // will resolve the pipes out of the dependency container and call it with
                    // the appropriate method and arguments, returning the results back out.
                    return $pipe($passable, $stack);
                }

                if (!is_object($pipe)) {
                    if (!is_string($pipe)) {
                        throw new InvalidArgumentException(sprintf(
                            'A hook pipe must be a class name string or object, %s given.',
                            gettype($pipe)
                        ));
                    }

                    $hookParameters = (array) config('git-hooks.'.$this->hook.'.'.$pipe);

                    // If the pipe is a string we will parse the string and resolve the class out
                    // of the dependency injection container. We can then build a callable and
                    // execute the pipe function giving in the parameters that are required.
                    $pipe = $this->getContainer()->make($pipe, ['parameters' => $hookParameters]);

                    if (!is_object($pipe)) {
                        throw new RuntimeException('Container::make() did not return an object.');
                    }

                    $this->handlePipeEnd(true);

                    if ($this->pipeStartCallback) {
                        call_user_func_array($this->pipeStartCallback, [$pipe]);
                    }
                }

                $parameters = [$passable, $stack];

                if (method_exists($pipe, $this->method)) {
                    $carry = $pipe->{$this->method}(...$parameters);
                } else {
                    if (!is_callable($pipe)) {
                        throw new BadMethodCallException(sprintf(
                            'Hook pipe [%s] does not implement method [%s] and is not invokable.',
                            $pipe::class,
                            $this->method
                        ));
                    }

                    $carry = $pipe(...$parameters);
                }

                return $this->handleCarry($carry);
            } catch (Throwable $e) {
                return $this->handleException($passable, $e);
            }
        };
    }

    /**
     * Handle the call back call once a specific pipe has finished or errored.
     */
    protected function handlePipeEnd(bool $success): void
    {
        if ($this->pipeEndCallback) {
            call_user_func_array($this->pipeEndCallback, [$success]);
        }
    }

    /**
     * Handle the value returned from each pipe before passing it to the next.
     */
    protected function handleCarry(mixed $carry): mixed
    {
        $this->handlePipeEnd(true);

        return $carry;
    }

    /**
     * Handle the given exception.
     *
     * @throws Throwable
     */
    protected function handleException(mixed $passable, Throwable $e): mixed
    {
        $this->handlePipeEnd(false);

        throw $e;
    }
}
