<?php

namespace Igorsgm\GitHooks\Tests\Fixtures;

use Closure;
use Igorsgm\GitHooks\Contracts\MessageHook;
use Illuminate\Console\Command;

class CommitMessageFixtureHook4 implements MessageHook
{
    /**
     * @var array
     */
    protected $parameters;

    public function __construct(array $parameters)
    {
        $this->parameters = $parameters;
    }

    /**
     * {@inheritDoc}
     */
    public function handle(\Igorsgm\GitHooks\Git\CommitMessage $message, Closure $next): mixed
    {
        $message->setMessage($message->getMessage().' '.$this->parameters['param1'].' '.$this->parameters['param2']);

        return $next($message);
    }

    /**
     * {@inheritDoc}
     */
    public function getName(): string
    {
        return 'hook 4';
    }

    /**
     * {@inheritDoc}
     */
    public function setCommand(Command $command): void
    {
        // nothing to do
    }
}
