<?php

declare(strict_types=1);

namespace Igorsgm\GitHooks\Git;

use Carbon\Carbon;
use Illuminate\Support\Str;

class Log implements \Stringable
{
    private ?string $hash = null;

    private ?string $author = null;

    private ?Carbon $date = null;

    /**
     * @var array<int, string>
     */
    private array $merge = [];

    private string $message = '';

    /**
     * Log constructor.
     */
    public function __construct(protected string $log)
    {
        $lines = preg_split("/\r\n|\n|\r/", $this->log);

        if ($lines !== false) {
            $this->parse($lines);
        }
    }

    /**
     * Parse current log into variables
     *
     * @param  array<int, string>  $lines
     */
    private function parse(array $lines): void
    {
        $handlers = collect(
            [
                'commit' => function ($line): void {
                    preg_match('/(?<hash>[a-z0-9]{40})/', $line, $matches);
                    $this->hash = $matches['hash'] ?? null;
                },
                'Author' => function ($line): void {
                    $this->author = substr($line, strlen('Author:') + 1);
                },
                'Date' => function ($line): void {
                    $this->date = Carbon::parse(substr($line, strlen('Date:') + 3));
                },
                'Merge' => function ($line): void {
                    $merge = substr($line, strlen('Merge:') + 1);
                    $this->merge = explode(' ', $merge);
                },
            ]
        );

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
    public function getHash(): ?string
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
    public function getDate(): ?Carbon
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
        return $this->getHash() ?? '';
    }

    public function getLog(): string
    {
        return $this->log;
    }
}
