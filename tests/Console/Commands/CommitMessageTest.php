<?php

namespace Igorsgm\GitHooks\Tests\Console\Commands;

use Closure;
use Exception;
use Igorsgm\GitHooks\Console\Commands\CommitMessage;
use Igorsgm\GitHooks\Contracts\MessageHook;
use Igorsgm\GitHooks\Git\GitHelper;
use Igorsgm\GitHooks\Tests\TestCase;
use Illuminate\Config\Repository;
use Illuminate\Console\OutputStyle;
use Mockery;

class CommitMessageTest extends TestCase
{
    public function test_get_command_name()
    {
        $config = $this->makeConfig();
        $commitMessageStorage = $this->makeCommitMessageStorage();

        $command = new CommitMessage($config, $commitMessageStorage);

        $this->assertEquals('git-hooks:commit-msg', $command->getName());
    }

    public function test_requires_file_argument()
    {
        $config = $this->makeConfig();
        $commitMessageStorage = $this->makeCommitMessageStorage();

        $command = new CommitMessage($config, $commitMessageStorage);

        $this->assertTrue($command->getDefinition()->hasArgument('file'));
    }

    public function test_a_message_should_be_send_through_the_hook_pipes()
    {
        $app = $this->makeApplication();
        $app->allows('basePath')->andReturnUsing(function ($path = null) {
            return $path;
        });

        $app->allows('make')->andReturnUsing(function ($class) {
            return new $class;
        });

        $config = new Repository([
            'git-hooks' => [
                'commit-msg' => [
                    CommitMessageTestHook1::class,
                    CommitMessageTestHook2::class,
                ],
            ],
        ]);

        $commitMessageStorage = $this->makeCommitMessageStorage();

        $commitMessageStorage
            ->expects('get')
            ->andReturns('Test commit');

        $commitMessageStorage
            ->expects('update')
            ->with('tmp/COMMIT_MESSAGE', 'Test commit hook1 hook2');

        $command = new CommitMessage($config, $commitMessageStorage);

        $command->setLaravel($app);

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

        $this->assertEquals(0, $command->handle());

        $this->assertTrue(true);
    }

    public function test_pass_hook_config_into_hook_object()
    {
        $app = $this->makeApplication();
        $app->allows('make')->andReturnUsing(function ($class, array $parameters = []) {
            return new $class(...array_values($parameters));
        });

        $app->allows('basePath')->andReturnUsing(function ($path = null) {
            return $path;
        });

        $config = new Repository([
            'git-hooks' => [
                'commit-msg' => [
                    CommitMessageTestHook4::class => [
                        'param1' => 'hello',
                        'param2' => 'world',
                    ],
                ],
            ],
        ]);

        $commitMessageStorage = $this->makeCommitMessageStorage();

        $commitMessageStorage
            ->expects('get')
            ->andReturns('Test commit');

        $commitMessageStorage
            ->expects('update')
            ->with('tmp/COMMIT_MESSAGE', 'Test commit hello world');

        $command = new CommitMessage($config, $commitMessageStorage);

        $command->setLaravel($app);

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
    protected $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * {@inheritDoc}
     */
    public function handle(\Igorsgm\GitHooks\Git\CommitMessage $message, Closure $next)
    {
        $message->setMessage($message->getMessage().' '.$this->config['param1'].' '.$this->config['param2']);

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
