<?php
/**
 * AlingAi Pro - User API Endpoint
 * 
 * 用户API端点，提供用户资源的RESTful接口
 * 
 * @version 1.0.0
 * @author AlingAi Team
 */

// 设置内容类型为JSON
header('Content-Type: application/json');

// 定义API根目录
define('API_ROOT', dirname(__DIR__, 4));

// 引入必要的文件
require_once API_ROOT . '/src/Controllers/Api/UserApiController.php';

// 获取请求方法
$method = $_SERVER['REQUEST_METHOD'];

// 获取请求URI
$requestUri = $_SERVER['REQUEST_URI'];
$path = parse_url($requestUri, PHP_URL_PATH);
$segments = explode('/', trim($path, '/'));

// 获取用户ID (如果在路径中)
$userId = null;
foreach ($segments as $i => $segment) {
    if ($segment === 'user' && isset($segments[$i + 1])) {
        $userId = $segments[$i + 1];
        break;
    }
}

// 创建控制器实例
$controller = new \AlingAi\Controllers\Api\UserApiController();

// 处理请求
try {
    // 创建请求和响应对象 (简化版)
    $request = new class {
        public function getQueryParams() {
            return $_GET;
        }
        
        public function getParsedBody() {
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);
            return $data ?: [];
        }
    };
    
    $response = new class {
        private $statusCode = 200;
        private $body = '';
        
        public function withJson($data, $status = null) {
            $this->body = json_encode($data);
            if ($status !== null) {
                $this->statusCode = $status;
            }
            return $this;
        }
        
        public function withStatus($code) {
            $this->statusCode = $code;
            return $this;
        }
        
        public function getStatusCode() {
            return $this->statusCode;
        }
        
        public function getBody() {
            return $this->body;
        }
    };
    
    // 根据请求方法和路径调用相应的控制器方法
    $result = null;
    
    if ($method === 'GET') {
        if ($userId === 'me') {
            // 获取当前用户信息
            $result = $controller->me($request, $response);
        } else if ($userId) {
            // 获取特定用户
            $args = ['id' => $userId];
            $result = $controller->show($request, $response, $args);
        } else {
            // 获取所有用户
            $result = $controller->index($request, $response);
        }
    } else if ($method === 'POST') {
        if ($userId === 'verify-email') {
            // 验证邮箱
            $result = $controller->verifyEmail($request, $response);
        } else if ($userId === 'update-password') {
            // 更新密码
            $result = $controller->updatePassword($request, $response);
        } else if ($userId === 'tokens') {
            // 生成令牌
            $result = $controller->generateToken($request, $response);
        } else {
            // 创建用户
            $result = $controller->create($request, $response);
        }
    } else if ($method === 'PUT' && $userId) {
        // 更新用户
        $args = ['id' => $userId];
        $result = $controller->update($request, $response, $args);
    } else if ($method === 'DELETE') {
        if ($userId === 'tokens') {
            // 撤销令牌
            $result = $controller->revokeToken($request, $response);
        } else if ($userId) {
            // 删除用户
            $args = ['id' => $userId];
            $result = $controller->delete($request, $response, $args);
        }
    }
    
    // 输出响应
    if ($result) {
        http_response_code($result->getStatusCode());
        echo $result->getBody();
    } else {
        // 如果没有匹配的路由
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => '无效的API请求',
            'error' => 'Not Found'
        ]);
    }
} catch (Exception $e) {
    // 处理异常
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => '服务器内部错误',
        'error' => $e->getMessage()
    ]);
} 