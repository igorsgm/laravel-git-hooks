<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\CodeQuality\Rector\Empty_\SimplifyEmptyCheckOnEmptyArrayRector;

return RectorConfig::configure()
    ->withPaths([
        './',
    ])
    // uncomment to reach your current PHP version
    ->withPhpSets(php81: true)
    ->withRules([
        SimplifyEmptyCheckOnEmptyArrayRector::class,
    ]);
