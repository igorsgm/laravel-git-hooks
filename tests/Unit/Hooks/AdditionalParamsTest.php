<?php

declare(strict_types=1);

namespace Igorsgm\GitHooks\Tests\Unit\Hooks;

use Igorsgm\GitHooks\Console\Commands\Hooks\CodeceptionPreCommitHook;
use Igorsgm\GitHooks\Console\Commands\Hooks\DeptracPreCommitHook;
use Igorsgm\GitHooks\Console\Commands\Hooks\ESLintPreCommitHook;
use Igorsgm\GitHooks\Console\Commands\Hooks\LarastanPreCommitHook;
use Igorsgm\GitHooks\Console\Commands\Hooks\PHPCodeSnifferPreCommitHook;
use Igorsgm\GitHooks\Console\Commands\Hooks\PHPUnitPreCommitHook;
use Igorsgm\GitHooks\Console\Commands\Hooks\PestPreCommitHook;
use Igorsgm\GitHooks\Console\Commands\Hooks\PhpInsightsPreCommitHook;
use Igorsgm\GitHooks\Console\Commands\Hooks\PsalmPreCommitHook;
use Igorsgm\GitHooks\Console\Commands\Hooks\RectorPreCommitHook;
use Igorsgm\GitHooks\Console\Commands\Hooks\VitestPreCommitHook;
use ReflectionClass;

/**
 * Helper to call protected methods via reflection.
 */
function callProtected(object $object, string $method, array $args = []): mixed
{
    $reflection = new ReflectionClass($object);
    $m = $reflection->getMethod($method);
    $m->setAccessible(true);

    return $m->invokeArgs($object, $args);
}

