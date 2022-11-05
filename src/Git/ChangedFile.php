<?php

namespace Igorsgm\LaravelGitHooks\Git;

class ChangedFile
{
    const A = 1; // added

    const M = 2; // modified

    const D = 4; // deleted

    const R = 8; // renamed

    const C = 16; // copied

    const U = 32; // updated but unmerged

    const N = 64; // untracked

    const PATTERN = '/^\s?(?<X>[A|M|D|R|C|U|\?]{1,2}| )(?<Y>[A|M|D|R|C|U|\?]{1,2}| )\s(?<file>\S+)(\s->\S+)?$/';

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
     * @var array
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

    /**
     * @param  string  $line
     */
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
     *
     * @return bool
     */
    public function isInCommit(): bool
    {
        return $this->X > 0 && $this->X ^ static::N;
    }

    /**
     * Get file path
     *
     * @return string
     */
    public function getFilePath(): string
    {
        return $this->file;
    }

    /**
     * @return bool
     */
    public function isAdded(): bool
    {
        return $this->X & static::A || $this->Y & static::A;
    }

    /**
     * @return bool
     */
    public function isModified(): bool
    {
        return $this->X & static::M || $this->Y & static::M;
    }

    /**
     * @return bool
     */
    public function isDeleted(): bool
    {
        return $this->X & static::D || $this->Y & static::D;
    }

    /**
     * @return bool
     */
    public function isUntracked(): bool
    {
        return $this->X & static::N || $this->Y & static::N;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->line;
    }
}
