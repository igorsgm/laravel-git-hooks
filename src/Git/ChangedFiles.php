<?php

namespace Igorsgm\GitHooks\Git;

use Illuminate\Support\Collection;

class ChangedFiles
{
    /**
     * @var Collection
     */
    protected $files;

    /**
     * @param  string  $log
     */
    public function __construct(string $log)
    {
        $files = (array) preg_split("/\r\n|\n|\r/", $log);

        $this->files = collect($files)
            ->filter()
            ->map(function (string $line) {
                return new ChangedFile($line);
            });
    }

    /**
     * Get all files with changes
     *
     * @return Collection
     */
    public function getFiles(): Collection
    {
        return $this->files;
    }

    /**
     * Get list of staged files
     *
     * @return Collection
     */
    public function getStaged(): Collection
    {
        return $this->files->filter(function (ChangedFile $file) {
            return $file->isStaged();
        });
    }

    /**
     * Get added to commit files
     *
     * @return Collection
     */
    public function getAddedToCommit(): Collection
    {
        return $this->files->filter(function (ChangedFile $file) {
            return $file->isInCommit();
        });
    }

    /**
     * @return Collection
     */
    public function getDeleted(): Collection
    {
        return $this->files->filter(function (ChangedFile $file) {
            return $file->isDeleted();
        });
    }

    /**
     * Get untracked files
     *
     * @return Collection
     */
    public function getUntracked(): Collection
    {
        return $this->files->filter(function (ChangedFile $file) {
            return $file->isUntracked();
        });
    }
}
