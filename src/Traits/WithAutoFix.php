<?php

namespace Igorsgm\GitHooks\Traits;

use Igorsgm\GitHooks\Exceptions\HookFailException;
use Symfony\Component\Console\Terminal;

trait WithAutoFix
{
    /**
     * @var array<int, string>
     */
    protected array $filesBadlyFormattedPaths = [];

    public function suggestAutoFixOrExit(): bool
    {
        $hasFixerCommand = ! empty($this->fixerCommand());

        if ($hasFixerCommand) {
            if (config('git-hooks.automatically_fix_errors')) {
                $this->command->getOutput()->writeln(
                    sprintf('<bg=green;fg=white> AUTOFIX </> <fg=green> %s Running Autofix</>', $this->getName())
                );
                if ($this->autoFixFiles()) {
                    return true;
                }
            } elseif ($this->shouldAttemptAutofix() && $this->autoFixFiles()) {
                return true;
            }
        }

        $this->markPipelineFailed();

        if (config('git-hooks.stop_at_first_analyzer_failure')) {
            throw new HookFailException;
        }

        return false;
    }

    private function shouldAttemptAutofix(): bool
    {
        // In test environment, skip Terminal check
        if (app()->environment('testing')) {
            return $this->command->confirm('Would you like to attempt to correct files automagically?');
        }

        return Terminal::hasSttyAvailable() &&
               $this->command->confirm('Would you like to attempt to correct files automagically?');
    }

    private function autoFixFiles(): bool
    {
        $params = ['show-output' => config('git-hooks.debug_output')];

        foreach ($this->filesBadlyFormattedPaths as $key => $filePath) {
            if ($this->attemptToFixFile($filePath, $params)) {
                unset($this->filesBadlyFormattedPaths[$key]);
            }
        }

        return empty($this->filesBadlyFormattedPaths);
    }

    /**
     * @param  array<string, mixed>  $params
     */
    private function attemptToFixFile(string $filePath, array $params): bool
    {
        $fixerCommand = $this->dockerCommand($this->fixerCommand().' '.$filePath);
        $process = $this->runCommands($fixerCommand, $params);

        $this->outputDebugCommandIfEnabled($process);

        if (config('git-hooks.rerun_analyzer_after_autofix')) {
            $process = $this->rerunAnalyzer($filePath, $params);
        }

        if ($process->isSuccessful()) {
            $this->runCommands('git add '.$filePath);

            return true;
        }

        $this->handleFixFailure($filePath, $process);

        return false;
    }

    protected function outputDebugCommandIfEnabled(mixed $process): void
    {
        if (config('git-hooks.debug_commands')) {
            $this->command->newLine();
            $this->command->getOutput()->write(PHP_EOL.' <bg=yellow;fg=white> DEBUG </> Executed command: '.$process->getCommandLine().PHP_EOL);
        }
    }

    /**
     * @param  array<string, mixed>  $params
     */
    protected function rerunAnalyzer(string $filePath, array $params): mixed
    {
        $command = $this->dockerCommand($this->analyzerCommand().' '.$filePath);
        $process = $this->runCommands($command, $params);

        if (config('git-hooks.debug_commands')) {
            $this->outputDebugCommandIfEnabled($process);
        }

        return $process;
    }

    protected function handleFixFailure(string $filePath, mixed $process): void
    {
        if (empty($this->filesBadlyFormattedPaths)) {
            $this->command->newLine();
        }

        $this->command->getOutput()->writeln(
            sprintf('<fg=red> %s Autofix Failed:</> %s', $this->getName(), $filePath)
        );

        if (config('git-hooks.output_errors') && ! config('git-hooks.debug_output')) {
            $this->command->newLine();
            $this->command->getOutput()->write($process->getOutput());
        }
    }
}
