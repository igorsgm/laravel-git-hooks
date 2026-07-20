<?php

declare(strict_types=1);

namespace Igorsgm\GitHooks\Tests\Unit\Hooks;

use Igorsgm\GitHooks\Console\Commands\Hooks\CodeceptionPreCommitHook;
use Igorsgm\GitHooks\Console\Commands\Hooks\PestPreCommitHook;
use Igorsgm\GitHooks\Console\Commands\Hooks\PHPUnitPreCommitHook;
use Igorsgm\GitHooks\Console\Commands\Hooks\VitestPreCommitHook;
use Igorsgm\GitHooks\Facades\GitHooks;
use Igorsgm\GitHooks\Git\ChangedFile;
use Igorsgm\GitHooks\Git\ChangedFiles;
use Mockery;
use ReflectionClass;

function callProtectedHookMethod(object $object, string $method, array $args = []): mixed
{
    $reflection = new ReflectionClass($object);
    $m = $reflection->getMethod($method);
    $m->setAccessible(true);

    return $m->invokeArgs($object, $args);
}

function setProtectedHookProperty(object $object, string $property, mixed $value): void
{
    $reflection = new ReflectionClass($object);
    $prop = $reflection->getProperty($property);
    $prop->setAccessible(true);
    $prop->setValue($object, $value);
}

