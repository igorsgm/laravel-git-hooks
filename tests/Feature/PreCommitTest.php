<?php

use Igorsgm\GitHooks\Contracts\PreCommitHook;
use Igorsgm\GitHooks\Exceptions\HookFailException;
use Igorsgm\GitHooks\Facades\GitHooks;
use Igorsgm\GitHooks\Git\ChangedFiles;

it('sends ChangedFiles through HookPipes', function () {
    $changedFilesString = 'AM src/ChangedFiles.php';

    $preCommitHook1 = mock(PreCommitHook::class)->expect(
        handle: function (ChangedFiles $files, Closure $closure) use ($changedFilesString) {
            $firstChangedFile = (string) $files->getFiles()->first();
            expect($firstChangedFile)->toBe($changedFilesString);
        }
    );
    $preCommitHook2 = clone $preCommitHook1;

    $this->config->set('git-hooks.pre-commit', [
        $preCommitHook1,
        $preCommitHook2,
    ]);

    GitHooks::shouldReceive('getListOfChangedFiles')->andReturn($changedFilesString);

    $this->artisan('git-hooks:pre-commit')->assertSuccessful();
});

it('returns 1 on HookFailException', function () {
    $changedFilesString = 'AM src/ChangedFiles.php';

    $preCommitHook1 = mock(PreCommitHook::class)->expect(
        handle: function (ChangedFiles $files, Closure $closure) {
            throw new HookFailException();
        }
    );

    $this->config->set('git-hooks.pre-commit', [
        $preCommitHook1,
    ]);

    GitHooks::shouldReceive('getListOfChangedFiles')->andReturn($changedFilesString);

    $this->artisan('git-hooks:pre-commit')->assertExitCode(1);
});
