<?php
/**
 * 简化API测试脚本
 * 快速验证基本API端点
 */

echo "=== AlingAi Pro API基本测试 ===\n\n";

$baseUrl = 'http://localhost:8000';
$basicEndpoints = [
    '/api/health',
    '/api/version', 
    '/api/status',
    '/api/test'
];

foreach ($basicEndpoints as $endpoint) {
    $url = $baseUrl . $endpoint;
    $startTime = microtime(true);
    
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 5,
        CURLOPT_HTTPHEADER => [
            'User-Agent: AlingAiPro-Test/1.0',
            'Accept: application/json'
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
    
    // 显示响应内容（如果成功）
    if ($status === 'SUCCESS' && $response) {
        $decoded = json_decode($response, true);
        if ($decoded) {
            echo "  响应: " . json_encode($decoded, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n";
        }
    }
    echo "\n";
}

echo "测试完成!\n";
