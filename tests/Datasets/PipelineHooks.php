<?php

use Igorsgm\GitHooks\Tests\Fixtures\HooksPipelineDefaultFixture1;
use Igorsgm\GitHooks\Tests\Fixtures\HooksPipelineInvokableFixture3;
use Igorsgm\GitHooks\Tests\Fixtures\HooksPipelineWithParamsFixture2;

dataset(
    'pipelineHooks', [
        'Default Hook' => [
            'hook' => HooksPipelineDefaultFixture1::class,
            'parameters' => null,
        ],
        'Hook with Params' => [
            'hook' => HooksPipelineWithParamsFixture2::class,
            'parameters' => [
                'param' => 'Hook 2',
            ],
        ],
        'Invokable Hook Class' => [
            'hook' => HooksPipelineInvokableFixture3::class,
            'parameters' => null,
        ],
    ]
);
