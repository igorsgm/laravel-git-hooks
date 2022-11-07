<?php

namespace Igorsgm\GitHooks\Contracts;

use Symfony\Component\Process\Process;

interface GitCommand
{
    /**
     * Execute command and return output
     *
     * @return Process
     */
    public function exec(): Process;
}
