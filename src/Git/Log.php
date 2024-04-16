<?php

namespace Igorsgm\GitHooks\Git;

use Carbon\Carbon;
use Illuminate\Support\Str;

class Log implements \Stringable
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
     * @var array<int, string>
     */
    private $merge = [];

    /**
     * @var string
     */
    private $message = '';

    /**
     * Log constructor.
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
     * @param  array<int, string>  $lines
     */
    private function parse(array $lines): void
    {
        $handlers = collect([
            'commit' => function ($line) {
                preg_match('/(?<hash>[a-z0-9]{40})/', $line, $matches);
                $this->hash = $matches['hash'] ?? null;
            },
            'Author' => function ($line) {
                $this->author = substr($line, strlen('Author:') + 1);
            },
            'Date' => function ($line) {
                $this->date = Carbon::parse(substr($line, strlen('Date:') + 3));
            },
            'Merge' => function ($line) {
                $merge = substr($line, strlen('Merge:') + 1);
                $this->merge = explode(' ', $merge);
            },
        ]);

        foreach ($lines as $line) {
            $handler = $handlers->first(fn ($handler, $prefix) => Str::startsWith($line, $prefix));

            if ($handler !== null) {
                $handler($line);
            } elseif (! empty($line)) {
                $this->message .= substr($line, 4)."\n";
            }
        }
    }

    /**
     * Get commit hash
     */
    public function getHash(): string
    {
        return $this->hash;
    }

    /**
     * Get author
     */
    public function getAuthor(): ?string
    {
        return $this->author;
    }

    /**
     * Get commit date
     */
    public function getDate(): Carbon
    {
        return $this->date;
    }

    /**
     * Get merge information
     *
     * @return array<int, string>
     */
    public function getMerge(): array
    {
        return $this->merge;
    }

    /**
     * Get commit message
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    public function __toString(): string
    {
        return $this->getHash();
    }

    public function getLog(): string
    {
        return $this->log;
    }
}
