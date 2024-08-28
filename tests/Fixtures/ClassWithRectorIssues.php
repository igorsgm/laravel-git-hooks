<?php 

namespace Igorsgm\GitHooks\Tests\Fixtures;

class ClassWithRectorIssues
{
    const CONSTANT = 3;

    protected function test()
    {
        $a = [];
        
        if (empty($a)) {
            $a = ["string", 'another string'];
        }

        return $a;
    }
}
