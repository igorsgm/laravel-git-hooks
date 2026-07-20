<?php

declare(strict_types=1);

namespace Igorsgm\GitHooks\Tests\Unit\Hooks;

use Igorsgm\GitHooks\Console\Commands\Hooks\CodeceptionPreCommitHook;
use Igorsgm\GitHooks\Console\Commands\Hooks\PHPUnitPreCommitHook;
use Igorsgm\GitHooks\Console\Commands\Hooks\PestPreCommitHook;
use Igorsgm\GitHooks\Console\Commands\Hooks\VitestPreCommitHook;
use Igorsgm\GitHooks\Git\ChangedFile;
use Igorsgm\GitHooks\Git\ChangedFiles;
use Mockery;

describe('Test Runner Hooks', function () {
    describe('PestPreCommitHook', function () {
        test('generates correct analyzer command', function () {
            $hook = new PestPreCommitHook();
            $hook->setCwd(base_path())
                ->setFileExtensions('/\.php$/')
                ->setAnalyzerExecutable('vendor/bin/pest')
                ->setDockerContainer('');

            expect($hook->analyzerCommand())
                ->toContain('vendor/bin/pest')
                ->not->toContain(' run ');
        });

        test('has no fixer command', function () {
            $hook = new PestPreCommitHook();
            $hook->setCwd(base_path())
                ->setFileExtensions('/\.php$/')
                ->setAnalyzerExecutable('vendor/bin/pest')
                ->setDockerContainer('');

            expect($hook->fixerCommand())->toBe('');
        });

        test('has correct name', function () {
            $hook = new PestPreCommitHook();
            expect($hook->getName())->toBe('Pest');
        });

        test('can set and get analyzer executable', function () {
            $hook = new PestPreCommitHook();
            $hook->setAnalyzerExecutable('vendor/bin/pest');

            expect($hook->getAnalyzerExecutable())->toBe('vendor/bin/pest');
        });

        test('generates different commands for different executables', function () {
            $hook1 = new PestPreCommitHook();
            $hook1->setCwd(base_path())
                ->setAnalyzerExecutable('vendor/bin/pest');

            $hook2 = new PestPreCommitHook();
            $hook2->setCwd(base_path())
                ->setAnalyzerExecutable('custom/path/pest');

            expect($hook1->analyzerCommand())->not->toBe($hook2->analyzerCommand());
        });
    });

    describe('PHPUnitPreCommitHook', function () {
        test('generates correct analyzer command', function () {
            $hook = new PHPUnitPreCommitHook();
            $hook->setCwd(base_path())
                ->setFileExtensions('/\.php$/')
                ->setAnalyzerExecutable('vendor/bin/phpunit')
                ->setDockerContainer('');

            $command = $hook->analyzerCommand();
            expect($command)->toContain('vendor/bin/phpunit');
        });

        test('has no fixer command', function () {
            $hook = new PHPUnitPreCommitHook();
            $hook->setCwd(base_path())
                ->setFileExtensions('/\.php$/')
                ->setAnalyzerExecutable('vendor/bin/phpunit')
                ->setDockerContainer('');

            expect($hook->fixerCommand())->toBe('');
        });

        test('has correct name', function () {
            $hook = new PHPUnitPreCommitHook();
            expect($hook->getName())->toBe('PHPUnit');
        });

        test('can set and get analyzer executable', function () {
            $hook = new PHPUnitPreCommitHook();
            $hook->setAnalyzerExecutable('vendor/bin/phpunit');

            expect($hook->getAnalyzerExecutable())->toBe('vendor/bin/phpunit');
        });

        test('generates different commands for different executables', function () {
            $hook1 = new PHPUnitPreCommitHook();
            $hook1->setCwd(base_path())
                ->setAnalyzerExecutable('vendor/bin/phpunit');

            $hook2 = new PHPUnitPreCommitHook();
            $hook2->setCwd(base_path())
                ->setAnalyzerExecutable('phpunit');

            expect($hook1->analyzerCommand())->not->toBe($hook2->analyzerCommand());
        });
    });

    describe('CodeceptionPreCommitHook', function () {
        test('generates correct analyzer command', function () {
            $hook = new CodeceptionPreCommitHook();
            $hook->setCwd(base_path())
                ->setFileExtensions('/\.php$/')
                ->setAnalyzerExecutable('vendor/bin/codecept')
                ->setDockerContainer('');

            expect($hook->analyzerCommand())
                ->toContain('vendor/bin/codecept')
                ->toContain('run');
        });

        test('has no fixer command', function () {
            $hook = new CodeceptionPreCommitHook();
            $hook->setCwd(base_path())
                ->setFileExtensions('/\.php$/')
                ->setAnalyzerExecutable('vendor/bin/codecept')
                ->setDockerContainer('');

            expect($hook->fixerCommand())->toBe('');
        });

        test('has correct name', function () {
            $hook = new CodeceptionPreCommitHook();
            expect($hook->getName())->toBe('Codeception');
        });

        test('can set and get analyzer executable', function () {
            $hook = new CodeceptionPreCommitHook();
            $hook->setAnalyzerExecutable('vendor/bin/codecept');

            expect($hook->getAnalyzerExecutable())->toBe('vendor/bin/codecept');
        });

        test('generates different commands for different executables', function () {
            $hook1 = new CodeceptionPreCommitHook();
            $hook1->setCwd(base_path())
                ->setAnalyzerExecutable('vendor/bin/codecept');

            $hook2 = new CodeceptionPreCommitHook();
            $hook2->setCwd(base_path())
                ->setAnalyzerExecutable('codecept');

            expect($hook1->analyzerCommand())->not->toBe($hook2->analyzerCommand());
        });
    });

    describe('VitestPreCommitHook', function () {
        test('generates correct analyzer command', function () {
            $hook = new VitestPreCommitHook();
            $hook->setCwd(base_path())
                ->setFileExtensions('/\.(js|ts|tsx|jsx)$/')
                ->setAnalyzerExecutable('node_modules/.bin/vitest')
                ->setDockerContainer('');

            expect($hook->analyzerCommand())
                ->toContain('node_modules/.bin/vitest')
                ->toContain('run');
        });

        test('has fixer command for auto-fix', function () {
            $hook = new VitestPreCommitHook();
            $hook->setCwd(base_path())
                ->setFileExtensions('/\.(js|ts|tsx|jsx)$/')
                ->setAnalyzerExecutable('node_modules/.bin/vitest', true)
                ->setDockerContainer('');

            expect($hook->fixerCommand())
                ->toContain('node_modules/.bin/vitest')
                ->toContain('run')
                ->toContain('--update');
        });

        test('has correct name', function () {
            $hook = new VitestPreCommitHook();
            expect($hook->getName())->toBe('Vitest');
        });

        test('can set and get analyzer executable', function () {
            $hook = new VitestPreCommitHook();
            $hook->setAnalyzerExecutable('node_modules/.bin/vitest');

            expect($hook->getAnalyzerExecutable())->toBe('node_modules/.bin/vitest');
        });

        test('can set fixer executable', function () {
            $hook = new VitestPreCommitHook();
            $hook->setAnalyzerExecutable('node_modules/.bin/vitest', true);

            expect($hook->getFixerExecutable())->toBe('node_modules/.bin/vitest');
        });

        test('analyzer and fixer commands differ', function () {
            $hook = new VitestPreCommitHook();
            $hook->setCwd(base_path())
                ->setFileExtensions('/\.(js|ts)$/')
                ->setAnalyzerExecutable('node_modules/.bin/vitest', true)
                ->setDockerContainer('');

            expect($hook->analyzerCommand())->not->toBe($hook->fixerCommand());
        });
    });

    describe('Test Runner Comparison', function () {
        test('all test runners generate non-empty analyzer commands', function () {
            $hooks = [
                PestPreCommitHook::class => 'vendor/bin/pest',
                PHPUnitPreCommitHook::class => 'vendor/bin/phpunit',
                CodeceptionPreCommitHook::class => 'vendor/bin/codecept',
                VitestPreCommitHook::class => 'node_modules/.bin/vitest',
            ];

            foreach ($hooks as $class => $executable) {
                $hook = new $class();
                $hook->setCwd(base_path())
                    ->setFileExtensions('/\.php$/')
                    ->setAnalyzerExecutable($executable)
                    ->setDockerContainer('');

                expect($hook->analyzerCommand())->not->toBe('');
            }
        });

        test('test runners have distinct names', function () {
            $hooks = [
                PestPreCommitHook::class => 'Pest',
                PHPUnitPreCommitHook::class => 'PHPUnit',
                CodeceptionPreCommitHook::class => 'Codeception',
                VitestPreCommitHook::class => 'Vitest',
            ];

            foreach ($hooks as $class => $expectedName) {
                $hook = new $class();
                expect($hook->getName())->toBe($expectedName);
            }
        });

        test('only vitest has a fixer command', function () {
            $hooksWithoutFixer = [
                PestPreCommitHook::class,
                PHPUnitPreCommitHook::class,
                CodeceptionPreCommitHook::class,
            ];

            foreach ($hooksWithoutFixer as $class) {
                $hook = new $class();
                $hook->setCwd(base_path())
                    ->setFileExtensions('/\.php$/')
                    ->setAnalyzerExecutable('vendor/bin/tool')
                    ->setDockerContainer('');

                expect($hook->fixerCommand())->toBe('');
            }

            $vitest = new VitestPreCommitHook();
            $vitest->setCwd(base_path())
                ->setFileExtensions('/\.ts$/')
                ->setAnalyzerExecutable('node_modules/.bin/vitest', true)
                ->setDockerContainer('');

            expect($vitest->fixerCommand())->not->toBe('');
        });
    });
});
