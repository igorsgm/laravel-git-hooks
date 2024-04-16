<?php

namespace Igorsgm\GitHooks\Git;

use Illuminate\Support\Collection;

class ChangedFiles
{
    /**
     * @var Collection<int, ChangedFile>
     */
    protected $files;

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
     * @return Collection<int, ChangedFile>
     */
    public function getFiles(): Collection
    {
        return $this->files;
    }

    /**
     * @return Collection<int, ChangedFile>
     */
    public function getStaged(): Collection
    {
        return $this->files->filter(function (ChangedFile $file) {
            return $file->isStaged();
        });
    }

    /**
     * @return Collection<int, ChangedFile>
     */
    public function getAddedToCommit(): Collection
    {
        return $this->files->filter(function (ChangedFile $file) {
            return $file->isInCommit();
        });
    }

    /**
     * @return Collection<int, ChangedFile>
     */
    public function getDeleted(): Collection
    {
        return $this->files->filter(function (ChangedFile $file) {
            return $file->isDeleted();
        });
    }

    /**
     * @return Collection<int, ChangedFile>
     */
    public function getUntracked(): Collection
    {
        return $this->files->filter(function (ChangedFile $file) {
            return $file->isUntracked();
        });
    }
}
