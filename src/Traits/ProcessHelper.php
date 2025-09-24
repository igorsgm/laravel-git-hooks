<?php

declare(strict_types=1);

namespace Igorsgm\GitHooks\Traits;

use RuntimeException;
use Symfony\Component\Process\Process;

/**
 * @codeCoverageIgnore
 */
trait ProcessHelper
{
    private string $cwd;

    /**
     * Run the given commands.
     *
     * @param  string|array<int, string>  $commands
     * @param  array<string, mixed>  $params
     */
    public function runCommands(string|array $commands, array $params = []): Process
    {
        /** @phpstan-ignore-next-line */
        $output = method_exists($this, 'getOutput') ? $this->getOutput() : null;

        if ($output && !$output->isDecorated()) {
            $commands = $this->transformCommands($commands, fn ($value) => $value.' --no-ansi');
        }

        if (data_get($params, 'silent')) {
            $commands = $this->transformCommands($commands, fn ($value) => $this->buildNoOutputCommand($value));
        }

        $process = Process::fromShellCommandline(
            implode(' && ', (array) $commands),
            data_get($params, 'cwd', $this->cwd ?? null),
            data_get($params, 'env'),
            data_get($params, 'input'),
            data_get($params, 'timeout')
        );

        $showOutput = (data_get($params, 'tty') === true || data_get($params, 'show-output') === true) && $output;
        if ($showOutput && DIRECTORY_SEPARATOR !== '\\' && file_exists('/dev/tty') && is_readable('/dev/tty')) {
            try {
                $process->setTty(true);
            } catch (RuntimeException $e) {
                $output->writeln('  <bg=yellow;fg=black> WARN </> '.$e->getMessage().PHP_EOL);
            }
        }

        $process->run(!$showOutput ? null : function (string $line) use ($output): void {
            /** @phpstan-ignore-next-line */
            if ($output !== null) {
                $output->write('    '.$line);
            }
        });

        return $process;
    }

    /**
     * @param  string|array<int, string>  $commands
     * @return array<int, string>
     */
    public function transformCommands(string|array $commands, callable $callback): array
    {
        return array_map(function ($value) use ($callback) {
            if (str_starts_with($value, 'chmod')) {
                return $value;
            }

            return $callback($value);
        }, (array) $commands);
    }

    /**
     * Builds the string for a command without console output
     */
    public function buildNoOutputCommand(string $command = ''): string
    {
        return mb_trim($command).' > '.(PHP_OS_FAMILY === 'Windows' ? 'NUL' : '/dev/null 2>&1');
    }

    public function setCwd(string $cwd): self
    {
        $this->cwd = $cwd;

        return $this;
    }
}
