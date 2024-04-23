<?php

namespace Igorsgm\GitHooks\Tests\Fixtures;

use Closure;
use Igorsgm\GitHooks\Contracts\MessageHook;
use Illuminate\Console\Command;

class CommitMessageFixtureHook2 implements MessageHook
{
    /**
     * {@inheritDoc}
     */
    public function handle(\Igorsgm\GitHooks\Git\CommitMessage $message, Closure $next): mixed
    {
        $message->setMessage($message->getMessage().' hook2');

        return $next($message);
    }

    /**
     * {@inheritDoc}
     */
    public function getName(): string
    {
        return 'Commit Message Test Hook 2';
    }

    /**
     * {@inheritDoc}
     */
    public function setCommand(Command $command): void
    {
        // nothing to do
    }
}
