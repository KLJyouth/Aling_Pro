<?php
/**
 * API 端点测试脚本
 * 测试所有可用的API端点并显示结果
 */

$baseUrl = 'http://localhost:8000';

$endpoints = [
    // 认证端点
    'GET /api/auth/test' => '/api/auth/test',
    
    // 用户端点
    'GET /api/user/test' => '/api/user/test',
    
    // 管理员端点
    'GET /api/admin/test' => '/api/admin/test',
    
    // 系统端点
    'GET /api/system/test' => '/api/system/test',
    'GET /api/system/health' => '/api/system/health',
    'GET /api/system/status' => '/api/system/status',
    
    // 聊天端点
    'GET /api/chat/test' => '/api/chat/test',
    
    // 文件端点
    'GET /api/files/test' => '/api/files/test',
    
    // 监控端点
    'GET /api/monitor/test' => '/api/monitor/test',
    'GET /api/monitor/metrics' => '/api/monitor/metrics',
    'GET /api/monitor/analytics' => '/api/monitor/analytics',
    'GET /api/monitor/errors' => '/api/monitor/errors',
];

echo "=== AlingAi Pro API 端点测试 ===\n";
echo "测试时间: " . date('Y-m-d H:i:s') . "\n";
echo "基础URL: $baseUrl\n\n";

$passed = 0;
$failed = 0;

foreach ($endpoints as $description => $endpoint) {
    echo "测试: $description\n";
    
    $url = $baseUrl . $endpoint;
    $ch = curl_init();
    
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json'
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        echo "  ❌ 错误: $error\n";
        $failed++;
    } elseif ($httpCode >= 200 && $httpCode < 300) {
        $data = json_decode($response, true);
        if ($data && isset($data['success']) && $data['success']) {
            echo "  ✅ 成功 (HTTP $httpCode)\n";
            $passed++;
        } else {
            echo "  ❌ 响应格式错误 (HTTP $httpCode)\n";
            $failed++;
        }
    } else {
        echo "  ❌ HTTP错误 (HTTP $httpCode)\n";
        $failed++;
    }
    
    // 显示响应片段
    if ($response) {
        $data = json_decode($response, true);
        if ($data && isset($data['data']['message'])) {
            echo "  消息: " . $data['data']['message'] . "\n";
        }
    }
    
    echo "\n";
}

echo "=== 测试总结 ===\n";
echo "总计: " . ($passed + $failed) . " 个端点\n";
echo "成功: $passed ✅\n";
echo "失败: $failed ❌\n";
echo "成功率: " . round(($passed / ($passed + $failed)) * 100, 1) . "%\n";

if ($failed === 0) {
    echo "\n🎉 所有API端点测试通过！\n";
} else {
    echo "\n⚠️  有 $failed 个端点测试失败，需要检查。\n";
}
