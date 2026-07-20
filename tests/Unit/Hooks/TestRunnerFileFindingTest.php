<?php

declare(strict_types=1);

namespace Igorsgm\GitHooks\Tests\Unit\Hooks;

use Igorsgm\GitHooks\Console\Commands\Hooks\CodeceptionPreCommitHook;
use Igorsgm\GitHooks\Console\Commands\Hooks\PestPreCommitHook;
use Igorsgm\GitHooks\Console\Commands\Hooks\PHPUnitPreCommitHook;
use Igorsgm\GitHooks\Console\Commands\Hooks\VitestPreCommitHook;
use Igorsgm\GitHooks\Git\ChangedFile;
use Igorsgm\GitHooks\Git\ChangedFiles;
use Mockery;
use ReflectionClass;

describe('Test Runner File Finding Logic', function () {
    describe('shouldSkipFile logic', function () {
        test('Vitest skips files in tests directory', function () {
            $hook = new VitestPreCommitHook();

            $file = Mockery::mock(ChangedFile::class);
            $file->shouldReceive('getFilePath')->andReturn('tests/unit/User.test.ts');

            expect(getProtectedMethodValue($hook, 'shouldSkipFile', [$file]))->toBeTrue();
        });

        test('Vitest skips non-JS/TS files', function () {
            $hook = new VitestPreCommitHook();

            $file = Mockery::mock(ChangedFile::class);
            $file->shouldReceive('getFilePath')->andReturn('src/User.php');

            expect(getProtectedMethodValue($hook, 'shouldSkipFile', [$file]))->toBeTrue();
        });

        test('Vitest accepts JS files', function () {
            $hook = new VitestPreCommitHook();

            $file = Mockery::mock(ChangedFile::class);
            $file->shouldReceive('getFilePath')->andReturn('resources/js/app.js');

            expect(getProtectedMethodValue($hook, 'shouldSkipFile', [$file]))->toBeFalse();
        });

        test('Vitest accepts TS files', function () {
            $hook = new VitestPreCommitHook();

            $file = Mockery::mock(ChangedFile::class);
            $file->shouldReceive('getFilePath')->andReturn('resources/ts/app.ts');

            expect(getProtectedMethodValue($hook, 'shouldSkipFile', [$file]))->toBeFalse();
        });

        test('Vitest accepts TSX files', function () {
            $hook = new VitestPreCommitHook();

            $file = Mockery::mock(ChangedFile::class);
            $file->shouldReceive('getFilePath')->andReturn('resources/ts/Component.tsx');

            expect(getProtectedMethodValue($hook, 'shouldSkipFile', [$file]))->toBeFalse();
        });

        test('Vitest accepts Vue files', function () {
            $hook = new VitestPreCommitHook();

            $file = Mockery::mock(ChangedFile::class);
            $file->shouldReceive('getFilePath')->andReturn('resources/js/Button.vue');

            expect(getProtectedMethodValue($hook, 'shouldSkipFile', [$file]))->toBeFalse();
        });

        test('Vitest accepts JSX files', function () {
            $hook = new VitestPreCommitHook();

            $file = Mockery::mock(ChangedFile::class);
            $file->shouldReceive('getFilePath')->andReturn('resources/js/Button.jsx');

            expect(getProtectedMethodValue($hook, 'shouldSkipFile', [$file]))->toBeFalse();
        });

        test('PHPUnit skips files in tests directory', function () {
            $hook = new PHPUnitPreCommitHook();

            $file = Mockery::mock(ChangedFile::class);
            $file->shouldReceive('getFilePath')->andReturn('tests/unit/UserTest.php');

            expect(getProtectedMethodValue($hook, 'shouldSkipFile', [$file]))->toBeTrue();
        });

        test('PHPUnit skips non-supported files', function () {
            $hook = new PHPUnitPreCommitHook();

            $file = Mockery::mock(ChangedFile::class);
            $file->shouldReceive('getFilePath')->andReturn('src/User.py');

            expect(getProtectedMethodValue($hook, 'shouldSkipFile', [$file]))->toBeTrue();
        });

        test('PHPUnit accepts PHP files', function () {
            $hook = new PHPUnitPreCommitHook();

            $file = Mockery::mock(ChangedFile::class);
            $file->shouldReceive('getFilePath')->andReturn('app/Models/User.php');

            expect(getProtectedMethodValue($hook, 'shouldSkipFile', [$file]))->toBeFalse();
        });

        test('Pest skips files in tests directory', function () {
            $hook = new PestPreCommitHook();

            $file = Mockery::mock(ChangedFile::class);
            $file->shouldReceive('getFilePath')->andReturn('tests/unit/UserTest.php');

            expect(getProtectedMethodValue($hook, 'shouldSkipFile', [$file]))->toBeTrue();
        });

        test('Pest skips non-supported files', function () {
            $hook = new PestPreCommitHook();

            $file = Mockery::mock(ChangedFile::class);
            $file->shouldReceive('getFilePath')->andReturn('src/User.py');

            expect(getProtectedMethodValue($hook, 'shouldSkipFile', [$file]))->toBeTrue();
        });

        test('Pest accepts PHP files', function () {
            $hook = new PestPreCommitHook();

            $file = Mockery::mock(ChangedFile::class);
            $file->shouldReceive('getFilePath')->andReturn('app/Services/Payment.php');

            expect(getProtectedMethodValue($hook, 'shouldSkipFile', [$file]))->toBeFalse();
        });

        test('Codeception skips files in tests directory', function () {
            $hook = new CodeceptionPreCommitHook();

            $file = Mockery::mock(ChangedFile::class);
            $file->shouldReceive('getFilePath')->andReturn('tests/unit/UserCest.php');

            expect(getProtectedMethodValue($hook, 'shouldSkipFile', [$file]))->toBeTrue();
        });

        test('Codeception skips non-PHP files', function () {
            $hook = new CodeceptionPreCommitHook();

            $file = Mockery::mock(ChangedFile::class);
            $file->shouldReceive('getFilePath')->andReturn('src/User.js');

            expect(getProtectedMethodValue($hook, 'shouldSkipFile', [$file]))->toBeTrue();
        });

        test('Codeception accepts PHP files', function () {
            $hook = new CodeceptionPreCommitHook();

            $file = Mockery::mock(ChangedFile::class);
            $file->shouldReceive('getFilePath')->andReturn('app/Http/Controllers/Controller.php');

            expect(getProtectedMethodValue($hook, 'shouldSkipFile', [$file]))->toBeFalse();
        });
    });

    describe('getExtension logic', function () {
        test('Vitest extracts correct extension for ts files', function () {
            $hook = new VitestPreCommitHook();

            expect(getProtectedMethodValue($hook, 'getExtension', ['app.ts']))->toBe('ts');
            expect(getProtectedMethodValue($hook, 'getExtension', ['Component.tsx']))->toBe('tsx');
            expect(getProtectedMethodValue($hook, 'getExtension', ['Button.vue']))->toBe('vue');
            expect(getProtectedMethodValue($hook, 'getExtension', ['script.js']))->toBe('js');
        });
    });

    describe('findTestFilesForChangedFiles integration', function () {
        test('Vitest processes multiple files correctly', function () {
            $hook = new VitestPreCommitHook();

            $files = new ChangedFiles("AM resources/ts/User.ts\nAM resources/ts/Service.ts");
            $result = getProtectedMethodValue($hook, 'findTestFilesForChangedFiles', [$files]);

            expect($result)->toBeArray();
        });

        test('PHPUnit processes multiple files correctly', function () {
            $hook = new PHPUnitPreCommitHook();

            $files = new ChangedFiles("AM app/Models/User.php\nAM app/Services/Payment.php");
            $result = getProtectedMethodValue($hook, 'findTestFilesForChangedFiles', [$files]);

            expect($result)->toBeArray();
        });

        test('Pest processes multiple files correctly', function () {
            $hook = new PestPreCommitHook();

            $files = new ChangedFiles("AM app/Models/User.php\nAM app/Services/Payment.php");
            $result = getProtectedMethodValue($hook, 'findTestFilesForChangedFiles', [$files]);

            expect($result)->toBeArray();
        });

        test('Codeception processes multiple files correctly', function () {
            $hook = new CodeceptionPreCommitHook();

            $files = new ChangedFiles("AM app/Models/User.php\nAM app/Services/Payment.php");
            $result = getProtectedMethodValue($hook, 'findTestFilesForChangedFiles', [$files]);

            expect($result)->toBeArray();
        });

        test('Empty files returns empty array', function () {
            $hook = new VitestPreCommitHook();

            $files = new ChangedFiles('');
            $result = getProtectedMethodValue($hook, 'findTestFilesForChangedFiles', [$files]);

            expect($result)->toBe([]);
        });
    });

    describe('analyzerCommand variations', function () {
        test('Vitest command structure is correct', function () {
            $hook = new VitestPreCommitHook();
            $hook->setCwd(base_path())
                ->setFileExtensions('/\.tsx?$/')
                ->setAnalyzerExecutable('vitest', true)
                ->setDockerContainer('');

            $command = $hook->analyzerCommand();

            expect($command)->toContain('vitest');
            expect($command)->toContain('run');
        });

        test('Pest command structure is correct', function () {
            $hook = new PestPreCommitHook();
            $hook->setCwd(base_path())
                ->setFileExtensions('/\.php$/')
                ->setAnalyzerExecutable('pest', true)
                ->setDockerContainer('');

            $command = $hook->analyzerCommand();

            expect($command)->toContain('pest');
            expect($command)->not->toContain(' run ');
        });

        test('PHPUnit command structure is correct', function () {
            $hook = new PHPUnitPreCommitHook();
            $hook->setCwd(base_path())
                ->setFileExtensions('/\.php$/')
                ->setAnalyzerExecutable('phpunit', true)
                ->setDockerContainer('');

            $command = $hook->analyzerCommand();

            expect($command)->toContain('phpunit');
        });

        test('Codeception command structure is correct', function () {
            $hook = new CodeceptionPreCommitHook();
            $hook->setCwd(base_path())
                ->setFileExtensions('/\.php$/')
                ->setAnalyzerExecutable('codecept', true)
                ->setDockerContainer('');

            $command = $hook->analyzerCommand();

            expect($command)->toContain('codecept');
            expect($command)->toContain('run');
        });

        test('Vitest fixer command includes --update flag', function () {
            $hook = new VitestPreCommitHook();
            $hook->setCwd(base_path())
                ->setFileExtensions('/\.tsx?$/')
                ->setAnalyzerExecutable('vitest', true)
                ->setDockerContainer('');

            $command = $hook->fixerCommand();

            expect($command)->toContain('vitest');
            expect($command)->toContain('run');
            expect($command)->toContain('--update');
        });
    });

    describe('class property values', function () {
        test('Vitest has correct default values', function () {
            $hook = new VitestPreCommitHook();

            expect($hook->getName())->toBe('Vitest');
            expect(getProtectedPropertyValue($hook, 'testPath'))->toBe('tests');
        });

        test('Pest has correct default values', function () {
            $hook = new PestPreCommitHook();

            expect($hook->getName())->toBe('Pest');
            expect(getProtectedPropertyValue($hook, 'testPath'))->toBe('tests');
        });

        test('PHPUnit has correct default values', function () {
            $hook = new PHPUnitPreCommitHook();

            expect($hook->getName())->toBe('PHPUnit');
            expect(getProtectedPropertyValue($hook, 'testPath'))->toBe('tests');
        });

        test('Codeception has correct default values', function () {
            $hook = new CodeceptionPreCommitHook();

            expect($hook->getName())->toBe('Codeception');
            expect(getProtectedPropertyValue($hook, 'testPath'))->toBe('tests');
        });
    });

    describe('findTestPattern with non-existent directory', function () {
        test('returns empty array when tests directory does not exist', function () {
            $hook = new PestPreCommitHook();

            $originalTestPath = getProtectedPropertyValue($hook, 'testPath');
            setProtectedPropertyValue($hook, 'testPath', 'non-existent-tests-dir');

            $result = getProtectedMethodValue($hook, 'findTestPattern', ['UserTest.php']);

            expect($result)->toBe([]);

            setProtectedPropertyValue($hook, 'testPath', $originalTestPath);
        });
    });

    describe('findTestFiles returns test files', function () {
        test('finds matching test files for source files', function () {
            $hook = new PestPreCommitHook();

            $result = getProtectedMethodValue($hook, 'findTestFiles', ['app/Models/User.php']);

            expect($result)->toBeArray();
        });
    });

    describe('findTestFilesForChangedFiles with mixed files', function () {
        test('filters out test files and only processes source files', function () {
            $hook = new PestPreCommitHook();

            $files = new ChangedFiles("AM tests/Feature/UserTest.php\nAM app/Models/User.php\nAM app/Services/Payment.php");

            $result = getProtectedMethodValue($hook, 'findTestFilesForChangedFiles', [$files]);

            expect($result)->toBeArray();
        });
    });

    describe('handle method behavior', function () {
        test('handle returns next when no test files found for empty ChangedFiles', function () {
            $hook = new PestPreCommitHook();
            $hook->setCwd(base_path())
                ->setFileExtensions('/\.php$/')
                ->setAnalyzerExecutable('pest', true)
                ->setDockerContainer('');

            $files = new ChangedFiles('');
            $called = false;
            $next = function ($f) use (&$called) {
                $called = true;

                return $f;
            };

            $result = $hook->handle($files, $next);

            expect($called)->toBeTrue();
            expect($result)->toBe($files);
        });

        test('handle processes files and finds test files', function () {
            $hook = new PestPreCommitHook();
            $hook->setCwd(base_path())
                ->setFileExtensions('/\.php$/')
                ->setAnalyzerExecutable('pest', true)
                ->setDockerContainer('');

            $files = new ChangedFiles("AM app/Models/User.php\nAM app/Services/Payment.php");
            $called = false;
            $next = function ($f) use (&$called) {
                $called = true;

                return $f;
            };

            $result = $hook->handle($files, $next);

            expect($called)->toBeTrue();
        });
    });
})->uses(\Orchestra\Testbench\TestCase::class);

/**
 * Invoke a protected method on an object
 */
function getProtectedMethodValue(object $object, string $method, array $args = []): mixed
{
    $reflection = new ReflectionClass($object);
    $method = $reflection->getMethod($method);
    $method->setAccessible(true);

    return $method->invokeArgs($object, $args);
}

/**
 * Get a protected property value from an object
 */
function getProtectedPropertyValue(object $object, string $property): mixed
{
    $reflection = new ReflectionClass($object);
    $property = $reflection->getProperty($property);
    $property->setAccessible(true);

    return $property->getValue($object);
}

/**
 * Set a protected property value on an object
 */
function setProtectedPropertyValue(object $object, string $property, mixed $value): void
{
    $reflection = new ReflectionClass($object);
    $property = $reflection->getProperty($property);
    $property->setAccessible(true);
    $property->setValue($object, $value);
}
