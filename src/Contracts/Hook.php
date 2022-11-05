<?php

namespace Igorsgm\LaravelGitHooks\Contracts;

interface Hook
{
    /**
     * Get hook name
     *
     * @return string
     */
    public function getName(): string;
}
