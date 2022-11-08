<?php

namespace Igorsgm\GitHooks\Git;

use Carbon\Carbon;

class Log
{
    /**
     * @var string
     */
    protected $log;

    /**
     * @var false|string
     */
    private $hash;

    /**
     * @var false|string
     */
    private $author;

    /**
     * @var Carbon
     */
    private $date;

    /**
     * @var array
     */
    private $merge = [];

    /**
     * @var string
     */
    private $message = '';

    /**
     * Log constructor.
     *
     * @param  string  $log
     */
    public function __construct(string $log)
    {
        $this->log = $log;
        $lines = preg_split("/\r\n|\n|\r/", $log);

        $this->parse($lines);
    }

    /**
     * Parse current log into variables
     *
     * @param  array  $lines
     */
    private function parse(array $lines): void
    {
        foreach ($lines as $line) {
            if (strpos($line, 'commit') === 0) {
                preg_match('/(?<hash>[a-z0-9]{40})/', $line, $matches);
                $this->hash = $matches['hash'] ?? null;
            } elseif (strpos($line, 'Author') === 0) {
                $this->author = substr($line, strlen('Author:') + 1);
            } elseif (strpos($line, 'Date') === 0) {
                $this->date = Carbon::parse(substr($line, strlen('Date:') + 3));
            } elseif (strpos($line, 'Merge') === 0) {
                $merge = substr($line, strlen('Merge:') + 1);
                $this->merge = explode(' ', $merge);
            } elseif (! empty($line)) {
                $this->message .= substr($line, 4)."\n";
            }
        }
    }

    /**
     * Get commit hash
     *
     * @return string
     */
    public function getHash(): string
    {
        return $this->hash;
    }

    /**
     * Get author
     *
     * @return string|null
     */
    public function getAuthor(): ?string
    {
        return $this->author;
    }

    /**
     * Get commit date
     *
     * @return Carbon
     */
    public function getDate(): Carbon
    {
        return $this->date;
    }

    /**
     * Get merge information
     *
     * @return array
     */
    public function getMerge(): array
    {
        return $this->merge;
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
     * @return string
     */
    public function __toString()
    {
        return $this->getHash();
    }

    /**
     * @return string
     */
    public function getLog(): string
    {
        return $this->log;
    }
}
