<?php
/**
 * 调试 renderPage 方法的详细执行过程
 */

// 启用错误报告
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    echo "=== Debug renderPage Method Step by Step ===\n";
    
    require_once __DIR__ . '/vendor/autoload.php';
    
    // 创建必要的依赖项
    $logger = new \Monolog\Logger('test');
    $logger->pushHandler(new \Monolog\Handler\StreamHandler('php://stdout', \Monolog\Logger::DEBUG));
    
    $dbService = new \AlingAi\Services\FileStorageService($logger);
    $cacheService = new \AlingAi\Services\CacheService($logger);
    
    // 创建测试控制器并手动实现 renderPage 的调试版本
    class DebugController extends \AlingAi\Controllers\BaseController {
        public function debugRenderPage($template, $data = []) {
            echo "=== Debug renderPage execution ===\n";
            
            $psr17Factory = new \Nyholm\Psr7\Factory\Psr17Factory();
            $response = $psr17Factory->createResponse();
            echo "✓ Response object created\n";
            
            try {
                // 构建模板文件路径
                $templatePath = __DIR__ . '/../../public/' . $template;
                echo "Template path: $templatePath\n";
                
                // 检查模板是否存在
                if (!file_exists($templatePath)) {
                    echo "✗ Template not found: {$templatePath}\n";
                    return $response->withStatus(404);
                }
                echo "✓ Template file exists\n";
                
                // 读取模板内容
                $content = file_get_contents($templatePath);
                echo "✓ Template content read: " . strlen($content) . " characters\n";
                
                // 简单的模板变量替换
                foreach ($data as $key => $value) {
                    if (is_string($value) || is_numeric($value)) {
                        $content = str_replace('{{' . $key . '}}', (string)$value, $content);
                        echo "✓ Replaced {{$key}} with: " . substr((string)$value, 0, 50) . "\n";
                    } elseif (is_array($value) || is_object($value)) {
                        $jsonValue = json_encode($value);
                        $content = str_replace('{{' . $key . '}}', $jsonValue, $content);
                        echo "✓ Replaced {{$key}} with JSON: " . substr($jsonValue, 0, 50) . "...\n";
                    }
                }
                
                // 替换默认变量
                $content = str_replace('{{app_name}}', 'AlingAi Pro', $content);
                $content = str_replace('{{version}}', '2.0.0', $content);
                $content = str_replace('{{timestamp}}', date('c'), $content);
                echo "✓ Default variables replaced\n";
                
                echo "Final content length: " . strlen($content) . " characters\n";
                
                // 写入响应体
                echo "✓ Writing to response body...\n";
                $response->getBody()->write($content);
                echo "✓ Content written to response body\n";
                
                // 设置响应头
                $response = $response->withHeader('Content-Type', 'text/html; charset=utf-8');
                echo "✓ Content-Type header set\n";
                
                return $response;
                
            } catch (\Exception $e) {
                echo "✗ Error in renderPage: " . $e->getMessage() . "\n";
                echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
                throw $e;
            }
        }
    }
    
    $debugController = new DebugController($dbService, $cacheService);
    echo "✓ DebugController created\n";
    
    // 测试渲染
    echo "\n=== Testing debug renderPage ===\n";
    $result = $debugController->debugRenderPage('index.html', [
        'title' => 'Test Title',
        'user' => 'Test User'
    ]);
    
    echo "\n=== Final Result ===\n";
    echo "Response status: " . $result->getStatusCode() . "\n";
    $finalBody = $result->getBody()->getContents();
    echo "Final response body length: " . strlen($finalBody) . " characters\n";
    
    if (strlen($finalBody) > 0) {
        echo "Success! Response preview: " . substr($finalBody, 0, 500) . "...\n";
    } else {
        echo "Still empty! Investigating PSR-7 response body...\n";
        
        // 测试 PSR-7 响应体的行为
        echo "\n=== PSR-7 Response Body Debug ===\n";
        $psr17Factory = new \Nyholm\Psr7\Factory\Psr17Factory();
        $testResponse = $psr17Factory->createResponse();
        $testContent = "Hello World Test";
        
        echo "Writing test content: '$testContent'\n";
        $testResponse->getBody()->write($testContent);
        $retrievedContent = $testResponse->getBody()->getContents();
        echo "Retrieved content: '$retrievedContent'\n";
        echo "Retrieved length: " . strlen($retrievedContent) . "\n";
        
        if (strlen($retrievedContent) === 0) {
            echo "PSR-7 response body issue detected!\n";
            
            // 尝试重新设置指针
            $testResponse->getBody()->rewind();
            $retrievedContent2 = $testResponse->getBody()->getContents();
            echo "After rewind - Retrieved content: '$retrievedContent2'\n";
        }
    }
    
} catch (\Exception $e) {
    echo "✗ Fatal error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
