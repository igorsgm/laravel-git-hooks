<?php

declare(strict_types=1);

return [
    'preset' => 'default',
    'exclude' => [
    ],
    'add' => [
        //  ExampleMetric::class => [
        //      ExampleInsight::class,
        //  ]
    ],
    'remove' => [
        NunoMaduro\PhpInsights\Domain\Insights\ForbiddenNormalClasses::class,
    ],
    'config' => [
        //  ExampleInsight::class => [
        //      'key' => 'value',
        //  ],
    ],
    'requirements' => [
        'min-quality' => 100,
        'min-complexity' => 0,
        'min-architecture' => 100,
        'min-style' => 100,
    ],
];