describe('Additional Params & Config Param Coverage', function () {
    beforeEach(function () {
        config(['git-hooks.validate_paths' => false]);
    });

    describe('PestPreCommitHook', function () {
        test('getAdditionalParams strips --filter and -f flags', function () {
            config(['git-hooks.code_analyzers.pest.additional_params' => '--filter=UserTest --verbose']);

            $hook = new PestPreCommitHook();
            $result = callProtected($hook, 'getAdditionalParams');

            expect($result)->not->toContain('--filter');
            expect($result)->toContain('--verbose');
        });

        test('getAdditionalParams strips short -f flag', function () {
            config(['git-hooks.code_analyzers.pest.additional_params' => '--f UserTest --stop-on-failure']);

            $hook = new PestPreCommitHook();
            $result = callProtected($hook, 'getAdditionalParams');

            expect($result)->not->toContain('--f UserTest');
            expect($result)->toContain('--stop-on-failure');
        });

        test('getAdditionalParams returns empty when no params set', function () {
            config(['git-hooks.code_analyzers.pest.additional_params' => '']);

            $hook = new PestPreCommitHook();
            $result = callProtected($hook, 'getAdditionalParams');

            expect($result)->toBe('');
        });
    });

    describe('PHPUnitPreCommitHook', function () {
        test('getAdditionalParams strips --filter flag', function () {
            config(['git-hooks.code_analyzers.phpunit.additional_params' => '--filter=UserTest --colors=always']);

            $hook = new PHPUnitPreCommitHook();
            $result = callProtected($hook, 'getAdditionalParams');

            expect($result)->not->toContain('--filter');
            expect($result)->toContain('--colors=always');
        });

        test('getAdditionalParams returns empty when no params', function () {
            config(['git-hooks.code_analyzers.phpunit.additional_params' => '']);

            $hook = new PHPUnitPreCommitHook();
            $result = callProtected($hook, 'getAdditionalParams');

            expect($result)->toBe('');
        });
    });

    describe('CodeceptionPreCommitHook', function () {
        test('getAdditionalParams strips --filter flag', function () {
            config(['git-hooks.code_analyzers.codeception.additional_params' => '--filter=UserCest --debug']);

            $hook = new CodeceptionPreCommitHook();
            $result = callProtected($hook, 'getAdditionalParams');

            expect($result)->not->toContain('--filter');
            expect($result)->toContain('--debug');
        });

        test('getAdditionalParams returns empty when no params', function () {
            config(['git-hooks.code_analyzers.codeception.additional_params' => '']);

            $hook = new CodeceptionPreCommitHook();
            $result = callProtected($hook, 'getAdditionalParams');

            expect($result)->toBe('');
        });
    });

    describe('VitestPreCommitHook', function () {
        test('getAdditionalParams strips --filter flag', function () {
            config(['git-hooks.code_analyzers.vitest.additional_params' => '--reporter=verbose --filter=UserSpec']);

            $hook = new VitestPreCommitHook();
            $result = callProtected($hook, 'getAdditionalParams');

            expect($result)->not->toContain('--filter');
            expect($result)->toContain('--reporter=verbose');
        });

        test('getAdditionalParams returns empty when no params', function () {
            config(['git-hooks.code_analyzers.vitest.additional_params' => '']);

            $hook = new VitestPreCommitHook();
            $result = callProtected($hook, 'getAdditionalParams');

            expect($result)->toBe('');
        });
    });

    describe('ESLintPreCommitHook', function () {
        test('additionalParams strips dot (.) shorthand and --config flag', function () {
            config(['git-hooks.code_analyzers.eslint.additional_params' => '--max-warnings=0 --config=.eslintrc . --ext .ts']);

            $hook = new ESLintPreCommitHook();
            $result = callProtected($hook, 'additionalParams');

            expect($result)->not->toContain('--config=.eslintrc');
            expect($result)->toContain('--max-warnings=0');
        });

        test('additionalParams strips --c shorthand', function () {
            config(['git-hooks.code_analyzers.eslint.additional_params' => '--max-warnings=0 --c .eslintrc']);

            $hook = new ESLintPreCommitHook();
            $result = callProtected($hook, 'additionalParams');

            expect($result)->not->toContain('--c .eslintrc');
            expect($result)->toContain('--max-warnings=0');
        });

        test('additionalParams returns empty when no params set', function () {
            config(['git-hooks.code_analyzers.eslint.additional_params' => '']);

            $hook = new ESLintPreCommitHook();
            $result = callProtected($hook, 'additionalParams');

            expect($result)->toBe('');
        });
    });

    describe('LarastanPreCommitHook', function () {
        test('additionalParams strips --configuration flag', function () {
            config(['git-hooks.code_analyzers.larastan.additional_params' => '--configuration=phpstan.neon --level=8']);

            $hook = new LarastanPreCommitHook();
            $result = callProtected($hook, 'additionalParams');

            expect($result)->not->toContain('--configuration');
            expect($result)->toContain('--level=8');
        });

        test('additionalParams strips --xdebug flag', function () {
            config(['git-hooks.code_analyzers.larastan.additional_params' => '--xdebug --memory-limit=2G']);

            $hook = new LarastanPreCommitHook();
            $result = callProtected($hook, 'additionalParams');

            expect($result)->not->toContain('--xdebug');
            expect($result)->toContain('--memory-limit=2G');
        });

        test('additionalParams strips --c shorthand', function () {
            config(['git-hooks.code_analyzers.larastan.additional_params' => '--c=phpstan.neon --verbose']);

            $hook = new LarastanPreCommitHook();
            $result = callProtected($hook, 'additionalParams');

            expect($result)->not->toContain('--c=');
            expect($result)->toContain('--verbose');
        });

        test('additionalParams returns empty when no params', function () {
            config(['git-hooks.code_analyzers.larastan.additional_params' => '']);

            $hook = new LarastanPreCommitHook();
            $result = callProtected($hook, 'additionalParams');

            expect($result)->toBe('');
        });
    });

    describe('DeptracPreCommitHook', function () {
        test('configParam returns config flag when config is set', function () {
            config(['git-hooks.code_analyzers.deptrac.config' => 'depfile.yaml']);

            $hook = new DeptracPreCommitHook();
            $result = callProtected($hook, 'configParam');

            expect($result)->toBe('--config=depfile.yaml');
        });

        test('configParam returns empty string when no config set', function () {
            config(['git-hooks.code_analyzers.deptrac.config' => '']);

            $hook = new DeptracPreCommitHook();
            $result = callProtected($hook, 'configParam');

            expect($result)->toBe('');
        });

        test('additionalParams strips --config flag', function () {
            config(['git-hooks.code_analyzers.deptrac.additional_params' => '--config=depfile.yaml --formatter=junit']);

            $hook = new DeptracPreCommitHook();
            $result = callProtected($hook, 'additionalParams');

            expect($result)->not->toContain('--config=');
            expect($result)->toContain('--formatter=junit');
        });

        test('additionalParams strips --no-progress flag', function () {
            config(['git-hooks.code_analyzers.deptrac.additional_params' => '--no-progress --verbose']);

            $hook = new DeptracPreCommitHook();
            $result = callProtected($hook, 'additionalParams');

            expect($result)->not->toContain('--no-progress');
            expect($result)->toContain('--verbose');
        });

        test('additionalParams strips --c shorthand', function () {
            config(['git-hooks.code_analyzers.deptrac.additional_params' => '--c=depfile.yaml --formatter=table']);

            $hook = new DeptracPreCommitHook();
            $result = callProtected($hook, 'additionalParams');

            expect($result)->not->toContain('--c=');
        });

        test('additionalParams returns empty when no params', function () {
            config(['git-hooks.code_analyzers.deptrac.additional_params' => '']);

            $hook = new DeptracPreCommitHook();
            $result = callProtected($hook, 'additionalParams');

            expect($result)->toBe('');
        });
    });

    describe('PsalmPreCommitHook', function () {
        test('configParam returns config flag when config is set', function () {
            config(['git-hooks.code_analyzers.psalm.config' => 'psalm.xml']);

            $hook = new PsalmPreCommitHook();
            $result = callProtected($hook, 'configParam');

            expect($result)->toBe('--config=psalm.xml');
        });

        test('configParam returns empty string when no config set', function () {
            config(['git-hooks.code_analyzers.psalm.config' => '']);

            $hook = new PsalmPreCommitHook();
            $result = callProtected($hook, 'configParam');

            expect($result)->toBe('');
        });

        test('additionalParams strips --config flag', function () {
            config(['git-hooks.code_analyzers.psalm.additional_params' => '--config=psalm.xml --show-info=true']);

            $hook = new PsalmPreCommitHook();
            $result = callProtected($hook, 'additionalParams');

            expect($result)->not->toContain('--config=');
            expect($result)->toContain('--show-info=true');
        });

        test('additionalParams strips --c shorthand', function () {
            config(['git-hooks.code_analyzers.psalm.additional_params' => '--c=psalm.xml --threads=4']);

            $hook = new PsalmPreCommitHook();
            $result = callProtected($hook, 'additionalParams');

            expect($result)->not->toContain('--c=');
            expect($result)->toContain('--threads=4');
        });

        test('additionalParams returns empty when no params', function () {
            config(['git-hooks.code_analyzers.psalm.additional_params' => '']);

            $hook = new PsalmPreCommitHook();
            $result = callProtected($hook, 'additionalParams');

            expect($result)->toBe('');
        });
    });

    describe('PhpInsightsPreCommitHook', function () {
        test('configParam returns empty string when no config set', function () {
            config(['git-hooks.code_analyzers.phpinsights.config' => '']);

            $hook = new PhpInsightsPreCommitHook();
            $result = callProtected($hook, 'configParam');

            expect($result)->toBe('');
        });

        test('configParam returns config path when config is set', function () {
            config(['git-hooks.code_analyzers.phpinsights.config' => 'phpinsights.php']);

            $hook = new PhpInsightsPreCommitHook();
            $result = callProtected($hook, 'configParam');

            expect($result)->toBe('--config-path=phpinsights.php');
        });

        test('additionalParams strips --config-path flag', function () {
            config(['git-hooks.code_analyzers.phpinsights.additional_params' => '--config-path=insights.php --min-quality=80']);

            $hook = new PhpInsightsPreCommitHook();
            $result = callProtected($hook, 'additionalParams');

            expect($result)->not->toContain('--config-path');
            expect($result)->toContain('--min-quality=80');
        });

        test('additionalParams strips --fix flag', function () {
            config(['git-hooks.code_analyzers.phpinsights.additional_params' => '--fix --min-quality=80']);

            $hook = new PhpInsightsPreCommitHook();
            $result = callProtected($hook, 'additionalParams');

            expect($result)->not->toContain('--fix');
            expect($result)->toContain('--min-quality=80');
        });

        test('additionalParams strips --no-interaction flag', function () {
            config(['git-hooks.code_analyzers.phpinsights.additional_params' => '--no-interaction --min-quality=80']);

            $hook = new PhpInsightsPreCommitHook();
            $result = callProtected($hook, 'additionalParams');

            expect($result)->not->toContain('--no-interaction');
            expect($result)->toContain('--min-quality=80');
        });

        test('additionalParams returns empty when no params', function () {
            config(['git-hooks.code_analyzers.phpinsights.additional_params' => '']);

            $hook = new PhpInsightsPreCommitHook();
            $result = callProtected($hook, 'additionalParams');

            expect($result)->toBe('');
        });
    });

    describe('RectorPreCommitHook', function () {
        test('configParam returns empty string when no config set', function () {
            config(['git-hooks.code_analyzers.rector.config' => '']);

            $hook = new RectorPreCommitHook();
            $result = callProtected($hook, 'configParam');

            expect($result)->toBe('');
        });

        test('configParam returns config flag when config is set', function () {
            config(['git-hooks.code_analyzers.rector.config' => 'rector.php']);

            $hook = new RectorPreCommitHook();
            $result = callProtected($hook, 'configParam');

            expect($result)->toBe('--config=rector.php');
        });

        test('additionalParams strips --config flag', function () {
            config(['git-hooks.code_analyzers.rector.additional_params' => '--config=rector.php --dry-run']);

            $hook = new RectorPreCommitHook();
            $result = callProtected($hook, 'additionalParams');

            expect($result)->not->toContain('--config=');
        });

        test('additionalParams strips --dry-run flag', function () {
            config(['git-hooks.code_analyzers.rector.additional_params' => '--dry-run --clear-cache']);

            $hook = new RectorPreCommitHook();
            $result = callProtected($hook, 'additionalParams');

            expect($result)->not->toContain('--dry-run');
            expect($result)->toContain('--clear-cache');
        });

        test('additionalParams strips --c shorthand', function () {
            config(['git-hooks.code_analyzers.rector.additional_params' => '--c=rector.php --clear-cache']);

            $hook = new RectorPreCommitHook();
            $result = callProtected($hook, 'additionalParams');

            expect($result)->not->toContain('--c=');
            expect($result)->toContain('--clear-cache');
        });

        test('additionalParams returns empty when no params', function () {
            config(['git-hooks.code_analyzers.rector.additional_params' => '']);

            $hook = new RectorPreCommitHook();
            $result = callProtected($hook, 'additionalParams');

            expect($result)->toBe('');
        });
    });

    describe('PHPCodeSnifferPreCommitHook', function () {
        test('analyzerCommand returns phpcs command with standard', function () {
            config(['git-hooks.validate_paths' => false]);
            config(['git-hooks.code_analyzers.php_code_sniffer.config' => 'PSR12']);

            $hook = new PHPCodeSnifferPreCommitHook();
            $hook->setCwd(base_path())
                ->setAnalyzerExecutable('vendor/bin/phpcs')
                ->setFixerExecutable('vendor/bin/phpcbf')
                ->setDockerContainer('');

            $configParamProp = (new \ReflectionClass($hook))->getProperty('configParam');
            $configParamProp->setAccessible(true);
            $configParamProp->setValue($hook, '--standard=PSR12');

            $command = $hook->analyzerCommand();

            expect($command)->toContain('vendor/bin/phpcs');
            expect($command)->toContain('--standard=PSR12');
        });

        test('fixerCommand returns phpcbf command with standard', function () {
            config(['git-hooks.validate_paths' => false]);
            config(['git-hooks.code_analyzers.php_code_sniffer.config' => 'PSR12']);

            $hook = new PHPCodeSnifferPreCommitHook();
            $hook->setCwd(base_path())
                ->setAnalyzerExecutable('vendor/bin/phpcs')
                ->setFixerExecutable('vendor/bin/phpcbf')
                ->setDockerContainer('');

            $configParamProp = (new \ReflectionClass($hook))->getProperty('configParam');
            $configParamProp->setAccessible(true);
            $configParamProp->setValue($hook, '--standard=PSR12');

            $command = $hook->fixerCommand();

            expect($command)->toContain('vendor/bin/phpcbf');
            expect($command)->toContain('--standard=PSR12');
        });

        test('analyzerCommand and fixerCommand use different executables', function () {
            $hook = new PHPCodeSnifferPreCommitHook();
            $hook->setCwd(base_path())
                ->setAnalyzerExecutable('vendor/bin/phpcs')
                ->setFixerExecutable('vendor/bin/phpcbf')
                ->setDockerContainer('');

            $configParamProp = (new \ReflectionClass($hook))->getProperty('configParam');
            $configParamProp->setAccessible(true);
            $configParamProp->setValue($hook, '');

            expect($hook->analyzerCommand())->toContain('phpcs');
            expect($hook->fixerCommand())->toContain('phpcbf');
            expect($hook->analyzerCommand())->not->toBe($hook->fixerCommand());
        });

        test('analyzerCommand returns empty configParam when no standard set', function () {
            config(['git-hooks.validate_paths' => false]);
            config(['git-hooks.code_analyzers.php_code_sniffer.config' => '']);

            $hook = new PHPCodeSnifferPreCommitHook();
            $hook->setCwd(base_path())
                ->setAnalyzerExecutable('vendor/bin/phpcs')
                ->setFixerExecutable('vendor/bin/phpcbf')
                ->setDockerContainer('');

            $configParamProp = (new \ReflectionClass($hook))->getProperty('configParam');
            $configParamProp->setAccessible(true);
            $configParamProp->setValue($hook, '');

            $command = $hook->analyzerCommand();

            expect($command)->toBe('vendor/bin/phpcs');
        });

        test('has correct name', function () {
            $hook = new PHPCodeSnifferPreCommitHook();
            expect($hook->getName())->toBe('PHP_CodeSniffer');
        });

        test('configParam method returns standard config', function () {
            config(['git-hooks.validate_paths' => false]);
            config(['git-hooks.code_analyzers.php_code_sniffer.config' => 'PSR2']);

            $hook = new PHPCodeSnifferPreCommitHook();
            $result = $hook->configParam();

            expect($result)->toBe('--standard=PSR2');
        });

        test('configParam method returns empty when no standard', function () {
            config(['git-hooks.validate_paths' => false]);
            config(['git-hooks.code_analyzers.php_code_sniffer.config' => '']);

            $hook = new PHPCodeSnifferPreCommitHook();
            $result = $hook->configParam();

            expect($result)->toBe('');
        });
    });
});
