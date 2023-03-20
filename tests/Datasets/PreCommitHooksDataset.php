<?php

use Igorsgm\GitHooks\Console\Commands\Hooks\BladeFormatterPreCommitHook;
use Igorsgm\GitHooks\Console\Commands\Hooks\LarastanPreCommitHook;
use Igorsgm\GitHooks\Console\Commands\Hooks\PHPCodeSnifferPreCommitHook;
use Igorsgm\GitHooks\Console\Commands\Hooks\PintPreCommitHook;

dataset('pintConfigurations', [
    'Config File' => [
        [
            'path' => '../../../bin/pint',
            'config' => __DIR__.'/../Fixtures/pintFixture.json',
        ],
    ],
    'Preset' => [
        [
            'path' => '../../../bin/pint',
            'preset' => 'psr12',
        ],
    ],
]);

dataset('phpcsConfiguration', [
    'phpcs.xml file' => [
        [
            'phpcs_path' => '../../../bin/phpcs',
            'phpcbf_path' => '../../../bin/phpcbf',
            'standard' => __DIR__.'/../Fixtures/phpcsFixture.xml',
        ],
    ],
]);

dataset('bladeFormatterConfiguration', [
    '.bladeformatterrc.json file' => [
        [
            'path' => '../../../../node_modules/.bin/blade-formatter',
            'config' => __DIR__.'/../Fixtures/bladeFormatterFixture.json',
        ],
    ],
]);

dataset('larastanConfiguration', [
    'phpstan.neon file & additional params' => [
        [
            'path' => '../../../bin/phpstan',
            'config' => __DIR__.'/../Fixtures/phpstanFixture.neon',
            'additional_params' => '--xdebug',
        ],
    ],
]);

$nonExistentPath = [
    'path' => 'nonexistent/path',
    'phpcs_path' => 'nonexistent/path',
];

dataset('codeAnalyzersList', [
    'Laravel Pint' => [
        'laravel_pint',
        $nonExistentPath,
        PintPreCommitHook::class,
    ],
    'PHP Code Sniffer' => [
        'php_code_sniffer',
        $nonExistentPath,
        PHPCodeSnifferPreCommitHook::class,
    ],
    'Blade Formatter' => [
        'blade_formatter',
        $nonExistentPath,
        BladeFormatterPreCommitHook::class,
    ],
    'Larastan' => [
        'larastan',
        $nonExistentPath,
        LarastanPreCommitHook::class,
    ],
]);
