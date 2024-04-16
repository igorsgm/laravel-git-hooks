<?php

namespace Igorsgm\GitHooks;

use Exception;
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
        $configGitHooks = config('git-hooks');

        return array_filter($this->getSupportedHooks(), function ($hook) use ($configGitHooks) {
            return ! empty($configGitHooks[$hook]);
        });
    }

    /**
     * Install git hook
     *
     * @throws Exception
     */
    public function install(string $hookName): void
    {
        if (! is_dir($this->getGitHooksDir())) {
            throw new Exception('Git not initialized in this project.');
        }

        $command = 'git-hooks:'.$hookName;

        $hookPath = $this->getGitHooksDir().'/'.$hookName;
        $hookScript = str_replace(
            ['{command}', '{artisanPath}'],
            [$command, config('git-hooks.artisan_path')],
            $this->getHookStub()
        );

        file_put_contents($hookPath, $hookScript);
        chmod($hookPath, 0777);
    }

    /**
     * Returns the content of the git hook stub.
     */
    public function getHookStub(): ?string
    {
        $hookStubPath = __DIR__.str_replace('/', DIRECTORY_SEPARATOR, '/Console/Commands/stubs/hook');

        return file_get_contents($hookStubPath);
    }

    /**
     * Returns the path to the git hooks directory.
     */
    public function getGitHooksDir(): string
    {
        return base_path('.git'.DIRECTORY_SEPARATOR.'hooks');
    }
}
