# CLAUDE.md

Guidance for Claude Code (claude.ai/code) when working with Laravel Git Hooks package.

## Quick Start

Laravel Git Hooks manages Git hooks for Laravel projects with pre-configured quality tools (Pint, PHPCS, ESLint, Prettier, Larastan) + Docker support.

### Essential Commands

**Quality Checks** (run these before committing):
```bash
vendor/bin/pint --test src config      # Check code style
vendor/bin/phpstan                     # Static analysis
composer test                           # Run tests
```

**Auto-fix Issues**:
```bash
vendor/bin/pint src config             # Fix code style
vendor/bin/rector                      # Apply code improvements
vendor/bin/phpcbf                      # Fix PHP CodeSniffer issues
```

**Hook Management**:
```bash
php artisan git-hooks:register         # Register hooks after config changes
php artisan git-hooks:make              # Create custom hook class
```

## Project Structure

```
src/
├── Contracts/           # Hook interfaces (PreCommitHook, MessageHook, etc.)
├── Console/Commands/
│   ├── Hooks/          # Pre-configured tool integrations
│   └── *.php           # Core hook commands
├── Traits/
│   ├── WithAutoFix     # Auto-fixing functionality
│   ├── WithDockerSupport # Docker execution
│   └── WithFileAnalysis  # File utilities
└── GitHooksServiceProvider.php

config/git-hooks.php     # Main configuration
tests/                   # Pest tests
```

## Key Concepts

- **Pipeline Processing**: Hooks run sequentially via Laravel Pipeline
- **Docker Support**: Configure per-hook with `run_in_docker`, `docker_container`, `use_sail`
- **Auto-fix**: Hooks can automatically fix issues when configured
- **Hook Types**: PreCommit, PostCommit, PrePush, CommitMessage

## Development Workflow

1. Make changes
2. `composer test` - Ensure tests pass
3. `vendor/bin/pint --test src config` - Check style
4. `vendor/bin/phpstan` - Static analysis
5. `php artisan git-hooks:register` - After config changes

## Testing

```bash
vendor/bin/pest                         # Run all tests
vendor/bin/pest tests/Unit/SomeTest.php # Specific test
composer test-coverage                  # With coverage
```