<?php

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
