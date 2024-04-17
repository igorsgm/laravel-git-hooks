<?php

declare(strict_types=1);

namespace Igorsgm\GitHooks\Git;

class ChangedFile implements \Stringable
{
    public const A = 1; // added

    public const M = 2; // modified

    public const D = 4; // deleted

    public const R = 8; // renamed

    public const C = 16; // copied

    public const U = 32; // updated but unmerged

    public const N = 64; // untracked

    public const PATTERN = '/^\s?(?<X>[A|M|D|R|C|U|\?]{1,2}| )(?<Y>[A|M|D|R|C|U|\?]{1,2}| )\s(?<file>\S+)(\s\->\S+)?$/';

    protected string $line;

    protected string $file;

    protected int $X = 0;

    protected int $Y = 0;

    /**
     * @var array<string, int>
     */
    protected array $bitMap = [
        'A' => self::A,
        'M' => self::M,
        'D' => self::D,
        'R' => self::R,
        'C' => self::C,
        'U' => self::U,
        '?' => self::N,
    ];

    public function __construct(bool|string $line)
    {
        $this->line = (string) $line;

        preg_match(self::PATTERN, $this->line, $matches);

        if (isset($matches['X'])) {
            $this->X = $this->bitMap[$matches['X']] ?? 0;
        }

        if (isset($matches['Y'])) {
            $this->Y = $this->bitMap[$matches['Y']] ?? 0;
        }

        $this->file = $matches['file'] ?? '';
    }

    public function __toString(): string
    {
        return $this->line;
    }

    /**
     * Check if file in commit
     */
    public function isInCommit(): bool
    {
        return $this->X > 0 && $this->X ^ self::N;
    }

    /**
     * Get file path
     */
    public function getFilePath(): string
    {
        return $this->file;
    }

    public function isStaged(): bool
    {
        return $this->isAdded() || $this->isModified() || $this->isCopied();
    }

    public function isAdded(): bool
    {
        return $this->X & self::A || $this->Y & self::A;
    }

    public function isModified(): bool
    {
        return $this->X & self::M || $this->Y & self::M;
    }

    public function isDeleted(): bool
    {
        return $this->X & self::D || $this->Y & self::D;
    }

    public function isUntracked(): bool
    {
        return $this->X & self::N || $this->Y & self::N;
    }

    public function isCopied(): bool
    {
        return $this->X & self::C || $this->Y & self::C;
    }
}
