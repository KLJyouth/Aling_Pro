<?php
/**
 * 快速系统诊断脚本
 * 检查 ChatApiController 相关的所有组件状态
 */

echo "=== AlingAi Pro 系统诊断 ===\n\n";

// 1. 检查环境文件
echo "1. 检查环境配置:\n";
echo str_repeat("-", 30) . "\n";

if (file_exists('.env')) {
    echo "✅ .env 文件存在\n";
    $env = parse_ini_file('.env');
    if ($env) {
        echo "✅ .env 文件格式正确\n";
        $required_keys = ['MYSQL_HOST', 'MYSQL_USER', 'MYSQL_PASSWORD', 'MYSQL_DATABASE'];
        foreach ($required_keys as $key) {
            if (isset($env[$key]) && !empty($env[$key])) {
                echo "✅ $key 已配置\n";
            } else {
                echo "❌ $key 缺失或为空\n";
            }
        }
    } else {
        echo "❌ .env 文件格式错误\n";
    }
} else {
    echo "❌ .env 文件不存在\n";
}

echo "\n";

// 2. 检查关键文件
echo "2. 检查关键文件:\n";
echo str_repeat("-", 30) . "\n";

$critical_files = [
    'src/Controllers/Api/ChatApiController.php' => '聊天API控制器',
    'src/Controllers/Api/BaseApiController.php' => 'API基础控制器',
    'public/api/index.php' => 'API路由入口',
    'src/Services/SecurityService.php' => '安全服务',
    'src/Services/PerformanceMonitorService.php' => '性能监控服务'
];

foreach ($critical_files as $file => $description) {
    if (file_exists($file)) {
        echo "✅ $description 存在\n";
    } else {
        echo "❌ $description 缺失: $file\n";
    }
}

echo "\n";

// 3. 检查自动加载
echo "3. 检查自动加载:\n";
echo str_repeat("-", 30) . "\n";

if (file_exists('vendor/autoload.php')) {
    echo "✅ Composer 自动加载文件存在\n";
    require_once 'vendor/autoload.php';
    echo "✅ 自动加载成功\n";
} else {
    echo "❌ Composer 自动加载文件缺失\n";
    echo "   请运行: composer install\n";
}

echo "\n";

// 4. 检查类是否可以加载
echo "4. 检查类加载:\n";
echo str_repeat("-", 30) . "\n";

$classes = [
    'AlingAi\\Controllers\\Api\\ChatApiController' => '聊天API控制器',
    'AlingAi\\Controllers\\Api\\BaseApiController' => 'API基础控制器',
    'AlingAi\\Services\\SecurityService' => '安全服务',
    'AlingAi\\Services\\PerformanceMonitorService' => '性能监控服务'
];

foreach ($classes as $class => $description) {
    if (class_exists($class)) {
        echo "✅ $description 类可以加载\n";
    } else {
        echo "❌ $description 类无法加载: $class\n";
    }
}

echo "\n";

// 5. 测试 PHP 开发服务器
echo "5. 检查 PHP 服务器:\n";
echo str_repeat("-", 30) . "\n";

$php_version = PHP_VERSION;
echo "PHP 版本: $php_version\n";

if (version_compare($php_version, '8.0.0', '>=')) {
    echo "✅ PHP 版本满足要求 (>=8.0)\n";
} else {
    echo "❌ PHP 版本过低，需要 >= 8.0\n";
}

// 检查必要的扩展
$required_extensions = ['pdo', 'pdo_mysql', 'json', 'mbstring'];
foreach ($required_extensions as $ext) {
    if (extension_loaded($ext)) {
        echo "✅ $ext 扩展已加载\n";
    } else {
        echo "❌ $ext 扩展未加载\n";
    }
}

echo "\n=== 诊断完成 ===\n";
echo "如有❌标记的项目，请先解决这些问题。\n";
