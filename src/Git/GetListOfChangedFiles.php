<?php

namespace Igorsgm\LaravelGitHooks\Git;

use Igorsgm\LaravelGitHooks\Contracts\GitCommand;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class GetListOfChangedFiles implements GitCommand
{
    /**
     * {@inheritDoc}
     */
    public function exec(): Process
    {
        $process = new Process(['git', 'status', '--short']);
        $process->run();

        // executes after the command finishes
        if (! $process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        return $process;
    }
}
