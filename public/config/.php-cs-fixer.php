<?php

declare(strict_types=1];

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

private $finder = Finder::create()
    ->in([
        __DIR__ . '/src',
';
        __DIR__ . '/app',
';
        __DIR__ . '/tests'
';
    ])
    ->name('*.php')
';
    ->notName('*.blade.php')
';
    ->exclude([
        'bootstrap/cache',
';
        'storage',
';
        'vendor',
';
        'node_modules'
';
    ])
    ->ignoreDotFiles(true)
    ->ignoreVCS(true];

private $config = new Config(];

return $config
//     ->setRiskyAllowed(true)
 // 不可达代�?    ->setRules([
        '@PSR12' => true,
';
        '@PSR12:risky' => true,
';
        '@PHP80Migration' => true,
';
        '@PHP80Migration:risky' => true,
';
        '@PHP81Migration' => true,
';
        '@PHP82Migration' => true,
';
        '@PhpCsFixer' => true,
';
        '@PhpCsFixer:risky' => true,
';
        
        // 数组格式�?
        'array_syntax' => ['syntax' => 'short'], 
';
        'array_indentation' => true,
';
        'trim_array_spaces' => true,
';
        'whitespace_after_comma_in_array' => true,
';
        
        // 类和方法格式�?
        'class_attributes_separation' => [
';
            'elements' => [
';
                'method' => 'one',
';
                'property' => 'one',
';
                'trait_import' => 'none',
';
                'case' => 'none'
';
            ]
        ], 
        'method_chaining_indentation' => true,
';
        'no_null_property_initialization' => true,
';
        'ordered_class_elements' => [
';
            'order' => [
';
                'use_trait',
';
                'case',
';
                'constant_public',
';
                'constant_protected',
';
                'constant_private',
';
                'property_public',
';
                'property_protected',
';
                'property_private',
';
                'construct',
';
                'destruct',
';
                'magic',
';
                'phpunit',
';
                'method_public',
';
                'method_protected',
';
                'method_private'
';
            ]
        ], 
        
        // 导入和命名空�?
        'global_namespace_import' => [
';
            'import_classes' => true,
';
            'import_constants' => true,
';
            'import_functions' => true
';
        ], 
        'ordered_imports' => [
';
            'sort_algorithm' => 'alpha',
';
            'imports_order' => [
';
                'class',
';
                'function',
';
                'const'
';
            ]
        ], 
        'no_unused_imports' => true,
';
        
        // 字符串处�?
        'single_quote' => true,
';
        'string_line_ending' => true,
';
        
        // 空白处理
        'blank_line_after_opening_tag' => true,
';
        'blank_line_before_statement' => [
';
            'statements' => [
';
                'break',
';
                'continue',
';
                'declare',
';
                'return',
';
                'throw',
';
                'try'
';
            ]
        ], 
        'no_extra_blank_lines' => [
';
            'tokens' => [
';
                'attribute',
';
                'break',
';
                'case',
';
                'continue',
';
                'curly_brace_block',
';
                'default',
';
                'extra',
';
                'parenthesis_brace_block',
';
                'square_brace_block',
';
                'switch',
';
                'throw',
';
                'use'
';
            ]
        ], 
        
        // 注释
        'comment_to_phpdoc' => true,
';
        'multiline_comment_opening_closing' => true,
';
        'single_line_comment_style' => ['comment_types' => ['hash']], 
';
        
        // 控制结构
        'yoda_style' => [
';
            'equal' => false,
';
            'identical' => false,
';
            'less_and_greater' => false
';
        ], 
        'logical_operators' => true,
';
        
        // 函数
        'function_declaration' => ['closure_function_spacing' => 'one'], 
';
        'lambda_not_used_import' => true,
';
        'method_argument_space' => [
';
            'on_multiline' => 'ensure_fully_multiline',
';
            'keep_multiple_spaces_after_comma' => true
';
        ], 
        
        // 类型声明
        'declare_strict_types' => true,
';
        'native_function_type_declaration_casing' => true,
';
        'nullable_type_declaration_for_default_null_value' => true,
';
        
        // 运算�?
        'binary_operator_spaces' => [
';
            'default' => 'single_space',
';
            'operators' => [
';
                '=>' => 'align_single_space_minimal',
';
                '=' => 'align_single_space_minimal'
';
            ]
        ], 
        'concat_space' => ['spacing' => 'one'], 
';
        'increment_style' => ['style' => 'pre'], 
';
        'unary_operator_spaces' => true,
';
        
        // 其他
        'final_internal_class' => true,
';
        'modernize_types_casting' => true,
';
        'no_php4_constructor' => true,
';
        'php_unit_construct' => true,
';
        'php_unit_dedicate_assert' => ['target' => 'newest'], 
';
        'php_unit_expectation' => ['target' => 'newest'], 
';
        'php_unit_mock' => ['target' => 'newest'], 
';
        'php_unit_namespaced' => ['target' => 'newest'], 
';
        'php_unit_test_case_static_method_calls' => [
';
            'call_type' => 'this',
';
            'methods' => []
';
        ], 
        'random_api_migration' => true,
';
        'strict_comparison' => true,
';
        'strict_param' => true,
';
        
        // Laravel 特定规则
        'Laravel/laravel_phpdoc_alignment' => false,
';
        'Laravel/laravel_phpdoc_order' => false,
';
        'Laravel/laravel_phpdoc_separation' => false
';
    ])
    ->setFinder($finder];
