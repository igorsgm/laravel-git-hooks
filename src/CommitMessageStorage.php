<?php

namespace Igorsgm\GitHooks;

class CommitMessageStorage implements Contracts\CommitMessageStorage
{
    /**
     * {@inheritDoc}
     */
    public function get(string $path): string
    {
        return file_get_contents($path);
    }

    /**
     * {@inheritDoc}
     */
    public function update(string $path, string $message): void
    {
        file_put_contents($path, $message);
    }
}
