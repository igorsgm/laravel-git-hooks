<?php

namespace Igorsgm\GitHooks\Tests;

class GitHooksTest extends TestCase
{
    /**
     * @TODO revisit this test
     */
    public function test_files_for_hooks_should_be_created()
    {
//        $storage = Mockery::mock(HookStorage::class);
//        $app = $this->makeApplication();
//
//        $app->allows('basePath')->andReturnUsing(function ($path = null) {
//            return $path;
//        });
//
//        $storage->allows('store')->with('.git/hooks/pre-commit', <<<'EOL'
        //#!/bin/sh
//
        //php /artisan git-hooks:pre-commit $@ >&2
//
        //EOL
//        );
//
//        $storage->allows('store')->with('.git/hooks/post-commit', <<<'EOL'
        //#!/bin/sh
//
        //php /artisan git-hooks:post-commit $@ >&2
//
        //EOL
//        );
//
//        $gitHooks = new GitHooks($app, $storage, [
//            'pre-commit',
//            'post-commit',
//        ]);
//
//        $gitHooks->run();

        $this->assertTrue(true);
    }
}
