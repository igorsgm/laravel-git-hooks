<?php

declare(strict_types=1);

namespace Igorsgm\GitHooks\Tests\Unit\Hooks;

use Igorsgm\GitHooks\Console\Commands\Hooks\BladeFormatterPreCommitHook;
use Igorsgm\GitHooks\Console\Commands\Hooks\ComposerNormalizePreCommitHook;
use Igorsgm\GitHooks\Console\Commands\Hooks\ESLintPreCommitHook;
use Igorsgm\GitHooks\Console\Commands\Hooks\PrettierPreCommitHook;
use Igorsgm\GitHooks\Git\ChangedFile;
use Igorsgm\GitHooks\Git\ChangedFiles;
use Mockery;

describe('Code Analyzer Hooks', function () {
    describe('PrettierPreCommitHook', function () {
        test('has correct name', function () {
            $hook = new PrettierPreCommitHook();
            expect($hook->getName())->toBe('Prettier');
        });

        test('can set file extensions', function () {
            $hook = new PrettierPreCommitHook();
            $hook->setFileExtensions('/\.ts$/');

            expect($hook->getFileExtensions())->toBe('/\.ts$/');
        });

        test('can set and get analyzer executable', function () {
            $hook = new PrettierPreCommitHook();
            $hook->setAnalyzerExecutable('prettier');

            expect($hook->getAnalyzerExecutable())->toBe('prettier');
        });

        test('can set fixer executable', function () {
            $hook = new PrettierPreCommitHook();
            $hook->setAnalyzerExecutable('prettier', true);

            expect($hook->getFixerExecutable())->toBe('prettier');
        });
    });

    describe('ESLintPreCommitHook', function () {
        test('has correct name', function () {
            $hook = new ESLintPreCommitHook();
            expect($hook->getName())->toBe('ESLint');
        });

        test('can set file extensions', function () {
            $hook = new ESLintPreCommitHook();
            $hook->setFileExtensions('/\.ts$/');

            expect($hook->getFileExtensions())->toBe('/\.ts$/');
        });

        test('can set and get analyzer executable', function () {
            $hook = new ESLintPreCommitHook();
            $hook->setAnalyzerExecutable('eslint');

            expect($hook->getAnalyzerExecutable())->toBe('eslint');
        });

        test('can set fixer executable', function () {
            $hook = new ESLintPreCommitHook();
            $hook->setAnalyzerExecutable('eslint', true);

            expect($hook->getFixerExecutable())->toBe('eslint');
        });
    });

    describe('BladeFormatterPreCommitHook', function () {
        test('has correct name', function () {
            $hook = new BladeFormatterPreCommitHook();
            expect($hook->getName())->toBe('Blade Formatter');
        });

        test('can set file extensions', function () {
            $hook = new BladeFormatterPreCommitHook();
            $hook->setFileExtensions('/\.blade\.php$/');

            expect($hook->getFileExtensions())->toBe('/\.blade\.php$/');
        });

        test('can set and get analyzer executable', function () {
            $hook = new BladeFormatterPreCommitHook();
            $hook->setAnalyzerExecutable('blade-formatter');

            expect($hook->getAnalyzerExecutable())->toBe('blade-formatter');
        });

        test('can set fixer executable', function () {
            $hook = new BladeFormatterPreCommitHook();
            $hook->setAnalyzerExecutable('blade-formatter', true);

            expect($hook->getFixerExecutable())->toBe('blade-formatter');
        });
    });

    describe('ComposerNormalizePreCommitHook', function () {
        test('has correct name', function () {
            $hook = new ComposerNormalizePreCommitHook();
            expect($hook->getName())->toBe('Composer Normalize');
        });

        test('generates correct analyzer command', function () {
            $hook = new ComposerNormalizePreCommitHook();
            $hook->setCwd(base_path())
                ->setFileExtensions('/composer\.json$/')
                ->setAnalyzerExecutable('vendor/bin/composer-normalize')
                ->setDockerContainer('');

            expect($hook->analyzerCommand())
                ->toContain('composer-normalize')
                ->toContain('normalize')
                ->toContain('--no-interaction');
        });

        test('generates correct fixer command', function () {
            $hook = new ComposerNormalizePreCommitHook();
            $hook->setCwd(base_path())
                ->setFileExtensions('/composer\.json$/')
                ->setAnalyzerExecutable('vendor/bin/composer-normalize')
                ->setDockerContainer('');

            expect($hook->fixerCommand())
                ->toContain('composer-normalize')
                ->toContain('normalize')
                ->toContain('--no-interaction');
        });

        test('analyzer and fixer commands are the same', function () {
            $hook = new ComposerNormalizePreCommitHook();
            $hook->setCwd(base_path())
                ->setFileExtensions('/composer\.json$/')
                ->setAnalyzerExecutable('vendor/bin/composer-normalize')
                ->setDockerContainer('');

            expect($hook->analyzerCommand())->toBe($hook->fixerCommand());
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

    describe('Code Analyzer Comparison', function () {
        test('all analyzers have distinct names', function () {
            $hooks = [
                PrettierPreCommitHook::class => 'Prettier',
                ESLintPreCommitHook::class => 'ESLint',
                BladeFormatterPreCommitHook::class => 'Blade Formatter',
                ComposerNormalizePreCommitHook::class => 'Composer Normalize',
            ];

            foreach ($hooks as $class => $expectedName) {
                $hook = new $class();
                expect($hook->getName())->toBe($expectedName);
            }
        });

        test('all analyzers can set executable paths', function () {
            $hooks = [
                PrettierPreCommitHook::class,
                ESLintPreCommitHook::class,
                BladeFormatterPreCommitHook::class,
                ComposerNormalizePreCommitHook::class,
            ];

            foreach ($hooks as $class) {
                $hook = new $class();
                $hook->setAnalyzerExecutable('test-executable');
                expect($hook->getAnalyzerExecutable())->toBe('test-executable');
            }
        });

        test('all analyzers can set file extensions', function () {
            $hooks = [
                PrettierPreCommitHook::class,
                ESLintPreCommitHook::class,
                BladeFormatterPreCommitHook::class,
                ComposerNormalizePreCommitHook::class,
            ];

            foreach ($hooks as $class) {
                $hook = new $class();
                $hook->setFileExtensions('/\.test$/');
                expect($hook->getFileExtensions())->toBe('/\.test$/');
            }
        });
    });
});
