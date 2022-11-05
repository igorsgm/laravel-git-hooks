<?php

namespace Igorsgm\LaravelGitHooks\Contracts;

interface HookStorage
{
    /**
     * @param  string  $hookPath
     * @param  string  $content
     * @return mixed
     */
    public function store(string $hookPath, string $content): void;
}
