<?php

declare(strict_types=1);

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
use Igorsgm\GitHooks\Traits\GitHelper;

uses(TestCase::class)->in(__DIR__);
uses(GitHelper::class)->in(__DIR__.'/Features/Hooks');

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

expect()->extend('toBeOne', fn () => $this->toBe(1));

expect()->extend('toContainHookArtisanCommand', function ($hookName) {
    $this->value = file_get_contents($this->value);
    $artisanCommand = sprintf('php %s git-hooks:%s $@ >&2', config('git-hooks.artisan_path'), $hookName);

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
