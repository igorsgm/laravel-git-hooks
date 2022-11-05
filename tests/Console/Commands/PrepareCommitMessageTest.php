<?php

namespace Igorsgm\LaravelGitHooks\Tests\Console\Commands;

use Closure;
use Exception;
use Igorsgm\LaravelGitHooks\Console\Commands\PrepareCommitMessage;
use Igorsgm\LaravelGitHooks\Contracts\MessageHook;
use Igorsgm\LaravelGitHooks\Git\GetListOfChangedFiles;
use Igorsgm\LaravelGitHooks\Tests\TestCase;
use Illuminate\Config\Repository;
use Illuminate\Console\OutputStyle;
use Mockery;
use Symfony\Component\Process\Process;

class PrepareCommitMessageTest extends TestCase
{
    public function test_get_command_name()
    {
        $config = $this->makeConfig();
        $commitMessageStorage = $this->makeCommitMessageStorage();

        $command = new PrepareCommitMessage($config, $commitMessageStorage);

        $this->assertEquals('git:prepare-commit-msg', $command->getName());
    }

    public function test_requires_file_argument()
    {
        $config = $this->makeConfig();
        $commitMessageStorage = $this->makeCommitMessageStorage();

        $command = new PrepareCommitMessage($config, $commitMessageStorage);

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
                'prepare-commit-msg' => [
                    PrepareCommitMessageTestHook1::class,
                    PrepareCommitMessageTestHook2::class,
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

        $command = new PrepareCommitMessage($config, $commitMessageStorage);

        $input = Mockery::mock(\Symfony\Component\Console\Input\InputInterface::class);
        $input->expects('getArgument')
            ->twice()
            ->with('file')
            ->andReturns('tmp/COMMIT_MESSAGE');

        $command->setLaravel($app);
        $command->setInput($input);

        $output = Mockery::mock(OutputStyle::class);

        $output->expects('writeln')
            ->with('<info>Hook: hook 1...</info>', 32);

        $output->expects('writeln')
            ->with('<info>Hook: hook 2...</info>', 32);

        $command->setOutput($output);

        $process = Mockery::mock(Process::class);
        $process->expects('getOutput')->andReturns('AM src/ChangedFiles.php');

        $gitCommand = Mockery::mock(GetListOfChangedFiles::class);
        $gitCommand->expects('exec')->andReturns($process);

        $this->assertEquals(0, $command->handle($gitCommand));
    }
}

class PrepareCommitMessageTestHook1 implements MessageHook
{
    /**
     * {@inheritDoc}
     */
    public function handle(\Igorsgm\LaravelGitHooks\Git\CommitMessage $message, Closure $next)
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
    public function handle(\Igorsgm\LaravelGitHooks\Git\CommitMessage $message, Closure $next)
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
    public function handle(\Igorsgm\LaravelGitHooks\Git\CommitMessage $message, Closure $next)
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
