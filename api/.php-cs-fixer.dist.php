<?php

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__)
    ->exclude(['config', 'public', 'var'])
;

return (new PhpCsFixer\Config())
    ->setRiskyAllowed(true)
    ->setRules([
        '@PhpCsFixer' => true,
        '@PhpCsFixer:risky' => true,
        '@PER-CS2.0' => true,
        '@PER-CS2.0:risky' => true,
        '@PHP84Migration' => true,
        '@Symfony' => true,
        '@Symfony:risky' => true,
        'mb_str_functions' => true,
        'no_unreachable_default_argument_value' => true,
        'void_return' => false,
    ])
    ->setFinder($finder)
;
