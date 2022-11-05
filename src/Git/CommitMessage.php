<?php

namespace Igorsgm\LaravelGitHooks\Git;

class CommitMessage
{
    /**
     * @var string
     */
    protected $message;

    /**
     * @var ChangedFiles
     */
    protected $files;

    /**
     * @param  string  $message
     * @param  ChangedFiles  $files
     */
    public function __construct(string $message, ChangedFiles $files)
    {
        $this->setMessage($message);
        $this->files = $files;
    }

    /**
     * Set commit message
     *
     * @param  string  $message
     */
    public function setMessage(string $message): void
    {
        $this->message = $message;
    }

    /**
     * Get commit message
     *
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * Get changed files
     *
     * @return ChangedFiles
     */
    public function getFiles(): ChangedFiles
    {
        return $this->files;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->message;
    }
}
