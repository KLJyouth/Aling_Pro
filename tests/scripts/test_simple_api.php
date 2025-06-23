<?php
/**
 * 简化 API 测试脚本
 */

declare(strict_types=1);

echo "=== 简化 API 测试 ===\n";

$baseUrl = 'http://localhost:8080';

/**
 * 测试API端点
 */
function testSimpleEndpoint(string $url): void {
    echo "\n--- 测试端点: $url ---\n";
    
    // 创建请求
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'User-Agent: AlingAi-Test-Client/1.0'
        ]
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    if (curl_error($ch)) {
        echo "❌ 请求错误: " . curl_error($ch) . "\n";
        curl_close($ch);
        return;
    }
    
    curl_close($ch);
    
    echo "HTTP 状态码: $httpCode\n";
    
    if ($httpCode !== 200) {
        echo "❌ 请求失败，状态码: $httpCode\n";
        echo "响应前500字符: " . substr($response, 0, 500) . "\n";
        return;
    }
    
    echo "✅ 请求成功\n";
    echo "响应长度: " . strlen($response) . " 字符\n";
    
    // 解析响应
    $responseData = json_decode($response, true);
    
    if (!$responseData) {
        echo "❌ 无法解析JSON响应\n";
        echo "响应前200字符: " . substr($response, 0, 200) . "\n";
        return;
    }
    
    // 检查是否加密
    if (isset($responseData['encrypted']) && $responseData['encrypted'] === true) {
        echo "🔒 响应已加密（版本: " . ($responseData['version'] ?? 'unknown') . "）\n";
        echo "加密数据长度: " . strlen($responseData['data']) . " 字符\n";
    } else {
        echo "🔓 响应未加密\n";
        echo "响应数据:\n";
        print_r($responseData);
    }
}

// 测试端点列表
$endpoints = [
    '/api',
    '/api/test',
    '/api/status',
    '/health',
    '/test-direct',
    '/api/v1/system/info',
    '/api/v2/enhanced/dashboard'
];

foreach ($endpoints as $endpoint) {
    testSimpleEndpoint($baseUrl . $endpoint);
}

echo "\n=== 测试完成 ===\n";
