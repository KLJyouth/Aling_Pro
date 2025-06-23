<?php

/**
 * 全面测试企业管理API端点功能
 */

$baseUrl = 'http://localhost:8080';

echo "=== 全面API功能测试 ===\n\n";

// 测试函数
function testApiEndpoint($url, $method = 'GET', $data = null, $description = '') {
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
    
    echo ($description ? "【$description】\n" : '') . "请求: $method $url\n";
    if ($data) {
        echo "数据: " . json_encode($data, JSON_UNESCAPED_UNICODE) . "\n";
    }
    echo "状态码: $httpCode\n";
    
    $result = json_decode($response, true);
    if ($result) {
        echo "成功: " . ($result['success'] ? '是' : '否') . "\n";
        if (isset($result['message'])) {
            echo "消息: " . $result['message'] . "\n";
        }
        if (isset($result['data']) && is_array($result['data'])) {
            if (isset($result['data'][0])) {
                echo "数据数量: " . count($result['data']) . "\n";
            } else {
                echo "数据字段: " . implode(', ', array_keys($result['data'])) . "\n";
            }
        }
    } else {
        echo "响应: " . $response . "\n";
    }
    echo "\n";
    
    return $result;
}

// 1. 获取系统统计
echo "1. 系统统计测试\n";
echo str_repeat("-", 50) . "\n";
testApiEndpoint($baseUrl . '/api/admin/stats', 'GET', null, '获取系统统计信息');

// 2. 获取所有申请
echo "2. 申请管理测试\n";
echo str_repeat("-", 50) . "\n";
testApiEndpoint($baseUrl . '/api/admin/applications', 'GET', null, '获取所有申请');
testApiEndpoint($baseUrl . '/api/admin/applications?status=pending', 'GET', null, '获取待审核申请');

// 3. 审核申请
echo "3. 申请审核测试\n";
echo str_repeat("-", 50) . "\n";
testApiEndpoint($baseUrl . '/api/admin/applications/review', 'POST', [
    'applicationId' => 3,
    'status' => 'approved',
    'adminNotes' => '审核通过，用户资质符合要求'
], '审核申请');

// 验证审核结果
testApiEndpoint($baseUrl . '/api/admin/applications?status=approved', 'GET', null, '验证审核结果');

// 4. 用户管理测试
echo "4. 用户管理测试\n";
echo str_repeat("-", 50) . "\n";
testApiEndpoint($baseUrl . '/api/admin/users', 'GET', null, '获取所有用户');
testApiEndpoint($baseUrl . '/api/admin/users?type=enterprise', 'GET', null, '获取企业用户');
testApiEndpoint($baseUrl . '/api/admin/users/details?userId=2', 'GET', null, '获取用户详情');

// 5. 配额管理测试
echo "5. 配额管理测试\n";
echo str_repeat("-", 50) . "\n";
testApiEndpoint($baseUrl . '/api/admin/quota/update', 'POST', [
    'userId' => 2,
    'quotaData' => [
        'api_calls_limit' => 1500000,
        'tokens_limit' => 75000000,
        'daily_limit' => 75000,
        'monthly_limit' => 1500000,
        'rate_limit' => 1200,
        'status' => 'active'
    ]
], '更新用户配额');

// 6. 企业配置测试
echo "6. 企业配置测试\n";
echo str_repeat("-", 50) . "\n";
testApiEndpoint($baseUrl . '/api/admin/enterprise-config?userId=2', 'GET', null, '获取企业配置');

testApiEndpoint($baseUrl . '/api/admin/enterprise-config/update', 'POST', [
    'userId' => 3,
    'configData' => [
        'ai_providers' => json_encode([
            'openai' => ['enabled' => true, 'api_key' => 'sk-new-key'],
            'anthropic' => ['enabled' => true, 'api_key' => 'sk-new-key'],
            'google' => ['enabled' => true, 'api_key' => 'sk-new-key']
        ]),
        'custom_models' => json_encode(['gpt-4-turbo', 'claude-3-opus']),
        'webhook_url' => 'https://aistartup.com/webhook',
        'features' => json_encode([
            'advanced_analytics' => true,
            'custom_fine_tuning' => true,
            'priority_support' => false
        ]),
        'status' => 'active'
    ]
], '更新企业配置');

// 7. 错误处理测试
echo "7. 错误处理测试\n";
echo str_repeat("-", 50) . "\n";
testApiEndpoint($baseUrl . '/api/admin/applications/review', 'POST', [
    'applicationId' => 999,
    'status' => 'approved'
], '审核不存在的申请');

testApiEndpoint($baseUrl . '/api/admin/quota/update', 'POST', [
    'userId' => 999,
    'quotaData' => ['api_calls_limit' => 1000]
], '更新不存在用户的配额');

echo "=== 全面API功能测试完成 ===\n";
