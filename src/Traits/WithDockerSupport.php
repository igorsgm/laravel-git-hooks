<?php

declare(strict_types=1);

namespace Igorsgm\GitHooks\Traits;

trait WithDockerSupport
{
    protected bool $runInDocker = false;

    protected string $dockerContainer = '';

    public function setRunInDocker(bool $runInDocker): self
    {
        $this->runInDocker = (bool) $runInDocker;

        return $this;
    }

    public function getRunInDocker(): bool
    {
        return $this->runInDocker;
    }

    public function setDockerContainer(string $dockerContainer): self
    {
        $this->dockerContainer = $dockerContainer;

        return $this;
    }

    public function getDockerContainer(): string
    {
        return $this->dockerContainer;
    }

    public function dockerCommand(string $command): string
    {
        if (!$this->runInDocker || empty($this->dockerContainer)) {
            return $command;
        }

        return 'docker exec '.escapeshellarg($this->dockerContainer).' sh -c '.escapeshellarg($command);
    }
}
