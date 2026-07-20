<?php

declare(strict_types=1);

namespace Igorsgm\GitHooks\Support;

/**
 * Typed accessors for the git-hooks configuration.
 *
 * Laravel's config() helper returns mixed, which requires explicit type guards
 * whenever PHPStan strict mode is enabled. This class centralises those guards
 * so call-sites stay readable.
 */
class Config
{
    public static function string(string $key, string $default = ''): string
    {
        $value = config($key, $default);

        return is_string($value) ? $value : $default;
    }

    public static function bool(string $key, bool $default = false): bool
    {
        $value = config($key, $default);

        if (is_bool($value)) {
            return $value;
        }

        // filter_var correctly maps strings like "false"/"0"/"off" to false,
        // which a plain (bool) cast cannot do (any non-empty string is truthy).
        $filtered = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

        return $filtered ?? $default;
    }

    public static function int(string $key, int $default = 0): int
    {
        $value = config($key, $default);

        return is_int($value) ? $value : $default;
    }
}
