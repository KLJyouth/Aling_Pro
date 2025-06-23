<?php

/**
 * 测试新的干净API服务器端点
 */

$baseUrl = 'http://localhost:8080';

echo "=== 测试干净API服务器端点 ===\n\n";

// 测试函数
function testApiEndpoint($url, $method = 'GET', $data = null) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    
    if ($data && $method === 'POST') {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "请求: $method $url\n";
    echo "状态码: $httpCode\n";
    echo "响应: " . substr($response, 0, 300) . (strlen($response) > 300 ? '...' : '') . "\n\n";
    
    return json_decode($response, true);
}

// 1. 测试API根目录
testApiEndpoint($baseUrl . '/api');

// 2. 测试获取系统统计信息
testApiEndpoint($baseUrl . '/api/admin/stats');

// 3. 测试获取所有申请
testApiEndpoint($baseUrl . '/api/admin/applications');

// 4. 测试获取用户列表
testApiEndpoint($baseUrl . '/api/admin/users');

// 5. 测试不存在的端点
testApiEndpoint($baseUrl . '/api/admin/nonexistent');

echo "=== API测试完成 ===\n";
