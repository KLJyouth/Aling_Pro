<?php
/**
 * API 端点测试脚本
 * 测试所有主要 API 端点，包括加密和解密
 */

declare(strict_types=1);

require_once 'vendor/autoload.php';

use AlingAI\Security\QuantumEncryption\FinalCompleteQuantumEncryptionSystem;

echo "=== AlingAi Pro API 端点测试 ===\n";

$baseUrl = 'http://localhost:8080';

// 创建量子加密服务实例用于解密
try {
    $encryption = new FinalCompleteQuantumEncryptionSystem();
    echo "✅ 量子加密服务初始化成功\n";
} catch (Exception $e) {
    echo "❌ 量子加密服务初始化失败: " . $e->getMessage() . "\n";
    exit(1);
}

/**
 * 测试API端点
 */
function testEndpoint(string $url, FinalCompleteQuantumEncryptionSystem $encryption): void {
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
        echo "响应: $response\n";
        return;
    }
    
    // 解析响应
    $responseData = json_decode($response, true);
    
    if (!$responseData) {
        echo "❌ 无法解析JSON响应\n";
        echo "响应: $response\n";
        return;
    }
    
    // 检查是否加密
    if (isset($responseData['encrypted']) && $responseData['encrypted'] === true) {
        echo "🔒 响应已加密，尝试解密...\n";
          try {
            // 构造加密数据格式
            $encryptedData = [
                'data' => $responseData['data'],
                'iv' => $responseData['iv'],
                'tag' => $responseData['tag']
            ];
            
            // 使用默认加密ID
            $encryptionId = $responseData['version'] ?? 'v6';
            
            $decrypted = $encryption->quantumDecrypt($encryptedData, $encryptionId);
            echo "✅ 解密成功\n";
            echo "解密后内容: " . $decrypted . "\n";
            
            // 尝试解析解密后的JSON
            $decryptedData = json_decode($decrypted, true);
            if ($decryptedData) {
                echo "解析后的数据:\n";
                print_r($decryptedData);
            }
        } catch (Exception $e) {
            echo "❌ 解密失败: " . $e->getMessage() . "\n";
        }
    } else {
        echo "🔓 响应未加密\n";
        echo "响应内容:\n";
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
    testEndpoint($baseUrl . $endpoint, $encryption);
}

echo "\n=== 测试完成 ===\n";
