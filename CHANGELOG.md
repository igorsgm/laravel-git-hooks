# Changelog

All notable changes to `laravel-git-hooks` will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [2.0.0] - 2024-09-24

### Added
- Major Docker & Laravel Sail Support
- Automatic Code Fixing capabilities with `automatically_fix_errors` and `rerun_analyzer_after_autofix` options
- New Code Analyzers:
  - PHP CS Fixer support
  - Rector support
  - PHP Insights support
- Performance improvements with chunked file processing
- Configurable path validation
- Enhanced debug options

### Changed
- **Breaking:** Requires Laravel 11+ (dropped Laravel 10 support)
- **Breaking:** Requires PHP 8.2+
- **Breaking:** Renamed environment variables for consistency
- Improved error handling and output

### Removed
- **Breaking:** Removed Enlightn package integration

## [1.3.0] - 2024-04-13

### Changed
- Improved Github actions pipelines
- Updated Node.js version requirements
- Replaced deprecated libraries with modern alternatives
- Updated testing frameworks

## [1.2.0] - 2024-03-28

### Added
- Laravel 11 Compatibility

## [1.1.2] - 2024-01-04

### Fixed
- Removed Pint defaults for better customization
- Fixed Pint style issues

## [1.1.1] - 2023-06-10

### Fixed
- Code Analyzers fixed to consider only staged files (not all modified files)

## [1.1.0] - 2023-06-10

### Added
- Artisan executable path configuration option
- Laravel Zero support

### Fixed
- Hook stub template for better compatibility

## [1.0.1] - 2023-05-07

### Changed
- Updated package description

## [1.0.0] - 2023-05-07

### Added
- Initial stable release
- Pre-configured hooks for popular tools:
  - Laravel Pint
  - PHPCS with PHPCBF
  - ESLint
  - Prettier
  - Larastan
  - Enlightn
  - Blade Formatter
- Git hooks management via Artisan commands
- Custom hook generation with `git-hooks:make` command
- Support for all Git hook types (pre-commit, prepare-commit-msg, commit-msg, post-commit, pre-push)
- Comprehensive configuration options
- Pipeline processing for sequential hook execution
- >95% code coverage with Pest tests

## [0.1.1] - 2023-05-07

### Added
- Pre-release version with basic functionality

[2.0.0]: https://github.com/igorsgm/laravel-git-hooks/compare/v1.3.0...v2.0.0
[1.3.0]: https://github.com/igorsgm/laravel-git-hooks/compare/v1.2.0...v1.3.0
[1.2.0]: https://github.com/igorsgm/laravel-git-hooks/compare/v1.1.2...v1.2.0
[1.1.2]: https://github.com/igorsgm/laravel-git-hooks/compare/v1.1.1...v1.1.2
[1.1.1]: https://github.com/igorsgm/laravel-git-hooks/compare/v1.1.0...v1.1.1
[1.1.0]: https://github.com/igorsgm/laravel-git-hooks/compare/v1.0.1...v1.1.0
[1.0.1]: https://github.com/igorsgm/laravel-git-hooks/compare/v1.0.0...v1.0.1
[1.0.0]: https://github.com/igorsgm/laravel-git-hooks/compare/v0.1.1...v1.0.0
[0.1.1]: https://github.com/igorsgm/laravel-git-hooks/releases/tag/v0.1.1