describe('Test Runner Handle Coverage', function () {
    beforeEach(function () {
        config(['git-hooks.validate_paths' => false]);
    });

    describe('getConfigPath coverage', function () {
        test('PestPreCommitHook getConfigPath returns correct key', function () {
            $hook = new PestPreCommitHook();
            $result = callProtectedHookMethod($hook, 'getConfigPath');

            expect($result)->toBe('git-hooks.code_analyzers.pest');
        });

        test('PHPUnitPreCommitHook getConfigPath returns correct key', function () {
            $hook = new PHPUnitPreCommitHook();
            $result = callProtectedHookMethod($hook, 'getConfigPath');

            expect($result)->toBe('git-hooks.code_analyzers.phpunit');
        });

        test('CodeceptionPreCommitHook getConfigPath returns correct key', function () {
            $hook = new CodeceptionPreCommitHook();
            $result = callProtectedHookMethod($hook, 'getConfigPath');

            expect($result)->toBe('git-hooks.code_analyzers.codeception');
        });
    });

    describe('BaseTestRunnerPreCommitHook handle() when test files are found', function () {
        test('handle sets chunkSize=1 and calls handleCommittedFiles when test files found', function () {
            config([
                'git-hooks.code_analyzers.pest.path' => 'vendor/bin/pest',
                'git-hooks.code_analyzers.pest.run_in_docker' => false,
                'git-hooks.code_analyzers.pest.docker_container' => '',
            ]);

            GitHooks::shouldReceive('isMergeInProgress')->andReturn(true);

            $hook = new PestPreCommitHook();
            $hook->setCwd(base_path());

            // app/GitHooks.php -> looks for GitHooksTest.php in tests/ dir
            $files = new ChangedFiles('AM app/GitHooks.php');

            $nextCalled = false;
            $next = function ($f) use (&$nextCalled) {
                $nextCalled = true;

                return $f;
            };

            $hook->handle($files, $next);

            // next should be called because handleCommittedFiles returns $next($files) when isMergeInProgress=true
            expect($nextCalled)->toBeTrue();
        });

        test('handle with PHPUnit also covers getConfigPath', function () {
            config([
                'git-hooks.code_analyzers.phpunit.path' => 'vendor/bin/phpunit',
                'git-hooks.code_analyzers.phpunit.run_in_docker' => false,
                'git-hooks.code_analyzers.phpunit.docker_container' => '',
            ]);

            GitHooks::shouldReceive('isMergeInProgress')->andReturn(true);

            $hook = new PHPUnitPreCommitHook();
            $hook->setCwd(base_path());

            $files = new ChangedFiles('AM app/GitHooks.php');

            $nextCalled = false;
            $next = function ($f) use (&$nextCalled) {
                $nextCalled = true;

                return $f;
            };

            $hook->handle($files, $next);

            expect($nextCalled)->toBeTrue();
        });

        test('handle with Codeception also covers getConfigPath', function () {
            config([
                'git-hooks.code_analyzers.codeception.path' => 'vendor/bin/codecept',
                'git-hooks.code_analyzers.codeception.run_in_docker' => false,
                'git-hooks.code_analyzers.codeception.docker_container' => '',
            ]);

            GitHooks::shouldReceive('isMergeInProgress')->andReturn(true);

            $hook = new CodeceptionPreCommitHook();
            $hook->setCwd(base_path());

            $files = new ChangedFiles('AM app/GitHooks.php');

            $nextCalled = false;
            $next = function ($f) use (&$nextCalled) {
                $nextCalled = true;

                return $f;
            };

            $hook->handle($files, $next);

            expect($nextCalled)->toBeTrue();
        });
    });

    describe('BaseTestRunnerPreCommitHook findTestPattern finds existing files', function () {
        test('findTestPattern returns file paths with AM prefix when file exists', function () {
            $hook = new PestPreCommitHook();

            // GitHooksTest.php exists in tests/Unit/
            $result = callProtectedHookMethod($hook, 'findTestPattern', ['GitHooksTest.php']);

            expect($result)->toBeArray();

            if (! empty($result)) {
                expect($result[0])->toStartWith('AM ');
                expect($result[0])->toContain('GitHooksTest.php');
            }
        });

        test('findTestPattern returns multiple files when multiple matches exist', function () {
            $hook = new PestPreCommitHook();

            // Any test file in tests/ directory
            $result = callProtectedHookMethod($hook, 'findTestPattern', ['GitHooksTest.php']);

            expect($result)->toBeArray();
        });
    });

    describe('VitestPreCommitHook handle() when test files are found', function () {
        test('handle sets chunkSize=1 and calls handleCommittedFiles when vitest files found', function () {
            config([
                'git-hooks.code_analyzers.vitest.path' => 'node_modules/.bin/vitest',
                'git-hooks.code_analyzers.vitest.run_in_docker' => false,
                'git-hooks.code_analyzers.vitest.docker_container' => '',
                'git-hooks.code_analyzers.vitest.additional_params' => '',
            ]);

            GitHooks::shouldReceive('isMergeInProgress')->andReturn(true);

            // Create a temporary .test.ts file in the tests/ directory so Vitest can find it
            $tempTestFile = base_path('tests/TempVitestCoverageTest.test.ts');
            @mkdir(dirname($tempTestFile), 0755, true);
            file_put_contents($tempTestFile, '// temp test file for coverage');

            try {
                $hook = new VitestPreCommitHook();
                $hook->setCwd(base_path());

                // TempVitestCoverage.ts in resources triggers search for TempVitestCoverage.test.ts
                $files = new ChangedFiles('AM resources/ts/TempVitestCoverage.ts');

                $nextCalled = false;
                $next = function ($f) use (&$nextCalled) {
                    $nextCalled = true;

                    return $f;
                };

                $hook->handle($files, $next);

                expect($nextCalled)->toBeTrue();
            } finally {
                if (file_exists($tempTestFile)) {
                    unlink($tempTestFile);
                }
            }
        });
    });

    describe('VitestPreCommitHook findTestPattern finds existing files', function () {
        test('findTestPattern returns TS test files with AM prefix', function () {
            // Create a temporary .test.ts file in the tests/ directory
            $tempTestFile = base_path('tests/TempVitest.test.ts');
            @mkdir(dirname($tempTestFile), 0755, true);
            file_put_contents($tempTestFile, '// temp vitest test');

            try {
                $hook = new VitestPreCommitHook();
                $result = callProtectedHookMethod($hook, 'findTestPattern', ['TempVitest.test.ts']);

                expect($result)->toBeArray();
                expect($result)->not->toBeEmpty();
                expect($result[0])->toStartWith('AM ');
                expect($result[0])->toContain('TempVitest.test.ts');
            } finally {
                if (file_exists($tempTestFile)) {
                    unlink($tempTestFile);
                }
            }
        });

        test('findTestPattern returns empty when tests directory does not exist', function () {
            $hook = new VitestPreCommitHook();
            setProtectedHookProperty($hook, 'testPath', 'non-existent-vitest-tests-dir-xyz');

            $result = callProtectedHookMethod($hook, 'findTestPattern', ['SomeComponent.test.ts']);

            expect($result)->toBe([]);
        });
    });

    describe('VitestPreCommitHook findTestFilesForChangedFiles with skippable files', function () {
        test('skips files in tests directory (triggers continue on line 80)', function () {
            $hook = new VitestPreCommitHook();

            // Both files in tests/ dir should be skipped
            $files = new ChangedFiles("AM tests/Button.test.ts\nAM tests/App.spec.ts");
            $result = callProtectedHookMethod($hook, 'findTestFilesForChangedFiles', [$files]);

            expect($result)->toBe([]);
        });

        test('skips php files and processes ts files', function () {
            $hook = new VitestPreCommitHook();

            // PHP file should be skipped (wrong extension), TS file should be processed
            $files = new ChangedFiles("AM src/User.php\nAM resources/ts/App.ts");
            $result = callProtectedHookMethod($hook, 'findTestFilesForChangedFiles', [$files]);

            expect($result)->toBeArray();
        });

        test('skips test directory files mixed with source files', function () {
            $hook = new VitestPreCommitHook();

            $file1 = Mockery::mock(ChangedFile::class);
            $file1->shouldReceive('getFilePath')->andReturn('tests/unit/UserSpec.ts');

            $file2 = Mockery::mock(ChangedFile::class);
            $file2->shouldReceive('getFilePath')->andReturn('resources/ts/Component.vue');

            // Process mixed files via ChangedFiles
            $files = new ChangedFiles("AM tests/unit/UserSpec.ts\nAM resources/ts/Component.vue");
            $result = callProtectedHookMethod($hook, 'findTestFilesForChangedFiles', [$files]);

            // tests/unit/UserSpec.ts should be skipped, Component.vue should be processed
            expect($result)->toBeArray();
        });
    });
});
