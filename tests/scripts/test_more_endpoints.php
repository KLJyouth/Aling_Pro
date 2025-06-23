<?php
/**
 * 测试更多 API 端点
 */

declare(strict_types=1);

echo "=== 测试更多 API 端点 ===\n";

$baseUrl = 'http://localhost:8080';

/**
 * 测试API端点
 */
function quickTest(string $url): void {
    echo "测试: $url ";
    
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 5,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json']
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200) {
        echo "✅ 200 OK\n";
    } else {
        echo "❌ $httpCode\n";
    }
}

// 测试更多端点
$moreEndpoints = [
    // 基本端点
    '/api/health',
    '/api/version',
    '/debug/routes',
    
    // V1 端点
    '/api/v1/users',
    '/api/v1/users/1',
    '/api/v1/security/overview',
    
    // V2 端点
    '/api/v2/ai/agents',
    '/api/v2/ai/analyze',
    
    // 管理端点
    '/admin/advanced',
    '/admin/system-monitor',
    '/admin/route-manager',
    
    // Web 端点
    '/dashboard',
    '/profile/1',
    '/settings',
    
    // 特殊端点
    '/ws/test',
    
    // 可能不存在的端点
    '/api/nonexistent',
    '/invalid'
];

foreach ($moreEndpoints as $endpoint) {
    quickTest($baseUrl . $endpoint);
}

echo "\n=== 快速测试完成 ===\n";
