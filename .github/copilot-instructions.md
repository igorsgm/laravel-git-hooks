# Copilot Instructions for Laravel Git Hooks

This file provides guidance for Copilot when working on the Laravel Git Hooks package. For detailed information, refer to CLAUDE.md and AGENTS.md in the repository root.

## Quick Commands

### Quality Checks
```bash
composer lint                               # Check code style with Laravel Pint
composer analyze                            # Static analysis with PHPStan (level 8, 2G memory)
composer test                               # Run all tests
composer qa                                 # Run all quality checks (lint + analyze + test)
```

### Auto-fix Issues
```bash
composer lint:fix                           # Fix code style with Laravel Pint
composer rector:fix                         # Apply code improvements with Rector
```

### Testing
```bash
composer test                               # Run all tests
composer test tests/Unit/SomeTest.php       # Run single test file
composer test --filter="test name"          # Run test by name pattern
composer test tests/Features/               # Run feature tests only
composer test tests/Unit/                   # Run unit tests only
composer test-coverage                      # Run tests with coverage (XDEBUG_MODE=coverage)
```

### Hook Management
```bash
php artisan git-hooks:register              # Register hooks after config changes
php artisan git-hooks:make                  # Generate a new custom hook class
```

## Architecture

### Core Concepts

- **Pipeline Processing**: Hooks execute sequentially through Laravel's Pipeline, allowing each hook to process data and pass to the next
- **Hook Types**: Five types exist (PreCommit, PostCommit, PrePush, PrepareCommitMessage, CommitMessage), each implementing a specific contract interface
- **Docker Support**: Each hook can run locally or in Docker via `run_in_docker`, `docker_container`, and `use_sail` config options
- **Auto-fix**: Hooks can automatically fix detected issues; configured via `automatically_fix_errors` and `rerun_analyzer_after_autofix` in config

### Project Structure

```
src/
├── Contracts/              # Hook interfaces (PreCommitHook, PrePushHook, MessageHook, etc.)
├── Console/Commands/
│   ├── Hooks/              # Pre-configured tool integrations (PintPreCommitHook, etc.)
│   │   ├── BaseCodeAnalyzerPreCommitHook.php
│   │   └── *.php           # Individual tool hooks (Pint, Larastan, Psalm, etc.)
│   └── *.php               # Core commands (PreCommit, CommitMessage, PostCommit, etc.)
├── Traits/
│   ├── WithAutoFix.php     # Auto-fix logic (execute fixer, rerun analyzer)
│   ├── WithDockerSupport.php # Docker execution wrapper
│   ├── WithFileAnalysis.php  # File utilities (staged files, changed files)
│   ├── WithPipeline.php     # Pipeline orchestration
│   └── ProcessHelper.php    # Command execution helpers
├── Exceptions/
│   └── HookFailException.php # Thrown to fail a hook
├── Facades/
│   └── GitHooks.php        # Public API facade
├── Git/
│   ├── ChangedFile.php     # Single file wrapper
│   ├── ChangedFiles.php    # Collection of changed files
│   ├── Log.php             # Git log wrapper
│   └── CommitMessage.php   # Commit message wrapper
└── GitHooksServiceProvider.php # Service provider registration

config/git-hooks.php         # Main configuration (hook arrays, docker settings, auto-fix options)
tests/
├── Features/               # Integration tests for hook execution
├── Unit/                   # Unit tests for individual hooks and traits
├── Fixtures/               # Static test data (sample files, config)
├── Datasets/               # Shared test data for multiple tests
└── Traits/                 # Test helper traits
```

### Key Classes

- **HooksPipeline**: Orchestrates sequential hook execution, handles failures and exit codes
- **BaseCodeAnalyzerPreCommitHook**: Base for code analyzer hooks, provides common logic for running tools
- **GitHooks (Facade)**: Public API for programmatic hook management
- **PreCommit, CommitMessage, etc. (Commands)**: Main command classes that invoke hooks from config

### Pre-configured Hooks

| Category | Hooks |
|----------|-------|
| PHP Code Quality | PintPreCommitHook, ComposerNormalizePreCommitHook, LarastanPreCommitHook, PsalmPreCommitHook, DeptracPreCommitHook, RectorPreCommitHook, PhpInsightsPreCommitHook |
| Test Runners | PestPreCommitHook, PHPUnitPreCommitHook, CodeceptionPreCommitHook, VitestPreCommitHook |
| Frontend | ESLintPreCommitHook, PrettierPreCommitHook, BladeFormatterPreCommitHook |

