<?php

namespace Igorsgm\LaravelGitHooks\Tests\Concerns;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

trait WithTmpFiles
{
    protected $tempDir;

    public function registerTmpTrait()
    {
        $this->tempDir = __DIR__.'/tmp';

        if (! is_dir($this->tempDir)) {
            mkdir($this->tempDir);
        }

        $this->tearDownCallback(function () {
            $this->deleteFiles($this->tempDir);
        });
    }

    protected function deleteFiles($target)
    {
        $it = new RecursiveDirectoryIterator($target, RecursiveDirectoryIterator::SKIP_DOTS);
        $files = new RecursiveIteratorIterator($it, RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($files as $file) {
            if ($file->isDir()) {
                rmdir($file->getRealPath());
            } else {
                unlink($file->getRealPath());
            }
        }

        rmdir($target);
    }

    /**
     * @param  string  $filename
     * @param  string  $content
     * @return string
     */
    protected function makeTmpFile(string $filename, string $content): string
    {
        $path = $this->getTmpFilePath($filename);

        file_put_contents($path, $content);

        return $path;
    }

    /**
     * @param  string  $filename
     * @param  string  $content
     */
    protected function assertTmpFileContains(string $filename, string $content)
    {
        $this->assertEquals($content, file_get_contents($this->getTmpFilePath($filename)));
    }

    /**
     * @param  string  $filename
     * @return string
     */
    protected function getTmpFilePath(string $filename): string
    {
        return $this->tempDir.'/'.$filename;
    }
}
