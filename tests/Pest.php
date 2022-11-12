<?php

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "uses()" function to bind a different classes or traits.
|
*/

use Igorsgm\GitHooks\Tests\TestCase;

uses(TestCase::class)->in(__DIR__);

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

expect()->extend('toContainHookArtisanCommand', function ($hookName) {
    $this->value = file_get_contents($this->value);
    $artisanCommand = sprintf('php %s git-hooks:%s $@ >&2', base_path('artisan'), $hookName);

    return $this->toContain($artisanCommand);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function mockCommitHash()
{
    return 'da39a3ee5e6b4b0d3255bfef95601890afd80709';
}

function mockListOfChangedFiles()
{
    return 'AM src/ChangedFiles.php';
}

function mockLastCommitLog()
{
    return sprintf('commit %s
Author: Igor Moraes <igor.sgm@gmail.com>
Date:   Wed Nov 9 04:50:40 2022 -0800

    wip
', mockCommitHash());
}
