<?php
/**
 * 文件上传API专项测试
 */

echo "=== 文件上传API专项测试 ===\n";

$baseUrl = 'http://localhost:3000/api/upload';
$testMethods = ['GET', 'POST'];

foreach ($testMethods as $method) {
    echo "\n测试方法: {$method}\n";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $baseUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_VERBOSE, true);
    
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, '{}');
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    $info = curl_getinfo($ch);
    
    echo "HTTP状态码: {$httpCode}\n";
    echo "响应内容: " . substr($response, 0, 200) . "...\n";
    
    if ($error) {
        echo "错误: {$error}\n";
    }
    
    if ($httpCode === 200) {
        echo "✓ 成功\n";
    } else {
        echo "✗ 失败\n";
        echo "完整响应: {$response}\n";
    }
    
    curl_close($ch);
}

echo "\n=== 测试完成 ===\n";
