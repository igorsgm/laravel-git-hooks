# AGENTS.md

Guidance for Claude Code (claude.ai/code) and Codex (codex.ai/code) when working with Laravel Git Hooks package.

## Quick Start

Laravel Git Hooks manages Git hooks for Laravel projects with pre-configured quality tools + Docker support.

### Essential Commands

```bash
# Quality checks (run before committing)
composer lint                           # Check code style with Laravel Pint
composer analyze                       # Static analysis with PHPStan (level max, 2G memory)
composer test                          # Run all unit + feature tests

# Auto-fix issues
composer lint:fix                      # Fix code style
composer rector:fix                   # Apply code improvements

# Testing
composer test tests/Unit/SomeTest.php # Single test file
composer test --filter="test name"    # Single test by name
composer test tests/Features/          # Feature tests only
composer test tests/Unit/             # Unit tests only
XDEBUG_MODE=coverage composer test-coverage  # With coverage

# Acceptance tests (slow — run real tool binaries, excluded from normal test run)
composer test:acceptance               # Run all acceptance tests
composer test:acceptance:cleanup       # Remove sandbox directories

# Hook management
php artisan git-hooks:register         # Register hooks after config changes
php artisan git-hooks:make            # Create custom hook class

# Full QA
composer qa                            # Run lint + analyze + test
```

## Project Structure

```
src/
├── Contracts/              # Hook interfaces (PreCommitHook, MessageHook, etc.)
├── Console/Commands/
│   ├── Hooks/             # Pre-configured tool integrations (PintPreCommitHook, etc.)
│   └── *.php               # Core hook commands (PreCommit, CommitMessage, etc.)
├── Traits/
│   ├── WithAutoFix         # Auto-fixing functionality
│   ├── WithDockerSupport   # Docker execution
│   ├── WithFileAnalysis    # File utilities
│   ├── WithPipeline        # Pipeline processing
│   └── ProcessHelper       # Command execution helpers
├── Exceptions/             # HookFailException
└── Git/                   # Git command wrappers (ChangedFile, ChangedFiles, etc.)

config/git-hooks.php        # Main configuration
tests/
├── Features/              # Integration/feature tests
├── Unit/                 # Unit tests
├── Fixtures/             # Test fixtures
├── Datasets/             # Shared test data
├── Traits/               # Test traits
└── Acceptance/           # Slow end-to-end tests (real binaries)
    ├── ToolSandbox.php   # Isolated per-tool installer
    └── Hooks/            # One file per supported tool
```

## Code Style Guidelines

### General Rules
1. **Always use strict types**: `declare(strict_types=1);` at the top of every PHP file
2. **Follow Laravel Pint preset**: `composer lint` before committing
3. **PHPStan Level 8**: No untyped properties/return types, no mixed without handling

### Naming Conventions
| Element | Convention | Example |
|---------|------------|---------|
| Classes/Interfaces/Traits | PascalCase | `GitHooksServiceProvider`, `PreCommitHook` |
| Methods/Properties | camelCase | `setRunInDocker()`, `$runInDocker` |
| Constants | SCREAMING_SNAKE | `DEFAULT_CHUNK_SIZE` |
| Hook Commands | PascalCase ending with Hook | `PintPreCommitHook` |

### Class Structure (ordered_class_elements)
```
use_trait > constants > properties > constructor > magic methods > public methods > protected/private methods
```

### Imports
1. Group imports: Laravel first, then package (`Igorsgm\GitHooks\*`), then project
2. Sort alphabetically within groups
3. Fully qualify strict types: `use Igorsgm\GitHooks\Contracts\Hook;`

### Type Declarations
1. Always use return types when possible
2. Use union types: `?string $value` for nullable parameters
3. Document array shapes:
   ```php
   /** @param array<int, string> $paths @return array<string, mixed> */
   ```

### Error Handling
1. Use `HookFailException` to indicate hook failures
2. Catch exceptions specifically, avoid bare `catch`
3. Return exit codes: `return 1` for failure, `return 0` for success

### Testing (Pest)
1. Use `test()` or `it()` for test definitions
2. Use `beforeEach()` / `afterEach()` for setup/teardown
3. Use `expect()` for assertions, `describe()` for grouping
4. Use `Mockery::mock()` for mocking (not `mock()` helper)
5. Target >90% code coverage for new code
6. **Do not run `composer test:acceptance`** during normal development — it is slow and reserved for CI

### Acceptance Tests

End-to-end tests in `tests/Acceptance/` exercise real tool binaries. Each tool's test covers:

- **Fails / passes** with matching/non-matching file extensions
- **Auto-fix** (both `automatically_fix_errors = true` and interactive user-confirm)
- **Multiple files** staged in a single run
- **Non-matching files alongside** matching ones

They run automatically every Sunday via `.github/workflows/acceptance.yml` across a PHP 8.2–8.5 × Node 20–24 matrix, and can be triggered manually from the GitHub Actions tab.

## Key Concepts

- **Pipeline Processing**: Hooks run sequentially via Laravel Pipeline
- **Docker Support**: Configure via `run_in_docker`, `docker_container`, `use_sail`
- **Auto-fix**: Hooks can automatically fix issues when configured
- **Hook Types**: PreCommit, PostCommit, PrePush, CommitMessage, PrepareCommitMessage

## Creating New Hooks

1. Create class in `src/Console/Commands/Hooks/`
2. Extend `BaseCodeAnalyzerPreCommitHook` for code analyzers
3. Implement required interface methods
4. Register in `config/git-hooks.php`
5. Add unit tests in `tests/Unit/`

## Supported Tools

| Category | Tools |
|---------|-------|
| PHP Code Quality | Laravel Pint, Composer Normalize, Larastan, Psalm, Deptrac, PHP Insights, Rector |
| Test Runners | Pest, PHPUnit, Codeception, Vitest |
| Frontend | ESLint, Prettier, Blade Formatter |
