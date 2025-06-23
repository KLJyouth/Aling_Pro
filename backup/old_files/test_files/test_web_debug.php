<?php
/**
 * 测试 Web 服务器错误调试
 */

$url = 'http://localhost:8080/';
$context = stream_context_create([
    'http' => [
        'method' => 'GET',
        'timeout' => 10
    ]
]);

echo "=== 测试 Web 服务器 ===\n";
echo "URL: $url\n";

try {
    $response = file_get_contents($url, false, $context);
    if ($response === false) {
        echo "❌ 请求失败\n";
        
        // 检查 HTTP 响应头
        if (isset($http_response_header)) {
            echo "HTTP 响应头:\n";
            foreach ($http_response_header as $header) {
                echo "  $header\n";
            }
        }
    } else {
        echo "✅ 请求成功\n";
        echo "响应长度: " . strlen($response) . " 字节\n";
        echo "响应内容预览:\n";
        echo substr($response, 0, 200) . "...\n";
    }
} catch (Exception $e) {
    echo "❌ 异常: " . $e->getMessage() . "\n";
}

echo "=== 测试完成 ===\n";
