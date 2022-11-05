<?php

namespace Igorsgm\LaravelGitHooks\Contracts;

interface Configurator
{
    /**
     * Register git hooks
     */
    public function run(): void;
}
