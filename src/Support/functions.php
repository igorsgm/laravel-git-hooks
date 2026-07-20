<?php

declare(strict_types=1);

/**
 * Polyfill for mb_trim(), mb_ltrim(), and mb_rtrim() introduced in PHP 8.4.
 *
 * These are no-ops on PHP 8.4+ where the native functions are already available.
 */
if (!function_exists('mb_trim')) {
    function mb_trim(string $string, ?string $characters = null, ?string $encoding = null): string
    {
        if ($characters !== null) {
            $chars = preg_quote($characters, '/');

            return (string) preg_replace('/^['.$chars.']+|['.$chars.']+$/u', '', $string);
        }

        return (string) preg_replace('/^\s+|\s+$/u', '', $string);
    }
}

if (!function_exists('mb_ltrim')) {
    function mb_ltrim(string $string, ?string $characters = null, ?string $encoding = null): string
    {
        if ($characters !== null) {
            $chars = preg_quote($characters, '/');

            return (string) preg_replace('/^['.$chars.']+/u', '', $string);
        }

        return (string) preg_replace('/^\s+/u', '', $string);
    }
}

if (!function_exists('mb_rtrim')) {
    function mb_rtrim(string $string, ?string $characters = null, ?string $encoding = null): string
    {
        if ($characters !== null) {
            $chars = preg_quote($characters, '/');

            return (string) preg_replace('/['.$chars.']+$/u', '', $string);
        }

        return (string) preg_replace('/\s+$/u', '', $string);
    }
}
