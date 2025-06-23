<?php
/**
 * 简单的API端点测试
 */

function testEndpoint($url) {
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Accept: application/json'
        ]
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    if (curl_error($ch)) {
        echo "cURL错误: " . curl_error($ch) . "\n";
        return false;
    }
    
    curl_close($ch);
    
    echo "HTTP Code: $httpCode\n";
    echo "Response: $response\n\n";
    
    return $httpCode === 200;
}

echo "测试API端点访问\n";
echo "===============\n\n";

// 测试几个端点
$endpoints = [
    'http://localhost:8000/api/auth/test',
    'http://localhost:8000/api/system/test',
    'http://localhost:8000/api/history/test'
];

foreach ($endpoints as $endpoint) {
    echo "测试: $endpoint\n";
    testEndpoint($endpoint);
}
