<?php

declare(strict_types=1);

use Igorsgm\GitHooks\Console\Commands\Hooks\LarastanPreCommitHook;
use Igorsgm\GitHooks\Console\Commands\Hooks\PHPCodeSnifferPreCommitHook;
use Igorsgm\GitHooks\Console\Commands\Hooks\PHPCSFixerPreCommitHook;
use Igorsgm\GitHooks\Console\Commands\Hooks\PhpInsightsPreCommitHook;
use Igorsgm\GitHooks\Console\Commands\Hooks\PintPreCommitHook;
use Igorsgm\GitHooks\Console\Commands\Hooks\RectorPreCommitHook;

$projectRoot = dirname(__DIR__, 2);

dataset('pintConfiguration', [
    'Config File' => [
        [
            'path' => $projectRoot.'/vendor/bin/pint',
            'config' => __DIR__.'/../Fixtures/pintFixture.json',
            'file_extensions' => '/\.php$/',
            'run_in_docker' => false,
            'docker_container' => '',
        ],
    ],
    'Preset' => [
        [
            'path' => $projectRoot.'/vendor/bin/pint',
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
            'phpcs_path' => $projectRoot.'/vendor/bin/phpcs',
            'phpcbf_path' => $projectRoot.'/vendor/bin/phpcbf',
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
            'path' => $projectRoot.'/vendor/bin/php-cs-fixer',
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
            'path' => $projectRoot.'/vendor/bin/phpinsights',
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
            'path' => $projectRoot.'/vendor/bin/rector',
            'config' => __DIR__.'/../Fixtures/rectorFixture.php',
            'additional_params' => '',
            'file_extensions' => '/\.php$/',
            'run_in_docker' => false,
            'docker_container' => '',
        ],
    ],
]);

dataset('larastanConfiguration', [
    'phpstan.neon file & additional params' => [
        [
            'path' => $projectRoot.'/vendor/bin/phpstan',
            'config' => __DIR__.'/../Fixtures/phpstanFixture.neon',
            'additional_params' => '',
            'file_extensions' => '/\.php$/',
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
    'Larastan' => [
        'larastan',
        $nonExistentPath,
        LarastanPreCommitHook::class,
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
