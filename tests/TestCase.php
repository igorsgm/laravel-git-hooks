<?php

namespace Igorsgm\LaravelGitHooks\Tests;

use Igorsgm\LaravelGitHooks\Contracts\CommitMessageStorage;
use Igorsgm\LaravelGitHooks\LaravelGitHooks;
use Igorsgm\LaravelGitHooks\Tests\Concerns\WithTmpFiles;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Foundation\Application;
use Mockery;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    /**
     * @var array
     */
    protected $tearDownCallbacks = [];

    protected function tearDown(): void
    {
        parent::tearDown();

        Mockery::close();

        $this->callTearDownCallbacks();
    }

    protected function setUp(): void
    {
        $this->setUpTraits();

        parent::setUp();
    }

    /**
     * Boot the testing helper traits.
     *
     * @return array
     */
    protected function setUpTraits()
    {
        $uses = array_flip(class_uses_recursive(static::class));

        if (isset($uses[WithTmpFiles::class])) {
            $this->registerTmpTrait();
        }

        return $uses;
    }

    /**
     * Register a callback to be run before the application is destroyed.
     *
     * @param  callable  $callback
     * @return void
     */
    protected function tearDownCallback(callable $callback)
    {
        $this->tearDownCallbacks[] = $callback;
    }

    /**
     * Execute the application's pre-destruction callbacks.
     *
     * @return void
     */
    protected function callTearDownCallbacks()
    {
        foreach ($this->tearDownCallbacks as $callback) {
            $callback();
        }
    }

    /**
     * @return Repository|Mockery\MockInterface
     */
    protected function makeConfig()
    {
        return Mockery::mock(Repository::class);
    }

    /**
     * @return Application|Mockery\MockInterface
     */
    protected function makeApplication()
    {
        return Mockery::mock(Application::class);
    }

    /**
     * @return LaravelGitHooks|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    protected function makeLaravelGitHooks()
    {
        return Mockery::mock(LaravelGitHooks::class);
    }

    /**
     * @return CommitMessageStorage|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    protected function makeCommitMessageStorage()
    {
        return Mockery::mock(CommitMessageStorage::class);
    }
}
