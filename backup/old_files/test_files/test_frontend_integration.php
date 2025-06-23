<?php
/**
 * 前端集成测试脚本
 * 测试前端JavaScript与后端API的集成
 */

// 启用错误报告
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "🔗 前端API集成测试\n\n";

// 定义要测试的前端API调用
$frontendApiCalls = [
    // 认证相关
    [
        'name' => '前端登录API',
        'endpoint' => '/api/auth/login',
        'method' => 'POST',
        'data' => ['email' => 'test@example.com', 'password' => 'test123']
    ],
    [
        'name' => '前端注册API', 
        'endpoint' => '/api/auth/register',
        'method' => 'POST',
        'data' => ['username' => 'testuser', 'email' => 'test@example.com', 'password' => 'test123']
    ],
    
    // 聊天相关
    [
        'name' => '前端聊天API - 发送消息',
        'endpoint' => '/api/chat/send',
        'method' => 'POST', 
        'data' => ['text' => '你好', 'modelType' => 'deepseek-chat']
    ],
    [
        'name' => '前端聊天API - 兼容endpoint',
        'endpoint' => '/api/chat/chat',
        'method' => 'POST',
        'data' => ['text' => '测试消息', 'modelType' => 'deepseek-chat']
    ],
    [
        'name' => '前端会话列表',
        'endpoint' => '/api/chat/sessions',
        'method' => 'GET'
    ],
    
    // 用户相关
    [
        'name' => '前端用户资料',
        'endpoint' => '/api/user/profile', 
        'method' => 'GET'
    ],
    
    // 历史记录相关 (前端期望的但可能不存在)
    [
        'name' => '前端历史记录',
        'endpoint' => '/api/history',
        'method' => 'GET'
    ],
    [
        'name' => '前端历史会话',
        'endpoint' => '/api/history/sessions',
        'method' => 'GET'
    ]
];

$successCount = 0;
$totalCount = count($frontendApiCalls);

foreach ($frontendApiCalls as $test) {
    echo "📡 测试: {$test['name']}\n";
    echo "   端点: {$test['endpoint']}\n";
    echo "   方法: {$test['method']}\n";
    
    // 构建URL
    $url = 'http://localhost:8000' . $test['endpoint'];
    
    // 构建curl请求
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $test['method']);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json'
    ]);
    
    if ($test['method'] === 'POST' && isset($test['data'])) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($test['data']));
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);
    
    if ($curlError) {
        echo "   ❌ cURL错误: $curlError\n";
    } else {
        echo "   📊 HTTP状态码: $httpCode\n";
        
        if ($httpCode >= 200 && $httpCode < 300) {
            $successCount++;
            echo "   ✅ 请求成功\n";
            
            // 解析响应
            $data = json_decode($response, true);
            if ($data) {
                echo "   📋 响应格式: " . (isset($data['success']) ? ' 标准格式' : '非标准格式') . "\n";
                if (isset($data['message'])) {
                    echo "   💬 消息: {$data['message']}\n";
                }
            }
        } else {
            echo "   ❌ 请求失败\n";
            echo "   📄 响应内容: " . substr($response, 0, 200) . "...\n";
        }
    }
    
    echo "\n";
}

echo "📊 测试结果汇总:\n";
echo "   总测试数: $totalCount\n";
echo "   成功数: $successCount\n";
echo "   失败数: " . ($totalCount - $successCount) . "\n";
echo "   成功率: " . round(($successCount / $totalCount) * 100, 2) . "%\n\n";

// 分析前端期望的端点vs实际可用端点
echo "🔍 端点映射分析:\n";
echo "前端期望的端点 -> 实际可用端点\n";
echo "/api/chat/send -> /api/chat/test (测试可用)\n";
echo "/api/chat/chat -> /api/chat/test (测试可用)\n"; 
echo "/api/history -> 需要创建 (前端依赖)\n";
echo "/api/history/sessions -> 需要创建 (前端依赖)\n";
echo "/api/v1/status -> /api/system/test (可用)\n";
echo "/api/v1/auth/validate -> /api/auth/test (测试可用)\n\n";

echo "🛠️ 建议的集成步骤:\n";
echo "1. 更新前端API基础URL为正确的端点\n";
echo "2. 实现缺失的端点 (/api/history/*)\n";
echo "3. 统一响应格式\n";
echo "4. 添加JWT认证支持\n";
echo "5. 实现实际的聊天功能\n\n";

echo "✅ 前端集成测试完成\n";
?>
