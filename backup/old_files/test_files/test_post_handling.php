<?php
/**
 * 验证路由器是否能接收POST请求
 */

function testEndpoint($url, $method = 'GET', $data = null) {
    $ch = curl_init();
    
    $options = [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Accept: application/json'
        ],
        CURLOPT_FOLLOWLOCATION => true
    ];
    
    if ($method === 'POST') {
        $options[CURLOPT_POST] = true;
        if ($data) {
            $options[CURLOPT_POSTFIELDS] = json_encode($data);
        }
    }
    
    curl_setopt_array($ch, $options);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $info = curl_getinfo($ch);
    
    if (curl_error($ch)) {
        echo "cURL错误: " . curl_error($ch) . "\n";
        return false;
    }
    
    curl_close($ch);
    
    echo "URL: {$url}\n";
    echo "Method: {$method}\n";
    echo "HTTP Code: {$httpCode}\n";
    echo "Response: {$response}\n";
    echo "Effective URL: {$info['url']}\n\n";
    
    return $httpCode === 200;
}

echo "验证路由器POST请求处理\n";
echo "====================\n\n";

// 先测试GET请求是否正常
echo "1. 测试GET请求:\n";
testEndpoint('http://localhost:8000/api/auth/test', 'GET');

echo "2. 测试POST请求到已知不存在的端点:\n";
testEndpoint('http://localhost:8000/api/auth/nonexistent', 'POST');

echo "3. 测试POST请求到登录端点:\n";
testEndpoint('http://localhost:8000/api/auth/login', 'POST', [
    'email' => 'test@example.com',
    'password' => 'password'
]);

echo "4. 测试根路径:\n";
testEndpoint('http://localhost:8000/', 'GET');
