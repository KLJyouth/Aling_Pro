<?php
/**
 * AlingAi Pro - API v1 Router
 * 
 * API v1版本的主路由器，负责分发请求到各个资源端点
 * 
 * @version 1.0.0
 * @author AlingAi Team
 */

// 设置内容类型为JSON
header('Content-Type: application/json');

// 定义API根目录
define('API_ROOT', dirname(__DIR__, 3));

// 引入必要的文件
require_once API_ROOT . '/src/Controllers/Api/ModelApiController.php';
require_once API_ROOT . '/src/Controllers/Api/UserApiController.php';
require_once API_ROOT . '/src/Controllers/Api/DocumentApiController.php';

// 获取请求URI
$requestUri = $_SERVER['REQUEST_URI'];
$path = parse_url($requestUri, PHP_URL_PATH);
$segments = explode('/', trim($path, '/'));

// 确保segments数组有基本部分
if (empty($segments)) $segments = [''];
if (count($segments) < 4) {
    while (count($segments) < 4) {
        $segments[] = '';
    }
}

// 路由请求到相应的处理程序
if ($segments[0] === 'api' && $segments[1] === 'v1') {
    $resourceType = $segments[2];
    
    switch ($resourceType) {
        case 'users':
            // 用户资源
            include __DIR__ . '/user/index.php';
            break;
            
        case 'documents':
            // 文档资源
            include __DIR__ . '/document/index.php';
            break;
            
        case 'auth':
            // 认证资源
            if (file_exists(__DIR__ . '/auth/index.php')) {
                include __DIR__ . '/auth/index.php';
            } else {
                http_response_code(501);
                echo json_encode([
                    'success' => false,
                    'message' => '认证API尚未实现',
                    'error' => 'Not Implemented'
                ]);
            }
            break;
            
        case 'system':
            // 系统资源
            if (file_exists(__DIR__ . '/system/index.php')) {
                include __DIR__ . '/system/index.php';
            } else {
                http_response_code(501);
                echo json_encode([
                    'success' => false,
                    'message' => '系统API尚未实现',
                    'error' => 'Not Implemented'
                ]);
            }
            break;
            
        case '':
            // API根路径
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'message' => 'AlingAi Pro API v1',
                'version' => '1.0.0',
                'resources' => [
                    'users' => '/api/v1/users',
                    'documents' => '/api/v1/documents',
                    'auth' => '/api/v1/auth',
                    'system' => '/api/v1/system'
                ],
                'documentation' => '/docs/api'
            ]);
            break;
            
        default:
            // 未知资源
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'message' => '未找到请求的资源',
                'error' => 'Resource Not Found',
                'available_resources' => ['users', 'documents', 'auth', 'system']
            ]);
    }
} else {
    // 无效的API请求
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => '无效的API请求',
        'error' => 'Invalid API Request',
        'hint' => '请使用 /api/v1/{resource} 格式的URL'
    ]);
} 