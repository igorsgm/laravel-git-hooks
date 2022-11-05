<?php

namespace Igorsgm\LaravelGitHooks\Console\Commands;

use Igorsgm\LaravelGitHooks\Contracts\HookCommand;
use Igorsgm\LaravelGitHooks\Exceptions\HookFailException;
use Igorsgm\LaravelGitHooks\Git\ChangedFiles;
use Igorsgm\LaravelGitHooks\Git\GetListOfChangedFiles;
use Igorsgm\LaravelGitHooks\Traits\WithPipeline;
use Illuminate\Console\Command;
use Illuminate\Contracts\Config\Repository;

class PreCommit extends Command implements HookCommand
{
    use WithPipeline;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'git:pre-commit';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run hook pre-commit';

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
        return 'pre-commit';
    }

    /**
     * Execute the console command.
     *
     * @param  GetListOfChangedFiles  $command
     * @return mixed
     */
    public function handle(GetListOfChangedFiles $command)
    {
        try {
            $this->sendChangedFilesThroughHooks(
                new ChangedFiles(
                    $command->exec()->getOutput()
                )
            );
        } catch (HookFailException $e) {
            return 1;
        }
    }

    /**
     * Send the changed files through the pipes
     *
     * @param  ChangedFiles  $files
     */
    protected function sendChangedFilesThroughHooks(ChangedFiles $files): void
    {
        $this->makePipeline()
            ->send($files)
            ->thenReturn();
    }
}
