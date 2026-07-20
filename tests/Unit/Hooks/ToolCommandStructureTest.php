<?php

declare(strict_types=1);

namespace Igorsgm\GitHooks\Tests\Unit\Hooks;

use Igorsgm\GitHooks\Console\Commands\Hooks\ComposerNormalizePreCommitHook;
use Igorsgm\GitHooks\Console\Commands\Hooks\DeptracPreCommitHook;
use Igorsgm\GitHooks\Console\Commands\Hooks\PsalmPreCommitHook;

describe('Tool Command Structure Tests', function () {
    describe('Composer Normalize', function () {
        test('generates correct analyzer command', function () {
            $hook = new ComposerNormalizePreCommitHook();
            $hook->setCwd(base_path())
                ->setFileExtensions('/composer\.json$/')
                ->setAnalyzerExecutable('vendor/bin/composer-normalize')
                ->setDockerContainer('');

            expect($hook->analyzerCommand())
                ->toContain('vendor/bin/composer-normalize')
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
                ->toContain('vendor/bin/composer-normalize')
                ->toContain('normalize')
                ->toContain('--no-interaction');
        });

        test('has auto-fixer capability', function () {
            $hook = new ComposerNormalizePreCommitHook();
            $hook->setCwd(base_path())
                ->setFileExtensions('/composer\.json$/')
                ->setAnalyzerExecutable('vendor/bin/composer-normalize')
                ->setDockerContainer('');

            expect($hook->fixerCommand())->not->toBe('');
        });

        test('uses correct file extensions', function () {
            $hook = new ComposerNormalizePreCommitHook();
            $hook->setCwd(base_path())
                ->setFileExtensions('/composer\.json$/')
                ->setAnalyzerExecutable('vendor/bin/composer-normalize')
                ->setDockerContainer('');

            expect($hook->getFileExtensions())->toBe('/composer\.json$/');
        });
    });

    describe('Psalm', function () {
        test('generates correct analyzer command', function () {
            $hook = new PsalmPreCommitHook();
            $hook->setCwd(base_path())
                ->setFileExtensions('/\.php$/')
                ->setAnalyzerExecutable('vendor/bin/psalm')
                ->setDockerContainer('');

            expect($hook->analyzerCommand())
                ->toContain('vendor/bin/psalm');
        });

        test('has no auto-fixer', function () {
            $hook = new PsalmPreCommitHook();
            $hook->setCwd(base_path())
                ->setFileExtensions('/\.php$/')
                ->setAnalyzerExecutable('vendor/bin/psalm')
                ->setDockerContainer('');

            expect($hook->fixerCommand())->toBe('');
        });

        test('uses correct file extensions', function () {
            $hook = new PsalmPreCommitHook();
            $hook->setCwd(base_path())
                ->setFileExtensions('/\.php$/')
                ->setAnalyzerExecutable('vendor/bin/psalm')
                ->setDockerContainer('');

            expect($hook->getFileExtensions())->toBe('/\.php$/');
        });
    });

    describe('Deptrac', function () {
        test('generates correct analyzer command', function () {
            $hook = new DeptracPreCommitHook();
            $hook->setCwd(base_path())
                ->setFileExtensions('/\.php$/')
                ->setAnalyzerExecutable('vendor/bin/deptrac')
                ->setDockerContainer('');

            expect($hook->analyzerCommand())
                ->toContain('vendor/bin/deptrac')
                ->toContain('analyse')
                ->toContain('--no-progress');
        });

        test('has no auto-fixer', function () {
            $hook = new DeptracPreCommitHook();
            $hook->setCwd(base_path())
                ->setFileExtensions('/\.php$/')
                ->setAnalyzerExecutable('vendor/bin/deptrac')
                ->setDockerContainer('');

            expect($hook->fixerCommand())->toBe('');
        });

        test('uses correct file extensions', function () {
            $hook = new DeptracPreCommitHook();
            $hook->setCwd(base_path())
                ->setFileExtensions('/\.php$/')
                ->setAnalyzerExecutable('vendor/bin/deptrac')
                ->setDockerContainer('');

            expect($hook->getFileExtensions())->toBe('/\.php$/');
        });
    });

    describe('Tool Comparison', function () {
        test('all tools have executable paths configured', function () {
            $tools = [
                ComposerNormalizePreCommitHook::class => 'vendor/bin/composer-normalize',
                PsalmPreCommitHook::class => 'vendor/bin/psalm',
                DeptracPreCommitHook::class => 'vendor/bin/deptrac',
            ];

            foreach ($tools as $hookClass => $expectedPath) {
                $hook = new $hookClass();
                $hook->setCwd(base_path())
                    ->setFileExtensions('/\.php$/')
                    ->setAnalyzerExecutable($expectedPath)
                    ->setDockerContainer('');

                expect($hook->getAnalyzerExecutable())->toBe($expectedPath);
            }
        });

        test('all tools generate non-empty analyzer commands', function () {
            $tools = [
                ComposerNormalizePreCommitHook::class,
                PsalmPreCommitHook::class,
                DeptracPreCommitHook::class,
            ];

            foreach ($tools as $hookClass) {
                $hook = new $hookClass();
                $hook->setCwd(base_path())
                    ->setFileExtensions('/\.php$/')
                    ->setAnalyzerExecutable('vendor/bin/tool')
                    ->setDockerContainer('');

                expect($hook->analyzerCommand())->not->toBe('');
            }
        });
    });
});
