<?php
/**
 * AlingAi Pro API Router
 * 
 * Main API router file for handling all API requests and routing
 * 
 * @version 2.1.0
 * @author AlingAi Team
 */

// Define constants
define('API_START_TIME', microtime(true));
define('API_ROOT', dirname(__DIR__, 2));
define('API_VERSION', '2.1.0');

// Error reporting settings
$isProduction = (getenv('APP_ENV') === 'production');
if ($isProduction) {
    error_reporting(E_ERROR | E_WARNING | E_PARSE);
    ini_set('display_errors', '0');
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
}

// CORS Headers - Allow from any origin in development
if (!$isProduction) {
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
    header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization");
    
    // Handle preflight OPTIONS request
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        header("HTTP/1.1 200 OK");
        exit;
    }
} else {
    // In production, we'd have more specific CORS rules
    $allowedOrigins = ['https://alingai.com', 'https://api.alingai.com', 'https://admin.alingai.com'];
    $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
    
    if (in_array($origin, $allowedOrigins)) {
        header("Access-Control-Allow-Origin: $origin");
        header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
        header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization");
        
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            header("HTTP/1.1 200 OK");
            exit;
        }
    }
}

// Set JSON content type
header('Content-Type: application/json');

// Basic security headers
header("X-Content-Type-Options: nosniff");
header("X-XSS-Protection: 1; mode=block");
header("X-Frame-Options: SAMEORIGIN");
header("Referrer-Policy: strict-origin-when-cross-origin");

// Basic route handling
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$path = parse_url($requestUri, PHP_URL_PATH);
$path = ltrim($path, '/');
$segments = explode('/', $path);

// Ensure the segments array has basic parts
if (empty($segments[0])) $segments[0] = '';
if (!isset($segments[1])) $segments[1] = '';
if (!isset($segments[2])) $segments[2] = '';

/**
 * Helper function for standard API responses
 * 
 * @param bool $success Success status
 * @param string $message Response message
 * @param mixed $data Response data
 * @param int $statusCode HTTP status code
 * @return void
 */
function sendResponse($success, $message = '', $data = null, $statusCode = 200) {
    http_response_code($statusCode);
    
    $response = [
        'success' => $success,
        'message' => $message,
        'timestamp' => date('Y-m-d H:i:s'),
        'version' => API_VERSION
    ];
    
    if ($data !== null) {
        $response['data'] = $data;
    }
    
    // Add execution time in development mode
    if (getenv('APP_ENV') !== 'production') {
        $response['execution_time_ms'] = round((microtime(true) - API_START_TIME) * 1000, 2);
    }
    
    echo json_encode($response);
    exit;
}

/**
 * Validate API request method
 * 
 * @param string|array $allowedMethods Allowed method or array of methods
 * @return bool
 */
function validateMethod($allowedMethods) {
    $method = $_SERVER['REQUEST_METHOD'];
    
    if (is_array($allowedMethods)) {
        return in_array($method, $allowedMethods);
    }
    
    return $method === $allowedMethods;
}

/**
 * Get request data based on method
 * 
 * @return array
 */
function getRequestData() {
    $method = $_SERVER['REQUEST_METHOD'];
    
    if ($method === 'GET') {
        return $_GET;
    }
    
    if (in_array($method, ['POST', 'PUT', 'DELETE'])) {
        $rawData = file_get_contents('php://input');
        $jsonData = json_decode($rawData, true);
        
        if ($jsonData === null && json_last_error() !== JSON_ERROR_NONE) {
            // If not valid JSON, try to parse as form data
            parse_str($rawData, $formData);
            return $formData ?: [];
        }
        
        return $jsonData ?: [];
    }
    
    return [];
}

/**
 * Authenticate user from token
 * 
 * @return array|null User data or null if unauthorized
 */
function authenticateUser() {
    $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    $token = '';
    
    if (preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
        $token = $matches[1];
    }
    
    if (!$token) {
        return null;
    }
    
    // In production, validate token and load user from database
    // This is a simplified mock implementation
    if ($token && strpos($token, 'sample_token_') === 0) {
        return [
            'id' => 1,
            'username' => 'demo_user',
            'email' => 'demo@alingai.com',
            'role' => 'user',
            'quota' => [
                'daily_limit' => 1000,
                'used' => rand(200, 500),
                'remaining' => rand(500, 800)
            ]
        ];
    }
    
    return null;
}

