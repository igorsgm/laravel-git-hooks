<?php

namespace Igorsgm\LaravelGitHooks;

class HookStorage implements Contracts\HookStorage
{
    /**
     * {@inheritDoc}
     */
    public function store(string $hookPath, string $content): void
    {
        file_put_contents($hookPath, $content);

        chmod($hookPath, 0777);
    }
}
