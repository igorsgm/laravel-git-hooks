<?php

declare(strict_types=1);

namespace Igorsgm\GitHooks\Console\Commands\Hooks;

use Closure;
use Igorsgm\GitHooks\Contracts\CodeAnalyzerPreCommitHook;
use Igorsgm\GitHooks\Facades\GitHooks;
use Igorsgm\GitHooks\Git\ChangedFiles;
use Illuminate\Support\Facades\Artisan;

class EnlightnPreCommitHook extends BaseCodeAnalyzerPreCommitHook implements CodeAnalyzerPreCommitHook
{
    /**
     * Name of the hook
     */
    protected string $name = 'Enlightn';

    /**
     * Analyzes committed files using Enlightn
     *
     * @param  ChangedFiles  $files  The list of committed files to analyze.
     * @param  Closure  $next  The next hook in the chain to execute.
     */
    public function handle(ChangedFiles $files, Closure $next): mixed
    {
        $commitFiles = $files->getAddedToCommit();

        if ($commitFiles->isEmpty() || GitHooks::isMergeInProgress()) {
            return $next($files);
        }

        if (Artisan::call('enlightn') !== 0) {
            $this->commitFailMessage()
                ->suggestAutoFixOrExit();
        }

        return $next($files);
    }

    /**
     * Returns the command to run Enlightn analyzer
     */
    public function analyzerCommand(): string
    {
        return 'php artisan enlightn';
    }

    /**
     * Empty fixer command because Enlightn doesn't provide any type of auto-fixing.
     */
    public function fixerCommand(): string
    {
        return '';
    }

    /**
     * Returns the message to display when the commit fails.
     */
    protected function commitFailMessage(): self
    {
        $this->command->newLine();

        $message = '<bg=red;fg=white> COMMIT FAILED </> ';
        $message .= sprintf(
            'You can check which %s errors happened by executing: <comment>%s</comment>',
            $this->getName(),
            $this->analyzerCommand()
        );

        $this->command->getOutput()->writeln($message);

        return $this;
    }
}
