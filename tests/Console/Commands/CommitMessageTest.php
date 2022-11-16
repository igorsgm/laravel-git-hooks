<?php

namespace Igorsgm\GitHooks\Tests\Console\Commands;

use Closure;
use Exception;
use Igorsgm\GitHooks\Console\Commands\CommitMessage;
use Igorsgm\GitHooks\Contracts\MessageHook;
use Igorsgm\GitHooks\Git\GitHelper;
use Igorsgm\GitHooks\Tests\TestCase;
use Illuminate\Console\OutputStyle;
use Illuminate\Support\Facades\Config;
use Mockery;

class CommitMessageTest extends TestCase
{
    public function test_get_command_name()
    {
        $command = new CommitMessage();

        $this->assertEquals('git-hooks:commit-msg', $command->getName());
    }

    public function test_requires_file_argument()
    {
        $command = new CommitMessage();

        $this->assertTrue($command->getDefinition()->hasArgument('file'));
    }

    public function test_a_message_should_be_send_through_the_hook_pipes()
    {
        Config::set('git-hooks', [
            'commit-msg' => [
                CommitMessageTestHook1::class,
                CommitMessageTestHook2::class,
            ],
        ]);

        $command = new CommitMessage();

        $input = Mockery::mock(\Symfony\Component\Console\Input\InputInterface::class);
        $input->allows('getArgument')
            ->with('file')
            ->andReturns('tmp/COMMIT_MESSAGE');

        $output = Mockery::mock(OutputStyle::class);

        $output->expects('writeln')
            ->with('<info>Hook: hook 1...</info>', 32);

        $output->expects('writeln')
            ->with('<info>Hook: hook 2...</info>', 32);

        $command->setOutput($output);
        $command->setInput($input);

        $gitHelper = Mockery::mock('alias:'.GitHelper::class);
        $gitHelper->expects('getListOfChangedFiles')->andReturns('AM src/ChangedFiles.php');
        $gitHelper->expects('getCommitMessageContentFromFile')->andReturns('Test commit');
        $gitHelper->expects('updateCommitMessageContentInFile')->with(base_path('tmp/COMMIT_MESSAGE'), 'Test commit hook1 hook2');

        $this->assertEquals(0, $command->handle());

        $this->assertTrue(true);
    }

    public function test_pass_hook_config_into_hook_object()
    {
        Config::set('git-hooks', [
            'commit-msg' => [
                CommitMessageTestHook4::class => [
                    'param1' => 'hello',
                    'param2' => 'world',
                ],
            ],
        ]);

        $command = new CommitMessage();

        $input = Mockery::mock(\Symfony\Component\Console\Input\InputInterface::class);
        $input->allows('getArgument')
            ->with('file')
            ->andReturns('tmp/COMMIT_MESSAGE');

        $output = Mockery::mock(OutputStyle::class);

        $output->expects('writeln')
            ->with('<info>Hook: hook 4...</info>', 32);

        $command->setOutput($output);
        $command->setInput($input);

        $gitHelper = Mockery::mock('alias:'.GitHelper::class);
        $gitHelper->expects('getListOfChangedFiles')->andReturns('AM src/ChangedFiles.php');
        $gitHelper->expects('getCommitMessageContentFromFile')->andReturns('Test commit');
        $gitHelper->expects('updateCommitMessageContentInFile')->with(base_path('tmp/COMMIT_MESSAGE'), 'Test commit hello world');

        $this->assertEquals(0, $command->handle());

        $this->assertTrue(true);
    }
}

class CommitMessageTestHook1 implements MessageHook
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

class CommitMessageTestHook2 implements MessageHook
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

class CommitMessageTestHook3 implements MessageHook
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

class CommitMessageTestHook4 implements MessageHook
{
    /**
     * @var array
     */
    protected $parameters;

    public function __construct(array $parameters)
    {
        $this->parameters = $parameters;
    }

    /**
     * {@inheritDoc}
     */
    public function handle(\Igorsgm\GitHooks\Git\CommitMessage $message, Closure $next)
    {
        $message->setMessage($message->getMessage().' '.$this->parameters['param1'].' '.$this->parameters['param2']);

        return $next($message);
    }

    /**
     * {@inheritDoc}
     */
    public function getName(): string
    {
        return 'hook 4';
    }
}
