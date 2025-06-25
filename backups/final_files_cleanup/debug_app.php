<?php
// 调试完整应用
require_once __DIR__ . '/vendor/autoload.php';

use AlingAi\Core\AlingAiProApplication;
use Slim\App;
use Slim\Factory\AppFactory;
use Slim\Routing\RouteCollectorProxy;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

try {
    echo "Creating application...\n";
    
    // 创建基础 Slim 应用
    $app = AppFactory::create();
    
    echo "App created successfully\n";
    
    // 直接添加 API 路由
    $app->group('/api', function (RouteCollectorProxy $group) {
        $group->get('', function (ServerRequestInterface $request, ResponseInterface $response) {
            $info = [
                'message' => 'Direct API Test',
                'status' => 'working',
                'timestamp' => date('Y-m-d H:i:s'),
                'note' => 'This bypasses CompleteRouterIntegration'
            ];
            $response->getBody()->write(json_encode($info, JSON_PRETTY_PRINT));
            return $response->withHeader('Content-Type', 'application/json');
        });
    });
    
    echo "Routes registered directly\n";
    echo "Running app...\n";
    
    // 运行应用
    $app->run();
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