## Code Style Guidelines

### Type System
1. **Always use strict types** at the top of every PHP file: `declare(strict_types=1);`
2. **Use return types** for all methods whenever possible
3. **Use nullable types**: `?string` for optional parameters, union types where appropriate
4. **Document array shapes** in phpDoc: `@param array<int, string> $paths @return array<string, mixed>`
5. **PHPStan Level 8** enforced: no untyped properties/returns, handle `mixed` explicitly

### Naming Conventions
| Element | Convention | Example |
|---------|------------|---------|
| Classes, Interfaces, Traits | PascalCase | `GitHooksServiceProvider`, `PreCommitHook`, `WithAutoFix` |
| Methods, Properties, Variables | camelCase | `setRunInDocker()`, `$runInDocker`, `$changedFiles` |
| Constants | SCREAMING_SNAKE_CASE | `DEFAULT_CHUNK_SIZE`, `HOOK_TYPE_PRE_COMMIT` |
| Hook Classes | PascalCase ending with Hook | `PintPreCommitHook`, `LarastanPreCommitHook` |

### Class Structure
Follow `ordered_class_elements` rule from `pint.json`:
1. Traits
2. Constants (public, protected, private)
3. Properties (public, protected, private)
4. Constructor
5. Magic methods (`__toString`, etc.)
6. Public methods
7. Protected methods
8. Private methods

### Imports
1. Group imports: Laravel first, then `Igorsgm\GitHooks\*`, then project app classes
2. Sort alphabetically within groups
3. Use fully qualified strict types: `use Igorsgm\GitHooks\Contracts\PreCommitHook;`

### Error Handling
1. Throw `HookFailException` to indicate hook failures (use `throw new HookFailException($message);`)
2. Catch exceptions specifically; avoid bare `catch` clauses
3. Return exit codes from commands: `return Command::SUCCESS` (0) or `return Command::FAILURE` (1)

## Testing Guidelines

- **Framework**: Pest v2+ with `test()` and `it()` syntax
- **Assertions**: Use `expect()` chains for assertions
- **Grouping**: Use `describe()` blocks for related tests
- **Mocking**: Use `Mockery::mock()` (not the `mock()` helper)
- **Setup/Teardown**: Use `beforeEach()` / `afterEach()` for fixture setup
- **Coverage Target**: >80% for new code
- **Test Structure**: Group tests by Unit (isolated) and Features (integration/end-to-end)

## Creating Custom Hooks

When adding a new pre-configured hook:

1. Create class in `src/Console/Commands/Hooks/` extending `BaseCodeAnalyzerPreCommitHook` or implementing the appropriate interface
2. Override required methods: `getCommand()`, `getFileExtensions()`, `getFixCommand()` (if auto-fix capable)
3. Register in `config/git-hooks.php` under appropriate hook type array
4. Add comprehensive unit tests in `tests/Unit/` covering success, failure, and auto-fix scenarios
5. Verify with `composer qa` before submitting

## Configuration

The `config/git-hooks.php` file configures:
- **Hook arrays** by type: `pre-commit`, `prepare-commit-msg`, `commit-msg`, `post-commit`, `pre-push`
- **Auto-fix behavior**: `automatically_fix_errors`, `rerun_analyzer_after_autofix`, `stop_at_first_analyzer_failure`
- **Docker options**: `run_in_docker`, `docker_container`, `use_sail`
- **File handling**: `validate_paths`, `analyzer_chunk_size`
- **Debug options**: `debug_commands`, `debug_output`, `output_errors`

After editing config, run `php artisan git-hooks:register` to update Git hook files.

## Key Patterns

### Pipeline Execution
Hooks are executed via `HooksPipeline` which:
1. Loads hooks from config
2. Passes data (ChangedFiles, CommitMessage, etc.) through each hook
3. Calls `$next($data)` to pass to next hook
4. Stops and throws exception on `HookFailException`

### File Processing
- Use `ChangedFiles` class to get staged/changed files with filtering
- Filter by extension: `$files->byExtension(['php', 'blade'])`
- Process in chunks: `$files->chunk()` for memory efficiency

### Docker Integration
- Check `config('git-hooks.run_in_docker')` to determine execution context
- Use `WithDockerSupport` trait to wrap commands for Docker
- Support `use_sail` for Laravel Sail environments

### Auto-fix Integration
- Hooks extending `BaseCodeAnalyzerPreCommitHook` automatically support auto-fix
- Define `getFixCommand()` method returning the fixer command
- Framework handles prompting, execution, and re-running analyzer
