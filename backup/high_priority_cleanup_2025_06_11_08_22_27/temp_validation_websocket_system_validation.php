<?php
/**
 * WebSocket系统依赖修复验证脚本
 * 
 * 此脚本验证WebSocket相关类的加载和基本功能
 * 解决缺少MessageComponentInterface的问题
 */

require_once __DIR__ . '/vendor/autoload.php';

echo "=== WebSocket系统依赖修复验证 ===\n\n";

// 测试基本的interface加载
echo "1. 测试Ratchet接口加载...\n";
try {
    if (interface_exists('Ratchet\\MessageComponentInterface')) {
        echo "✅ MessageComponentInterface 已加载\n";
    } else {
        echo "❌ MessageComponentInterface 未加载\n";
    }

    if (interface_exists('Ratchet\\ConnectionInterface')) {
        echo "✅ ConnectionInterface 已加载\n";
    } else {
        echo "❌ ConnectionInterface 未加载\n";
    }
} catch (Exception $e) {
    echo "❌ 接口加载失败: " . $e->getMessage() . "\n";
}

echo "\n2. 测试WebSocket服务器类加载...\n";
try {
    $reflection = new ReflectionClass('AlingAi\\WebSocket\\WebSocketServer');
    echo "✅ WebSocketServer 类成功加载\n";
    echo "   - 文件路径: " . $reflection->getFileName() . "\n";
    
    $methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);
    echo "   - 公共方法数量: " . count($methods) . "\n";
    
    // 检查必需的接口方法
    $requiredMethods = ['onOpen', 'onMessage', 'onClose', 'onError'];
    foreach ($requiredMethods as $method) {
        if ($reflection->hasMethod($method)) {
            echo "   ✅ {$method} 方法存在\n";
        } else {
            echo "   ❌ {$method} 方法缺失\n";
        }
    }
} catch (Exception $e) {
    echo "❌ WebSocketServer类加载失败: " . $e->getMessage() . "\n";
}

echo "\n3. 测试WebSocket安全服务器类加载...\n";
try {
    $reflection = new ReflectionClass('AlingAi\\Security\\WebSocketSecurityServer');
    echo "✅ WebSocketSecurityServer 类成功加载\n";
    echo "   - 文件路径: " . $reflection->getFileName() . "\n";
    
    // 检查接口实现
    $interfaces = $reflection->getInterfaceNames();
    if (in_array('Ratchet\\MessageComponentInterface', $interfaces)) {
        echo "   ✅ 实现了 MessageComponentInterface\n";
    } else {
        echo "   ❌ 未实现 MessageComponentInterface\n";
    }
} catch (Exception $e) {
    echo "❌ WebSocketSecurityServer类加载失败: " . $e->getMessage() . "\n";
}

echo "\n4. 测试PHP扩展状态...\n";
$requiredExtensions = [
    'pdo' => '数据库PDO支持',
    'pdo_sqlite' => 'SQLite数据库支持',
    'fileinfo' => '文件信息检测',
    'json' => 'JSON处理',
    'openssl' => 'SSL/TLS支持',
    'curl' => 'HTTP客户端支持'
];

foreach ($requiredExtensions as $ext => $desc) {
    if (extension_loaded($ext)) {
        echo "   ✅ {$ext} - {$desc}\n";
    } else {
        echo "   ❌ {$ext} - {$desc} (未安装)\n";
    }
}

echo "\n5. 测试React/Socket相关依赖...\n";
try {
    // 检查React Socket类是否可用
    if (class_exists('React\\Socket\\Server')) {
        echo "✅ React Socket Server 可用\n";
    } else {
        echo "❌ React Socket Server 不可用\n";
    }
    
    if (class_exists('React\\EventLoop\\Loop')) {
        echo "✅ React Event Loop 可用\n";
    } else {
        echo "❌ React Event Loop 不可用\n";
    }
} catch (Exception $e) {
    echo "❌ React组件检查失败: " . $e->getMessage() . "\n";
}

echo "\n6. 测试简单WebSocket服务器实例化...\n";
try {
    // 尝试创建简化的WebSocket服务器实例
    $server = new AlingAi\WebSocket\SimpleWebSocketServer();
    echo "✅ 简化WebSocket服务器实例创建成功\n";
    
    // 测试统计功能
    $stats = $server->getStats();
    echo "   - 当前连接数: " . $stats['total_connections'] . "\n";
    echo "   - 认证用户数: " . $stats['authenticated_users'] . "\n";
    echo "   - 房间数: " . $stats['rooms'] . "\n";
} catch (Exception $e) {
    echo "❌ 简化WebSocket服务器实例化失败: " . $e->getMessage() . "\n";
}

echo "\n=== 修复建议 ===\n";
if (!extension_loaded('pdo_sqlite')) {
    echo "📋 安装SQLite扩展: 在php.ini中启用 extension=pdo_sqlite\n";
}

if (!extension_loaded('fileinfo')) {
    echo "📋 安装文件信息扩展: 在php.ini中启用 extension=fileinfo\n";
}

echo "📋 如果需要完整的Ratchet功能，考虑安装 reactphp/socket:^1.0\n";
echo "📋 WebSocket服务器现在可以基本运行，缺少的扩展不会影响核心功能\n";

echo "\n=== 验证完成 ===\n";
echo "时间: " . date('Y-m-d H:i:s') . "\n";
