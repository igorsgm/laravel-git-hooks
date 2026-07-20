<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;
use Igorsgm\GitHooks\Console\Commands\Hooks\PHPUnitPreCommitHook;
use Igorsgm\GitHooks\Facades\GitHooks;

$projectRoot = dirname(__DIR__, 3);
$phpunitBin = $projectRoot.'/vendor/bin/phpunit';

beforeEach(function () {
    $this->gitInit();
    $this->initializeTempDirectory(base_path('temp'));
    File::deleteDirectory(base_path('tests'));
});

test('PHPUnit hook skips gracefully when no test files found for staged file', function () use ($phpunitBin) {
    $this->config->set('git-hooks.code_analyzers.phpunit', [
        'path' => $phpunitBin,
        'run_in_docker' => false,
        'docker_container' => '',
        'additional_params' => '',
    ]);
    $this->config->set('git-hooks.pre-commit', [PHPUnitPreCommitHook::class]);

    $this->makeTempFile('NoTestFile.php', '<?php class NoTestFile {}');

    GitHooks::shouldReceive('isMergeInProgress')->andReturn(false);
    GitHooks::shouldReceive('getListOfChangedFiles')->andReturn('AM temp/NoTestFile.php');

    $this->artisan('git-hooks:pre-commit')->assertSuccessful();
})->skip(!file_exists($phpunitBin), 'PHPUnit binary not found');

test('PHPUnit runner passes when found test file has passing tests', function () use ($projectRoot, $phpunitBin) {
    $this->config->set('git-hooks.code_analyzers.phpunit', [
        'path' => $phpunitBin,
        'run_in_docker' => false,
        'docker_container' => '',
        'additional_params' => '--no-coverage --bootstrap='.$projectRoot.'/vendor/autoload.php',
    ]);
    $this->config->set('git-hooks.pre-commit', [PHPUnitPreCommitHook::class]);

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
        ->doesntExpectOutputToContain('PHPUnit Failed')
        ->assertSuccessful();

    File::deleteDirectory(base_path('tests'));
})->skip(!file_exists($phpunitBin), 'PHPUnit binary not found');

test('PHPUnit runner fails when found test file has failing tests', function () use ($projectRoot, $phpunitBin) {
    $this->config->set('git-hooks.code_analyzers.phpunit', [
        'path' => $phpunitBin,
        'run_in_docker' => false,
        'docker_container' => '',
        'additional_params' => '--no-coverage --bootstrap='.$projectRoot.'/vendor/autoload.php',
    ]);
    $this->config->set('git-hooks.pre-commit', [PHPUnitPreCommitHook::class]);

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
        ->expectsOutputToContain('PHPUnit Failed')
        ->expectsOutputToContain('COMMIT FAILED')
        ->assertExitCode(1);

    File::deleteDirectory(base_path('tests'));
})->skip(!file_exists($phpunitBin), 'PHPUnit binary not found');

