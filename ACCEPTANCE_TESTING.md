# Acceptance Testing

Acceptance tests validate each supported tool end-to-end by running real binaries against actual fixture files through the full Artisan pre-commit pipeline.

## CI / Automated Schedule

Acceptance tests run automatically every **Sunday at 06:00 UTC** via the `acceptance.yml` GitHub Actions workflow. They are intentionally excluded from the main `main.yml` CI workflow (which runs on every push/PR) because they are slow and require real tool binaries.

The acceptance workflow runs two separate jobs to avoid unnecessary cross-product combinations:

| Job | PHP version | Node version | Tests run |
|-----|-------------|--------------|-----------|
| `acceptance-php` | 8.2, 8.3, 8.4, 8.5 | fixed (none needed) | PHP tool tests |
| `acceptance-node` | fixed (8.4) | 20, 22, 24 | Node tool tests |

This gives **7 jobs** instead of the naive 4×3=12 cross-product matrix.

You can also trigger it manually from the GitHub Actions tab using `workflow_dispatch`.

Tool sandboxes are cached between weekly runs using `actions/cache` keyed on PHP or Node version and the acceptance test file hashes.

## Why Separate from Feature Tests?

Feature tests mock binaries and focus on hook pipeline logic. Acceptance tests run the actual tool binaries to ensure the integration works with real tool output. They are kept separate so optional tools do not block the standard test suite.

## How It Works: Isolated Sandboxes

Each optional tool is installed in its own isolated temporary directory via `ToolSandbox`. This means:

- **No global pollution** — tools never conflict with each other or the project's own `vendor/`.
- **Cached installs** — the sandbox is reused on subsequent runs (no re-download if the binary is already present).
- **Automatic skip** — if installation fails (no network, incompatible PHP, etc.), the test is skipped with a clear message instead of failing.
- **One tool per directory** — `PHP_CodeSniffer`, `Psalm`, `Deptrac`, etc. each live under their own subdirectory:

```
/tmp/laravel-git-hooks-acceptance/
├── phpcodesniffer/      ← vendor/bin/phpcs, phpcbf
├── psalm/               ← vendor/bin/psalm
├── deptrac/             ← vendor/bin/deptrac
├── phpinsights/         ← vendor/bin/phpinsights
├── codeception/         ← vendor/bin/codecept
├── eslint/              ← node_modules/.bin/eslint
├── prettier/            ← node_modules/.bin/prettier
├── blade-formatter/     ← node_modules/.bin/blade-formatter
└── vitest/              ← node_modules/.bin/vitest
```

## Running Acceptance Tests

```bash
# Run all acceptance tests (tools install automatically on first run)
composer test:acceptance

# Run only PHP tool tests (Pint, PHPStan, Pest, PHPUnit, Rector, etc.)
composer test:acceptance:php

# Run only Node tool tests (ESLint, Prettier, BladeFormatter, Vitest)
composer test:acceptance:node

# Or directly with Pest:
vendor/bin/pest --configuration phpunit-acceptance.xml
vendor/bin/pest --configuration phpunit-acceptance.xml --testsuite acceptance-php
vendor/bin/pest --configuration phpunit-acceptance.xml --testsuite acceptance-node

# Clean up all sandbox directories when done
composer test:acceptance:cleanup
```

## Pre-installed Tools (always run)

These tools ship with the package's dev dependencies and their acceptance tests run without any setup:

| Tool | Binary | Acceptance Test |
|------|--------|-----------------|
| Laravel Pint | `vendor/bin/pint` | `PintAcceptanceTest.php` |
| Larastan (PHPStan) | `vendor/bin/phpstan` | `LarastanAcceptanceTest.php` |
| PHP CS Fixer | `vendor/bin/php-cs-fixer` | `PHPCSFixerAcceptanceTest.php` |
| Rector | `vendor/bin/rector` | `RectorAcceptanceTest.php` |
| Pest | `vendor/bin/pest` | `PestRunnerAcceptanceTest.php` |
| PHPUnit | `vendor/bin/phpunit` | `PHPUnitRunnerAcceptanceTest.php` |

## Optional PHP Tools (auto-installed via sandbox)

On first run each tool is installed into its own isolated Composer project under the OS temp directory. Subsequent runs reuse the cached install.

| Tool | Package | Acceptance Test |
|------|---------|-----------------|
| PHP_CodeSniffer | `squizlabs/php_codesniffer` | `PHPCodeSnifferAcceptanceTest.php` |
| PHP Insights | `nunomaduro/phpinsights` | `PhpInsightsAcceptanceTest.php` |
| Psalm | `vimeo/psalm` | `PsalmAcceptanceTest.php` |
| Deptrac | `qossmic/deptrac` | `DeptracAcceptanceTest.php` |
| Codeception | `codeception/codeception` | `CodeceptionAcceptanceTest.php` |

> **Note:** `ergebnis/composer-normalize` is a project dev dependency (not a sandbox). Its acceptance test uses `vendor/bin/validate-json` directly.

## Optional JS Tools (auto-installed via sandbox)

Each JS tool is installed into its own isolated `npm` project.

