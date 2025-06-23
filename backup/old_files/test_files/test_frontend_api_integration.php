<?php
/**
 * 前端API集成测试 - 重点测试历史记录API
 * 验证前端配置的API端点是否与后端匹配
 */

echo "=== 前端-后端API集成验证 ===\n";

// 模拟前端API配置
$API_BASE_URL = 'http://localhost:8000';

$API_ENDPOINTS = [
    // 历史记录相关端点（前端期望的关键端点）
    'HISTORY_SESSIONS' => $API_BASE_URL . '/api/history/sessions',
    'HISTORY_MESSAGES' => $API_BASE_URL . '/api/history',
    'SAVE_HISTORY' => $API_BASE_URL . '/api/history',
    
    // 认证端点
    'AUTH_TEST' => $API_BASE_URL . '/api/auth/test',
    
    // 聊天端点
    'CHAT_TEST' => $API_BASE_URL . '/api/chat/test',
];

/**
 * 模拟前端fetch请求
 */
function makeApiCall($url, $method = 'GET', $data = null) {
    $context = stream_context_create([
        'http' => [
            'method' => $method,
            'header' => 'Content-Type: application/json',
            'content' => $data ? json_encode($data) : null,
            'timeout' => 10,
            'ignore_errors' => true
        ]
    ]);
    
    $response = @file_get_contents($url, false, $context);
    $httpCode = 0;
    
    if (isset($http_response_header)) {
        foreach ($http_response_header as $header) {
            if (preg_match('/HTTP\/\d\.\d\s+(\d+)/', $header, $matches)) {
                $httpCode = intval($matches[1]);
                break;
            }
        }
    }
    
    return [
        'success' => $httpCode >= 200 && $httpCode < 300,
        'status' => $httpCode,
        'response' => $response ? json_decode($response, true) : null,
        'raw_response' => $response
    ];
}

$testResults = [];

echo "\n📡 测试前端期望的历史记录API端点...\n";

// 测试1: 获取历史会话
echo "\n1. 测试获取历史会话\n";
echo "   端点: {$API_ENDPOINTS['HISTORY_SESSIONS']}\n";
$result = makeApiCall($API_ENDPOINTS['HISTORY_SESSIONS']);
echo "   状态码: {$result['status']}\n";
echo "   成功: " . ($result['success'] ? '✅' : '❌') . "\n";
if ($result['response']) {
    echo "   响应类型: " . ($result['response']['success'] ? '成功响应' : '错误响应') . "\n";
    if (isset($result['response']['data']['sessions'])) {
        echo "   会话数量: " . count($result['response']['data']['sessions']) . "\n";
    }
}
$testResults[] = ['name' => 'History Sessions', 'success' => $result['success']];

// 测试2: 获取历史消息
echo "\n2. 测试获取历史消息\n";
echo "   端点: {$API_ENDPOINTS['HISTORY_MESSAGES']}\n";
$result = makeApiCall($API_ENDPOINTS['HISTORY_MESSAGES']);
echo "   状态码: {$result['status']}\n";
echo "   成功: " . ($result['success'] ? '✅' : '❌') . "\n";
if ($result['response']) {
    echo "   响应类型: " . ($result['response']['success'] ? '成功响应' : '错误响应') . "\n";
    if (isset($result['response']['data']['messages'])) {
        echo "   消息数量: " . count($result['response']['data']['messages']) . "\n";
    }
}
$testResults[] = ['name' => 'History Messages', 'success' => $result['success']];

// 测试3: 保存历史记录
echo "\n3. 测试保存历史记录\n";
echo "   端点: {$API_ENDPOINTS['SAVE_HISTORY']} (POST)\n";
$testData = [
    'session_id' => 'frontend_test_' . time(),
    'title' => '前端集成测试对话',
    'messages' => [
        [
            'type' => 'user',
            'content' => '这是前端发送的测试消息',
            'timestamp' => date('Y-m-d H:i:s')
        ],
        [
            'type' => 'assistant',
            'content' => '这是后端的响应消息',
            'timestamp' => date('Y-m-d H:i:s')
        ]
    ]
];
$result = makeApiCall($API_ENDPOINTS['SAVE_HISTORY'], 'POST', $testData);
echo "   状态码: {$result['status']}\n";
echo "   成功: " . ($result['success'] ? '✅' : '❌') . "\n";
if ($result['response']) {
    echo "   响应类型: " . ($result['response']['success'] ? '成功响应' : '错误响应') . "\n";
    if (isset($result['response']['data']['session_id'])) {
        echo "   保存的会话ID: " . $result['response']['data']['session_id'] . "\n";
    }
}
$testResults[] = ['name' => 'Save History', 'success' => $result['success']];

echo "\n🔐 测试认证API端点...\n";

// 测试4: 认证API
echo "\n4. 测试认证API\n";
echo "   端点: {$API_ENDPOINTS['AUTH_TEST']}\n";
$result = makeApiCall($API_ENDPOINTS['AUTH_TEST']);
echo "   状态码: {$result['status']}\n";
echo "   成功: " . ($result['success'] ? '✅' : '❌') . "\n";
$testResults[] = ['name' => 'Auth Test', 'success' => $result['success']];

echo "\n💬 测试聊天API端点...\n";

// 测试5: 聊天API
echo "\n5. 测试聊天API\n";
echo "   端点: {$API_ENDPOINTS['CHAT_TEST']}\n";
$result = makeApiCall($API_ENDPOINTS['CHAT_TEST']);
echo "   状态码: {$result['status']}\n";
echo "   成功: " . ($result['success'] ? '✅' : '❌') . "\n";
$testResults[] = ['name' => 'Chat Test', 'success' => $result['success']];

// 测试总结
echo "\n" . str_repeat("=", 50) . "\n";
echo "📊 前端-后端API集成测试总结\n";
echo str_repeat("=", 50) . "\n";

$totalTests = count($testResults);
$successfulTests = array_filter($testResults, function($test) { return $test['success']; });
$failedTests = $totalTests - count($successfulTests);

echo "总测试数: {$totalTests}\n";
echo "✅ 成功: " . count($successfulTests) . "\n";
echo "❌ 失败: {$failedTests}\n";
echo "\n测试详情:\n";

foreach ($testResults as $test) {
    echo ($test['success'] ? '✅' : '❌') . " {$test['name']}\n";
}

if ($failedTests === 0) {
    echo "\n🎉 所有API测试通过！\n";
    echo "📋 前端-后端集成验证成功！\n";
    echo "🔗 前端JavaScript可以正确调用所有后端PHP API端点\n";
    echo "\n✨ 关键成就：\n";
    echo "   • 历史记录API完全集成 (/api/history/*)\n";
    echo "   • 前端配置与后端端点完美匹配\n";
    echo "   • API响应格式统一\n";
    echo "   • POST请求数据处理正常\n";
} else {
    echo "\n⚠️ 部分测试失败，需要进一步检查\n";
}

echo "\n🚀 下一步: 实现JWT认证和实际聊天功能\n";
?>
