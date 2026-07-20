<?php

declare(strict_types=1);

namespace Igorsgm\GitHooks\Tests\Unit\Traits;

use Igorsgm\GitHooks\Git\ChangedFile;
use Igorsgm\GitHooks\Traits\WithDockerSupport;
use Igorsgm\GitHooks\Traits\WithFileAnalysis;
use Illuminate\Support\Collection;
use Mockery;
use ReflectionClass;

describe('WithDockerSupport Trait', function () {
    beforeEach(function () {
        $this->hook = new class
        {
            use WithDockerSupport;

            public function __construct() {}
        };
    });

    test('sets and gets run in docker flag', function () {
        expect($this->hook->getRunInDocker())->toBeFalse();

        $this->hook->setRunInDocker(true);
        expect($this->hook->getRunInDocker())->toBeTrue();

        $this->hook->setRunInDocker(false);
        expect($this->hook->getRunInDocker())->toBeFalse();
    });

    test('sets and gets docker container', function () {
        expect($this->hook->getDockerContainer())->toBe('');

        $this->hook->setDockerContainer('my-app');
        expect($this->hook->getDockerContainer())->toBe('my-app');
    });

    test('returns command unchanged when not running in docker', function () {
        $this->hook->setRunInDocker(false);
        $this->hook->setDockerContainer('');

        expect($this->hook->dockerCommand('pint'))->toBe('pint');
    });

    test('returns command unchanged when docker container is empty', function () {
        $this->hook->setRunInDocker(true);
        $this->hook->setDockerContainer('');

        expect($this->hook->dockerCommand('pint'))->toBe('pint');
    });

    test('wraps command in docker exec when enabled', function () {
        $this->hook->setRunInDocker(true);
        $this->hook->setDockerContainer('my-app');

        $result = $this->hook->dockerCommand('pint --test src');

        expect($result)->toContain('docker exec');
        expect($result)->toContain('my-app');
        expect($result)->toContain('pint --test src');
    });

    test('escapes docker container and command properly', function () {
        $this->hook->setRunInDocker(true);
        $this->hook->setDockerContainer('my-app');

        $result = $this->hook->dockerCommand('pint --test "src/app.php"');

        expect($result)->toContain('docker exec');
        expect($result)->toContain('sh -c');
    });
});

