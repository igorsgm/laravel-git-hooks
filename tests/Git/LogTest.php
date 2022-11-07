<?php

namespace Igorsgm\GitHooks\Tests\Git;

use Igorsgm\GitHooks\Git\Log;
use Igorsgm\GitHooks\Tests\TestCase;

class LogTest extends TestCase
{
    public function test_parse_log_from_console()
    {
        $log = new Log(<<<'EOL'
commit bfdc6c406626223bf3cbb65b8d269f7b65ca0570 (HEAD -> feature/1, origin/feature/1)
Author: Igor Moraes <igor.sgm@gmail.com>
Date:   Tue Feb 18 12:01:15 2020 +0300

    Added PreCommit hooks.

    Added docs for `pre-commit`, `prepare-commit-msg`, `commit-msg`

    fixed #2
EOL
        );

        $this->assertEquals('Igor Moraes <igor.sgm@gmail.com>', $log->getAuthor());
        $this->assertEquals('2020-02-18 12:01:15', $log->getDate()->toDateTimeString());
        $this->assertEquals('bfdc6c406626223bf3cbb65b8d269f7b65ca0570', $log->getHash());
        $this->assertEquals(<<<'EOL'
Added PreCommit hooks.
Added docs for `pre-commit`, `prepare-commit-msg`, `commit-msg`
fixed #2

EOL
            , $log->getMessage());
    }
}
