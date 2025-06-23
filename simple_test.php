<?php
// 调试路由测试
require_once __DIR__ . '/vendor/autoload.php';

use Slim\App;
use Slim\Factory\AppFactory;
use Slim\Routing\RouteCollectorProxy;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

// 创建简单的Slim应用
$app = AppFactory::create();

// 测试根路由
$app->get('/', function (ServerRequestInterface $request, ResponseInterface $response) {
    $response->getBody()->write('Hello, this is root path!');
    return $response;
});

// 测试API路由组
$app->group('/api', function (RouteCollectorProxy $group) {
    $group->get('', function (ServerRequestInterface $request, ResponseInterface $response) {
        $info = [
            'message' => 'Simple API Test',
            'status' => 'working',
            'timestamp' => date('Y-m-d H:i:s')
        ];
        $response->getBody()->write(json_encode($info, JSON_PRETTY_PRINT));
        return $response->withHeader('Content-Type', 'application/json');
    });
    
    $group->get('/test', function (ServerRequestInterface $request, ResponseInterface $response) {
        $response->getBody()->write(json_encode(['test' => 'success']));
        return $response->withHeader('Content-Type', 'application/json');
    });
});

echo "Simple Slim app running...\n";
echo "Test URLs:\n";
echo "- http://localhost:8001/\n";
echo "- http://localhost:8001/api\n";
echo "- http://localhost:8001/api/test\n";

// 运行应用
$app->run();
