<?php
/**
 * 测试登录端点
 */

function testLogin() {
    $url = 'http://localhost:8000/api/auth/login';
    $data = [
        'email' => 'test@alingai.com',
        'password' => 'test123456'
    ];
    
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Accept: application/json'
        ],
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($data)
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
    
    return json_decode($response, true);
}

echo "测试登录端点\n";
echo "============\n\n";

$result = testLogin();
