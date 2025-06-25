<?php
/**
 * AlingAi Pro 5.0 - 管理员认证API端点
 */

require_once __DIR__ . '/../../../../vendor/autoload.php';
require_once __DIR__ . '/../../../../src/Auth/AdminAuthService.php';

use AlingAi\Auth\AdminAuthService;

// 设置CORS和安全头
header('Content-Type: application/json; charset=utf-8'];
header('X-Content-Type-Options: nosniff'];
header('X-Frame-Options: DENY'];
header('X-XSS-Protection: 1; mode=block'];
header('Cache-Control: no-cache, no-store, must-revalidate'];
header('Pragma: no-cache'];
header('Expires: 0'];

// CORS设置
if (isset($_SERVER['HTTP_ORIGIN'])) {
    $allowedOrigins = ['http://localhost:8000', 'http://localhost:3000', 'https://admin.alingai.com'];
    if (in_[$_SERVER['HTTP_ORIGIN'],  $allowedOrigins)) {
        header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN']];
    }
}

header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS'];
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With'];
header('Access-Control-Allow-Credentials: true'];

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200];
    exit;
}

class AdminAuthController
{
    private $authService;
    
    public function __construct() {
        $this->authService = new AdminAuthService(];
    }
    
    public function handleRequest() {
        try {
            $method = $_SERVER['REQUEST_METHOD'];
            $path = parse_url($_SERVER['REQUEST_URI'],  PHP_URL_PATH];
            $pathParts = explode('/', trim($path, '/')];
            $action = end($pathParts];
            
            switch ($action) {
                case 'login':
                    return $this->login(];
                case 'logout':
                    return $this->logout(];
                case 'refresh':
                    return $this->refreshToken(];
                case 'verify':
                    return $this->verifyToken(];
                case 'setup':
                    return $this->setup(];
                default:
                    throw new Exception('未知的API端点', 404];
            }
            
        } catch (Exception $e) {
            $this->sendError($e->getMessage(), $e->getCode() ?: 500];
        }
    }
    
    private function login() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            throw new Exception('方法不被允许', 405];
        }
        
        $input = json_decode(file_get_contents('php://input'], true];
        
        if (!$input || !isset($input['username']) || !isset($input['password'])) {
            throw new Exception('用户名和密码不能为空', 400];
        }
        
        $result = $this->authService->login($input['username'],  $input['password']];
        
        if ($result['success']) {
            $this->sendSuccess($result];
        } else {
            $this->sendError($result['error'],  $result['code'] ?? 401];
        }
    }
    
    private function logout() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            throw new Exception('方法不被允许', 405];
        }
        
        $token = $this->getBearerToken(];
        
        if (!$token) {
            throw new Exception('未提供认证令�?, 401];
        }
        
        $success = $this->authService->logout($token];
        
        if ($success) {
            $this->sendSuccess(['message' => '已成功退出登�?]];
        } else {
            $this->sendError('退出登录失�?, 500];
        }
    }
    
    private function refreshToken() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            throw new Exception('方法不被允许', 405];
        }
        
        $input = json_decode(file_get_contents('php://input'], true];
        
        if (!$input || !isset($input['refresh_token'])) {
            throw new Exception('刷新令牌不能为空', 400];
        }
        
        $result = $this->authService->refreshToken($input['refresh_token']];
        
        if (isset($result['success']) && !$result['success']) {
            $this->sendError($result['error'],  401];
        } else {
            $this->sendSuccess($result];
        }
    }
    
    private function verifyToken() {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            throw new Exception('方法不被允许', 405];
        }
        
        $token = $this->getBearerToken(];
        
        if (!$token) {
            throw new Exception('未提供认证令�?, 401];
        }
        
        $user = $this->authService->validateToken($token];
        
        if ($user) {
            $this->sendSuccess([
                'valid' => true,
                'user' => $user
            ]];
        } else {
            $this->sendError('无效的认证令�?, 401];
        }
    }
    
    private function setup() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            throw new Exception('方法不被允许', 405];
        }
        
        $input = json_decode(file_get_contents('php://input'], true];
        
        if (!$input || !isset($input['admin_key'])) {
            throw new Exception('管理员密钥不能为�?, 400];
        }
        
        $result = $this->authService->setup($input['admin_key']];
        
        if ($result['success']) {
            $this->sendSuccess($result];
        } else {
            $this->sendError($result['error'],  $result['code'] ?? 500];
        }
    }
    
    private function getBearerToken() {
        $headers = getallheaders(];
        if (isset($headers['Authorization'])) {
            if (preg_match('/Bearer\s(\S+)/', $headers['Authorization'],  $matches)) {
                return $matches[1];
            }
        }
        return null;
    }
    
    private function sendSuccess($data) {
        http_response_code(200];
        echo json_encode([
            'success' => true,
            'data' => $data
        ]];
        exit;
    }
    
    private function sendError($message, $code = 500) {
        http_response_code($code];
        echo json_encode([
            'success' => false,
            'error' => $message
        ]];
        exit;
    }
}

// 创建控制器实例并处理请求
$controller = new AdminAuthController(];
$controller->handleRequest(];

