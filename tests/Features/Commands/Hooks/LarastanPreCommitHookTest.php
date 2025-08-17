<?php

use Igorsgm\GitHooks\Console\Commands\Hooks\LarastanPreCommitHook;
use Igorsgm\GitHooks\Facades\GitHooks;

beforeEach(
    function () {
        $this->gitInit();
        $this->initializeTempDirectory(base_path('temp'));

        copy(__DIR__.'/../../../Fixtures/fake-tool', __DIR__.'/../../../Fixtures/fake-Larastan');
        copy(__DIR__.'/../../../Fixtures/fake-tool.bat', __DIR__.'/../../../Fixtures/fake-Larastan.bat');

        // Always start clean
        @unlink(__DIR__.'/../../../Fixtures/.Larastan_mode');

        // Force Terminal::hasSttyAvailable() to return true
        $reflection = new ReflectionClass(\Symfony\Component\Console\Terminal::class);
        $property = $reflection->getProperty('stty');
        $property->setAccessible(true);
        $property->setValue(true); // force stty as available
    }
);

afterEach(
    function () {
        @unlink(__DIR__.'/../../../Fixtures/.Larastan_mode');
        @unlink(__DIR__.'/../../../Fixtures/fake-Larastan');
        @unlink(__DIR__.'/../../../Fixtures/fake-Larastan.bat');
    }
);

test(
    'Fails commit when Larastan is not passing',
    function ($larastanConfiguration) {
        $this->config->set('git-hooks.code_analyzers.larastan', $larastanConfiguration);
        $this->config->set(
            'git-hooks.pre-commit', [
                LarastanPreCommitHook::class,
            ]
        );

        $this->makeTempFile(
            'ClassWithFixableIssues.php',
            file_get_contents(__DIR__.'/../../../Fixtures/ClassWithFixableIssues.php')
        );

        GitHooks::shouldReceive('isMergeInProgress')->andReturn(false);
        GitHooks::shouldReceive('getListOfChangedFiles')->andReturn(
            implode(
                PHP_EOL, [
                    'AM temp/ClassWithFixableIssues.php',
                ]
            )
        );

        $this->artisan('git-hooks:pre-commit')
            ->expectsOutputToContain('Larastan Failed')
            ->expectsOutputToContain('COMMIT FAILED')
            ->assertExitCode(1);
    }
)->with('larastanConfiguration');

test(
    'Commit passes when Larastan check is OK', function ($larastanConfiguration) {
        $this->config->set('git-hooks.code_analyzers.larastan', $larastanConfiguration);
        $this->config->set(
            'git-hooks.pre-commit', [
                LarastanPreCommitHook::class,
            ]
        );

        $tempFileName = 'ClassWithoutFixableIssues.php';
        $this->makeTempFile(
            $tempFileName,
            file_get_contents(__DIR__.'/../../../Fixtures/'.$tempFileName)
        );

        GitHooks::shouldReceive('isMergeInProgress')->andReturn(false);
        GitHooks::shouldReceive('getListOfChangedFiles')->andReturn(
            implode(
                PHP_EOL, [
                    'AM temp/'.$tempFileName,
                ]
            )
        );

        // Switch to success
        touch(__DIR__.'/../../../Fixtures/.Larastan_mode');

        $this->artisan('git-hooks:pre-commit')
            ->doesntExpectOutputToContain('Larastan Failed')
            ->assertSuccessful();
    }
)->with('larastanConfiguration');
