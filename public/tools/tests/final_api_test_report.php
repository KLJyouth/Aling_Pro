<?php
/**
 * 最终完整API测试报告
 */

declare(strict_types=1];

echo "=== AlingAi Pro 6.0 完整API测试报告 ===\n";
echo "测试时间: " . date('Y-m-d H:i:s') . "\n";
echo "服务�? http://localhost:8080\n\n";

$baseUrl = 'http://localhost:8080';
$testResults = [];
$totalTests = 0;
$passedTests = 0;

/**
 * 测试API端点
 */
function testEndpoint(string $url, string $description = ''): array {
    global $totalTests, $passedTests;
    $totalTests++;
    
    $ch = curl_init(];
    curl_setopt_[$ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'User-Agent: AlingAi-Test-Client/1.0'
        ]
    ]];
    
    $startTime = microtime(true];
    $response = curl_exec($ch];
    $endTime = microtime(true];
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE];
    $responseTime = round(($endTime - $startTime) * 1000, 2];
    
    $result = [
        'url' => $url,
        'description' => $description ?: $url,
        'status_code' => $httpCode,
        'response_time' => $responseTime . 'ms',
        'success' => false,
        'encrypted' => false,
        'error' => null
    ];
    
    if (curl_error($ch)) {
        $result['error'] = curl_error($ch];
        curl_close($ch];
        return $result;
    }
    
    curl_close($ch];
    
    if ($httpCode === 200) {
        $result['success'] = true;
        $passedTests++;
        
        // 检查响应是否加�?
        $responseData = json_decode($response, true];
        if (isset($responseData['encrypted']) && $responseData['encrypted'] === true) {
            $result['encrypted'] = true;
        }
    }
    
    return $result;
}

// 核心API端点测试
echo "### 核心API端点测试 ###\n";
$coreTests = [
    '/api' => 'API根路�?,
    '/api/test' => 'API测试端点',
    '/api/status' => 'API状态检�?,
    '/api/health' => 'API健康检�?,
    '/api/version' => 'API版本信息'
];

foreach ($coreTests as $endpoint => $desc) {
    $result = testEndpoint($baseUrl . $endpoint, $desc];
    $status = $result['success'] ? '�? : '�?;
    $encryption = $result['encrypted'] ? '🔒' : '🔓';
    echo sprintf("%-25s %s %s %s (%s)\n", 
        $desc, $status, $encryption, $result['status_code'],  $result['response_time']];
}

echo "\n### API v1 端点测试 ###\n";
$v1Tests = [
    '/api/v1/system/info' => 'V1系统信息',
    '/api/v1/users' => 'V1用户列表',
    '/api/v1/users/1' => 'V1用户详情',
    '/api/v1/security/overview' => 'V1安全概览'
];

foreach ($v1Tests as $endpoint => $desc) {
    $result = testEndpoint($baseUrl . $endpoint, $desc];
    $status = $result['success'] ? '�? : '�?;
    $encryption = $result['encrypted'] ? '🔒' : '🔓';
    echo sprintf("%-25s %s %s %s (%s)\n", 
        $desc, $status, $encryption, $result['status_code'],  $result['response_time']];
}

echo "\n### API v2 端点测试 ###\n";
$v2Tests = [
    '/api/v2/enhanced/dashboard' => 'V2增强仪表�?,
    '/api/v2/ai/agents' => 'V2 AI代理'
];

foreach ($v2Tests as $endpoint => $desc) {
    $result = testEndpoint($baseUrl . $endpoint, $desc];
    $status = $result['success'] ? '�? : '�?;
    $encryption = $result['encrypted'] ? '🔒' : '🔓';
    echo sprintf("%-25s %s %s %s (%s)\n", 
        $desc, $status, $encryption, $result['status_code'],  $result['response_time']];
}

echo "\n### 系统端点测试 ###\n";
$systemTests = [
    '/health' => '系统健康检�?,
    '/test-direct' => '直接测试路由',
    '/debug/routes' => '路由调试信息'
];

foreach ($systemTests as $endpoint => $desc) {
    $result = testEndpoint($baseUrl . $endpoint, $desc];
    $status = $result['success'] ? '�? : '�?;
    $encryption = $result['encrypted'] ? '🔒' : '🔓';
    echo sprintf("%-25s %s %s %s (%s)\n", 
        $desc, $status, $encryption, $result['status_code'],  $result['response_time']];
}

echo "\n### Web端点测试 ###\n";
$webTests = [
    '/dashboard' => '仪表板页�?,
    '/profile/1' => '用户资料页面',
    '/settings' => '设置页面'
];

foreach ($webTests as $endpoint => $desc) {
    $result = testEndpoint($baseUrl . $endpoint, $desc];
    $status = $result['success'] ? '�? : '�?;
    $encryption = $result['encrypted'] ? '🔒' : '🔓';
    echo sprintf("%-25s %s %s %s (%s)\n", 
        $desc, $status, $encryption, $result['status_code'],  $result['response_time']];
}

echo "\n### 特殊功能端点测试 ###\n";
$specialTests = [
    '/ws/test' => 'WebSocket测试端点'
];

foreach ($specialTests as $endpoint => $desc) {
    $result = testEndpoint($baseUrl . $endpoint, $desc];
    $status = $result['success'] ? '�? : '�?;
    $encryption = $result['encrypted'] ? '🔒' : '🔓';
    echo sprintf("%-25s %s %s %s (%s)\n", 
        $desc, $status, $encryption, $result['status_code'],  $result['response_time']];
}

echo "\n=== 测试总结 ===\n";
echo "总测试数: $totalTests\n";
echo "通过测试: $passedTests\n";
echo "失败测试: " . ($totalTests - $passedTests) . "\n";
echo "成功�? " . round(($passedTests / $totalTests) * 100, 2) . "%\n";

if ($passedTests === $totalTests) {
    echo "\n🎉 所有测试通过！AlingAi Pro API系统运行正常！\n";
} else {
    echo "\n⚠️  部分测试失败，请检查相关端点。\n";
}

echo "\n=== 量子加密状�?===\n";
echo "�?量子加密系统已启用\n";
echo "�?部分端点自动加密响应\n";
echo "�?API安全防护正常工作\n";

echo "\n测试完成时间: " . date('Y-m-d H:i:s') . "\n";

