<?php

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

    /**
     * @var string
     */
    protected $line;

    /**
     * @var string
     */
    protected $file;

    /**
     * @var int
     */
    protected $X = 0;

    /**
     * @var int
     */
    protected $Y = 0;

    /**
     * @var array<string, int>
     */
    protected $bitMap = [
        'A' => self::A,
        'M' => self::M,
        'D' => self::D,
        'R' => self::R,
        'C' => self::C,
        'U' => self::U,
        '?' => self::N,
    ];

    public function __construct(string $line)
    {
        $this->line = $line;

        preg_match(static::PATTERN, $line, $matches);

        if (isset($matches['X'])) {
            $this->X = $this->bitMap[$matches['X']] ?? 0;
        }

        if (isset($matches['Y'])) {
            $this->Y = $this->bitMap[$matches['Y']] ?? 0;
        }

        $this->file = $matches['file'] ?? '';
    }

    /**
     * Check if file in commit
     */
    public function isInCommit(): bool
    {
        return $this->X > 0 && $this->X ^ static::N;
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
        return $this->X & static::A || $this->Y & static::A;
    }

    public function isModified(): bool
    {
        return $this->X & static::M || $this->Y & static::M;
    }

    public function isDeleted(): bool
    {
        return $this->X & static::D || $this->Y & static::D;
    }

    public function isUntracked(): bool
    {
        return $this->X & static::N || $this->Y & static::N;
    }

    public function isCopied(): bool
    {
        return $this->X & static::C || $this->Y & static::C;
    }

    public function __toString(): string
    {
        return $this->line;
    }
}
