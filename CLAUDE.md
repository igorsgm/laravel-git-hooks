# CLAUDE.md

Guidance for Claude Code (claude.ai/code) when working with Laravel Git Hooks package.

## Quick Start

Laravel Git Hooks manages Git hooks for Laravel projects with pre-configured quality tools (Pint, Composer Normalize, Larastan, Psalm, Deptrac, Rector, PHP Insights, Pest, PHPUnit, Codeception, Vitest, ESLint, Prettier, Blade Formatter) + Docker support.

### Essential Commands

**Quality Checks** (run these before committing):

```bash
composer lint                           # Check code style with Laravel Pint
composer analyze                        # Static analysis with PHPStan (level max)
composer test                           # Run tests
```

**Auto-fix Issues**:

```bash
composer lint:fix                       # Fix code style
composer rector:fix                     # Apply code improvements
```

**Hook Management**:

```bash
php artisan git-hooks:register          # Register hooks after config changes
php artisan git-hooks:make              # Create custom hook class
```

**Testing**:

```bash
composer test                           # Run all unit + feature tests
composer test tests/Unit/SomeTest.php   # Single test file
composer test --filter="test name"     # Single test by name
composer test tests/Features/           # Feature tests only
composer test tests/Unit/               # Unit tests only
composer test-coverage                 # With coverage

# Acceptance tests (slow — run real tool binaries)
composer test:acceptance                # Run all acceptance tests
composer test:acceptance:cleanup        # Remove sandbox directories
```

**Full QA**:

```bash
composer qa                             # Run lint + analyze + test
```

## Project Structure

```
src/
├── Contracts/              # Hook interfaces (PreCommitHook, MessageHook, etc.)
├── Console/Commands/
│   ├── Hooks/              # Pre-configured tool integrations
│   └── *.php               # Core hook commands
├── Traits/
│   ├── WithAutoFix         # Auto-fixing functionality
│   ├── WithDockerSupport   # Docker execution
│   ├── WithFileAnalysis    # File utilities
│   ├── WithPipeline        # Pipeline processing
│   └── ProcessHelper       # Command execution helpers
├── Exceptions/              # HookFailException
├── Facades/                # GitHooks facade
└── Git/                    # Git command wrappers

config/git-hooks.php        # Main configuration
tests/
├── Features/               # Integration/feature tests
├── Unit/                   # Unit tests
├── Fixtures/               # Test fixtures
├── Datasets/               # Shared test data
├── Traits/                 # Test traits
└── Acceptance/             # Slow end-to-end tests (real binaries)
    ├── ToolSandbox.php     # Isolated per-tool installer
    └── Hooks/              # One file per supported tool
```

## Key Concepts

- **Pipeline Processing**: Hooks run sequentially via Laravel Pipeline
- **Docker Support**: Configure per-hook with `run_in_docker`, `docker_container`, `use_sail`
- **Auto-fix**: Hooks can automatically fix issues when configured
- **Hook Types**: PreCommit, PostCommit, PrePush, CommitMessage, PrepareCommitMessage

## Supported Tools

### PHP Code Analyzers

| Tool               | Auto-fix | Purpose                          |
| ------------------ | -------- | -------------------------------- |
| Laravel Pint       | Yes      | Code style (Laravel preset)      |
| Composer Normalize | Yes      | Normalize composer.json          |
| Larastan           | No       | Static analysis                  |
| Psalm              | No       | Static analysis + taint analysis |
| Deptrac            | No       | Architecture enforcement         |
| PHPInsights        | Yes      | Code quality metrics             |
| Rector             | Yes      | Code upgrades & refactoring      |

### Test Runners

| Tool        | Purpose                           |
| ----------- | --------------------------------- |
| Pest        | Run tests for changed PHP files   |
| PHPUnit     | Run tests for changed PHP files   |
| Codeception | Run tests for changed PHP files   |
| Vitest      | Run tests for changed JS/TS files |

### Frontend Code Analyzers

| Tool           | Auto-fix | Purpose                   |
| -------------- | -------- | ------------------------- |
| ESLint         | Yes      | JS/TS linting             |
| Prettier       | Yes      | Code formatting           |
| BladeFormatter | Yes      | Blade template formatting |

## Development Workflow

1. Make changes
2. `composer lint` - Check code style
3. `composer analyze` - Static analysis
4. `composer test` - Ensure tests pass
5. `php artisan git-hooks:register` - After config changes

## Code Style Guidelines

1. **Always use strict types**: `declare(strict_types=1);` at the top of every PHP file
2. **Follow Laravel Pint preset**: The project uses `pint.json` with Laravel preset
3. **Run Pint before committing**: `composer lint`

## Testing

- Use Pest framework with `test()` and `it()` syntax
- Use `Mockery::mock()` for mocking
- Group related tests with `describe()` blocks

### Acceptance Tests

Acceptance tests live in `tests/Acceptance/` and are excluded from the normal `composer test` run. They exercise real tool binaries end-to-end. Each tool's test covers:

- **Fails / passes** with matching/non-matching file extensions
- **Auto-fix** (both `automatically_fix_errors = true` and interactive user-confirm)
- **Multiple files** staged in a single run
- **Non-matching files alongside** matching ones

Run them with:

```bash
composer test:acceptance
```

They run automatically every Sunday via `.github/workflows/acceptance.yml` (PHP 8.2–8.5 × Node 20–24 matrix). You can also trigger the workflow manually from the GitHub Actions tab.

**Do not run acceptance tests as part of normal development** — use `composer test` for the fast unit/feature suite.
