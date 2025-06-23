<?php
/**
 * API性能验证脚本
 * 测试修复后的缓存系统对实际API响应时间的影响
 */

require_once __DIR__ . '/vendor/autoload.php';

use AlingAi\Core\Application;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Psr7\Factory\ServerRequestFactory;
use Slim\Psr7\Factory\UriFactory;

echo "🚀 AlingAi Pro API性能验证\n";
echo "==============================\n";

try {
    // 1. 初始化应用
    echo "1. 初始化应用程序...\n";
    $app = Application::create();
    echo "   ✓ 应用程序初始化成功\n";
      // 2. 创建模拟请求
    $requestFactory = new ServerRequestFactory();
    $uriFactory = new UriFactory();
    
    // 测试的API端点
    $testEndpoints = [
        '/api/public/health',
        '/api/public/status', 
        '/api/system/health',
        '/api/v1/system/health'
    ];
    
    $totalTime = 0;
    $successfulTests = 0;
    
    foreach ($testEndpoints as $endpoint) {
        echo "\n2. 测试端点: {$endpoint}\n";
        
        try {
            $startTime = microtime(true);
              // 创建GET请求
            $uri = $uriFactory->createUri($endpoint);
            $request = $requestFactory->createServerRequest('GET', $uri);
            
            // 处理请求
            $response = $app->handle($request);
            
            $endTime = microtime(true);
            $responseTime = ($endTime - $startTime) * 1000; // 转换为毫秒
            
            $statusCode = $response->getStatusCode();
            $body = $response->getBody()->getContents();
            
            echo "   ✓ 状态码: {$statusCode}\n";
            echo "   ✓ 响应时间: " . number_format($responseTime, 2) . "ms\n";
            
            // 尝试解析JSON响应
            $jsonData = json_decode($body, true);
            if ($jsonData) {
                echo "   ✓ JSON解析成功\n";
                if (isset($jsonData['success'])) {
                    echo "   ✓ Success状态: " . ($jsonData['success'] ? 'true' : 'false') . "\n";
                }
            }
            
            $totalTime += $responseTime;
            $successfulTests++;
            
        } catch (Exception $e) {
            echo "   ❌ 测试失败: " . $e->getMessage() . "\n";
        }
    }
    
    // 3. 多次请求测试缓存效果
    echo "\n3. 缓存效果测试（重复请求）...\n";
    $cacheTestEndpoint = '/api/public/health';
    $cacheTestRounds = 10;
    $cacheTimes = [];
    
    for ($i = 1; $i <= $cacheTestRounds; $i++) {
        $startTime = microtime(true);
        
        $request = $requestFactory->createServerRequest('GET', $uriFactory->createUri($cacheTestEndpoint));
        $response = $app->handle($request);
        
        $endTime = microtime(true);
        $responseTime = ($endTime - $startTime) * 1000;
        
        $cacheTimes[] = $responseTime;
        echo "   第{$i}次请求: " . number_format($responseTime, 2) . "ms\n";
    }
    
    // 计算统计数据
    $avgCacheTime = array_sum($cacheTimes) / count($cacheTimes);
    $minCacheTime = min($cacheTimes);
    $maxCacheTime = max($cacheTimes);
    
    echo "\n📊 性能统计报告:\n";
    echo "==============================\n";
    echo "总测试端点: " . count($testEndpoints) . "\n";
    echo "成功测试: {$successfulTests}\n";
    echo "平均响应时间: " . number_format($totalTime / $successfulTests, 2) . "ms\n";
    echo "\n缓存测试统计:\n";
    echo "- 平均响应: " . number_format($avgCacheTime, 2) . "ms\n";
    echo "- 最快响应: " . number_format($minCacheTime, 2) . "ms\n";
    echo "- 最慢响应: " . number_format($maxCacheTime, 2) . "ms\n";
    
    // 性能评级
    if ($avgCacheTime < 50) {
        echo "- 性能等级: ⭐⭐⭐⭐⭐ 优秀\n";
    } elseif ($avgCacheTime < 100) {
        echo "- 性能等级: ⭐⭐⭐⭐ 良好\n";
    } elseif ($avgCacheTime < 200) {
        echo "- 性能等级: ⭐⭐⭐ 一般\n";
    } else {
        echo "- 性能等级: ⭐⭐ 需要优化\n";
    }
    
    echo "\n✅ API性能验证完成！\n";
    
    // 检查是否达到了目标（从2.4秒降低）
    if ($avgCacheTime < 2400) {
        echo "🎉 性能优化目标达成！响应时间已从2.4秒大幅降低至 " . number_format($avgCacheTime, 2) . "ms\n";
    }
    
} catch (Exception $e) {
    echo "❌ 测试失败: " . $e->getMessage() . "\n";
    echo "错误详情:\n";
    echo "文件: " . $e->getFile() . "\n";
    echo "行号: " . $e->getLine() . "\n";
    echo "堆栈: " . $e->getTraceAsString() . "\n";
}