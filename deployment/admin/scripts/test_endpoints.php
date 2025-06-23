<?php

// 测试主要端点
$baseUrl = 'http://localhost:8080';

echo "=== AlingAi Pro 端点测试 ===\n";

// 测试的端点列表
$endpoints = [
    ['GET', '/', 'Homepage'],
    ['GET', '/chat', 'Chat Page'],
    ['GET', '/login', 'Login Page'],
    ['GET', '/register', 'Register Page'],
    ['GET', '/profile', 'Profile Page'],
    ['GET', '/admin', 'Admin Dashboard'],
    ['GET', '/api/chat/history', 'Chat History API'],
    ['GET', '/api/user/profile', 'User Profile API'],
    ['GET', '/api/app/info', 'App Info API'],
];

function testEndpoint($method, $url, $description) {
    $ch = curl_init();
    
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Accept: application/json',
        'User-Agent: AlingAi-Test/1.0'
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    
    curl_close($ch);
    
    if ($error) {
        return ['success' => false, 'error' => $error, 'code' => 0];
    }
    
    return [
        'success' => true,
        'code' => $httpCode,
        'response' => $response,
        'length' => strlen($response)
    ];
}

foreach ($endpoints as $endpoint) {
    [$method, $path, $description] = $endpoint;
    $url = $baseUrl . $path;
    
    echo "\n正在测试: {$description} ({$method} {$path})\n";
    
    $result = testEndpoint($method, $url, $description);
    
    if (!$result['success']) {
        echo "  ❌ 连接失败: {$result['error']}\n";
        continue;
    }
    
    $code = $result['code'];
    $length = $result['length'];
    
    if ($code >= 200 && $code < 300) {
        echo "  ✅ HTTP {$code} - 响应长度: {$length} 字节\n";
    } elseif ($code >= 300 && $code < 400) {
        echo "  🔄 HTTP {$code} - 重定向\n";
    } elseif ($code >= 400 && $code < 500) {
        echo "  ⚠️  HTTP {$code} - 客户端错误\n";
    } else {
        echo "  ❌ HTTP {$code} - 服务器错误\n";
    }
    
    // 显示响应片段（前200字符）
    if ($length > 0) {
        $preview = substr($result['response'], 0, 200);
        $preview = str_replace(["\n", "\r"], '', $preview);
        if ($length > 200) {
            $preview .= '...';
        }
        echo "  📄 响应预览: {$preview}\n";
    }
}

echo "\n=== 测试完成 ===\n";
echo "提示: 应用程序正在 {$baseUrl} 上运行\n";