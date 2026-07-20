<?php

declare(strict_types=1);

namespace Igorsgm\GitHooks;

use Exception;
use Igorsgm\GitHooks\Support\Config;
use Igorsgm\GitHooks\Traits\GitHelper;

class GitHooks
{
    use GitHelper;

    /**
     * Get all supported git hooks
     *
     * @return array<int, string>
     */
    public function getSupportedHooks(): array
    {
        return [
            'pre-commit',
            'prepare-commit-msg',
            'commit-msg',
            'post-commit',
            'pre-push',
            'pre-rebase',
            'post-rewrite',
            'post-checkout',
            'post-merge',
        ];
    }

    /**
     * Get all available git hooks being used
     *
     * @return array<int, string>
     */
    public function getAvailableHooks(): array
    {
        $configGitHooks = (array) config('git-hooks');

        return array_filter($this->getSupportedHooks(), fn ($hook) => !empty($configGitHooks[$hook]));
    }

    /**
     * Install git hook
     *
     * @throws Exception
     */
    public function install(string $hookName): void
    {
        if (!is_dir($this->getGitHooksDir())) {
            throw new Exception('Git not initialized in this project.');
        }

        $command = 'git-hooks:'.$hookName;

        $hookPath = $this->getGitHooksDir().'/'.$hookName;
        $hookScript = str_replace(
            '{command}',
            $command,
            (string) $this->getHookStub()
        );

        if (config('git-hooks.use_sail')) {
            $hookScript = str_replace(
                ['{php|sail}', '{artisanPath}'],
                ['vendor/bin/sail', 'artisan'],
                $hookScript
            );
        } else {
            $hookScript = str_replace(
                ['{php|sail}', '{artisanPath}'],
                ['php', Config::string('git-hooks.artisan_path')],
                $hookScript
            );
        }

        file_put_contents($hookPath, $hookScript);
        chmod($hookPath, 0755);
    }

    /**
     * Returns the content of the git hook stub.
     */
    public function getHookStub(): string
    {
        $hookStubPath = __DIR__.str_replace('/', DIRECTORY_SEPARATOR, '/Console/Commands/stubs/hook');

        $stub = file_get_contents($hookStubPath);

        if ($stub === false) {
            throw new Exception('Hook stub not found: '.$hookStubPath);
        }

        return $stub;
    }

    /**
     * Returns the path to the git hooks directory.
     * Uses git rev-parse to resolve the correct path in both regular repos and worktrees.
     */
    public function getGitHooksDir(): string
    {
        $basePath = base_path();
        $output = mb_rtrim((string) shell_exec('git -C '.escapeshellarg($basePath).' rev-parse --show-toplevel --git-common-dir 2>/dev/null'));
        $lines = explode("\n", $output);

        if (count($lines) === 2) {
            [$toplevel, $gitCommonDir] = $lines;

            // Resolve relative paths (e.g. ".git") against base_path
            if ($gitCommonDir !== '' && !str_starts_with($gitCommonDir, DIRECTORY_SEPARATOR)) {
                $gitCommonDir = $basePath.DIRECTORY_SEPARATOR.$gitCommonDir;
            }

            // Verify git is rooted at base_path, not a parent directory
            if (realpath($toplevel) === realpath($basePath) && is_dir($gitCommonDir)) {
                return $gitCommonDir.DIRECTORY_SEPARATOR.'hooks';
            }
        }

        return base_path('.git'.DIRECTORY_SEPARATOR.'hooks');
    }
}
