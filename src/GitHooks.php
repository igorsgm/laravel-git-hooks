<?php

namespace Igorsgm\GitHooks;

use Exception;
use Igorsgm\GitHooks\Traits\GitHelper;

class GitHooks
{
    use GitHelper;

    /**
     * Get all supported git hooks
     */
    public function getSupportedHooks()
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
     */
    public function getAvailableHooks()
    {
        $configGitHooks = config('git-hooks');

        return array_filter($this->getSupportedHooks(), function ($hook) use ($configGitHooks) {
            return ! empty($configGitHooks[$hook]);
        });
    }

    /**
     * Install git hook
     *
     * @param  string  $hookName
     * @return void
     *
     * @throws Exception
     */
    public function install(string $hookName)
    {
        if (! is_dir($this->getGitHooksDir())) {
            throw new Exception('Git not initialized in this project.');
        }

        $command = 'git-hooks:'.$hookName;

        $hookPath = $this->getGitHooksDir().'/'.$hookName;
        $hookScript = str_replace(
            ['{command}', '{path}'],
            [$command, base_path()],
            $this->getHookStub()
        );

        file_put_contents($hookPath, $hookScript);
        chmod($hookPath, 0777);
    }

    /**
     * Returns the content of the git hook stub.
     *
     * @return false|string
     */
    public function getHookStub()
    {
        $hookStubPath = __DIR__.str_replace('/', DIRECTORY_SEPARATOR, '/Console/Commands/stubs/hook');

        return file_get_contents($hookStubPath);
    }

    /**
     * Returns the path to the git hooks directory.
     *
     * @return string
     */
    public function getGitHooksDir()
    {
        return base_path('.git'.DIRECTORY_SEPARATOR.'hooks');
    }
}
