<?php

declare(strict_types=1);

namespace Igorsgm\GitHooks\Tests\Unit\Hooks;

use Igorsgm\GitHooks\Console\Commands\Hooks\ComposerNormalizePreCommitHook;
use Igorsgm\GitHooks\Console\Commands\Hooks\DeptracPreCommitHook;
use Igorsgm\GitHooks\Console\Commands\Hooks\PsalmPreCommitHook;
use Mockery;

describe('Tool Hook Process Execution Tests', function () {
    describe('Composer Normalize Hook', function () {
        test('generates command for composer.json files', function () {
            $hook = new ComposerNormalizePreCommitHook();
            $hook->setCwd(base_path())
                ->setFileExtensions('/composer\.json$/')
                ->setAnalyzerExecutable('vendor/bin/composer-normalize')
                ->setDockerContainer('');

            $command = $hook->analyzerCommand();

            expect($command)
                ->toContain('vendor/bin/composer-normalize')
                ->toContain('normalize')
                ->toContain('--no-interaction');
        });
    });

    describe('Psalm Hook', function () {
        test('generates command for php files', function () {
            $hook = new PsalmPreCommitHook();
            $hook->setCwd(base_path())
                ->setFileExtensions('/\.php$/')
                ->setAnalyzerExecutable('vendor/bin/psalm')
                ->setDockerContainer('');

            $command = $hook->analyzerCommand();

            expect($command)
                ->toContain('vendor/bin/psalm');
        });

        test('has no fixer command', function () {
            $hook = new PsalmPreCommitHook();
            $hook->setCwd(base_path())
                ->setFileExtensions('/\.php$/')
                ->setAnalyzerExecutable('vendor/bin/psalm')
                ->setDockerContainer('');

            expect($hook->fixerCommand())->toBe('');
        });
    });

    describe('Deptrac Hook', function () {
        test('generates analyze command for php files', function () {
            $hook = new DeptracPreCommitHook();
            $hook->setCwd(base_path())
                ->setFileExtensions('/\.php$/')
                ->setAnalyzerExecutable('vendor/bin/deptrac')
                ->setDockerContainer('');

            $command = $hook->analyzerCommand();

            expect($command)
                ->toContain('vendor/bin/deptrac')
                ->toContain('analyse')
                ->toContain('--no-progress');
        });

        test('has no fixer command', function () {
            $hook = new DeptracPreCommitHook();
            $hook->setCwd(base_path())
                ->setFileExtensions('/\.php$/')
                ->setAnalyzerExecutable('vendor/bin/deptrac')
                ->setDockerContainer('');

            expect($hook->fixerCommand())->toBe('');
        });
    });

    describe('Hook Configuration', function () {
        test('can set and get analyzer executable', function () {
            $hook = new ComposerNormalizePreCommitHook();
            $hook->setAnalyzerExecutable('vendor/bin/composer-normalize');

            expect($hook->getAnalyzerExecutable())->toBe('vendor/bin/composer-normalize');
        });

        test('can set file extensions', function () {
            $hook = new ComposerNormalizePreCommitHook();
            $hook->setFileExtensions('/\.json$/');

            expect($hook->getFileExtensions())->toBe('/\.json$/');
        });

        test('can set multiple file extensions as array', function () {
            $hook = new ComposerNormalizePreCommitHook();
            $hook->setFileExtensions(['/\.php$/', '/\.json$/']);

            expect($hook->getFileExtensions())->toBe(['/\.php$/', '/\.json$/']);
        });
    });

    describe('Tool Hook Names', function () {
        test('ComposerNormalize has correct name', function () {
            $hook = new ComposerNormalizePreCommitHook();
            expect($hook->getName())->toBe('Composer Normalize');
        });

        test('Psalm has correct name', function () {
            $hook = new PsalmPreCommitHook();
            expect($hook->getName())->toBe('Psalm');
        });

        test('Deptrac has correct name', function () {
            $hook = new DeptracPreCommitHook();
            expect($hook->getName())->toBe('Deptrac');
        });
    });

    describe('Command Structure Comparison', function () {
        test('all hooks have distinct commands', function () {
            $hooks = [
                ComposerNormalizePreCommitHook::class => 'composer-normalize',
                PsalmPreCommitHook::class => 'psalm',
                DeptracPreCommitHook::class => 'deptrac',
            ];

            $commands = [];
            foreach ($hooks as $class => $toolName) {
                $hook = new $class();
                $hook->setCwd(base_path())
                    ->setFileExtensions('/\.php$/')
                    ->setAnalyzerExecutable('vendor/bin/'.$toolName)
                    ->setDockerContainer('');

                $commands[$toolName] = $hook->analyzerCommand();
            }

            expect($commands['composer-normalize'])->toContain('normalize');
            expect($commands['psalm'])->not->toContain('analyse');
            expect($commands['deptrac'])->toContain('analyse');
        });

        test('composer normalize has fixer command', function () {
            $hook = new ComposerNormalizePreCommitHook();
            $hook->setCwd(base_path())
                ->setFileExtensions('/composer\.json$/')
                ->setAnalyzerExecutable('vendor/bin/composer-normalize')
                ->setDockerContainer('');

            expect($hook->fixerCommand())->not->toBe('');
        });
    });
});
