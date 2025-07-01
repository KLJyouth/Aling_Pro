<?php
/**
 * AlingAi Pro API入口文件
 * 
 * 这是API请求的主要入口点，负责处理所有API请求
 */

// 定义常量
define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH', ROOT_PATH . '/app');
define('CONFIG_PATH', ROOT_PATH . '/config');
define('STORAGE_PATH', ROOT_PATH . '/storage');

// 设置请求的内容类型为JSON
header('Content-Type: application/json');

// 加载配置
$configFile = CONFIG_PATH . '/config.php';
if (file_exists($configFile)) {
    $config = require_once $configFile;
} else {
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'message' => '配置文件未找到',
        'code' => 500
    ]);
    exit;
}

// 错误处理设置
if (isset($config['app']['debug']) && $config['app']['debug'] === true) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// 设置时区
date_default_timezone_set($config['app']['timezone'] ?? 'Asia/Shanghai');

// 处理CORS请求
if (isset($_SERVER['HTTP_ORIGIN'])) {
    // 允许的域名列表
    $allowedOrigins = $config['api']['allowed_origins'] ?? ['*'];
    $origin = $_SERVER['HTTP_ORIGIN'];
    
    if (in_array('*', $allowedOrigins) || in_array($origin, $allowedOrigins)) {
        header("Access-Control-Allow-Origin: {$origin}");
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Max-Age: 86400');    // 缓存1天
    }
}

// 处理预检请求
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'])) {
        header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
    }
    
    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'])) {
        header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
    }
    
    exit(0);
}

// 获取API版本和请求路径
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$requestUri = parse_url($requestUri, PHP_URL_PATH);

// 提取API版本和路径
$apiPattern = '#^/api(?:/v([0-9]+))?(/.*)?$#';
if (preg_match($apiPattern, $requestUri, $matches)) {
    $version = $matches[1] ?? '1'; // 默认为版本1
    $path = $matches[2] ?? '/';
} else {
    http_response_code(404);
    echo json_encode([
        'error' => true,
        'message' => '无效的API请求路径',
        'code' => 404
    ]);
    exit;
}

// 获取请求方法
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

// 加载API路由器
$apiRouterFile = ROOT_PATH . '/routes/api_routes.php';
if (file_exists($apiRouterFile)) {
    $apiRoutes = require_once $apiRouterFile;
} else {
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'message' => 'API路由文件未找到',
        'code' => 500
    ]);
    exit;
}

// 构造路由键
$routeKey = "{$method}:{$version}:{$path}";
$routeFound = false;

// 尝试精确匹配
if (isset($apiRoutes[$routeKey])) {
    $handler = $apiRoutes[$routeKey];
    $routeFound = true;
} else {
    // 尝试使用正则表达式匹配动态路由
    foreach ($apiRoutes as $pattern => $handlerInfo) {
        // 分离HTTP方法、版本和路径模式
        list($routeMethod, $routeVersion, $routePath) = explode(':', $pattern, 3);
        
        // 检查方法和版本是否匹配
        if ($routeMethod === $method && $routeVersion === $version) {
            // 将路径模式转换为正则表达式
            $routeRegex = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^/]+)', $routePath);
            $routeRegex = '#^' . $routeRegex . '$#';
            
            if (preg_match($routeRegex, $path, $matches)) {
                $handler = $handlerInfo;
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                $routeFound = true;
                break;
            }
        }
    }
}

// 处理路由
if ($routeFound) {
    try {
        // 解析请求体
        $requestBody = file_get_contents('php://input');
        $requestData = [];
        
        if (!empty($requestBody)) {
            $requestData = json_decode($requestBody, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('无效的JSON请求数据');
            }
        }
        
        // 处理请求
        list($controllerName, $methodName) = explode('@', $handler);
        $controllerFile = APP_PATH . '/Controllers/Api/' . $controllerName . '.php';
        
        if (!file_exists($controllerFile)) {
            throw new Exception("API控制器文件不存在: {$controllerFile}");
        }
        
        require_once $controllerFile;
        $controllerClass = "\\App\\Controllers\\Api\\{$controllerName}";
        
        if (!class_exists($controllerClass)) {
            throw new Exception("API控制器类不存在: {$controllerClass}");
        }
        
        $controller = new $controllerClass();
        
        if (!method_exists($controller, $methodName)) {
            throw new Exception("API方法不存在: {$methodName}");
        }
        
        // 执行控制器方法
        $response = $controller->$methodName($requestData, $params ?? []);
        
        // 输出JSON响应
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        
    } catch (Exception $e) {
        // 处理异常
        http_response_code(500);
        echo json_encode([
            'error' => true,
            'message' => $e->getMessage(),
            'code' => $e->getCode() ?: 500
        ]);
    }
} else {
    // 路由未找到
    http_response_code(404);
    echo json_encode([
        'error' => true,
        'message' => '未找到请求的API接口',
        'code' => 404
    ]);
} 