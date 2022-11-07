<?php

namespace Igorsgm\GitHooks\Tests;

use Igorsgm\GitHooks\Contracts\CommitMessageStorage;
use Igorsgm\GitHooks\GitHooks;
use Igorsgm\GitHooks\Tests\Traits\WithTmpFiles;
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
     * @return GitHooks|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    protected function makeGitHooks()
    {
        return Mockery::mock(GitHooks::class);
    }

    /**
     * @return CommitMessageStorage|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    protected function makeCommitMessageStorage()
    {
        return Mockery::mock(CommitMessageStorage::class);
    }
}
