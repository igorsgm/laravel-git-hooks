<?php

namespace Igorsgm\GitHooks\Tests\Fixtures;

class ClassWithRectorIssues
{
    const CONSTANT = 3;

    protected function test()
    {
        $a = [];

        return $a;
    }
}
