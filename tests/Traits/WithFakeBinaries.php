<?php

declare(strict_types=1);

namespace Igorsgm\GitHooks\Tests\Traits;

trait WithFakeBinaries
{
    /**
     * Absolute path to a fake binary that always exits 0 (passes).
     * Use for tools that have no fix mode (e.g. Larastan) or to simulate a clean run.
     */
    public function fakePassBin(): string
    {
        return escapeshellarg(PHP_BINARY).' '.escapeshellarg((string) realpath(__DIR__.'/../Fixtures/bin/fake-pass.php'));
    }

    /**
     * Absolute path to a fake binary that always exits 1 (fails).
     * Use for tools with no fix mode when you need to simulate a finding.
     */
    public function fakeFailBin(): string
    {
        return escapeshellarg(PHP_BINARY).' '.escapeshellarg((string) realpath(__DIR__.'/../Fixtures/bin/fake-fail.php'));
    }

    /**
     * Absolute path to a fake binary that exits 1 in analyze mode (--test / --dry-run)
     * and exits 0 in fix mode (no dry-run flag).
     *
     * Compatible with Pint (--test), Rector (--dry-run), and PHP-CS-Fixer (--dry-run).
     */
    public function fakeFixBin(): string
    {
        return escapeshellarg(PHP_BINARY).' '.escapeshellarg((string) realpath(__DIR__.'/../Fixtures/bin/fake-fix.php'));
    }
}
