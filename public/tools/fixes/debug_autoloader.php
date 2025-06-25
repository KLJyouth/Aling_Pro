<?php

/**
 * 自动加载器调试脚�?
 * 基于搜索结果中的最佳实�?
 * 参�? https://uberbrady.com/2015/01/debugging-or-troubleshooting-the-php-autoloader/
 * 参�? https://linuxsagas.digitaleagle.net/2018/05/16/troubleshooting-php-composer-class-autoloading/
 */

// 设置错误报告
error_reporting(E_ALL];
ini_set('display_errors', '1'];

echo "=== AlingAi Pro 自动加载器调�?===\n\n";

// 1. 检查autoload.php文件
echo "1. 检查autoload.php文件...\n";
if (file_exists('autoload.php')) {
    echo "�?autoload.php 存在\n";
    
    // 尝试加载autoloader
    try {
        $autoloader = require 'autoload.php';
        echo "�?autoloader 加载成功\n";
        
        // 测试autoloader功能
        if (is_object($autoloader)) {
            echo "�?autoloader 是有效对象\n";
            
            // 测试查找文件功能
            $testClasses = [
                'AlingAi\\Services\\DeepSeekAIService',
                'AlingAi\\Services\\ChatService',
                'AlingAi\\Controllers\\Api\\EnhancedChatApiController'
            ];
            
            foreach ($testClasses as $class) {
                if (method_exists($autoloader, 'findFile')) {
                    $file = $autoloader->findFile($class];
                    if ($file) {
                        echo "�?找到�?{$class} �? {$file}\n";
                    } else {
                        echo "�?未找到类 {$class}\n";
                    }
                }
            }
        }
    } catch (Exception $e) {
        echo "�?autoloader 加载失败: " . $e->getMessage() . "\n";
    }
} else {
    echo "�?autoload.php 不存在\n";
}
echo "\n";

// 2. 检查核心文件是否存�?
echo "2. 检查核心文�?..\n";
$coreFiles = [
    'src/Services/DeepSeekAIService.php',
    'src/Services/ChatService.php',
    'src/Controllers/Api/EnhancedChatApiController.php',
    'src/Core/Container/ServiceContainer.php',
    'src/Core/Logger/LoggerFactory.php',
    'src/Config/Routes.php'
];

foreach ($coreFiles as $file) {
    if (file_exists($file)) {
        echo "�?{$file} 存在\n";
        
        // 检查语�?
        $output = shell_exec("php -l {$file} 2>&1"];
        if (strpos($output, 'No syntax errors') !== false) {
            echo "  �?语法正确\n";
        } else {
            echo "  �?语法错误: " . $output . "\n";
        }
    } else {
        echo "�?{$file} 不存在\n";
    }
}
echo "\n";

// 3. 测试类加�?
echo "3. 测试类加�?..\n";
try {
    // 手动测试类加�?
    $testClasses = [
        'AlingAi\\Services\\DeepSeekAIService',
        'AlingAi\\Services\\ChatService',
        'AlingAi\\Controllers\\Api\\EnhancedChatApiController'
    ];
    
    foreach ($testClasses as $class) {
        if (class_exists($class)) {
            echo "�?�?{$class} 已加载\n";
        } else {
            echo "�?�?{$class} 未加载\n";
            
            // 尝试手动包含文件
            $filePath = str_replace('\\', '/', $class) . '.php';
            $fullPath = 'src/' . $filePath;
            
            if (file_exists($fullPath)) {
                echo "  尝试手动包含: {$fullPath}\n";
                require_once $fullPath;
                
                if (class_exists($class)) {
                    echo "  �?手动包含成功\n";
                } else {
                    echo "  �?手动包含失败\n";
                }
            }
        }
    }
} catch (Exception $e) {
    echo "�?类加载测试失�? " . $e->getMessage() . "\n";
}
echo "\n";

// 4. 检查命名空间映�?
echo "4. 检查命名空间映�?..\n";
$namespaceMap = [
    'AlingAi\\Services\\' => 'src/Services/',
    'AlingAi\\Controllers\\Api\\' => 'src/Controllers/Api/',
    'AlingAi\\Core\\' => 'src/Core/',
    'AlingAi\\Config\\' => 'src/Config/'
];

foreach ($namespaceMap as $namespace => $path) {
    if (is_dir($path)) {
        echo "�?命名空间 {$namespace} 映射�?{$path} 存在\n";
    } else {
        echo "�?命名空间 {$namespace} 映射�?{$path} 不存在\n";
    }
}
echo "\n";

// 5. 检查依�?
echo "5. 检查依�?..\n";
$requiredExtensions = ['pdo', 'pdo_mysql', 'json', 'curl', 'mbstring'];
foreach ($requiredExtensions as $ext) {
    if (extension_loaded($ext)) {
        echo "�?{$ext} 扩展已加载\n";
    } else {
        echo "�?{$ext} 扩展未加载\n";
    }
}

// 检查Composer依赖
if (file_exists('vendor/autoload.php')) {
    echo "�?Composer autoloader 存在\n";
} else {
    echo "�?Composer autoloader 不存在\n";
}
echo "\n";

// 6. 修复建议
echo "6. 修复建议...\n";

// 检查autoload.php内容
if (file_exists('autoload.php')) {
    $content = file_get_contents('autoload.php'];
    if (strpos($content, 'spl_autoload_register') !== false) {
        echo "�?autoload.php 包含自动加载注册\n";
    } else {
        echo "�?autoload.php 缺少自动加载注册\n";
    }
}

// 检查是否有类映�?
if (strpos($content, 'classMap') !== false) {
    echo "�?检测到类映射配置\n";
} else {
    echo "�?未检测到类映射配置\n";
}

echo "\n";

// 7. 生成修复脚本
echo "7. 生成修复脚本...\n";
$fixScript = '<?php
/**
 * 自动加载器修复脚�?
 */

// 设置错误报告
error_reporting(E_ALL];
ini_set("display_errors", "1"];

echo "=== 修复自动加载�?===\n\n";

// 1. 重新生成autoload.php
echo "1. 重新生成autoload.php...\n";
if (file_exists("generate_autoload.php")) {
    include "generate_autoload.php";
    echo "�?autoload.php 重新生成完成\n";
} else {
    echo "�?generate_autoload.php 不存在\n";
}

// 2. 检查并修复文件权限
echo "2. 检查文件权�?..\n";
$dirs = ["src", "storage", "logs"];
foreach ($dirs as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true];
        echo "�?创建目录: {$dir}\n";
    }
}

// 3. 测试修复后的自动加载�?
echo "3. 测试修复后的自动加载�?..\n";
try {
    require_once "autoload.php";
    echo "�?自动加载器加载成功\n";
    
    // 测试类加�?
    $testClass = "AlingAi\\Services\\DeepSeekAIService";
    if (class_exists($testClass)) {
        echo "�?测试类加载成功\n";
    } else {
        echo "�?测试类加载失败\n";
    }
} catch (Exception $e) {
    echo "�?自动加载器测试失�? " . $e->getMessage() . "\n";
}

echo "\n=== 修复完成 ===\n";
';

file_put_contents('fix_autoloader.php', $fixScript];
echo "�?生成修复脚本: fix_autoloader.php\n";

echo "\n=== 调试完成 ===\n";
echo "如果发现问题，请运行: php fix_autoloader.php\n"; 
