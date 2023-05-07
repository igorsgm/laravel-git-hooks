<?php

namespace Igorsgm\GitHooks\Traits;

use RuntimeException;
use Symfony\Component\Process\Process;

/**
 * @codeCoverageIgnore
 */
trait ProcessHelper
{
    private $cwd;

    /**
     * Run the given commands.
     *
     *
     * @param  array|string  $commands
     * @param  array  $params
     * @return \Symfony\Component\Process\Process
     */
    public function runCommands($commands, $params = [])
    {
        $input = $this->input ?? null;
        $output = method_exists($this, 'getOutput') ? $this->getOutput() : null;

        if ($output && ! $output->isDecorated()) {
            $commands = $this->transformCommands($commands, function ($value) {
                return $value.' --no-ansi';
            });
        }

        if (! empty($input->definition) && $input->definition->hasOption('quiet') &&
            ! empty($input) && $input->getOption('quiet')
        ) {
            $commands = $this->transformCommands($commands, function ($value) {
                return $value.' --quiet';
            });
        }

        if (data_get($params, 'silent')) {
            $commands = $this->transformCommands($commands, function ($value) {
                return $this->buildNoOutputCommand($value);
            });
        }

        $process = Process::fromShellCommandline(
            implode(' && ', (array) $commands),
            data_get($params, 'cwd', $this->cwd ?? null),
            data_get($params, 'env'),
            data_get($params, 'input'),
            data_get($params, 'timeout')
        );

        $showOutput = data_get($params, 'tty') === true || data_get($params, 'show-output') === true;
        if ($showOutput && '\\' !== DIRECTORY_SEPARATOR && file_exists('/dev/tty') && is_readable('/dev/tty')) {
            try {
                $process->setTty(true);
            } catch (RuntimeException $e) {
                $output->writeln('  <bg=yellow;fg=black> WARN </> '.$e->getMessage().PHP_EOL);
            }
        }

        $process->run(! $showOutput ? null : function ($type, $line) use ($output) {
            $output->write('    '.$line);
        });

        return $process;
    }

    /**
     * @param  array|string  $commands
     * @param  callable  $callback
     * @return array
     */
    public function transformCommands($commands, $callback)
    {
        return array_map(function ($value) use ($callback) {
            if (substr($value, 0, 5) === 'chmod') {
                return $value;
            }

            return $callback($value);
        }, (array) $commands);
    }

    /**
     * Builds the string for a command without console output
     *
     * @param  string  $command
     * @return string
     */
    public function buildNoOutputCommand($command = '')
    {
        return trim($command).' > '.(PHP_OS_FAMILY == 'Windows' ? 'NUL' : '/dev/null 2>&1');
    }

    /**
     * @param  string  $cwd
     * @return $this
     */
    public function setCwd($cwd)
    {
        $this->cwd = $cwd;

        return $this;
    }
}
