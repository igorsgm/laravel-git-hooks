<?php

namespace Igorsgm\GitHooks\Tests\Traits;

use Illuminate\Support\Facades\File;

trait WithTmpFiles
{
    private $tempDirectoryPath;

    public function initializeTempDirectory(?string $path = '', bool $force = false)
    {
        if ($path) {
            $this->setTempDirectoryPath($path);
        }

        $tempDirectoryPath = $this->getTempDirectoryPath();

        if ($force) {
            $this->deleteTempDirectory();
        }

        if (! is_dir($tempDirectoryPath)) {
            File::makeDirectory($tempDirectoryPath, 0755, true);
        }
    }

    protected function deleteTempDirectory()
    {
        File::deleteDirectory(
            $this->getTempDirectoryPath()
        );
    }

    /**
     * @param  string  $filename
     * @return string
     */
    protected function getTempFilePath(string $filename): string
    {
        return $this->getTempDirectoryPath().DIRECTORY_SEPARATOR.$filename;
    }

    /**
     * @return string
     */
    public function getTempDirectoryPath(): string
    {
        return $this->tempDirectoryPath ?: __DIR__.DIRECTORY_SEPARATOR.'temp';
    }

    /**
     * @param  string  $directoryName
     * @return void
     */
    public function setTempDirectoryPath(string $directoryName): void
    {
        $this->tempDirectoryPath = $directoryName;
    }

    /**
     * @param  string  $filename
     * @param  string  $content
     * @return string
     */
    protected function makeTempFile(string $filename, string $content): string
    {
        $path = $this->getTempFilePath($filename);

        file_put_contents($path, $content);

        return $path;
    }
}
