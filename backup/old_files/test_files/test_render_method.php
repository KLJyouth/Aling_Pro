<?php
/**
 * 测试 BaseController renderPage 方法
 */

// 启用错误报告
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    echo "=== BaseController renderPage Test ===\n";
    
    require_once __DIR__ . '/vendor/autoload.php';
    
    // 创建必要的依赖项
    $logger = new \Monolog\Logger('test');
    $logger->pushHandler(new \Monolog\Handler\StreamHandler('php://stdout', \Monolog\Logger::DEBUG));
    
    $dbService = new \AlingAi\Services\FileStorageService($logger);
    $cacheService = new \AlingAi\Services\CacheService($logger);
    
    // 创建一个简单的测试控制器来访问 protected 方法
    class TestController extends \AlingAi\Controllers\BaseController {
        public function testRenderPage($template, $data = []) {
            $psr17Factory = new \Nyholm\Psr7\Factory\Psr17Factory();
            $response = $psr17Factory->createResponse();
            return $this->renderPage($response, $template, $data);
        }
    }
    
    $testController = new TestController($dbService, $cacheService);
    echo "✓ TestController created\n";
    
    // 测试渲染
    echo "\n=== Testing renderPage method ===\n";
    try {
        $result = $testController->testRenderPage('index.html', [
            'title' => 'Test Title',
            'user' => 'Test User'
        ]);
        
        echo "✓ renderPage method executed\n";
        echo "Response status: " . $result->getStatusCode() . "\n";
        
        $body = $result->getBody()->getContents();
        echo "Response body length: " . strlen($body) . " characters\n";
        
        if (strlen($body) > 0) {
            echo "Response preview: " . substr($body, 0, 500) . "...\n";
        } else {
            echo "Response body is empty!\n";
            
            // 让我们直接测试文件读取
            echo "\n=== Direct file read test ===\n";
            $templatePath = __DIR__ . '/public/index.html';
            echo "Template path: $templatePath\n";
            
            if (file_exists($templatePath)) {
                $content = file_get_contents($templatePath);
                echo "Direct file read successful: " . strlen($content) . " characters\n";
                echo "File content preview: " . substr($content, 0, 200) . "...\n";
            } else {
                echo "File does not exist!\n";
            }
        }
        
    } catch (\Exception $e) {
        echo "✗ renderPage failed: " . $e->getMessage() . "\n";
        echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
        echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    }
    
} catch (\Exception $e) {
    echo "✗ Fatal error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
