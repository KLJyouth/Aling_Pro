<?php

echo "=== API服务器端点测试 ===\n\n";

$baseUrl = 'http://localhost:8080';

/**
 * 发送HTTP请求
 */
function makeRequest($url, $method = 'GET', $data = null) {
    $ch = curl_init();
    
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return [
        'code' => $httpCode,
        'body' => $response,
        'json' => json_decode($response, true)
    ];
}

// 测试API端点
$tests = [
    [
        'name' => '获取API信息',
        'url' => $baseUrl . '/api',
        'method' => 'GET'
    ],
    [
        'name' => '获取系统统计',
        'url' => $baseUrl . '/api/admin/stats',
        'method' => 'GET'
    ],
    [
        'name' => '获取企业申请列表',
        'url' => $baseUrl . '/api/admin/applications',
        'method' => 'GET'
    ],
    [
        'name' => '获取用户列表',
        'url' => $baseUrl . '/api/admin/users',
        'method' => 'GET'
    ],
    [
        'name' => '审核企业申请',
        'url' => $baseUrl . '/api/admin/applications/review',
        'method' => 'POST',
        'data' => [
            'applicationId' => 1,
            'status' => 'approved',
            'adminNotes' => 'API测试审核通过'
        ]
    ],
    [
        'name' => '更新用户配额',
        'url' => $baseUrl . '/api/admin/quota/update',
        'method' => 'POST',
        'data' => [
            'userId' => 1,
            'quotaData' => [
                'api_quota_daily' => 20000,
                'api_quota_monthly' => 600000
            ]
        ]
    ]
];

$passedTests = 0;
$totalTests = count($tests);

foreach ($tests as $i => $test) {
    echo ($i + 1) . ". 测试: {$test['name']}\n";
    echo "   URL: {$test['url']}\n";
    echo "   方法: {$test['method']}\n";
    
    if (isset($test['data'])) {
        echo "   数据: " . json_encode($test['data'], JSON_UNESCAPED_UNICODE) . "\n";
    }
    
    $result = makeRequest($test['url'], $test['method'], $test['data'] ?? null);
    
    echo "   响应码: {$result['code']}\n";
    
    if ($result['code'] === 200) {
        echo "   ✓ 测试通过\n";
        $passedTests++;
        
        if ($result['json']) {
            if (isset($result['json']['success'])) {
                echo "   状态: " . ($result['json']['success'] ? '成功' : '失败') . "\n";
                if (isset($result['json']['message'])) {
                    echo "   消息: {$result['json']['message']}\n";
                }
            }
            
            if (isset($result['json']['data']) && is_array($result['json']['data'])) {
                $dataCount = count($result['json']['data']);
                echo "   数据条数: $dataCount\n";
            }
        }
    } else {
        echo "   ✗ 测试失败\n";
        echo "   响应: {$result['body']}\n";
    }
    
    echo "\n";
}

echo "=== 测试汇总 ===\n";
echo "总测试数: $totalTests\n";
echo "通过数: $passedTests\n";
echo "失败数: " . ($totalTests - $passedTests) . "\n";
echo "成功率: " . round(($passedTests / $totalTests) * 100, 2) . "%\n\n";

if ($passedTests === $totalTests) {
    echo "🎉 所有测试通过！API服务器运行正常。\n";
} else {
    echo "⚠️ 部分测试失败，请检查API服务器状态。\n";
}

echo "\n=== 额外功能测试 ===\n";

// 测试获取特定状态的申请
echo "测试获取待审核申请...\n";
$pendingResult = makeRequest($baseUrl . '/api/admin/applications?status=pending');
if ($pendingResult['code'] === 200 && $pendingResult['json']['success']) {
    $pendingCount = count($pendingResult['json']['data']);
    echo "✓ 待审核申请数量: $pendingCount\n";
} else {
    echo "✗ 获取待审核申请失败\n";
}

// 测试获取企业配置
echo "\n测试获取企业配置...\n";
$configResult = makeRequest($baseUrl . '/api/admin/enterprise-config?userId=1');
if ($configResult['code'] === 200) {
    if ($configResult['json']['success'] && $configResult['json']['data']) {
        echo "✓ 企业配置获取成功\n";
        $config = $configResult['json']['data'];
        echo "   优先支持: " . ($config['priority_support'] ? '是' : '否') . "\n";
        echo "   高级分析: " . ($config['advanced_analytics'] ? '是' : '否') . "\n";
    } else {
        echo "✓ 响应正常，但用户无企业配置\n";
    }
} else {
    echo "✗ 获取企业配置失败\n";
}

echo "\n✓ API服务器功能测试完成！\n";
