<?php
/*
 * This document has been generated with
 * https://mlocati.github.io/php-cs-fixer-configurator/#version:3.16|configurator
 * you can change this configuration by importing this file.
 */

$finder = PhpCsFixer\Finder::create()
    ->exclude('vendor')
    ->exclude('tests')
    ->exclude('runtime')
    ->in(__DIR__);

$config = new PhpCsFixer\Config();

return $config->setRiskyAllowed(true)
    ->setIndent('    ')
    ->setRules([
        '@PSR2'                                       => true,
        '@PhpCsFixer'                                 => true,
        '@Symfony'                                    => true,
        'concat_space'                                => ['spacing' => 'one'],
        'array_syntax'                                => ['syntax' => 'short'],
        'array_indentation'                           => true,
        'combine_consecutive_unsets'                  => true,
        'phpdoc_separation'                           => true,
        'single_quote'                                => true,
        'declare_equal_normalize'                     => true,
        'function_typehint_space'                     => true,
        'single_line_comment_style'                   => true,
        'include'                                     => true,
        'lowercase_cast'                              => true,
        'multiline_whitespace_before_semicolons'      => false,
        'no_leading_import_slash'                     => true,
        'no_multiline_whitespace_around_double_arrow' => true,
        'no_spaces_around_offset'                     => true,
        'no_unneeded_control_parentheses'             => true,
        'no_unused_imports'                           => true,
        'no_whitespace_before_comma_in_array'         => true,
        'no_whitespace_in_blank_line'                 => true,
        'object_operator_without_whitespace'          => true,
        'single_blank_line_before_namespace'          => true,
        'single_class_element_per_statement'          => true,
        'space_after_semicolon'                       => true,
        'standardize_not_equals'                      => true,
        'ternary_operator_spaces'                     => true,
        'trailing_comma_in_multiline'                 => true,
        'trim_array_spaces'                           => true,
        'unary_operator_spaces'                       => true,
        'whitespace_after_comma_in_array'             => true,
        'no_extra_blank_lines'                        => [
            'tokens' => [
                'curly_brace_block',
                'extra',
                'parenthesis_brace_block',
                'square_brace_block',
                'throw',
                'use',
            ],
        ],
        'binary_operator_spaces' => [
            'operators' => [
                '=>'  => 'align_single_space_minimal',
                '|'   => 'align_single_space_minimal',
                '===' => 'align_single_space_minimal',
                '+='  => 'align_single_space',
                '='   => 'align_single_space',
                'xor' => 'align_single_space',
            ],
        ],
    ])
    ->setFinder($finder);
