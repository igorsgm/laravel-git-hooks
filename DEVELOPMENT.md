# Development Guide

Internal reference for contributors and maintainers of `laravel-git-hooks`.

---

## Test Suite Overview

There are three distinct layers of tests, each with a different purpose and cost.

| Layer | Location | Command | Speed | Runs in CI |
|---|---|---|---|---|
| Unit | `tests/Unit/` | `composer test` | Fast (~0.01s/test) | Every push/PR |
| Feature (integration) | `tests/Features/` | `composer test` | Mixed | Every push/PR |
| Acceptance (end-to-end) | `tests/Acceptance/` | `composer test:acceptance` | Slow | Weekly + manual |

**Unit tests** use mocks exclusively and never touch the filesystem or real binaries.

**Feature tests** mock only the git interaction (`GitHooks::shouldReceive`) and use **fake PHP binaries** (in `tests/Fixtures/bin/`) instead of real tool binaries. The fake binaries exit with the correct code based on the command flags passed to them (`--test` for Pint, `--dry-run` for Rector, `check`/`fix` for PHP-CS-Fixer), so the hook's analysis/fix/rerun logic is fully exercised without running the actual tools. Most tests complete in under 1 second.

**Acceptance tests** go one step further: they install each tool in its own isolated sandbox directory (outside the project) via `ToolSandbox`, and exercise the full hook end-to-end including auto-fix flows and non-matching file extension skipping. They are intentionally excluded from the normal `composer test` run.

---

## Running Acceptance Tests Locally

### Prerequisites

- PHP 8.2+
- Composer
- Node.js / npm (for Node tool tests)
- A global `git` installation with a configured user (`git config --global user.name` / `user.email`)

### Commands

```bash
# All acceptance tests (PHP tools + Node tools)
composer test:acceptance

# PHP tools only (Pint, Larastan, Psalm, Rector, Pest, PHPUnit, …)
composer test:acceptance:php

# Node tools only (ESLint, Prettier, Vitest, BladeFormatter)
composer test:acceptance:node

# Clean up cached sandbox directories afterwards (see Sandboxing below)
composer test:acceptance:cleanup
```

Each command runs Pest against `phpunit-acceptance.xml`, which defines two test suites (`acceptance-php` and `acceptance-node`) that glob `tests/Acceptance/PHP/` and `tests/Acceptance/Node/` respectively.

### Sandboxing

Each tool is installed once in its own isolated directory under the OS temp folder:

```
/tmp/laravel-git-hooks-acceptance/<tool-name>/vendor/bin/<binary>
# or
/tmp/laravel-git-hooks-acceptance/<tool-name>/node_modules/.bin/<binary>
```

The `ToolSandbox` class (`tests/Acceptance/ToolSandbox.php`) manages this. On the **first run** the tool is downloaded via Composer or npm — this can take 30–120 seconds per tool. **Subsequent runs reuse the cache** and are much faster.

Call `composer test:acceptance:cleanup` to wipe all sandboxes and force a fresh download next time (useful if a tool update causes test breakage).

Individual tests are automatically **skipped** if their binary is not found or installation failed. This means a partial run (e.g., no npm) only skips the Node tests without failing the suite.

### Running a single acceptance test

```bash
# Single file
vendor/bin/pest --configuration phpunit-acceptance.xml tests/Acceptance/PHP/PintAcceptanceTest.php

# By name filter
vendor/bin/pest --configuration phpunit-acceptance.xml --filter="Pint"
```

---

## Acceptance Tests in CI

Acceptance tests run automatically on **two triggers**:

### 1. Scheduled (automatic)

Every **Sunday at 06:00 UTC**, the `Acceptance Tests` workflow (`.github/workflows/acceptance.yml`) runs the full acceptance matrix automatically. This catches regressions introduced by new tool versions published during the week.

### 2. Manual (on demand)

You can trigger the workflow at any time from the GitHub Actions tab:

1. Go to **Actions** → **Acceptance Tests**
2. Click **Run workflow**
3. Select the branch (default: `master`) and click **Run workflow**

This is useful after merging a change that touches acceptance tests, or when investigating a CI failure before the next scheduled run.

### CI Matrix

| Job | Matrix |
|---|---|
| `acceptance-php` | PHP 8.2, 8.3, 8.4, 8.5 |
| `acceptance-node` | Node 20, 22, 24 (fixed PHP 8.4) |

`fail-fast: false` is set so every matrix cell always runs to completion even if one fails — making it easier to spot whether a failure is version-specific.

### Caching in CI

The workflow caches two things to keep runs fast:

- **Composer downloads** — keyed on OS + PHP version + `composer.json` hash
- **Acceptance tool sandboxes** — keyed on OS + version + `tests/Acceptance/**` hash (same cache path as local: `/tmp/laravel-git-hooks-acceptance`)

On a cache hit the tool installation step is skipped entirely. The cache is invalidated when acceptance test files change (e.g., a new package version is pinned in the test).

---

## Unit & Feature Tests in CI

The `Laravel Git Hooks` workflow (`.github/workflows/main.yml`) runs on every push/PR to `master`, `main`, and `develop`, and also on a daily schedule.

**Matrix:** PHP 8.2–8.5 × Laravel 11–13 (some combinations excluded — see the workflow file).

Each cell installs the correct combination of `laravel/framework`, `orchestra/testbench`, `pestphp/pest`, and `pestphp/pest-plugin-laravel`, then runs:

```bash
composer test      # unit + feature tests
composer lint      # Laravel Pint style check
composer analyze   # PHPStan level 8
composer rector:check
```

---

## Writing New Acceptance Tests

1. Create a file in `tests/Acceptance/PHP/` or `tests/Acceptance/Node/` depending on the tool.
2. Use `ToolSandbox::php()` or `ToolSandbox::js()` to install the binary in an isolated sandbox.
3. In `beforeEach`, call `$sandbox->install()` (wrapped in try/catch → `markTestSkipped`), then `$this->gitInit()` and `$this->initializeTempDirectory(base_path('temp'))`.
4. Each test should cover: **passes** (matching files, correct code), **fails** (matching files, broken code), **skips** (non-matching file extensions), and — if applicable — **auto-fix** (both config-driven and interactive confirmation).

Refer to `tests/Acceptance/PHP/PintAcceptanceTest.php` as the canonical example for a PHP tool with auto-fix, and `tests/Acceptance/Node/ESLintAcceptanceTest.php` for a Node tool.

### Pest v3 / Testbench quirk

When a test spawns Pest as a **subprocess** (i.e. the Pest runner hooks like `PestPreCommitHook`), a minimal `phpunit.xml` must exist in `base_path()` so that Pest does not walk up to the project root and pick up the project's own `phpunit.xml`.

The project's `phpunit.xml` has a `cacheDirectory` attribute that triggers a Pest v3.x bug: it constructs PHPUnit's `--configuration` flag incorrectly, causing PHPUnit to exit with code 2 and the message `Could not read XML from file "--cache-directory"`.

The fix is already applied in `PestRunnerAcceptanceTest.php` — replicate the `beforeEach`/`afterEach` pattern if you ever write another test that spawns Pest as a subprocess:

```php
beforeEach(function () use ($projectRoot) {
    // ...
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
```

---

## Full Quality Check Before a Release

```bash
composer lint:fix          # auto-fix code style
composer rector:fix        # auto-apply Rector suggestions
composer lint              # verify style is clean
composer analyze           # PHPStan (level 8)
composer test              # all unit + feature tests
composer test:acceptance   # full acceptance suite (slow — run at least once before tagging)
```

All of these must pass before tagging a release.
