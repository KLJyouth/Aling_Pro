<?php

/**
 * 测试历史记录API端点
 * 验证新添加的HistoryApiController是否正常工作
 */

echo "=== 历史记录API端点测试 ===\n";

$baseUrl = 'http://localhost:8000';

// 测试的历史记录端点列表
$historyEndpoints = [
    ['GET', '/api/history/test', '历史记录测试端点'],
    ['GET', '/api/history/sessions', '获取历史会话 (前端期望)'],
    ['GET', '/api/history', '获取历史消息 (前端期望)'],
    ['POST', '/api/history', '保存历史记录 (前端期望)'],
    ['GET', '/api/history/1', '获取特定历史记录'],
    ['GET', '/api/history/search', '搜索历史记录'],
    ['GET', '/api/history/export', '导出历史记录']
];

function testEndpoint($method, $url, $description, $data = null) {
    $ch = curl_init();
    
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json'
    ]);
    
    if ($data && in_array($method, ['POST', 'PUT', 'PATCH'])) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    echo "\n📡 测试: $description\n";
    echo "   端点: $method $url\n";
    
    if ($error) {
        echo "   ❌ 连接错误: $error\n";
        return false;
    }
    
    echo "   🔢 状态码: $httpCode\n";
    
    if ($httpCode === 200) {
        echo "   ✅ 成功\n";
        
        $jsonResponse = json_decode($response, true);
        if ($jsonResponse) {
            if (isset($jsonResponse['success']) && $jsonResponse['success']) {
                echo "   📄 响应: API工作正常\n";
                if (isset($jsonResponse['data']['message'])) {
                    echo "   💬 消息: " . $jsonResponse['data']['message'] . "\n";
                }
                if (isset($jsonResponse['data']['endpoints'])) {
                    echo "   🔗 可用端点: " . count($jsonResponse['data']['endpoints']) . "个\n";
                }
            } else {
                echo "   ⚠️ 响应格式: " . substr($response, 0, 200) . "\n";
            }
        } else {
            echo "   ⚠️ 响应: " . substr($response, 0, 200) . "\n";
        }
        return true;
    } else {
        echo "   ❌ 失败 (HTTP $httpCode)\n";
        if ($response) {
            echo "   📄 错误响应: " . substr($response, 0, 200) . "\n";
        }
        return false;
    }
}

$successCount = 0;
$totalCount = count($historyEndpoints);

foreach ($historyEndpoints as $endpoint) {
    [$method, $path, $description] = $endpoint;
    $url = $baseUrl . $path;
    
    // 为POST请求准备测试数据
    $testData = null;
    if ($method === 'POST' && $path === '/api/history') {
        $testData = [
            'session_id' => 'test_session_001',
            'message' => '这是一条测试消息',
            'response' => '这是AI的测试回复',
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }
    
    $success = testEndpoint($method, $url, $description, $testData);
    if ($success) {
        $successCount++;
    }
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "📊 历史记录API测试结果\n";
echo "✅ 成功: $successCount/$totalCount 个端点\n";
echo "❌ 失败: " . ($totalCount - $successCount) . "/$totalCount 个端点\n";

if ($successCount === $totalCount) {
    echo "🎉 所有历史记录API端点测试通过！\n";
    echo "📋 前端-后端历史记录API集成成功\n";
} else {
    echo "⚠️ 部分端点需要检查\n";
}

echo "\n🔗 前端期望的关键端点状态:\n";
echo "- GET /api/history/sessions (历史会话列表)\n";
echo "- GET /api/history (历史消息)\n";
echo "- POST /api/history (保存历史)\n";
echo "\n📝 这些端点现在应该可以与前端 apiConfig.js 中的配置匹配\n";

?>
