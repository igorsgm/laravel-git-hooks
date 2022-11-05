<?php

namespace Igorsgm\LaravelGitHooks\Console\Commands;

use Igorsgm\LaravelGitHooks\Contracts\CommitMessageStorage;
use Igorsgm\LaravelGitHooks\Contracts\HookCommand;
use Igorsgm\LaravelGitHooks\Traits\WithCommitMessage;
use Illuminate\Console\Command;
use Illuminate\Contracts\Config\Repository;

class CommitMessage extends Command implements HookCommand
{
    use WithCommitMessage;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'git-hooks:commit-msg {file}';

    /**
     * The console command description.
     */
    protected $description = 'Run hook commit-msg';

    /**
     * @param  Repository  $config
     * @param  CommitMessageStorage  $messageStorage
     */
    public function __construct(Repository $config, CommitMessageStorage $messageStorage)
    {
        parent::__construct();

        $this->config = $config;
        $this->messageStorage = $messageStorage;
    }

    /**
     * {@inheritDoc}
     */
    public function getHook(): string
    {
        return 'commit-msg';
    }
}
