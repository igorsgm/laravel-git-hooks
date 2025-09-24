<?php

$finder = PhpCsFixer\Finder::create()
    ->in([
        './',
    ])
    ->name('*.php')
    ->ignoreDotFiles(true)
    ->ignoreVCS(true);

return (new PhpCsFixer\Config)
    ->setRules([
        '@PSR12' => true,
        'array_syntax' => ['syntax' => 'short'],
        'no_unused_imports' => true,
        'no_whitespace_before_comma_in_array' => true,
        'ordered_imports' => ['sort_algorithm' => 'alpha'],
        'phpdoc_order' => true,
        'blank_line_before_statement' => true,
        'single_quote' => true,
        'declare_strict_types' => true,
        'strict_comparison' => true,
        'strict_param' => true,
        'no_superfluous_phpdoc_tags' => true,
        'modernize_types_casting' => true,
        'fully_qualified_strict_types' => true,
        'native_function_invocation' => ['include' => ['@internal']],
    ])
    ->setFinder($finder)
    ->setRiskyAllowed(true);
