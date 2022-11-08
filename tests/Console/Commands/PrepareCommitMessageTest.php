<?php

namespace Igorsgm\GitHooks\Tests\Console\Commands;

use Closure;
use Exception;
use Igorsgm\GitHooks\Console\Commands\PrepareCommitMessage;
use Igorsgm\GitHooks\Contracts\MessageHook;
use Igorsgm\GitHooks\Git\GitHelper;
use Igorsgm\GitHooks\Tests\TestCase;
use Illuminate\Console\OutputStyle;
use Illuminate\Support\Facades\Config;
use Mockery;

class PrepareCommitMessageTest extends TestCase
{
    public function test_get_command_name()
    {
        $commitMessageStorage = $this->makeCommitMessageStorage();

        $command = new PrepareCommitMessage($commitMessageStorage);

        $this->assertEquals('git-hooks:prepare-commit-msg', $command->getName());
    }

    public function test_requires_file_argument()
    {
        $commitMessageStorage = $this->makeCommitMessageStorage();

        $command = new PrepareCommitMessage($commitMessageStorage);

        $this->assertTrue($command->getDefinition()->hasArgument('file'));
    }

    public function test_a_message_should_be_send_through_the_hook_pipes()
    {
        Config::set('git-hooks', [
            'prepare-commit-msg' => [
                PrepareCommitMessageTestHook1::class,
                PrepareCommitMessageTestHook2::class,
            ],
        ]);

        $commitMessageStorage = $this->makeCommitMessageStorage();

        $commitMessageStorage
            ->expects('get')
            ->andReturns('Test commit');

        $commitMessageStorage
            ->expects('update')
            ->with(base_path('tmp/COMMIT_MESSAGE'), 'Test commit hook1 hook2');

        $command = new PrepareCommitMessage($commitMessageStorage);

        $input = Mockery::mock(\Symfony\Component\Console\Input\InputInterface::class);
        $input->expects('getArgument')
            ->twice()
            ->with('file')
            ->andReturns('tmp/COMMIT_MESSAGE');

        $command->setInput($input);

        $output = Mockery::mock(OutputStyle::class);

        $output->expects('writeln')
            ->with('<info>Hook: hook 1...</info>', 32);

        $output->expects('writeln')
            ->with('<info>Hook: hook 2...</info>', 32);

        $command->setOutput($output);

        $gitHelper = Mockery::mock('alias:'.GitHelper::class);
        $gitHelper->expects('getListOfChangedFiles')->andReturns('AM src/ChangedFiles.php');

        $this->assertEquals(0, $command->handle());
    }
}

class PrepareCommitMessageTestHook1 implements MessageHook
{
    /**
     * {@inheritDoc}
     */
    public function handle(\Igorsgm\GitHooks\Git\CommitMessage $message, Closure $next)
    {
        $message->setMessage($message->getMessage().' hook1');

        return $next($message);
    }

    /**
     * {@inheritDoc}
     */
    public function getName(): string
    {
        return 'hook 1';
    }
}

class PrepareCommitMessageTestHook2 implements MessageHook
{
    /**
     * {@inheritDoc}
     */
    public function handle(\Igorsgm\GitHooks\Git\CommitMessage $message, Closure $next)
    {
        $message->setMessage($message->getMessage().' hook2');

        return $next($message);
    }

    /**
     * {@inheritDoc}
     */
    public function getName(): string
    {
        return 'hook 2';
    }
}

class PrepareCommitMessageTestHook3 implements MessageHook
{
    /**
     * {@inheritDoc}
     */
    public function handle(\Igorsgm\GitHooks\Git\CommitMessage $message, Closure $next)
    {
        $message->setMessage($message->getMessage().' hook2');

        throw new Exception('Failed hook');
    }

    /**
     * {@inheritDoc}
     */
    public function getName(): string
    {
        return 'hook 3';
    }
}
