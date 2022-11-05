<?php

namespace Igorsgm\LaravelGitHooks\Tests;

use Igorsgm\LaravelGitHooks\Configurator;
use Igorsgm\LaravelGitHooks\Contracts\HookStorage;
use Mockery;

class ConfiguratorTest extends TestCase
{
    public function test_files_for_hooks_should_be_created()
    {
        $storage = Mockery::mock(HookStorage::class);
        $app = $this->makeApplication();

        $app->allows('basePath')->andReturnUsing(function ($path = null) {
            return $path;
        });

        $storage->allows('store')->with('.git/hooks/pre-commit', <<<'EOL'
#!/bin/sh

php /artisan git-hooks:pre-commit $@ >&2

EOL
        );

        $storage->allows('store')->with('.git/hooks/post-commit', <<<'EOL'
#!/bin/sh

php /artisan git-hooks:post-commit $@ >&2

EOL
        );

        $configurator = new Configurator($app, $storage, [
            'pre-commit',
            'post-commit',
        ]);

        $configurator->run();

        $this->assertTrue(true);
    }
}
