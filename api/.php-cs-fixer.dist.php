<?php

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__)
    ->exclude(['config', 'public', 'var'])
;

return (new PhpCsFixer\Config())
    ->setRiskyAllowed(true)
    ->setRules([
        '@Symfony' => true,
        '@Symfony:risky' => true,
        '@DoctrineAnnotation' => true,
        '@PHP71Migration' => true,
        '@PHP71Migration:risky' => true,
        '@PHP73Migration' => true,
        '@PHPUnit75Migration:risky' => true,
        'align_multiline_comment' => ['comment_type' => 'phpdocs_like'],
        'array_indentation' => true,
        'array_syntax' => ['syntax' => 'short'],
        'combine_consecutive_unsets' => true,
        'comment_to_phpdoc' => true,
        'compact_nullable_typehint' => true,
        'doctrine_annotation_array_assignment' => ['operator' => '='],
        'doctrine_annotation_spaces' => [
            'after_array_assignments_equals' => false,
            'before_array_assignments_equals' => false,
        ],
        'echo_tag_syntax' => ['format' => 'short'],
        'explicit_indirect_variable' => true,
        'fully_qualified_strict_types' => true,
        'multiline_comment_opening_closing' => true,
        'header_comment' => ['header' => ''],
        'heredoc_to_nowdoc' => true,
        'logical_operators' => true,
        'method_argument_space' => ['on_multiline' => 'ignore'],
        'mb_str_functions' => true,
        'no_alternative_syntax' => true,
        'no_extra_blank_lines' => ['tokens' => ['break', 'continue', 'extra', 'return', 'throw', 'use', 'parenthesis_brace_block', 'square_brace_block', 'curly_brace_block']],
        'no_superfluous_elseif' => true,
        'no_superfluous_phpdoc_tags' => true,
        'no_unreachable_default_argument_value' => true,
        'no_useless_else' => true,
        'no_useless_return' => true,
        'ordered_class_elements' => true,
        'ordered_imports' => true,
        'php_unit_method_casing' => ['case' => 'camel_case'],
        'php_unit_set_up_tear_down_visibility' => true,
        'php_unit_strict' => true,
        'phpdoc_trim_consecutive_blank_line_separation' => true,
        'psr_autoloading' => true,
        'return_assignment' => true,
        'static_lambda' => true,
        'strict_comparison' => true,
        'strict_param' => true,
        'void_return' => false,
    ])
    ->setFinder($finder)
;
