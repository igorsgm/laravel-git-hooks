<?php

declare(strict_types=1);

namespace Igorsgm\GitHooks\Tests\Unit\Hooks;

use Igorsgm\GitHooks\Console\Commands\Hooks\BladeFormatterPreCommitHook;
use Igorsgm\GitHooks\Console\Commands\Hooks\ComposerNormalizePreCommitHook;
use Igorsgm\GitHooks\Console\Commands\Hooks\DeptracPreCommitHook;
use Igorsgm\GitHooks\Console\Commands\Hooks\ESLintPreCommitHook;
use Igorsgm\GitHooks\Console\Commands\Hooks\PhpInsightsPreCommitHook;
use Igorsgm\GitHooks\Console\Commands\Hooks\PrettierPreCommitHook;
use Igorsgm\GitHooks\Console\Commands\Hooks\PsalmPreCommitHook;
use Igorsgm\GitHooks\Console\Commands\Hooks\RectorPreCommitHook;
use Igorsgm\GitHooks\Git\ChangedFile;
use Igorsgm\GitHooks\Git\ChangedFiles;
use Mockery;

function setupCodeAnalyzerConfig(string $key, array $values): void
{
    $defaults = [
        'path' => 'tool',
        'config' => '',
        'file_extensions' => '/\.php$/',
        'run_in_docker' => false,
        'docker_container' => '',
        'additional_params' => '',
    ];
    $config = array_merge($defaults, $values);
    foreach ($config as $configKey => $value) {
        config(["git-hooks.code_analyzers.{$key}.{$configKey}" => $value]);
    }
}

