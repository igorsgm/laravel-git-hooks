<?php

declare(strict_types=1);

namespace Igorsgm\GitHooks\Tests\Fixtures;

class ClassWithRectorIssues
{
    public const CONSTANT = 3;

    protected function test()
    {
        $a = [];

        if (empty($a)) {
            $a = ['string', 'another string'];
        }

        return $a;
    }
}