describe('WithFileAnalysis Trait', function () {
    beforeEach(function () {
        $this->hook = new class
        {
            use WithDockerSupport;
            use WithFileAnalysis;

            protected array|string $fileExtensions = '/\.php$/';

            public function __construct() {}

            public function getFileExtensions(): array|string
            {
                return $this->fileExtensions;
            }

            public function getAnalyzerExecutable(): string
            {
                return 'pint';
            }

            public function getName(): string
            {
                return 'Test';
            }

            public function analyzerCommand(): string
            {
                return 'pint --test';
            }

            public function dockerCommand(string $command): string
            {
                return $command;
            }

            public function runCommands(string $command, array $params = []): mixed
            {
                return Mockery::mock(\Symfony\Component\Process\Process::class)
                    ->shouldReceive('isSuccessful')->andReturn(true)->getMock();
            }

            public function outputDebugCommandIfEnabled($process): void {}
        };
    });

    test('canFileBeAnalyzed returns true for matching extension', function () {
        $file = Mockery::mock(ChangedFile::class);
        $file->shouldReceive('getFilePath')->andReturn('src/User.php');

        expect($this->hook->canFileBeAnalyzed($file))->toBeTrue();
    });

    test('canFileBeAnalyzed returns false for non-matching extension', function () {
        $file = Mockery::mock(ChangedFile::class);
        $file->shouldReceive('getFilePath')->andReturn('src/User.js');

        expect($this->hook->canFileBeAnalyzed($file))->toBeFalse();
    });

    test('canFileBeAnalyzed returns true when extensions is "all"', function () {
        $file = Mockery::mock(ChangedFile::class);
        $file->shouldReceive('getFilePath')->andReturn('src/User.anything');

        $reflection = new ReflectionClass($this->hook);
        $property = $reflection->getProperty('fileExtensions');
        $property->setAccessible(true);
        $property->setValue($this->hook, 'all');

        expect($this->hook->canFileBeAnalyzed($file))->toBeTrue();
    });

    test('canFileBeAnalyzed returns true when extensions is empty', function () {
        $file = Mockery::mock(ChangedFile::class);
        $file->shouldReceive('getFilePath')->andReturn('src/User.anything');

        $reflection = new ReflectionClass($this->hook);
        $property = $reflection->getProperty('fileExtensions');
        $property->setAccessible(true);
        $property->setValue($this->hook, '');

        expect($this->hook->canFileBeAnalyzed($file))->toBeTrue();
    });

    test('canFileBeAnalyzed uses regex pattern for string extensions', function () {
        $file = Mockery::mock(ChangedFile::class);
        $file->shouldReceive('getFilePath')->andReturn('src/User.php');

        $reflection = new ReflectionClass($this->hook);
        $property = $reflection->getProperty('fileExtensions');
        $property->setAccessible(true);
        $property->setValue($this->hook, '/\.php$/');

        expect($this->hook->canFileBeAnalyzed($file))->toBeTrue();
    });

    test('canFileBeAnalyzed rejects non-matching regex pattern', function () {
        $file = Mockery::mock(ChangedFile::class);
        $file->shouldReceive('getFilePath')->andReturn('src/User.js');

        $reflection = new ReflectionClass($this->hook);
        $property = $reflection->getProperty('fileExtensions');
        $property->setAccessible(true);
        $property->setValue($this->hook, '/\.php$/');

        expect($this->hook->canFileBeAnalyzed($file))->toBeFalse();
    });

    test('canFileBeAnalyzed handles array extensions via base class', function () {
        $file = Mockery::mock(ChangedFile::class);
        $file->shouldReceive('getFilePath')->andReturn('src/User.php');

        $reflection = new ReflectionClass($this->hook);
        $property = $reflection->getProperty('fileExtensions');
        $property->setAccessible(true);
        $property->setValue($this->hook, ['php', 'js']);

        expect($this->hook->canFileBeAnalyzed($file))->toBeFalse();
    });

    test('getAnalyzableFilePaths filters files correctly', function () {
        $files = new Collection([
            Mockery::mock(ChangedFile::class)->shouldReceive('getFilePath')->andReturn('src/User.php')->getMock(),
            Mockery::mock(ChangedFile::class)->shouldReceive('getFilePath')->andReturn('src/User.js')->getMock(),
            Mockery::mock(ChangedFile::class)->shouldReceive('getFilePath')->andReturn('src/Service.php')->getMock(),
        ]);

        $reflection = new ReflectionClass($this->hook);
        $method = $reflection->getMethod('getAnalyzableFilePaths');
        $method->setAccessible(true);

        $result = array_values($method->invoke($this->hook, $files));

        expect($result)->toBe(['src/User.php', 'src/Service.php']);
    });

    test('getAnalyzableFilePaths returns empty array when no files match', function () {
        $files = new Collection([
            Mockery::mock(ChangedFile::class)->shouldReceive('getFilePath')->andReturn('src/User.js')->getMock(),
            Mockery::mock(ChangedFile::class)->shouldReceive('getFilePath')->andReturn('src/User.vue')->getMock(),
        ]);

        $reflection = new ReflectionClass($this->hook);
        $method = $reflection->getMethod('getAnalyzableFilePaths');
        $method->setAccessible(true);

        $result = $method->invoke($this->hook, $files);

        expect($result)->toBe([]);
    });

    test('analyzeCommittedFiles processes files in chunks', function () {
        $files = new Collection([
            Mockery::mock(ChangedFile::class)->shouldReceive('getFilePath')->andReturn('src/User.php')->getMock(),
        ]);

        $reflection = new ReflectionClass($this->hook);
        $property = $reflection->getProperty('chunkSize');
        $property->setAccessible(true);
        $property->setValue($this->hook, 1);

        $result = $this->hook->analyzeCommittedFiles($files);

        expect($result)->toBe($this->hook);
    });

    test('analyzeCommittedFiles skips when no analyzable files', function () {
        $files = new Collection([
            Mockery::mock(ChangedFile::class)->shouldReceive('getFilePath')->andReturn('src/User.js')->getMock(),
        ]);

        $reflection = new ReflectionClass($this->hook);
        $property = $reflection->getProperty('chunkSize');
        $property->setAccessible(true);
        $property->setValue($this->hook, 1);

        $result = $this->hook->analyzeCommittedFiles($files);

        expect($result)->toBe($this->hook);
    });
});

