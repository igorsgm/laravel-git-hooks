<?php

namespace Igorsgm\LaravelGitHooks\Console\Commands;

use Igorsgm\LaravelGitHooks\Contracts\HookCommand;
use Igorsgm\LaravelGitHooks\Exceptions\HookFailException;
use Igorsgm\LaravelGitHooks\Git\GetLasCommitFromLog;
use Igorsgm\LaravelGitHooks\Git\Log;
use Igorsgm\LaravelGitHooks\Traits\WithPipeline;
use Illuminate\Console\Command;
use Illuminate\Contracts\Config\Repository;

class PostCommit extends Command implements HookCommand
{
    use WithPipeline;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'git:post-commit';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run hook post-commit';

    /**
     * @var Repository
     */
    protected $config;

    /**
     * @param  Repository  $config
     */
    public function __construct(Repository $config)
    {
        parent::__construct();

        $this->config = $config;
    }

    /**
     * {@inheritDoc}
     */
    public function getHook(): string
    {
        return 'post-commit';
    }

    /**
     * Execute the console command.
     *
     * @param  GetLasCommitFromLog  $command
     * @return mixed
     */
    public function handle(GetLasCommitFromLog $command)
    {
        try {
            $this->sendLogCommitThroughHooks(
                new Log(
                    $command->exec()->getOutput()
                )
            );
        } catch (HookFailException $e) {
            return 1;
        }
    }

    /**
     * Send the log commit through the pipes
     *
     * @param  Log  $log
     */
    protected function sendLogCommitThroughHooks(Log $log): void
    {
        $this->makePipeline()
            ->send($log)
            ->thenReturn();
    }
}
