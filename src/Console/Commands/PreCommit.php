<?php

namespace Igorsgm\GitHooks\Console\Commands;

use Igorsgm\GitHooks\Contracts\HookCommand;
use Igorsgm\GitHooks\Exceptions\HookFailException;
use Igorsgm\GitHooks\Git\ChangedFiles;
use Igorsgm\GitHooks\Git\GitHelper;
use Igorsgm\GitHooks\Traits\WithPipeline;
use Illuminate\Console\Command;

class PreCommit extends Command implements HookCommand
{
    use WithPipeline;

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
     * @return mixed
     */
    public function handle()
    {
        try {
            $this->sendChangedFilesThroughHooks(
                new ChangedFiles(
                    GitHelper::getListOfChangedFiles()
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
