<?php
/**
 * 调试路由问题
 */

echo "调试路由系统\n";
echo "============\n\n";

// 模拟请求参数
$_SERVER['REQUEST_METHOD'] = 'POST';
$_SERVER['REQUEST_URI'] = '/api/auth/login';

echo "请求方法: " . $_SERVER['REQUEST_METHOD'] . "\n";
echo "请求URI: " . $_SERVER['REQUEST_URI'] . "\n";

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
echo "解析路径: " . $path . "\n\n";

// 直接调用index.php并查看所有路由
require_once __DIR__ . '/vendor/autoload.php';

// 包含API路由文件但不执行
$content = file_get_contents(__DIR__ . '/public/api/index.php');

echo "检查 public/api/index.php 文件是否存在...\n";
if (file_exists(__DIR__ . '/public/api/index.php')) {
    echo "✅ 文件存在\n";
} else {
    echo "❌ 文件不存在\n";
}

echo "\n检查是否通过路由器访问...\n";

// 直接测试路由
include_once __DIR__ . '/public/api/index.php';
