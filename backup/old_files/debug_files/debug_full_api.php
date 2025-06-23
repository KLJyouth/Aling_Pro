<?php
/**
 * 完整的API路由调试
 */

// 模拟Web环境
$_SERVER['REQUEST_METHOD'] = 'POST';
$_SERVER['REQUEST_URI'] = '/api/auth/login';
$_SERVER['CONTENT_TYPE'] = 'application/json';

// 捕获输出
ob_start();

echo "=== API路由完整调试 ===\n\n";

echo "1. 环境检查:\n";
echo "   REQUEST_METHOD: " . $_SERVER['REQUEST_METHOD'] . "\n";
echo "   REQUEST_URI: " . $_SERVER['REQUEST_URI'] . "\n";
echo "   CONTENT_TYPE: " . ($_SERVER['CONTENT_TYPE'] ?? 'undefined') . "\n\n";

// 尝试加载API路由器
try {
    echo "2. 加载API路由器...\n";
    
    // 直接包含文件，但不让它执行
    $apiContent = file_get_contents(__DIR__ . '/public/api/index.php');
    
    // 检查是否包含登录路由注册
    if (strpos($apiContent, "'/api/auth/login'") !== false) {
        echo "   ✅ 找到登录路由注册代码\n";
    } else {
        echo "   ❌ 未找到登录路由注册代码\n";
    }
    
    if (strpos($apiContent, "'auth', 'login'") !== false) {
        echo "   ✅ 找到auth控制器login方法调用\n";
    } else {
        echo "   ❌ 未找到auth控制器login方法调用\n";
    }
    
    echo "\n3. 尝试直接访问API端点...\n";
    
    // 清空输出缓冲区
    ob_clean();
    
    // 包含并执行API路由器
    include __DIR__ . '/public/api/index.php';
    
} catch (Exception $e) {
    echo "   ❌ 错误: " . $e->getMessage() . "\n";
} catch (Error $e) {
    echo "   ❌ 致命错误: " . $e->getMessage() . "\n";
}
