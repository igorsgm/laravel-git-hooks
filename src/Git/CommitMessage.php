<?php

declare(strict_types=1);

namespace Igorsgm\GitHooks\Git;

class CommitMessage implements \Stringable
{
    protected string $message;

    public function __construct(string $message, protected ChangedFiles $files)
    {
        $this->setMessage($message);
    }

    public function __toString(): string
    {
        return $this->message;
    }

    /**
     * Set commit message
     */
    public function setMessage(string $message): void
    {
        $this->message = $message;
    }

    /**
     * Get commit message
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * Get changed files
     */
    public function getFiles(): ChangedFiles
    {
        return $this->files;
    }
}