| Tool | Package | Acceptance Test |
|------|---------|-----------------|
| ESLint | `eslint` | `ESLintAcceptanceTest.php` |
| Prettier | `prettier` | `PrettierAcceptanceTest.php` |
| Blade Formatter | `blade-formatter` | `BladeFormatterAcceptanceTest.php` |
| Vitest | `vitest` | `VitestAcceptanceTest.php` |

## What Each Test Validates

### Code Analyzers

Each tool has tests covering the following scenarios:

- **Fails** — staged fixture file with known issues triggers the hook to report failure and exit 1.
- **Passes** — staged clean fixture file (or no matching file extension) allows the commit to proceed.
- **Auto-fix (automatic)** — `automatically_fix_errors = true` runs the fixer without a prompt and exits 0 on success.
- **Auto-fix (user confirms)** — user answers `yes` at the interactive prompt; fixer runs and exits 0.
- **Non-matching files alongside** — staging a bad file alongside a file whose extension doesn't match means only the matching file is processed.
- **Multiple files** — two or more matching files are staged together and processed in a single batch.

> **PHP Insights note:** tests pass `--disable-security-check` in `additional_params` to skip the network security vulnerability check (~10 s) and keep tests fast.

### Test Runners (Pest, PHPUnit, Codeception, Vitest)
- **Skip gracefully** — staged source file with no matching test file causes the hook to pass through without running tests.
- **Passes** — staged source file with a matching, passing test file runs the test binary successfully.
- **Fails** — staged source file with a matching, failing test file causes the hook to report failure and exit 1.

## Fixture Files Used

| Fixture | Purpose |
|---------|---------|
| `ClassWithFixableIssues.php` | Missing `declare(strict_types=1)`, `return null` for `string` type — fails Pint, Larastan, PHP CS Fixer, PHPCS, Psalm |
| `ClassWithoutFixableIssues.php` | Clean, PSR-12 compliant empty class — passes all PHP analyzers |
| `ClassWithRectorIssues.php` | Uses `empty()` on array — fails Rector's `SimplifyEmptyCheckOnEmptyArrayRector` rule |
| `fixable-js-file.js` | JS file with double-quoted strings — fails ESLint/Prettier when single-quote rule is active |
| `clean-js-file.js` | JS file with single-quoted strings — passes ESLint/Prettier |
| `fixable-blade-file.blade.php` | Poorly indented Blade template — fails Blade Formatter |
| `sample.js` | Simple JS file used as a non-matching file in PHP tool tests |
| `vitestFixture.config.mjs` | Vitest config enabling `globals: true` so test files use `test()`/`expect()` without imports |

## Adding a New Tool's Acceptance Test

1. Create `tests/Acceptance/Hooks/YourToolAcceptanceTest.php`
2. Choose the right sandbox factory:

```php
<?php

declare(strict_types=1);

use Igorsgm\GitHooks\Console\Commands\Hooks\YourToolPreCommitHook;
use Igorsgm\GitHooks\Facades\GitHooks;
use Igorsgm\GitHooks\Tests\Acceptance\ToolSandbox;

$projectRoot = dirname(__DIR__, 3);

// For PHP tools:
$sandbox = ToolSandbox::php('your-tool', 'vendor/package', 'binary-name');

// For JS tools:
// $sandbox = ToolSandbox::js('your-tool', 'npm-package', 'binary-name');

beforeEach(function () use ($sandbox) {
    try {
        $sandbox->install();
    } catch (Throwable $e) {
        $this->markTestSkipped('YourTool sandbox setup failed: '.$e->getMessage());
    }

    $this->gitInit();
    $this->initializeTempDirectory(base_path('temp'));
});

test('YourTool fails when staged file has issues', function () use ($projectRoot, $sandbox) {
    $this->config->set('git-hooks.code_analyzers.your_tool', [
        'path' => $sandbox->binaryPath(),
        'file_extensions' => '/\.php$/',
        'run_in_docker' => false,
        'docker_container' => '',
    ]);
    $this->config->set('git-hooks.pre-commit', [YourToolPreCommitHook::class]);

    $this->makeTempFile(
        'ClassWithFixableIssues.php',
        file_get_contents($projectRoot.'/tests/Fixtures/ClassWithFixableIssues.php')
    );

    GitHooks::shouldReceive('isMergeInProgress')->andReturn(false);
    GitHooks::shouldReceive('getListOfChangedFiles')->andReturn('AM temp/ClassWithFixableIssues.php');

    $this->artisan('git-hooks:pre-commit')
        ->expectsOutputToContain('YourTool Failed')
        ->expectsOutputToContain('COMMIT FAILED')
        ->assertExitCode(1);
});
```

3. The test is automatically included in the acceptance suite — no configuration needed.

## Expected Output

When running `composer test:acceptance` for the first time (sandboxes install on demand):

```
PASS  Tests\Acceptance\Hooks\PintAcceptanceTest
  ✓ Pint fails when staged PHP file has style issues
  ✓ Pint passes when staged PHP file has no style issues

...

PASS  Tests\Acceptance\Hooks\PHPCodeSnifferAcceptanceTest    ← installs squizlabs/php_codesniffer first run
  ✓ PHP_CodeSniffer fails when staged PHP file violates coding standard
  ✓ PHP_CodeSniffer passes when no PHP files are staged

- Psalm sandbox setup failed: ...   → skipped if no network
```

On subsequent runs all sandboxes are cached and tests run at full speed.