<?php

declare(strict_types=1);

return [
    'preset' => 'laravel',
    'exclude' => [
        'src/Console/Commands/',
        'src/HooksPipeline.php',
        'src/Git/Log.php',
    ],
    'add' => [
        //  ExampleMetric::class => [
        //      ExampleInsight::class,
        //  ]
    ],
    'remove' => [
        SlevomatCodingStandard\Sniffs\Classes\ForbiddenPublicPropertySniff::class,
        SlevomatCodingStandard\Sniffs\ControlStructures\DisallowEmptySniff::class,
        SlevomatCodingStandard\Sniffs\TypeHints\DisallowMixedTypeHintSniff::class,
        SlevomatCodingStandard\Sniffs\Classes\SuperfluousExceptionNamingSniff::class,
        SlevomatCodingStandard\Sniffs\Functions\FunctionLengthSniff::class,
        SlevomatCodingStandard\Sniffs\Commenting\DocCommentSpacingSniff::class,
        NunoMaduro\PhpInsights\Domain\Sniffs\ForbiddenSetterSniff::class,
        NunoMaduro\PhpInsights\Domain\Insights\CyclomaticComplexityIsHigh::class,
        NunoMaduro\PhpInsights\Domain\Insights\ForbiddenNormalClasses::class,
        NunoMaduro\PhpInsights\Domain\Insights\ForbiddenTraits::class,
        PHP_CodeSniffer\Standards\Generic\Sniffs\Commenting\TodoSniff::class,
        PHP_CodeSniffer\Standards\Generic\Sniffs\Files\LineLengthSniff::class,
        PhpCsFixer\Fixer\ClassNotation\OrderedClassElementsFixer::class,
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
