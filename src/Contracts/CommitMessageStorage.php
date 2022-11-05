<?php

namespace Igorsgm\LaravelGitHooks\Contracts;

interface CommitMessageStorage
{
    /**
     * Get commit message content
     *
     * @param  string  $path
     * @return string
     */
    public function get(string $path): string;

    /**
     * Update commit message
     *
     * @param  string  $path
     * @param  string  $message
     */
    public function update(string $path, string $message): void;
}
