<?php

namespace Igorsgm\GitHooks\Console\Commands;

use Igorsgm\GitHooks\Contracts\HookCommand;
use Igorsgm\GitHooks\Exceptions\HookFailException;
use Igorsgm\GitHooks\Facades\GitHooks;
use Igorsgm\GitHooks\Git\ChangedFiles;
use Igorsgm\GitHooks\Traits\WithPipeline;
use Igorsgm\GitHooks\Traits\WithPipelineFailCheck;
use Illuminate\Console\Command;

class PreCommit extends Command implements HookCommand
{
    use WithPipeline;
    use WithPipelineFailCheck;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'git-hooks:pre-commit';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run hook pre-commit';

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
     * @return int|void
     */
    public function handle()
    {
        try {
            $this->clearPipelineFailed();

            $this->sendChangedFilesThroughHooks(
                new ChangedFiles(
                    GitHooks::getListOfChangedFiles()
                )
            );

            if ($this->checkPipelineFailed()) {
                $this->clearPipelineFailed();
                throw new HookFailException();
            }
        } catch (HookFailException $e) {
            return 1;
        }
    }

    /**
     * Send the changed files through the pipes
     */
    protected function sendChangedFilesThroughHooks(ChangedFiles $files): void
    {
        $this->makePipeline()
            ->send($files)
            ->thenReturn();
    }
}
