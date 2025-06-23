<?php
/**
 * 统一管理系统API端点测试
 */

function testUnifiedAPI($endpoint, $method = 'GET') {
    $url = "http://localhost:8080" . $endpoint;
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json'
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        return [
            'success' => false,
            'error' => $error,
            'http_code' => $httpCode
        ];
    }
    
    return [
        'success' => true,
        'http_code' => $httpCode,
        'response' => json_decode($response, true)
    ];
}

echo "=== 统一管理系统API测试 ===\n";

$endpoints = [
    ['GET', '/api/unified-admin/dashboard', '仪表板数据'],
    ['GET', '/api/unified-admin/diagnostics', '系统诊断'],
    ['POST', '/api/unified-admin/tests/comprehensive', '综合测试'],
    ['GET', '/api/unified-admin/health', '健康检查'],
    ['GET', '/api/unified-admin/monitoring/current', '当前监控'],
    ['POST', '/api/unified-admin/security/scan', '安全扫描']
];

$passed = 0;
$failed = 0;

foreach ($endpoints as [$method, $endpoint, $description]) {
    echo "\n--- 测试: {$description} ---\n";
    echo "端点: {$method} {$endpoint}\n";
    
    $result = testUnifiedAPI($endpoint, $method);
    
    if ($result['success']) {
        echo "✅ HTTP状态码: {$result['http_code']}\n";
        
        if ($result['http_code'] === 200) {
            $response = $result['response'];
            if (isset($response['success']) && $response['success']) {
                echo "✅ API响应成功\n";
                
                // 显示一些关键数据
                if (isset($response['data'])) {
                    $dataKeys = array_keys($response['data']);
                    echo "📊 数据字段: " . implode(', ', array_slice($dataKeys, 0, 5));
                    if (count($dataKeys) > 5) {
                        echo " (+" . (count($dataKeys) - 5) . "个更多字段)";
                    }
                    echo "\n";
                }
                $passed++;
            } else {
                echo "❌ API返回错误: " . ($response['error'] ?? '未知错误') . "\n";
                $failed++;
            }
        } else {
            echo "⚠️ HTTP状态码异常: {$result['http_code']}\n";
            echo "响应: " . json_encode($result['response'], JSON_UNESCAPED_UNICODE) . "\n";
            $failed++;
        }
    } else {
        echo "❌ 请求失败: {$result['error']}\n";
        $failed++;
    }
}

echo "\n=== 测试总结 ===\n";
echo "✅ 通过: {$passed}\n";
echo "❌ 失败: {$failed}\n";
echo "📊 成功率: " . round(($passed / ($passed + $failed)) * 100, 2) . "%\n";

if ($passed > 0) {
    echo "\n🎉 统一管理系统API基本功能正常！\n";
    echo "前端测试页面: test_unified_admin_frontend.html\n";
    echo "API服务器: http://localhost:8080\n";
} else {
    echo "\n⚠️  需要检查API配置和服务器状态。\n";
}
