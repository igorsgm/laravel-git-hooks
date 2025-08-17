<?php

use Igorsgm\GitHooks\Console\Commands\Hooks\BladeFormatterPreCommitHook;
use Igorsgm\GitHooks\Console\Commands\Hooks\LarastanPreCommitHook;
use Igorsgm\GitHooks\Console\Commands\Hooks\PHPCodeSnifferPreCommitHook;
use Igorsgm\GitHooks\Console\Commands\Hooks\PHPCSFixerPreCommitHook;
use Igorsgm\GitHooks\Console\Commands\Hooks\PhpInsightsPreCommitHook;
use Igorsgm\GitHooks\Console\Commands\Hooks\PintPreCommitHook;
use Igorsgm\GitHooks\Console\Commands\Hooks\PrettierPreCommitHook;
use Igorsgm\GitHooks\Console\Commands\Hooks\RectorPreCommitHook;

dataset('pintConfiguration', [
    'Config File' => [
        [
            'path' => '../../../bin/pint',
            'config' => __DIR__.'/../Fixtures/pintFixture.json',
            'file_extensions' => '/\.php$/',
            'run_in_docker' => false,
            'docker_container' => '',
        ],
    ],
    'Preset' => [
        [
            'path' => '../../../bin/pint',
            'preset' => 'psr12',
            'file_extensions' => '/\.php$/',
            'run_in_docker' => false,
            'docker_container' => '',
        ],
    ],
]);

dataset('phpcsConfiguration', [
    'phpcs.xml file' => [
        [
            'phpcs_path' => '../../../bin/phpcs',
            'phpcbf_path' => '../../../bin/phpcbf',
            'config' => __DIR__.'/../Fixtures/phpcsFixture.xml',
            'file_extensions' => '/\.php$/',
            'run_in_docker' => false,
            'docker_container' => '',
        ],
    ],
]);

dataset('phpcsFixerConfiguration', [
    '.php-cs-fixer.php file' => [
        [
            'path' => '../../../bin/php-cs-fixer',
            'config' => __DIR__.'/../Fixtures/phpcsFixerFixture.php',
            'file_extensions' => '/\.php$/',
            'run_in_docker' => false,
            'docker_container' => '',
        ],
    ],
]);

dataset('phpinsightsConfiguration', [
    'phpinsights.php file' => [
        [
            'path' => '../../../bin/phpinsights',
            'config' => __DIR__.'/../Fixtures/phpinsightsFixture.php',
            'additional_params' => '',
            'file_extensions' => '/\.php$/',
            'run_in_docker' => false,
            'docker_container' => '',
        ],
    ],
]);

dataset('rectorConfiguration', [
    'rector.php file' => [
        [
            'path' => '../../../bin/rector',
            'config' => __DIR__.'/../Fixtures/rectorFixture.php',
            'additional_params' => '',
            'file_extensions' => '/\.php$/',
            'run_in_docker' => false,
            'docker_container' => '',
        ],
    ],
]);

dataset('bladeFormatterConfiguration', [
    '.bladeformatterrc.json file' => [
        [
            'path' => '../../../../node_modules/.bin/blade-formatter',
            'config' => __DIR__.'/../Fixtures/bladeFormatterFixture.json',
            'file_extensions' => '/\.blade\.php$/',
            'run_in_docker' => false,
            'docker_container' => '',
        ],
    ],
]);

dataset('larastanConfiguration', [
    'phpstan.neon file & additional params' => [
        [
            'path' => '../../../bin/phpstan',
            'config' => __DIR__.'/../Fixtures/phpstanFixture.neon',
            'additional_params' => '--xdebug',
            'file_extensions' => '/\.php$/',
            'run_in_docker' => false,
            'docker_container' => '',
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
            'run_in_docker' => false,
            'docker_container' => '',
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
            'run_in_docker' => false,
            'docker_container' => '',
        ],
    ],
]);

$nonExistentPath = [
    'path' => 'nonexistent/path',
    'phpcs_path' => 'nonexistent/path',
    'phpcbf_path' => 'nonexistent/path',
    'preset' => null,
    'config' => __DIR__.'/../Fixtures/pintFixture.json',
    'file_extensions' => '',
    'run_in_docker' => false,
    'docker_container' => '',
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
    'PHP CS Fixer' => [
        'php_cs_fixer',
        $nonExistentPath,
        PHPCSFixerPreCommitHook::class,
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
    'PHP Insights' => [
        'phpinsights',
        $nonExistentPath,
        PhpInsightsPreCommitHook::class,
    ],
    'Rector' => [
        'rector',
        $nonExistentPath,
        RectorPreCommitHook::class,
    ],
]);
