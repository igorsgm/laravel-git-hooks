<?php

declare(strict_types=1);

// PHPStan stubs for PHP 8.4 mb_* functions that are missing return-type
// information in PHPStan's bundled stubs.

/**
 * @param non-empty-string|null $characters
 */
function mb_trim(string $string, ?string $characters = null, ?string $encoding = null): string
{
}

/**
 * @param non-empty-string|null $characters
 */
function mb_ltrim(string $string, ?string $characters = null, ?string $encoding = null): string
{
}

/**
 * @param non-empty-string|null $characters
 */
function mb_rtrim(string $string, ?string $characters = null, ?string $encoding = null): string
{
}