// Process different API endpoints based on request path
if ($segments[0] == 'api') {
    // API version handling
    $version = $segments[1];
    $endpoint = $segments[2] ?? '';
    
    // Get user if authenticated
    $currentUser = authenticateUser();
    
    // API v1 route handling
    if ($version === 'v1') {
        switch ($endpoint) {
            case 'auth':
                $action = $segments[3] ?? '';
                
                if ($action === 'login' && validateMethod('POST')) {
                    $data = getRequestData();
                    $username = $data['username'] ?? '';
                    $password = $data['password'] ?? '';
                    
                    if ($username === 'demo' && $password === 'password') {
                        sendResponse(true, '登录成功', [
                            'token' => 'sample_token_' . time(),
                            'user' => [
                                'id' => 1,
                                'username' => 'demo_user',
                                'email' => 'demo@alingai.com'
                            ]
                        ]);
                    } else {
                        sendResponse(false, '用户名或密码错误', null, 401);
                    }
                } else if ($action === 'register' && validateMethod('POST')) {
                    $data = getRequestData();
                    // In a real app, validate and create user
                    sendResponse(true, '注册成功', ['userId' => rand(100, 999)]);
                } else {
                    sendResponse(false, '无效的认证请求', null, 400);
                }
                break;
                
            case 'users':
                if (!$currentUser) {
                    sendResponse(false, '未授权访问', null, 401);
                    break;
                }
                
                $userId = $segments[3] ?? '';
                
                if (empty($userId) && validateMethod('GET')) {
                    // List users (admin only)
                    if ($currentUser['role'] !== 'admin') {
                        sendResponse(false, '权限不足', null, 403);
                        break;
                    }
                    
                    sendResponse(true, '获取用户列表成功', [
                        'users' => [
                            ['id' => 1, 'username' => 'demo_user', 'email' => 'demo@alingai.com'],
                            ['id' => 2, 'username' => 'test_user', 'email' => 'test@alingai.com']
                        ]
                    ]);
                } else if ($userId === 'me' && validateMethod('GET')) {
                    // Get current user profile
                    sendResponse(true, '获取个人资料成功', [
                        'user' => $currentUser
                    ]);
                } else if (is_numeric($userId) && validateMethod('GET')) {
                    // Get specific user
                    sendResponse(true, '获取用户资料成功', [
                        'user' => [
                            'id' => (int)$userId,
                            'username' => 'user_' . $userId,
                            'email' => 'user' . $userId . '@alingai.com'
                        ]
                    ]);
                } else {
                    sendResponse(false, '无效的用户请求', null, 400);
                }
                break;
                
            case 'documents':
                if (!$currentUser) {
                    sendResponse(false, '未授权访问', null, 401);
                    break;
                }
                
                $documentId = $segments[3] ?? '';
                
                if (empty($documentId) && validateMethod('GET')) {
                    // List documents
                    sendResponse(true, '获取文档列表成功', [
                        'documents' => [
                            ['id' => 101, 'title' => '示例文档1', 'created_at' => date('Y-m-d H:i:s', time() - 86400)],
                            ['id' => 102, 'title' => '示例文档2', 'created_at' => date('Y-m-d H:i:s')]
                        ]
                    ]);
                } else if (empty($documentId) && validateMethod('POST')) {
                    // Create document
                    $data = getRequestData();
                    $title = $data['title'] ?? '未命名文档';
                    
                    sendResponse(true, '创建文档成功', [
                        'document' => [
                            'id' => rand(1000, 9999),
                            'title' => $title,
                            'created_at' => date('Y-m-d H:i:s')
                        ]
                    ], 201);
                } else if (is_numeric($documentId) && validateMethod('GET')) {
                    // Get specific document
                    sendResponse(true, '获取文档成功', [
                        'document' => [
                            'id' => (int)$documentId,
                            'title' => '文档 #' . $documentId,
                            'content' => '这是文档 #' . $documentId . ' 的内容',
                            'created_at' => date('Y-m-d H:i:s', time() - 86400),
                            'updated_at' => date('Y-m-d H:i:s')
                        ]
                    ]);
                } else {
                    sendResponse(false, '无效的文档请求', null, 400);
                }
                break;
                
            default:
                sendResponse(false, '未知的API端点', null, 404);
        }
    } else {
        sendResponse(false, '不支持的API版本', null, 400);
    }
} else {
    sendResponse(false, '无效的API请求', null, 404);
}

