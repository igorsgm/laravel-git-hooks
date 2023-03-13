<?php

namespace Igorsgm\GitHooks\Contracts;

interface CodeAnalyzerPreCommitHook extends PreCommitHook
{
    /**
     * Returns the command to run the code analyzer/tester.
     * i.e: Command to execute PHPCS
     */
    public function analyzerCommand(): string;

    /**
     * Returns the command to run the code fixer.
     * i.e: Command to execute PHPCBF
     */
    public function fixerCommand(): string;
}
