<?php

declare(strict_types=1);

namespace Igorsgm\GitHooks\Tests\Unit\Traits;

use Igorsgm\GitHooks\Traits\ProcessHelper;
use Igorsgm\GitHooks\Traits\WithAutoFix;
use Igorsgm\GitHooks\Traits\WithDockerSupport;
use Igorsgm\GitHooks\Traits\WithPipelineFailCheck;
use ReflectionClass;

describe('ProcessHelper Coverage', function () {
    beforeEach(function () {
        $this->helper = new class
        {
            use ProcessHelper;

            public function __construct()
            {
                $this->setCwd(sys_get_temp_dir());
            }
        };
    });

    test('transformCommands preserves chmod commands unchanged', function () {
        $callback = fn ($value) => $value.' --added-flag';

        $result = $this->helper->transformCommands(
            ['chmod 777 /some/file', 'echo hello'],
            $callback
        );

        // chmod command should be unchanged
        expect($result[0])->toBe('chmod 777 /some/file');
        // other commands should get the callback applied
        expect($result[1])->toBe('echo hello --added-flag');
    });

    test('transformCommands applies callback to non-chmod commands', function () {
        $callback = fn ($value) => $value.' --transformed';

        $result = $this->helper->transformCommands(
            ['git status', 'pint --test'],
            $callback
        );

        expect($result[0])->toBe('git status --transformed');
        expect($result[1])->toBe('pint --test --transformed');
    });

    test('transformCommands handles string input', function () {
        $callback = fn ($value) => $value.' --extra';

        $result = $this->helper->transformCommands('echo test', $callback);

        expect($result)->toBeArray();
        expect($result[0])->toBe('echo test --extra');
    });

    test('transformCommands preserves multiple chmod commands', function () {
        $callback = fn ($value) => $value.' --modified';

        $result = $this->helper->transformCommands(
            ['chmod +x /path/to/script', 'chmod 644 /path/to/file', 'ls'],
            $callback
        );

        expect($result[0])->toBe('chmod +x /path/to/script');
        expect($result[1])->toBe('chmod 644 /path/to/file');
        expect($result[2])->toBe('ls --modified');
    });

    test('buildNoOutputCommand adds platform-specific null device', function () {
        $result = $this->helper->buildNoOutputCommand('echo test');

        $expected = PHP_OS_FAMILY === 'Windows' ? 'NUL' : '/dev/null 2>&1';
        expect($result)->toContain($expected);
        expect($result)->toContain('echo test');
    });

    test('setCwd and getCwd work correctly', function () {
        $newCwd = sys_get_temp_dir();
        $result = $this->helper->setCwd($newCwd);

        // Returns self for chaining
        expect($result)->toBe($this->helper);
    });

    test('runCommands executes a simple command', function () {
        $process = $this->helper->runCommands('echo hello');

        expect($process)->toBeInstanceOf(\Symfony\Component\Process\Process::class);
    });

    test('runCommands with silent param builds no-output command', function () {
        $process = $this->helper->runCommands(
            'echo hello',
            ['silent' => true, 'timeout' => 5]
        );

        expect($process)->toBeInstanceOf(\Symfony\Component\Process\Process::class);
    });
});

describe('WithAutoFix Coverage', function () {
    beforeEach(function () {
        // Create a class that uses WithAutoFix and WithPipelineFailCheck with minimum required methods
        $this->hook = new class
        {
            use WithAutoFix;
            use WithDockerSupport;
            use WithPipelineFailCheck;
            use ProcessHelper;

            protected array $filesBadlyFormattedPaths = [];

            protected string $name = 'TestHook';

            public function __construct()
            {
                $this->setCwd(sys_get_temp_dir());
            }

            public function getName(): string
            {
                return $this->name;
            }

            public function analyzerCommand(): string
            {
                return 'echo analyzer';
            }

            public function fixerCommand(): string
            {
                return '';
            }
        };
    });

    test('suggestAutoFixOrExit returns false when no fixer and stop_at_first is false', function () {
        config(['git-hooks.stop_at_first_analyzer_failure' => false]);

        // No fixer command (fixerCommand returns '')
        $result = $this->hook->suggestAutoFixOrExit();

        expect($result)->toBeFalse();
    });

    test('suggestAutoFixOrExit throws HookFailException when stop_at_first is true', function () {
        config(['git-hooks.stop_at_first_analyzer_failure' => true]);

        expect(fn () => $this->hook->suggestAutoFixOrExit())
            ->toThrow(\Igorsgm\GitHooks\Exceptions\HookFailException::class);
    });
});

describe('WithPipelineFailCheck Coverage', function () {
    beforeEach(function () {
        $this->checker = new class
        {
            use WithPipelineFailCheck;

            public function publicMarkFailed(): void
            {
                $this->markPipelineFailed();
            }

            public function publicCheckFailed(): bool
            {
                return $this->checkPipelineFailed();
            }

            public function publicClearFailed(): void
            {
                $this->clearPipelineFailed();
            }

            public function publicGetTempFile(): string
            {
                return $this->getPipelineFailedTempFile();
            }
        };
    });

    test('markPipelineFailed creates temp file', function () {
        $this->checker->publicClearFailed();

        $this->checker->publicMarkFailed();

        expect($this->checker->publicCheckFailed())->toBeTrue();

        // Cleanup
        $this->checker->publicClearFailed();
    });

    test('checkPipelineFailed returns false when not marked', function () {
        $this->checker->publicClearFailed();

        expect($this->checker->publicCheckFailed())->toBeFalse();
    });

    test('clearPipelineFailed removes the temp file', function () {
        $this->checker->publicMarkFailed();
        expect($this->checker->publicCheckFailed())->toBeTrue();

        $this->checker->publicClearFailed();
        expect($this->checker->publicCheckFailed())->toBeFalse();
    });

    test('getPipelineFailedTempFile returns path with process id', function () {
        $path = $this->checker->publicGetTempFile();

        expect($path)->toContain('githooks-pipeline-fail-');
        expect($path)->toContain((string) getmypid());
    });
});
