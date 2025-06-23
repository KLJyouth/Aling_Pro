<?php
// 测试数据库连接

// 设置响应头
header('Content-Type: text/html; charset=utf-8');

// 输出基本信息
echo "<h1>数据库连接测试</h1>";
echo "<p>时间: " . date('Y-m-d H:i:s') . "</p>";

// 数据库配置
$dbConfig = [
    'host' => 'localhost',
    'dbname' => 'alingai_pro',
    'user' => 'root',
    'password' => '',
    'charset' => 'utf8mb4'
];

// 尝试连接数据库
try {
    echo "<h2>尝试连接MySQL数据库</h2>";
    
    // 创建PDO连接
    $dsn = "mysql:host={$dbConfig['host']};charset={$dbConfig['charset']}";
    $pdo = new PDO($dsn, $dbConfig['user'], $dbConfig['password']);
    
    // 设置PDO错误模式
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<p style='color:green'>成功连接到MySQL服务器!</p>";
    
    // 尝试选择数据库
    try {
        $pdo->query("USE {$dbConfig['dbname']}");
        echo "<p style='color:green'>成功选择数据库: {$dbConfig['dbname']}</p>";
    } catch (PDOException $e) {
        echo "<p style='color:orange'>无法选择数据库: {$dbConfig['dbname']}</p>";
        echo "<p>错误信息: " . $e->getMessage() . "</p>";
    }
    
    // 获取MySQL版本
    $stmt = $pdo->query('SELECT VERSION() as version');
    $version = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<p>MySQL版本: " . ($version['version'] ?? 'Unknown') . "</p>";
    
} catch (PDOException $e) {
    echo "<p style='color:red'>数据库连接失败!</p>";
    echo "<p>错误信息: " . $e->getMessage() . "</p>";
}

echo "<h2>PHP信息</h2>";
echo "<p>PHP版本: " . phpversion() . "</p>";
echo "<p>PDO扩展: " . (extension_loaded('pdo') ? '已加载' : '未加载') . "</p>";
echo "<p>PDO MySQL驱动: " . (extension_loaded('pdo_mysql') ? '已加载' : '未加载') . "</p>";

echo "<p><a href='index.php'>返回首页</a></p>";
?> 