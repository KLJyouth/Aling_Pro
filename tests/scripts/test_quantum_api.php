<?php
/**
 * 带量子加密支持的API测试脚本
 */

echo "=== AlingAi Pro 量子加密API测试 ===\n\n";

$baseUrl = 'http://localhost:8000';
$endpoints = [
    '/api/health',
    '/api/version',
    '/api/status', 
    '/api/test',
    '/health',
    '/version'
];

function testEndpoint($url, $endpoint) {
    $fullUrl = $url . $endpoint;
    $startTime = microtime(true);
    
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $fullUrl,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_HTTPHEADER => [
            'User-Agent: AlingAiPro-Test/6.0',
            'Accept: application/json',
            'X-API-Version: v6.0',
            'X-API-Security: plain',  // 明文请求
            'X-API-Timestamp: ' . time(),
            'X-API-Nonce: ' . bin2hex(random_bytes(16))
        ]
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $responseTime = round((microtime(true) - $startTime) * 1000, 2);
    curl_close($ch);
    
    $status = ($httpCode >= 200 && $httpCode < 400) ? 'SUCCESS' : 'FAILURE';
    $statusColor = ($status === 'SUCCESS') ? "\033[32m" : "\033[31m";
    $resetColor = "\033[0m";
    
    echo sprintf(
        "%s%-7s%s GET %s (%dms) [HTTP %d]\n",
        $statusColor,
        $status,
        $resetColor,
        $endpoint,
        $responseTime,
        $httpCode
    );
    
    // 如果成功且有响应内容，显示响应
    if ($status === 'SUCCESS' && !empty($response)) {
        $decoded = json_decode($response, true);
        if ($decoded) {
            // 检查是否是加密响应
            if (isset($decoded['encrypted']) && $decoded['encrypted'] === true) {
                echo "  ✓ 接收到加密响应\n";
                echo "  加密算法: " . ($decoded['version'] ?? 'unknown') . "\n";
                echo "  数据长度: " . strlen($decoded['data'] ?? '') . " bytes\n";
            } else {
                echo "  ✓ 接收到明文响应: " . json_encode($decoded, JSON_UNESCAPED_UNICODE) . "\n";
            }
        } else {
            echo "  响应长度: " . strlen($response) . " bytes\n";
        }
    } elseif ($httpCode === 429) {
        echo "  ⚠️  请求被限流\n";
    } elseif ($httpCode === 404) {
        echo "  ❌ 路由未找到\n";
    } elseif ($httpCode === 500) {
        echo "  ❌ 服务器内部错误\n";
    }
    
    echo "\n";
    return $status === 'SUCCESS';
}

$successCount = 0;
$totalCount = count($endpoints);

foreach ($endpoints as $endpoint) {
    if (testEndpoint($baseUrl, $endpoint)) {
        $successCount++;
    }
}

echo "=== 测试结果汇总 ===\n";
echo "成功: $successCount/$totalCount\n";
echo "成功率: " . round(($successCount / $totalCount) * 100, 1) . "%\n";

if ($successCount > 0) {
    echo "✅ 部分或全部API端点可用\n";
} else {
    echo "❌ 所有API端点均不可用\n";
}
