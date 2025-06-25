<?php

/**
 * 简单聊天系统测�?
 */

// 设置错误报告
error_reporting(E_ALL];
ini_set('display_errors', '1'];

echo "=== 简单聊天系统测�?===\n\n";

// 1. 检查文件是否存�?
echo "1. 检查核心文�?..\n";
$files = [
    'src/Services/DeepSeekAIService.php',
    'src/Services/ChatService.php', 
    'src/Controllers/Api/EnhancedChatApiController.php',
    'src/Config/Routes.php',
    'database/migrations/2025_01_20_create_chat_tables.sql'
];

foreach ($files as $file) {
    if (file_exists($file)) {
        echo "�?{$file} 存在\n";
    } else {
        echo "�?{$file} 不存在\n";
    }
}
echo "\n";

// 2. 检查PHP扩展
echo "2. 检查PHP扩展...\n";
$extensions = ['pdo', 'pdo_mysql', 'json', 'curl', 'mbstring'];
foreach ($extensions as $ext) {
    if (extension_loaded($ext)) {
        echo "�?{$ext} 已加载\n";
    } else {
        echo "�?{$ext} 未加载\n";
    }
}
echo "\n";

// 3. 检查环境变�?
echo "3. 检查环境变�?..\n";
$envVars = ['DEEPSEEK_API_KEY'];
foreach ($envVars as $var) {
    if (getenv($var)) {
        echo "�?{$var} 已设置\n";
    } else {
        echo "�?{$var} 未设置\n";
    }
}
echo "\n";

// 4. 检查数据库连接
echo "4. 检查数据库连接...\n";
try {
    $pdo = new PDO('mysql:host=localhost;dbname=alingai_pro', 'root', ''];
    echo "�?数据库连接成功\n";
    
    // 检查聊天表是否存在
    $tables = ['conversations', 'messages', 'usage_stats'];
    foreach ($tables as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '{$table}'"];
        if ($stmt->rowCount() > 0) {
            echo "�?�?{$table} 存在\n";
        } else {
            echo "�?�?{$table} 不存在\n";
        }
    }
} catch (PDOException $e) {
    echo "�?数据库连接失�? " . $e->getMessage() . "\n";
}
echo "\n";

// 5. 语法检�?
echo "5. 语法检�?..\n";
$phpFiles = [
    'src/Services/DeepSeekAIService.php',
    'src/Services/ChatService.php',
    'src/Controllers/Api/EnhancedChatApiController.php'
];

foreach ($phpFiles as $file) {
    if (file_exists($file)) {
        $output = shell_exec("php -l {$file} 2>&1"];
        if (strpos($output, 'No syntax errors') !== false) {
            echo "�?{$file} 语法正确\n";
        } else {
            echo "�?{$file} 语法错误: " . $output . "\n";
        }
    }
}
echo "\n";

// 6. 系统信息
echo "6. 系统信息...\n";
echo "   PHP版本: " . PHP_VERSION . "\n";
echo "   系统架构: " . php_uname('m') . "\n";
echo "   操作系统: " . php_uname('s') . " " . php_uname('r') . "\n";
echo "   内存限制: " . ini_get('memory_limit') . "\n";
echo "   最大执行时�? " . ini_get('max_execution_time') . "秒\n";
echo "   时区: " . date_default_timezone_get() . "\n\n";

echo "=== 测试完成 ===\n";
echo "如果所有检查都通过，聊天系统应该可以正常工作。\n";
echo "如有问题，请根据错误信息进行修复。\n"; 
