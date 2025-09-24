<?php

declare(strict_types=1);

namespace Igorsgm\GitHooks\Tests\Traits;

use Illuminate\Support\Facades\File;

trait WithTmpFiles
{
    private $tempDirectoryPath;

    public function initializeTempDirectory(?string $path = '', bool $force = false): void
    {
        if ($path) {
            $this->setTempDirectoryPath($path);
        }

        $tempDirectoryPath = $this->getTempDirectoryPath();

        if ($force) {
            $this->deleteTempDirectory();
        }

        if (!is_dir($tempDirectoryPath)) {
            File::makeDirectory($tempDirectoryPath, 0755, true);
        }
    }

    public function getTempDirectoryPath(): string
    {
        return $this->tempDirectoryPath ?: __DIR__.DIRECTORY_SEPARATOR.'temp';
    }

    public function setTempDirectoryPath(string $directoryName): void
    {
        $this->tempDirectoryPath = $directoryName;
    }

    protected function deleteTempDirectory()
    {
        File::deleteDirectory(
            $this->getTempDirectoryPath()
        );
    }

    protected function getTempFilePath(string $filename): string
    {
        return $this->getTempDirectoryPath().DIRECTORY_SEPARATOR.$filename;
    }

    protected function makeTempFile(string $filename, string $content): string
    {
        $path = $this->getTempFilePath($filename);

        file_put_contents($path, $content);

        return $path;
    }
}
