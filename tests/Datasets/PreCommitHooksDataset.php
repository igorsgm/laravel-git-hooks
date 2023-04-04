<?php

use Igorsgm\GitHooks\Console\Commands\Hooks\BladeFormatterPreCommitHook;
use Igorsgm\GitHooks\Console\Commands\Hooks\LarastanPreCommitHook;
use Igorsgm\GitHooks\Console\Commands\Hooks\PHPCodeSnifferPreCommitHook;
use Igorsgm\GitHooks\Console\Commands\Hooks\PintPreCommitHook;
use Igorsgm\GitHooks\Console\Commands\Hooks\PrettierPreCommitHook;

dataset('pintConfiguration', [
    'Config File' => [
        [
            'path' => '../../../bin/pint',
            'config' => __DIR__.'/../Fixtures/pintFixture.json',
            'file_extensions' => '/\.php$/',
        ],
    ],
    'Preset' => [
        [
            'path' => '../../../bin/pint',
            'preset' => 'psr12',
            'file_extensions' => '/\.php$/',
        ],
    ],
]);

dataset('phpcsConfiguration', [
    'phpcs.xml file' => [
        [
            'phpcs_path' => '../../../bin/phpcs',
            'phpcbf_path' => '../../../bin/phpcbf',
            'standard' => __DIR__.'/../Fixtures/phpcsFixture.xml',
            'file_extensions' => '/\.php$/',
        ],
    ],
]);

dataset('bladeFormatterConfiguration', [
    '.bladeformatterrc.json file' => [
        [
            'path' => '../../../../node_modules/.bin/blade-formatter',
            'config' => __DIR__.'/../Fixtures/bladeFormatterFixture.json',
            'file_extensions' => '/\.blade\.php$/',
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

dataset('prettierConfiguration', [
    '.prettierrc.json file & additional params' => [
        [
            'path' => '../../../../node_modules/.bin/prettier',
            'config' => __DIR__.'/../Fixtures/.prettierrcFixture.json',
            'additional_params' => '--config --find-config-path',
            'file_extensions' => '/\.(jsx?|tsx?|vue)$/',
        ],
    ],
]);

dataset('eslintConfiguration', [
    '.eslintrc.js file & additional params' => [
        [
            'path' => '../../../../node_modules/.bin/eslint',
            'config' => __DIR__.'/../Fixtures/.eslintrcFixture.js',
            'additional_params' => '--config',
            'file_extensions' => '/\.(jsx?|tsx?|vue)$/',
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
    'Prettier' => [
        'prettier',
        $nonExistentPath,
        PrettierPreCommitHook::class,
    ],
]);
