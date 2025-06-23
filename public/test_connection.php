<?php
// 测试连接

// 设置响应头
header('Content-Type: text/plain');

// 输出基本信息
echo "=== 连接测试 ===\n";
echo "时间: " . date('Y-m-d H:i:s') . "\n";
echo "PHP版本: " . phpversion() . "\n";
echo "服务器名称: " . ($_SERVER['SERVER_NAME'] ?? 'Unknown') . "\n";
echo "服务器地址: " . ($_SERVER['SERVER_ADDR'] ?? 'Unknown') . "\n";
echo "服务器端口: " . ($_SERVER['SERVER_PORT'] ?? 'Unknown') . "\n";
echo "请求方法: " . ($_SERVER['REQUEST_METHOD'] ?? 'Unknown') . "\n";
echo "请求URI: " . ($_SERVER['REQUEST_URI'] ?? 'Unknown') . "\n";
echo "客户端IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'Unknown') . "\n";
echo "=== 测试完成 ===\n";
?> 