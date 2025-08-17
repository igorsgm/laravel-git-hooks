<?php

use Igorsgm\GitHooks\Contracts\PreCommitHook;

beforeEach(
    function () {
        $this->initializeGitAsTempDirectory();
    }
);

afterEach(
    function () {
        $this->deleteTempDirectory();
    }
);

test(
    'Throws exception when git was not initialized in project', function () {
        // delete .git folder
        $this->deleteTempDirectory();

        $preCommitHookClass = Mockery::mock(PreCommitHook::class);

        $this->config->set(
            'git-hooks.pre-commit', [
                $preCommitHookClass,
            ]
        );

        expect(fn () => $this->artisan('git-hooks:register'))->toThrow(
            Exception::class,
            'Git not initialized in this project.'
        );
    }
);

test(
    'Installs hook file in .git/hooks folder', function ($hookClass, $hookName) {
        $this->config->set(
            'git-hooks.'.$hookName, [
                $hookClass,
            ]
        );

        $this->artisan('git-hooks:register')->assertSuccessful();

        $hookFile = base_path('.git/hooks/'.$hookName);

        expect($hookFile)
            ->toBeFile()
            ->toContainHookArtisanCommand($hookName);
    }
)->with('registrableHookTypes');

test(
    'Installs hook file in .git/hooks folder with custom artisan path', function ($hookClass, $hookName) {
        $this->config->set(
            'git-hooks.'.$hookName, [
                $hookClass,
            ]
        );

        $this->config->set('git-hooks.artisan_path', base_path('custom-artisan-path'));

        $this->artisan('git-hooks:register')->assertSuccessful();

        $hookFile = base_path('.git/hooks/'.$hookName);

        expect($hookFile)
            ->toBeFile()
            ->toContainHookArtisanCommand($hookName);
    }
)->with('registrableHookTypes');
