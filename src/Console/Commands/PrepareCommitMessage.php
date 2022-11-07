<?php

namespace Igorsgm\GitHooks\Console\Commands;

use Igorsgm\GitHooks\Contracts\CommitMessageStorage;
use Igorsgm\GitHooks\Contracts\HookCommand;
use Igorsgm\GitHooks\Traits\WithCommitMessage;
use Illuminate\Console\Command;
use Illuminate\Contracts\Config\Repository;

class PrepareCommitMessage extends Command implements HookCommand
{
    use WithCommitMessage;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'git-hooks:prepare-commit-msg {file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run hook prepare-commit-msg';

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
     * Get hook name
     *
     * @return string
     */
    public function getHook(): string
    {
        return 'prepare-commit-msg';
    }
}
