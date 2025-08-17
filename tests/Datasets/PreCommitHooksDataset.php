<?php

use Igorsgm\GitHooks\Console\Commands\Hooks\BladeFormatterPreCommitHook;
use Igorsgm\GitHooks\Console\Commands\Hooks\LarastanPreCommitHook;
use Igorsgm\GitHooks\Console\Commands\Hooks\PHPCodeSnifferPreCommitHook;
use Igorsgm\GitHooks\Console\Commands\Hooks\PhpInsightsPreCommitHook;
use Igorsgm\GitHooks\Console\Commands\Hooks\PintPreCommitHook;
use Igorsgm\GitHooks\Console\Commands\Hooks\PrettierPreCommitHook;
use Igorsgm\GitHooks\Console\Commands\Hooks\RectorPreCommitHook;

dataset(
    'pintConfiguration', [
        'Config File' => [
            [
                'path' => dirname(__DIR__).'/Fixtures/fake-Pint',
                'config' => __DIR__.'/../Fixtures/pintFixture.json',
                'file_extensions' => '/\.php$/',
                'run_in_docker' => false,
                'docker_container' => '',
            ],
        ],
        'Preset' => [
            [
                'path' => dirname(__DIR__).'/Fixtures/fake-Pint',
                'preset' => 'psr12',
                'file_extensions' => '/\.php$/',
                'run_in_docker' => false,
                'docker_container' => '',
            ],
        ],
    ]
);

dataset(
    'phpcsConfiguration', [
        'phpcs.xml file' => [
            [
                'phpcs_path' => dirname(__DIR__).'/Fixtures/fake-PHP_CodeSniffer',
                'phpcbf_path' => dirname(__DIR__).'/Fixtures/fake-PHP_CodeSniffer',
                'config' => __DIR__.'/../Fixtures/phpcsFixture.xml',
                'file_extensions' => '/\.php$/',
                'run_in_docker' => false,
                'docker_container' => '',
            ],
        ],
    ]
);

dataset(
    'phpinsightsConfiguration', [
        'phpinsights.php file' => [
            [
                'path' => dirname(__DIR__).'/Fixtures/fake-PhpInsights',
                'config' => __DIR__.'/../Fixtures/phpinsightsFixture.php',
                'additional_params' => '',
                'file_extensions' => '/\.php$/',
                'run_in_docker' => false,
                'docker_container' => '',
            ],
        ],
    ]
);

dataset(
    'rectorConfiguration', [
        'rector.php file' => [
            [
                'path' => dirname(__DIR__).'/Fixtures/fake-Rector',
                'config' => __DIR__.'/../Fixtures/rectorFixture.php',
                'additional_params' => '',
                'file_extensions' => '/\.php$/',
                'run_in_docker' => false,
                'docker_container' => '',
            ],
        ],
    ]
);

dataset(
    'bladeFormatterConfiguration', [
        '.bladeformatterrc.json file' => [
            [
                'path' => dirname(__DIR__).'/Fixtures/fake-BladeFormatter',
                'config' => __DIR__.'/../Fixtures/bladeFormatterFixture.json',
                'file_extensions' => '/\.blade\.php$/',
                'run_in_docker' => false,
                'docker_container' => '',
            ],
        ],
    ]
);

dataset(
    'larastanConfiguration', [
        'phpstan.neon file & additional params' => [
            [
                'path' => dirname(__DIR__).'/Fixtures/fake-Larastan',
                'config' => __DIR__.'/../Fixtures/phpstanFixture.neon',
                'additional_params' => '--xdebug',
                'file_extensions' => '/\.php$/',
                'run_in_docker' => false,
                'docker_container' => '',
            ],
        ],
    ]
);

dataset(
    'prettierConfiguration', [
        '.prettierrc.json file & additional params' => [
            [
                'path' => dirname(__DIR__).'/Fixtures/fake-Prettier',
                'config' => __DIR__.'/../Fixtures/.prettierrcFixture.json',
                'additional_params' => '--config --find-config-path',
                'file_extensions' => '/\.(jsx?|tsx?|vue)$/',
                'run_in_docker' => false,
                'docker_container' => '',
            ],
        ],
    ]
);

dataset(
    'eslintConfiguration', [
        '.eslintrc.js file & additional params' => [
            [
                'path' => dirname(__DIR__).'/Fixtures/fake-ESLint',
                'config' => __DIR__.'/../Fixtures/.eslintrcFixture.js',
                'additional_params' => '--config',
                'file_extensions' => '/\.(jsx?|tsx?|vue)$/',
                'run_in_docker' => false,
                'docker_container' => '',
            ],
        ],
    ]
);

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

dataset(
    'codeAnalyzersList', [
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
    ]
);
