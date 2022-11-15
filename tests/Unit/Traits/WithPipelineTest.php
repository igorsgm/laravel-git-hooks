<?php

use Igorsgm\GitHooks\Console\Commands\PreCommit;
use Igorsgm\GitHooks\Tests\Fixtures\PreCommitFixtureHook;
use Symfony\Component\Console\Output\OutputInterface;

test('Starts and Finishes Pipe Task with decorated output', function () {
    $command = new PreCommit();
    $hook = new PreCommitFixtureHook();
    $outputMock = $this->createMock(OutputInterface::class);

    $outputMock->expects($this->once())
        ->method('isDecorated')
        ->willReturn(true);

    $outputMock->expects($this->exactly(3))
        ->method('write')
        ->withConsecutive(
            [$this->equalTo('  <bg=blue;fg=white> HOOK </> '.$hook->getName().': <comment>loading...</comment>')],
            [$this->equalTo("\x0D")],
            [$this->equalTo("\x1B[2K")]
        );

    $outputMock->expects($this->once())
        ->method('writeln')
        ->with('  <bg=blue;fg=white> HOOK </> '.$hook->getName().': <info>✔</info>');

    $commandReflection = new ReflectionClass($command);

    $commandOutputProperty = $commandReflection->getProperty('output');
    $commandOutputProperty->setValue($command, $outputMock);

    $startHookConsoleTask = $commandReflection->getMethod('startHookConsoleTask');
    $startClosure = $startHookConsoleTask->invoke($command);
    expect($startClosure)->toBeCallable();
    $startClosure($hook);

    $finishHookConsoleTask = $commandReflection->getMethod('finishHookConsoleTask');
    $finishClosure = $finishHookConsoleTask->invoke($command);
    expect($finishClosure)->toBeCallable();
    $finishClosure(true);
});