describe('WithFileAnalysis Trait - analyzeFiles method', function () {
    test('analyzeFiles handles successful process', function () {
        $hook = new class
        {
            use WithDockerSupport;
            use WithFileAnalysis;

            public $command;

            protected array|string $fileExtensions = '/\.php$/';

            public function __construct() {}

            public function getFileExtensions(): array|string
            {
                return $this->fileExtensions;
            }

            public function getAnalyzerExecutable(): string
            {
                return 'pint';
            }

            public function getName(): string
            {
                return 'Test';
            }

            public function analyzerCommand(): string
            {
                return 'pint --test';
            }

            public function dockerCommand(string $command): string
            {
                return $command;
            }

            public function runCommands(string $command, array $params = []): mixed
            {
                $process = Mockery::mock(\Symfony\Component\Process\Process::class);
                $process->shouldReceive('isSuccessful')->andReturn(true);

                return $process;
            }

            public function outputDebugCommandIfEnabled($process): void {}
        };

        $reflection = new ReflectionClass($hook);
        $method = $reflection->getMethod('analyzeFiles');
        $method->setAccessible(true);

        $method->invoke($hook, ['src/User.php']);

        expect(true)->toBeTrue();
    });

    test('analyzeFiles calls outputDebugCommandIfEnabled when debug_commands is enabled', function () {
        config(['git-hooks.debug_commands' => true]);

        $debugCalled = false;

        $hook = new class($debugCalled)
        {
            use WithDockerSupport;
            use WithFileAnalysis;

            protected array|string $fileExtensions = '/\.php$/';

            public function __construct(public bool &$debugCalled) {}

            public function getFileExtensions(): array|string
            {
                return $this->fileExtensions;
            }

            public function getAnalyzerExecutable(): string
            {
                return 'pint';
            }

            public function getName(): string
            {
                return 'Test';
            }

            public function analyzerCommand(): string
            {
                return 'pint --test';
            }

            public function dockerCommand(string $command): string
            {
                return $command;
            }

            public function runCommands(string $command, array $params = []): mixed
            {
                $process = Mockery::mock(\Symfony\Component\Process\Process::class);
                $process->shouldReceive('isSuccessful')->andReturn(true);

                return $process;
            }

            public function outputDebugCommandIfEnabled($process): void
            {
                $this->debugCalled = true;
            }
        };

        $reflection = new ReflectionClass($hook);
        $method = $reflection->getMethod('analyzeFiles');
        $method->setAccessible(true);

        $method->invoke($hook, ['src/User.php']);

        expect($debugCalled)->toBeTrue();

        config(['git-hooks.debug_commands' => false]);
    });
});

describe('WithFileAnalysis Trait - handleAnalysisFailure', function () {
    test('handleAnalysisFailure outputs errors when output_errors is enabled', function () {
        config(['git-hooks.output_errors' => true]);
        config(['git-hooks.debug_output' => false]);

        $hook = new class
        {
            use WithDockerSupport;
            use WithFileAnalysis;

            public $command;

            protected array|string $fileExtensions = '/\.php$/';

            public function __construct()
            {
                $this->command = Mockery::mock(\Illuminate\Console\Command::class);
                $output = Mockery::mock(\Symfony\Component\Console\Output\OutputInterface::class);
                $output->shouldReceive('writeln')->once();
                $output->shouldReceive('write')->once();
                $this->command->shouldReceive('newLine')->twice();
                $this->command->shouldReceive('getOutput')->andReturn($output);
            }

            public function getFileExtensions(): array|string
            {
                return $this->fileExtensions;
            }

            public function getName(): string
            {
                return 'TestHook';
            }
        };

        $process = Mockery::mock(\Symfony\Component\Process\Process::class);
        $process->shouldReceive('isSuccessful')->andReturn(false);
        $process->shouldReceive('getOutput')->andReturn('error output');

        $reflection = new ReflectionClass($hook);
        $method = $reflection->getMethod('handleAnalysisFailure');
        $method->setAccessible(true);

        $method->invoke($hook, 'src/User.php', $process);

        $property = $reflection->getProperty('filesBadlyFormattedPaths');
        $property->setAccessible(true);
        $filesBadlyFormattedPaths = $property->getValue($hook);

        expect($filesBadlyFormattedPaths)->toHaveCount(1);
        expect($filesBadlyFormattedPaths[0])->toBe('src/User.php');

        config(['git-hooks.output_errors' => false]);
    });

    test('handleAnalysisFailure does not output errors when output_errors is disabled', function () {
        config(['git-hooks.output_errors' => false]);
        config(['git-hooks.debug_output' => false]);

        $hook = new class
        {
            use WithDockerSupport;
            use WithFileAnalysis;

            public $command;

            protected array|string $fileExtensions = '/\.php$/';

            public function __construct()
            {
                $this->command = Mockery::mock(\Illuminate\Console\Command::class);
                $output = Mockery::mock(\Symfony\Component\Console\Output\OutputInterface::class);
                $output->shouldReceive('writeln')->once();
                $this->command->shouldReceive('newLine')->once();
                $this->command->shouldReceive('getOutput')->andReturn($output);
            }

            public function getFileExtensions(): array|string
            {
                return $this->fileExtensions;
            }

            public function getName(): string
            {
                return 'TestHook';
            }
        };

        $process = Mockery::mock(\Symfony\Component\Process\Process::class);
        $process->shouldReceive('isSuccessful')->andReturn(false);

        $reflection = new ReflectionClass($hook);
        $method = $reflection->getMethod('handleAnalysisFailure');
        $method->setAccessible(true);

        $method->invoke($hook, 'src/User.php', $process);

        $property = $reflection->getProperty('filesBadlyFormattedPaths');
        $property->setAccessible(true);
        $filesBadlyFormattedPaths = $property->getValue($hook);

        expect($filesBadlyFormattedPaths)->toHaveCount(1);
    });
});
