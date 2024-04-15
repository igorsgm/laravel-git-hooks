<?php

namespace Igorsgm\GitHooks\Traits;

use Igorsgm\GitHooks\Exceptions\HookFailException;

trait WithPipelineFailCheck
{
    protected function markPipelineFailed(): void
    {
        $tmpFile = $this->getPipelineFailedTempFile();
        if (touch($tmpFile) === false) {
            throw new HookFailException();
        }
    }

    protected function checkPipelineFailed(): bool
    {
        $tmpFile = $this->getPipelineFailedTempFile();
        return file_exists($tmpFile);
    }

    protected function clearPipelineFailed(): void
    {
        $tmpFile = $this->getPipelineFailedTempFile();

        if (file_exists($tmpFile)) {
            unlink($tmpFile);
        }
    }
    
    protected function getPipelineFailedTempFile(): string
    {
        $tmpFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'githooks-pipeline-fail-' . getmypid();
        return $tmpFile;
    }
}