describe('Code Analyzer Hooks Comprehensive Tests', function () {
    beforeEach(function () {
        config(['git-hooks.validate_paths' => false]);
    });

    describe('BladeFormatterPreCommitHook', function () {
        test('generates correct analyzer command with config', function () {
            setupCodeAnalyzerConfig('blade_formatter', [
                'path' => 'blade-formatter',
                'file_extensions' => '/\.blade\.php$/',
                'config' => '.bladeformatterrc.json',
            ]);

            $files = Mockery::mock(ChangedFiles::class);
            $files->shouldReceive('getStaged')->andReturn(collect());

            $hook = new BladeFormatterPreCommitHook();
            $hook->handle($files, fn ($f) => null);

            $command = $hook->analyzerCommand();
            expect($command)->toContain('blade-formatter');
        });

        test('generates correct fixer command with --write', function () {
            setupCodeAnalyzerConfig('blade_formatter', [
                'path' => 'blade-formatter',
                'file_extensions' => '/\.blade\.php$/',
                'config' => '.bladeformatterrc.json',
            ]);

            $files = Mockery::mock(ChangedFiles::class);
            $files->shouldReceive('getStaged')->andReturn(collect());

            $hook = new BladeFormatterPreCommitHook();
            $hook->handle($files, fn ($f) => null);

            $command = $hook->fixerCommand();
            expect($command)->toContain('blade-formatter');
            expect($command)->toContain('--write');
        });

        test('analyzer and fixer commands differ', function () {
            setupCodeAnalyzerConfig('blade_formatter', [
                'path' => 'blade-formatter',
                'file_extensions' => '/\.blade\.php$/',
                'config' => '.bladeformatterrc.json',
            ]);

            $files = Mockery::mock(ChangedFiles::class);
            $files->shouldReceive('getStaged')->andReturn(collect());

            $hook = new BladeFormatterPreCommitHook();
            $hook->handle($files, fn ($f) => null);

            expect($hook->analyzerCommand())->not->toBe($hook->fixerCommand());
        });

        test('has correct name', function () {
            $hook = new BladeFormatterPreCommitHook();
            expect($hook->getName())->toBe('Blade Formatter');
        });

        test('can set and get analyzer executable', function () {
            $hook = new BladeFormatterPreCommitHook();
            $hook->setAnalyzerExecutable('blade-formatter', true);

            expect($hook->getAnalyzerExecutable())->toBe('blade-formatter');
            expect($hook->getFixerExecutable())->toBe('blade-formatter');
        });

        test('can set file extensions', function () {
            $hook = new BladeFormatterPreCommitHook();
            $hook->setFileExtensions('/\.blade\.php$/');

            expect($hook->getFileExtensions())->toBe('/\.blade\.php$/');
        });
    });

    describe('ESLintPreCommitHook', function () {
        test('generates correct analyzer command', function () {
            setupCodeAnalyzerConfig('eslint', [
                'path' => 'eslint',
                'file_extensions' => '/\.(jsx?|tsx?|vue)$/',
                'config' => '.eslintrc.js',
            ]);

            $files = Mockery::mock(ChangedFiles::class);
            $files->shouldReceive('getStaged')->andReturn(collect());

            $hook = new ESLintPreCommitHook();
            $hook->handle($files, fn ($f) => null);

            $command = $hook->analyzerCommand();
            expect($command)->toContain('eslint');
        });

        test('generates correct fixer command with --fix', function () {
            setupCodeAnalyzerConfig('eslint', [
                'path' => 'eslint',
                'file_extensions' => '/\.(jsx?|tsx?|vue)$/',
                'config' => '.eslintrc.js',
            ]);

            $files = Mockery::mock(ChangedFiles::class);
            $files->shouldReceive('getStaged')->andReturn(collect());

            $hook = new ESLintPreCommitHook();
            $hook->handle($files, fn ($f) => null);

            $command = $hook->fixerCommand();
            expect($command)->toContain('eslint');
            expect($command)->toContain('--fix');
        });

        test('analyzer command does not have --fix', function () {
            setupCodeAnalyzerConfig('eslint', [
                'path' => 'eslint',
                'file_extensions' => '/\.(jsx?|tsx?|vue)$/',
                'config' => '.eslintrc.js',
            ]);

            $files = Mockery::mock(ChangedFiles::class);
            $files->shouldReceive('getStaged')->andReturn(collect());

            $hook = new ESLintPreCommitHook();
            $hook->handle($files, fn ($f) => null);

            expect($hook->analyzerCommand())->not->toContain('--fix');
        });

        test('has correct name', function () {
            $hook = new ESLintPreCommitHook();
            expect($hook->getName())->toBe('ESLint');
        });

        test('can set and get analyzer executable', function () {
            $hook = new ESLintPreCommitHook();
            $hook->setAnalyzerExecutable('eslint', true);

            expect($hook->getAnalyzerExecutable())->toBe('eslint');
        });

        test('can set file extensions', function () {
            $hook = new ESLintPreCommitHook();
            $hook->setFileExtensions('/\.ts$/');

            expect($hook->getFileExtensions())->toBe('/\.ts$/');
        });
    });

    describe('PrettierPreCommitHook', function () {
        test('generates correct analyzer command with --check', function () {
            setupCodeAnalyzerConfig('prettier', [
                'path' => 'prettier',
                'file_extensions' => '/\.(jsx?|tsx?|vue)$/',
                'config' => '.prettierrc.json',
            ]);

            $files = Mockery::mock(ChangedFiles::class);
            $files->shouldReceive('getStaged')->andReturn(collect());

            $hook = new PrettierPreCommitHook();
            $hook->handle($files, fn ($f) => null);

            $command = $hook->analyzerCommand();
            expect($command)->toContain('prettier');
            expect($command)->toContain('--check');
        });

        test('generates correct fixer command with --write', function () {
            setupCodeAnalyzerConfig('prettier', [
                'path' => 'prettier',
                'file_extensions' => '/\.(jsx?|tsx?|vue)$/',
                'config' => '.prettierrc.json',
            ]);

            $files = Mockery::mock(ChangedFiles::class);
            $files->shouldReceive('getStaged')->andReturn(collect());

            $hook = new PrettierPreCommitHook();
            $hook->handle($files, fn ($f) => null);

            $command = $hook->fixerCommand();
            expect($command)->toContain('prettier');
            expect($command)->toContain('--write');
        });

        test('analyzer and fixer commands differ', function () {
            setupCodeAnalyzerConfig('prettier', [
                'path' => 'prettier',
                'file_extensions' => '/\.(jsx?|tsx?|vue)$/',
                'config' => '.prettierrc.json',
            ]);

            $files = Mockery::mock(ChangedFiles::class);
            $files->shouldReceive('getStaged')->andReturn(collect());

            $hook = new PrettierPreCommitHook();
            $hook->handle($files, fn ($f) => null);

            expect($hook->analyzerCommand())->not->toBe($hook->fixerCommand());
        });

        test('has correct name', function () {
            $hook = new PrettierPreCommitHook();
            expect($hook->getName())->toBe('Prettier');
        });

        test('can set and get analyzer executable', function () {
            $hook = new PrettierPreCommitHook();
            $hook->setAnalyzerExecutable('prettier', true);

            expect($hook->getAnalyzerExecutable())->toBe('prettier');
        });
    });

    describe('ComposerNormalizePreCommitHook', function () {
        test('generates correct analyzer command', function () {
            $hook = new ComposerNormalizePreCommitHook();
            $hook->setCwd(base_path())
                ->setFileExtensions('/composer\.json$/')
                ->setAnalyzerExecutable('vendor/bin/composer-normalize')
                ->setDockerContainer('');

            $command = $hook->analyzerCommand();
            expect($command)->toContain('composer-normalize');
            expect($command)->toContain('normalize');
            expect($command)->toContain('--no-interaction');
        });

        test('generates correct fixer command', function () {
            $hook = new ComposerNormalizePreCommitHook();
            $hook->setCwd(base_path())
                ->setFileExtensions('/composer\.json$/')
                ->setAnalyzerExecutable('vendor/bin/composer-normalize')
                ->setDockerContainer('');

            $command = $hook->fixerCommand();
            expect($command)->toContain('composer-normalize');
            expect($command)->toContain('normalize');
            expect($command)->toContain('--no-interaction');
        });

        test('analyzer and fixer are same command', function () {
            $hook = new ComposerNormalizePreCommitHook();
            $hook->setCwd(base_path())
                ->setFileExtensions('/composer\.json$/')
                ->setAnalyzerExecutable('vendor/bin/composer-normalize')
                ->setDockerContainer('');

            expect($hook->analyzerCommand())->toBe($hook->fixerCommand());
        });

        test('has correct name', function () {
            $hook = new ComposerNormalizePreCommitHook();
            expect($hook->getName())->toBe('Composer Normalize');
        });

        test('can set file extensions', function () {
            $hook = new ComposerNormalizePreCommitHook();
            $hook->setFileExtensions('/composer\.json$/');

            expect($hook->getFileExtensions())->toBe('/composer\.json$/');
        });

        test('can set and get analyzer executable', function () {
            $hook = new ComposerNormalizePreCommitHook();
            $hook->setAnalyzerExecutable('vendor/bin/composer-normalize');

            expect($hook->getAnalyzerExecutable())->toBe('vendor/bin/composer-normalize');
        });
    });

    describe('PsalmPreCommitHook', function () {
        test('generates correct analyzer command', function () {
            $hook = new PsalmPreCommitHook();
            $hook->setCwd(base_path())
                ->setFileExtensions('/\.php$/')
                ->setAnalyzerExecutable('vendor/bin/psalm')
                ->setDockerContainer('');

            $command = $hook->analyzerCommand();
            expect($command)->toContain('psalm');
        });

        test('has no fixer command', function () {
            $hook = new PsalmPreCommitHook();
            $hook->setCwd(base_path())
                ->setFileExtensions('/\.php$/')
                ->setAnalyzerExecutable('vendor/bin/psalm')
                ->setDockerContainer('');

            expect($hook->fixerCommand())->toBe('');
        });

        test('has correct name', function () {
            $hook = new PsalmPreCommitHook();
            expect($hook->getName())->toBe('Psalm');
        });

        test('can set file extensions', function () {
            $hook = new PsalmPreCommitHook();
            $hook->setFileExtensions('/\.php$/');

            expect($hook->getFileExtensions())->toBe('/\.php$/');
        });

        test('can set and get analyzer executable', function () {
            $hook = new PsalmPreCommitHook();
            $hook->setAnalyzerExecutable('vendor/bin/psalm');

            expect($hook->getAnalyzerExecutable())->toBe('vendor/bin/psalm');
        });
    });

    describe('DeptracPreCommitHook', function () {
        test('generates correct analyzer command', function () {
            $hook = new DeptracPreCommitHook();
            $hook->setCwd(base_path())
                ->setFileExtensions('/\.php$/')
                ->setAnalyzerExecutable('vendor/bin/deptrac')
                ->setDockerContainer('');

            $command = $hook->analyzerCommand();
            expect($command)->toContain('deptrac');
            expect($command)->toContain('analyse');
            expect($command)->toContain('--no-progress');
        });

        test('has no fixer command', function () {
            $hook = new DeptracPreCommitHook();
            $hook->setCwd(base_path())
                ->setFileExtensions('/\.php$/')
                ->setAnalyzerExecutable('vendor/bin/deptrac')
                ->setDockerContainer('');

            expect($hook->fixerCommand())->toBe('');
        });

        test('has correct name', function () {
            $hook = new DeptracPreCommitHook();
            expect($hook->getName())->toBe('Deptrac');
        });

        test('can set file extensions', function () {
            $hook = new DeptracPreCommitHook();
            $hook->setFileExtensions('/\.php$/');

            expect($hook->getFileExtensions())->toBe('/\.php$/');
        });

        test('can set and get analyzer executable', function () {
            $hook = new DeptracPreCommitHook();
            $hook->setAnalyzerExecutable('vendor/bin/deptrac');

            expect($hook->getAnalyzerExecutable())->toBe('vendor/bin/deptrac');
        });
    });

    describe('PhpInsightsPreCommitHook', function () {
        test('generates correct analyzer command', function () {
            setupCodeAnalyzerConfig('phpinsights', [
                'path' => 'vendor/bin/phpinsights',
                'file_extensions' => '/\.php$/',
                'config' => 'phpinsights.php',
            ]);

            $files = Mockery::mock(ChangedFiles::class);
            $files->shouldReceive('getStaged')->andReturn(collect());

            $hook = new PhpInsightsPreCommitHook();
            $hook->handle($files, fn ($f) => null);

            $command = $hook->analyzerCommand();
            expect($command)->toContain('phpinsights');
            expect($command)->toContain('analyse');
            expect($command)->toContain('--no-interaction');
        });

        test('generates correct fixer command', function () {
            setupCodeAnalyzerConfig('phpinsights', [
                'path' => 'vendor/bin/phpinsights',
                'file_extensions' => '/\.php$/',
                'config' => 'phpinsights.php',
            ]);

            $files = Mockery::mock(ChangedFiles::class);
            $files->shouldReceive('getStaged')->andReturn(collect());

            $hook = new PhpInsightsPreCommitHook();
            $hook->handle($files, fn ($f) => null);

            $command = $hook->fixerCommand();
            expect($command)->toContain('phpinsights');
            expect($command)->toContain('--fix');
        });

        test('analyzer and fixer commands differ', function () {
            setupCodeAnalyzerConfig('phpinsights', [
                'path' => 'vendor/bin/phpinsights',
                'file_extensions' => '/\.php$/',
                'config' => 'phpinsights.php',
            ]);

            $files = Mockery::mock(ChangedFiles::class);
            $files->shouldReceive('getStaged')->andReturn(collect());

            $hook = new PhpInsightsPreCommitHook();
            $hook->handle($files, fn ($f) => null);

            expect($hook->analyzerCommand())->not->toBe($hook->fixerCommand());
        });

        test('has correct name', function () {
            $hook = new PhpInsightsPreCommitHook();
            expect($hook->getName())->toBe('PhpInsights');
        });
    });

    describe('RectorPreCommitHook', function () {
        test('generates correct analyzer command', function () {
            setupCodeAnalyzerConfig('rector', [
                'path' => 'vendor/bin/rector',
                'file_extensions' => '/\.php$/',
                'config' => 'rector.php',
            ]);

            $files = Mockery::mock(ChangedFiles::class);
            $files->shouldReceive('getStaged')->andReturn(collect());

            $hook = new RectorPreCommitHook();
            $hook->handle($files, fn ($f) => null);

            $command = $hook->analyzerCommand();
            expect($command)->toContain('rector');
            expect($command)->toContain('process');
            expect($command)->toContain('--dry-run');
        });

        test('generates correct fixer command', function () {
            setupCodeAnalyzerConfig('rector', [
                'path' => 'vendor/bin/rector',
                'file_extensions' => '/\.php$/',
                'config' => 'rector.php',
            ]);

            $files = Mockery::mock(ChangedFiles::class);
            $files->shouldReceive('getStaged')->andReturn(collect());

            $hook = new RectorPreCommitHook();
            $hook->handle($files, fn ($f) => null);

            $command = $hook->fixerCommand();
            expect($command)->toContain('rector');
            expect($command)->toContain('process');
            expect($command)->not->toContain('--dry-run');
        });

        test('analyzer and fixer commands differ', function () {
            setupCodeAnalyzerConfig('rector', [
                'path' => 'vendor/bin/rector',
                'file_extensions' => '/\.php$/',
                'config' => 'rector.php',
            ]);

            $files = Mockery::mock(ChangedFiles::class);
            $files->shouldReceive('getStaged')->andReturn(collect());

            $hook = new RectorPreCommitHook();
            $hook->handle($files, fn ($f) => null);

            expect($hook->analyzerCommand())->not->toBe($hook->fixerCommand());
        });

        test('has correct name', function () {
            $hook = new RectorPreCommitHook();
            expect($hook->getName())->toBe('Rector');
        });

        test('can set and get analyzer executable', function () {
            $hook = new RectorPreCommitHook();
            $hook->setAnalyzerExecutable('vendor/bin/rector');

            expect($hook->getAnalyzerExecutable())->toBe('vendor/bin/rector');
        });
    });

    describe('VitestPreCommitHook', function () {
        test('has correct name', function () {
            $hook = new \Igorsgm\GitHooks\Console\Commands\Hooks\VitestPreCommitHook();
            expect($hook->getName())->toBe('Vitest');
        });

        test('can set file extensions', function () {
            $hook = new \Igorsgm\GitHooks\Console\Commands\Hooks\VitestPreCommitHook();
            $hook->setFileExtensions('all');

            expect($hook->getFileExtensions())->toBe('all');
        });

        test('can set analyzer executable', function () {
            $hook = new \Igorsgm\GitHooks\Console\Commands\Hooks\VitestPreCommitHook();
            $hook->setAnalyzerExecutable('vitest', true);

            expect($hook->getAnalyzerExecutable())->toBe('vitest');
        });

        test('fixer command returns non-empty string', function () {
            $hook = new \Igorsgm\GitHooks\Console\Commands\Hooks\VitestPreCommitHook();
            $hook->setAnalyzerExecutable('vitest', true);

            expect($hook->fixerCommand())->not->toBe('');
            expect($hook->fixerCommand())->toContain('--update');
        });

        test('shouldSkipFile returns true for test files', function () {
            $hook = new \Igorsgm\GitHooks\Console\Commands\Hooks\VitestPreCommitHook();

            $file = Mockery::mock(ChangedFile::class);
            $file->shouldReceive('getFilePath')->andReturn('tests/Example.test.ts');

            $reflection = new \ReflectionClass($hook);
            $method = $reflection->getMethod('shouldSkipFile');
            $method->setAccessible(true);

            expect($method->invoke($hook, $file))->toBeTrue();
        });

        test('shouldSkipFile returns true for non-JS/TS files', function () {
            $hook = new \Igorsgm\GitHooks\Console\Commands\Hooks\VitestPreCommitHook();

            $file = Mockery::mock(ChangedFile::class);
            $file->shouldReceive('getFilePath')->andReturn('src/User.php');

            $reflection = new \ReflectionClass($hook);
            $method = $reflection->getMethod('shouldSkipFile');
            $method->setAccessible(true);

            expect($method->invoke($hook, $file))->toBeTrue();
        });

        test('shouldSkipFile returns false for source JS/TS files', function () {
            $hook = new \Igorsgm\GitHooks\Console\Commands\Hooks\VitestPreCommitHook();

            $file = Mockery::mock(ChangedFile::class);
            $file->shouldReceive('getFilePath')->andReturn('src/App.ts');

            $reflection = new \ReflectionClass($hook);
            $method = $reflection->getMethod('shouldSkipFile');
            $method->setAccessible(true);

            expect($method->invoke($hook, $file))->toBeFalse();
        });

        test('getExtension returns correct extension', function () {
            $hook = new \Igorsgm\GitHooks\Console\Commands\Hooks\VitestPreCommitHook();

            $reflection = new \ReflectionClass($hook);
            $method = $reflection->getMethod('getExtension');
            $method->setAccessible(true);

            expect($method->invoke($hook, 'src/App.ts'))->toBe('ts');
            expect($method->invoke($hook, 'src/App.tsx'))->toBe('tsx');
            expect($method->invoke($hook, 'src/App.vue'))->toBe('vue');
        });
    });

    describe('PHPUnitPreCommitHook', function () {
        test('has correct name', function () {
            $hook = new \Igorsgm\GitHooks\Console\Commands\Hooks\PHPUnitPreCommitHook();
            expect($hook->getName())->toBe('PHPUnit');
        });

        test('can set file extensions', function () {
            $hook = new \Igorsgm\GitHooks\Console\Commands\Hooks\PHPUnitPreCommitHook();
            $hook->setFileExtensions('all');

            expect($hook->getFileExtensions())->toBe('all');
        });

        test('can set analyzer executable', function () {
            $hook = new \Igorsgm\GitHooks\Console\Commands\Hooks\PHPUnitPreCommitHook();
            $hook->setAnalyzerExecutable('phpunit', true);

            expect($hook->getAnalyzerExecutable())->toBe('phpunit');
        });

        test('fixer command returns empty string', function () {
            $hook = new \Igorsgm\GitHooks\Console\Commands\Hooks\PHPUnitPreCommitHook();
            expect($hook->fixerCommand())->toBe('');
        });
    });

    describe('PestPreCommitHook', function () {
        test('has correct name', function () {
            $hook = new \Igorsgm\GitHooks\Console\Commands\Hooks\PestPreCommitHook();
            expect($hook->getName())->toBe('Pest');
        });

        test('can set file extensions', function () {
            $hook = new \Igorsgm\GitHooks\Console\Commands\Hooks\PestPreCommitHook();
            $hook->setFileExtensions('all');

            expect($hook->getFileExtensions())->toBe('all');
        });

        test('can set analyzer executable', function () {
            $hook = new \Igorsgm\GitHooks\Console\Commands\Hooks\PestPreCommitHook();
            $hook->setAnalyzerExecutable('pest', true);

            expect($hook->getAnalyzerExecutable())->toBe('pest');
        });

        test('fixer command returns empty string', function () {
            $hook = new \Igorsgm\GitHooks\Console\Commands\Hooks\PestPreCommitHook();
            expect($hook->fixerCommand())->toBe('');
        });
    });

    describe('CodeceptionPreCommitHook', function () {
        test('has correct name', function () {
            $hook = new \Igorsgm\GitHooks\Console\Commands\Hooks\CodeceptionPreCommitHook();
            expect($hook->getName())->toBe('Codeception');
        });

        test('can set file extensions', function () {
            $hook = new \Igorsgm\GitHooks\Console\Commands\Hooks\CodeceptionPreCommitHook();
            $hook->setFileExtensions('all');

            expect($hook->getFileExtensions())->toBe('all');
        });

        test('can set analyzer executable', function () {
            $hook = new \Igorsgm\GitHooks\Console\Commands\Hooks\CodeceptionPreCommitHook();
            $hook->setAnalyzerExecutable('codecept', true);

            expect($hook->getAnalyzerExecutable())->toBe('codecept');
        });

        test('fixer command returns empty string', function () {
            $hook = new \Igorsgm\GitHooks\Console\Commands\Hooks\CodeceptionPreCommitHook();
            expect($hook->fixerCommand())->toBe('');
        });
    });

    describe('All hooks have distinct behaviors', function () {
        test('fixers only available for autofixable tools', function () {
            $hooksWithoutFixer = [
                PsalmPreCommitHook::class,
                DeptracPreCommitHook::class,
            ];

            $hooksWithFixer = [
                BladeFormatterPreCommitHook::class,
                ESLintPreCommitHook::class,
                PrettierPreCommitHook::class,
                ComposerNormalizePreCommitHook::class,
                PhpInsightsPreCommitHook::class,
                RectorPreCommitHook::class,
            ];

            setupCodeAnalyzerConfig('psalm', [
                'path' => 'vendor/bin/psalm',
                'file_extensions' => '/\.php$/',
            ]);
            setupCodeAnalyzerConfig('deptrac', [
                'path' => 'vendor/bin/deptrac',
                'file_extensions' => '/\.php$/',
            ]);

            foreach ($hooksWithoutFixer as $class) {
                $files = Mockery::mock(ChangedFiles::class);
                $files->shouldReceive('getStaged')->andReturn(collect());

                $hook = new $class();
                if (method_exists($hook, 'handle')) {
                    $hook->handle($files, fn ($f) => null);
                } else {
                    $hook->setCwd(base_path())
                        ->setFileExtensions('/\.php$/')
                        ->setAnalyzerExecutable('tool')
                        ->setDockerContainer('');
                }

                expect($hook->fixerCommand())->toBe('');
            }

            setupCodeAnalyzerConfig('blade_formatter', [
                'path' => 'blade-formatter',
                'file_extensions' => '/\.blade\.php$/',
                'config' => '.bladeformatterrc.json',
            ]);
            setupCodeAnalyzerConfig('eslint', [
                'path' => 'eslint',
                'file_extensions' => '/\.(jsx?|tsx?|vue)$/',
                'config' => '.eslintrc.js',
            ]);
            setupCodeAnalyzerConfig('prettier', [
                'path' => 'prettier',
                'file_extensions' => '/\.(jsx?|tsx?|vue)$/',
                'config' => '.prettierrc.json',
            ]);
            setupCodeAnalyzerConfig('phpinsights', [
                'path' => 'vendor/bin/phpinsights',
                'file_extensions' => '/\.php$/',
                'config' => 'phpinsights.php',
            ]);
            setupCodeAnalyzerConfig('rector', [
                'path' => 'vendor/bin/rector',
                'file_extensions' => '/\.php$/',
                'config' => 'rector.php',
            ]);
            setupCodeAnalyzerConfig('composer_normalize', [
                'path' => 'vendor/bin/composer-normalize',
                'file_extensions' => '/composer\.json$/',
            ]);

            foreach ($hooksWithFixer as $class) {
                $files = Mockery::mock(ChangedFiles::class);
                $files->shouldReceive('getStaged')->andReturn(collect());

                $hook = new $class();
                if (method_exists($hook, 'handle')) {
                    $hook->handle($files, fn ($f) => null);
                } else {
                    $hook->setCwd(base_path())
                        ->setFileExtensions('/\.php$/')
                        ->setAnalyzerExecutable('tool')
                        ->setDockerContainer('');
                }

                expect($hook->fixerCommand())->not->toBe('');
            }
        });
    });
})->uses(\Orchestra\Testbench\TestCase::class);
