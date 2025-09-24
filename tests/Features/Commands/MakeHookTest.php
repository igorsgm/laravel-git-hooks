<?php

declare(strict_types=1);

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

afterEach(function () {
    File::deleteDirectory($this->app->basePath('app/Console/GitHooks'));
    File::deleteDirectory($this->app->basePath('stubs'));
});

it('Handles invalid hook types', function () {
    $this->artisan('git-hooks:make', [
        'hookType' => 'invalid',
        'name' => 'InvalidHook',
    ])
        ->assertFailed()
        ->expectsOutputToContain('ERROR');
});

test('Generates a hook file with a valid hook type', function () {
    $filesystem = Mockery::mock(Filesystem::class)
        ->expects('put')->withArgs(fn ($path, $contents) => Str::contains($path, 'MyCustomPreCommitHook.php'))->andReturns(true)
        ->shouldReceive([
            'isDirectory' => true,
            'exists' => false,
            'get' => '',
        ])->getMock();

    $this->app->instance(Filesystem::class, $filesystem);

    $this->artisan('git-hooks:make', [
        'hookType' => 'pre-commit',
        'name' => 'MyCustomPreCommitHook',
    ])->assertExitCode(0);
});

test('Does not overwrite existing hook file', function () {
    $filesystem = Mockery::mock(Filesystem::class)->allows([
        'isDirectory' => true,
        'exists' => true,
        'get' => '',
    ]);

    $this->app->instance(Filesystem::class, $filesystem);

    $this->artisan('git-hooks:make', [
        'hookType' => 'pre-commit',
        'name' => 'MyCustomPreCommitHook',
    ])->expectsOutputToContain('already exists');
});

test('Uses custom stub if available', function () {
    $customStubContent = 'Custom stub content';
    $customStubPath = $this->app->basePath('stubs/pre-commit-console.stub');
    File::ensureDirectoryExists(dirname((string) $customStubPath));
    File::put($customStubPath, $customStubContent);

    $hookPath = $this->app->basePath('app/Console/GitHooks/PreCommitHook.php');

    $this->artisan('git-hooks:make', [
        'hookType' => 'pre-commit',
        'name' => 'PreCommitHook',
    ])->assertSuccessful();

    // Check if the custom stub content is used in the generated file
    $generatedFileContent = File::get($hookPath);
    expect($generatedFileContent)->toContain($customStubContent);
});

test('prompts for missing arguments and creates hook', function () {
    $possibleValues = implode(', ', array_keys($this->config->get('git-hooks')));
    $hookPath = $this->app->basePath('app/Console/GitHooks/MyCustomPreCommitHook.php');

    // Run the command without providing required arguments
    $this->artisan('git-hooks:make')
        ->expectsQuestion("What type of the git hook? Possible values: ($possibleValues)", 'pre-commit')
        ->expectsQuestion('What should the git hook be named?', 'MyCustomPreCommitHook')
        ->assertSuccessful();

    expect(File::exists($hookPath))->toBeTrue();
});
