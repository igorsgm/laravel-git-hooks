<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;
use Igorsgm\GitHooks\Console\Commands\Hooks\PestPreCommitHook;
use Igorsgm\GitHooks\Facades\GitHooks;

$projectRoot = dirname(__DIR__, 3);
$pestBin = $projectRoot.'/vendor/bin/pest';

beforeEach(function () use ($projectRoot) {
    $this->gitInit();
    $this->initializeTempDirectory(base_path('temp'));
    File::deleteDirectory(base_path('tests'));

    // Pest v3.x walks parent directories to discover phpunit.xml even when
    // --no-configuration is passed (for Pest-specific settings like cacheDirectory).
    // The project's phpunit.xml is found 4 levels up from base_path() and its
    // cacheDirectory attribute triggers a Pest bug: PHPUnit receives
    // "--configuration --cache-directory ..." causing it to read "--cache-directory"
    // as an XML file and exit 2.
    //
    // Fix: place a minimal phpunit.xml with no cacheDirectory in base_path() so
    // Pest finds it first and stops traversing.
    file_put_contents(base_path('phpunit.xml'), sprintf(
        '<?xml version="1.0" encoding="UTF-8"?>'.PHP_EOL.
        '<phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"'.PHP_EOL.
        '         xsi:noNamespaceSchemaLocation="https://schema.phpunit.de/11.0/phpunit.xsd"'.PHP_EOL.
        '         bootstrap="%s/vendor/autoload.php">'.PHP_EOL.
        '</phpunit>',
        $projectRoot
    ));
});

afterEach(function () {
    @unlink(base_path('phpunit.xml'));
});

test('Pest hook skips gracefully when no test files found for staged file', function () use ($pestBin) {
    $this->config->set('git-hooks.code_analyzers.pest', [
        'path' => $pestBin,
        'run_in_docker' => false,
        'docker_container' => '',
        'additional_params' => '',
    ]);
    $this->config->set('git-hooks.pre-commit', [PestPreCommitHook::class]);

    $this->makeTempFile('NoTestFile.php', '<?php class NoTestFile {}');

    GitHooks::shouldReceive('isMergeInProgress')->andReturn(false);
    GitHooks::shouldReceive('getListOfChangedFiles')->andReturn('AM temp/NoTestFile.php');

    $this->artisan('git-hooks:pre-commit')->assertSuccessful();
})->skip(!file_exists($pestBin), 'Pest binary not found');

test('Pest runner passes when found test file has passing tests', function () use ($pestBin) {
    $this->config->set('git-hooks.code_analyzers.pest', [
        'path' => $pestBin,
        'run_in_docker' => false,
        'docker_container' => '',
        'additional_params' => '--no-coverage',
    ]);
    $this->config->set('git-hooks.pre-commit', [PestPreCommitHook::class]);

    $this->makeTempFile('SomeClass.php', '<?php class SomeClass {}');

    File::makeDirectory(base_path('tests'), 0755, true, true);
    file_put_contents(base_path('tests/SomeClassTest.php'), <<<'PHP'
<?php
use PHPUnit\Framework\TestCase;
class SomeClassTest extends TestCase {
    public function testSomeClassWorks(): void { $this->assertTrue(true); }
}
PHP);

    GitHooks::shouldReceive('isMergeInProgress')->andReturn(false);
    GitHooks::shouldReceive('getListOfChangedFiles')->andReturn('AM temp/SomeClass.php');

    $this->artisan('git-hooks:pre-commit')
        ->doesntExpectOutputToContain('Pest Failed')
        ->assertSuccessful();

    File::deleteDirectory(base_path('tests'));
})->skip(!file_exists($pestBin), 'Pest binary not found');

test('Pest runner fails when found test file has failing tests', function () use ($pestBin) {
    $this->config->set('git-hooks.code_analyzers.pest', [
        'path' => $pestBin,
        'run_in_docker' => false,
        'docker_container' => '',
        'additional_params' => '--no-coverage',
    ]);
    $this->config->set('git-hooks.pre-commit', [PestPreCommitHook::class]);

    $this->makeTempFile('SomeClass.php', '<?php class SomeClass {}');

    File::makeDirectory(base_path('tests'), 0755, true, true);
    file_put_contents(base_path('tests/SomeClassTest.php'), <<<'PHP'
<?php
use PHPUnit\Framework\TestCase;
class SomeClassTest extends TestCase {
    public function testSomeClassIsBroken(): void { $this->assertTrue(false); }
}
PHP);

    GitHooks::shouldReceive('isMergeInProgress')->andReturn(false);
    GitHooks::shouldReceive('getListOfChangedFiles')->andReturn('AM temp/SomeClass.php');

    $this->artisan('git-hooks:pre-commit')
        ->expectsOutputToContain('Pest Failed')
        ->expectsOutputToContain('COMMIT FAILED')
        ->assertExitCode(1);

    File::deleteDirectory(base_path('tests'));
})->skip(!file_exists($pestBin), 'Pest binary not found');

