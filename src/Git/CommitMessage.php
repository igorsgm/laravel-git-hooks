<?php

namespace Igorsgm\GitHooks\Git;

class CommitMessage implements \Stringable
{
    /**
     * @var string
     */
    protected $message;

    /**
     * @var ChangedFiles
     */
    protected $files;

    public function __construct(string $message, ChangedFiles $files)
    {
        $this->setMessage($message);
        $this->files = $files;
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

    public function __toString(): string
    {
        return $this->message;
    }
}
