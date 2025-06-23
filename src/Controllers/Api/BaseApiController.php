<?php

declare(strict_types=1);

namespace AlingAi\Controllers\Api;

use AlingAi\Services\SecurityService;
use AlingAi\Services\PerformanceMonitorService;
use Exception;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LoggerInterface;

/**
 * API 控制器基类
 * 
 * 提供统一的 API 响应格式、错误处理、认证验证和性能监控
 * 
 * @package AlingAi\Controllers\Api
 * @version 1.0.0
 * @since 2024-12-19
 */
abstract class BaseApiController
{
    protected SecurityService $security;
    protected PerformanceMonitorService $monitor;
    protected array $config;
    protected ?array $currentUser = null;
    protected float $requestStartTime;
    protected LoggerInterface $logger;

    public function __construct(?LoggerInterface $logger = null)
    {
        $this->requestStartTime = microtime(true);
        $this->security = new SecurityService();
        $this->monitor = new PerformanceMonitorService();
        $this->config = $this->loadConfig();
        
        // 初始化安全检查
        $this->initializeSecurity();
        
        // 记录API请求
        $this->logApiRequest();

        $this->logger = $logger ?? $this->createDefaultLogger();
    }

    /**
     * 加载配置
     */
    protected function loadConfig(): array
    {
        // 首先尝试加载环境配置
        try {
            require_once __DIR__ . '/../../Config/EnvConfig.php';
            return \AlingAi\Config\EnvConfig::load();
        } catch (Exception $e) {
            // 回退到默认配置
            $configPath = __DIR__ . '/../../Config/config.php';
            return file_exists($configPath) ? require $configPath : [];
        }
    }

    /**
     * 初始化安全检查
     */
    protected function initializeSecurity(): void
    {
        try {
            // 基本安全检查
            $this->security->validateRequest();
            
            // IP白名单检查（如果配置了）
            if (isset($this->config['security']['ip_whitelist'])) {
                $this->security->checkIpWhitelist($this->config['security']['ip_whitelist']);
            }
              // 速率限制检查（仅在Web环境下）
            if (php_sapi_name() !== 'cli' && isset($_SERVER['REMOTE_ADDR'])) {
                if (!$this->security->checkRateLimit($_SERVER['REMOTE_ADDR'])) {
                    $this->sendErrorResponse('Rate limit exceeded', 429);
                    exit;
                }
            }
            
        } catch (Exception $e) {
            $this->monitor->logError('Security initialization failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $this->sendErrorResponse('Security check failed', 403);
            exit;
        }
    }

    /**
     * 记录API请求
     */
    protected function logApiRequest(): void
    {
        $this->monitor->recordApiRequest([
            'endpoint' => $_SERVER['REQUEST_URI'] ?? '',
            'method' => $_SERVER['REQUEST_METHOD'] ?? '',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'ip' => $_SERVER['REMOTE_ADDR'] ?? '',
            'timestamp' => date('Y-m-d H:i:s'),
            'start_time' => $this->requestStartTime
        ]);
    }

    /**
     * 验证认证
     */
    protected function requireAuth(): bool
    {
        try {
            $token = $this->getBearerToken();
            if (!$token) {
                $this->sendErrorResponse('Authentication required', 401);
                return false;
            }

            $user = $this->security->validateJwtToken($token);
            if (!$user) {
                $this->sendErrorResponse('Invalid or expired token', 401);
                return false;
            }

            $this->currentUser = $user;
            return true;

        } catch (Exception $e) {
            $this->monitor->logError('Authentication failed', [
                'error' => $e->getMessage(),
                'ip' => $_SERVER['REMOTE_ADDR'] ?? ''
            ]);
            $this->sendErrorResponse('Authentication failed', 401);
            return false;
        }
    }

    /**
     * 验证管理员权限
     */
    protected function requireAdmin(): bool
    {
        if (!$this->requireAuth()) {
            return false;
        }

        if (!isset($this->currentUser['role']) || $this->currentUser['role'] !== 'admin') {
            $this->sendErrorResponse('Admin privileges required', 403);
            return false;
        }

        return true;
    }

    /**
     * 获取Bearer Token
     */
    protected function getBearerToken(): ?string
    {
        $headers = getallheaders();
        
        if (isset($headers['Authorization'])) {
            $authHeader = $headers['Authorization'];
            if (strpos($authHeader, 'Bearer ') === 0) {
                return substr($authHeader, 7);
            }
        }
        
        return null;
    }

    /**
     * 获取请求数据
     */
    protected function getRequestData(Request $request): array
    {
        $contentType = $request->getHeaderLine('Content-Type');
        
        if (strpos($contentType, 'application/json') !== false) {
            $contents = $request->getBody()->getContents();
            
            if ($contents) {
                return json_decode($contents, true) ?? [];
            }
        }
        
        return $request->getParsedBody() ?? [];
    }

    /**
     * 验证请求数据
     */
    protected function validateRequestData(array $data, array $rules): array
    {
        $errors = [];
        $validated = [];
        
        foreach ($rules as $field => $rule) {
            $value = $data[$field] ?? null;
            
            // 必填验证
            if (isset($rule['required']) && $rule['required'] && empty($value)) {
                $errors[$field] = "Field {$field} is required";
                continue;
            }
            
            // 如果值为空且不是必填，跳过其他验证
            if (empty($value) && (!isset($rule['required']) || !$rule['required'])) {
                continue;
            }
            
            // 类型验证
            if (isset($rule['type'])) {
                switch ($rule['type']) {
                    case 'email':
                        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                            $errors[$field] = "Field {$field} must be a valid email";
                        }
                        break;
                    case 'numeric':
                        if (!is_numeric($value)) {
                            $errors[$field] = "Field {$field} must be numeric";
                        }
                        break;
                    case 'string':
                        if (!is_string($value)) {
                            $errors[$field] = "Field {$field} must be a string";
                        }
                        break;
                }
            }
            
            // 长度验证
            if (isset($rule['min_length']) && strlen($value) < $rule['min_length']) {
                $errors[$field] = "Field {$field} must be at least {$rule['min_length']} characters";
            }
            
            if (isset($rule['max_length']) && strlen($value) > $rule['max_length']) {
                $errors[$field] = "Field {$field} must not exceed {$rule['max_length']} characters";
            }
            
            // 如果通过验证，添加到已验证数据
            if (!isset($errors[$field])) {
                $validated[$field] = $this->security->sanitizeInput($value);
            }
        }
        
        if (!empty($errors)) {
            throw new InvalidArgumentException(json_encode($errors));
        }
        
        return $validated;
    }

    /**
     * 发送成功响应
     */
    protected function sendSuccess(Response $response, $data = null, string $message = 'Success', int $status = 200): Response
    {
        $payload = [
            'success' => true,
            'message' => $message,
            'data' => $data
        ];
        
        $response->getBody()->write(json_encode($payload));
        
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($status);
    }

    /**
     * 发送错误响应
     */
    protected function sendErrorResponse(string $message, int $statusCode = 400, $details = null): void
    {
        $response = [
            'success' => false,
            'error' => $message,
            'timestamp' => date('c'),
            'execution_time' => round((microtime(true) - $this->requestStartTime) * 1000, 2) . 'ms'
        ];

        if ($details !== null) {
            $response['details'] = $details;
        }

        // 记录错误响应
        $this->monitor->recordApiResponse($statusCode, strlen(json_encode($response)));
        $this->monitor->logError($message, [
            'status_code' => $statusCode,
            'details' => $details,
            'endpoint' => $_SERVER['REQUEST_URI'] ?? '',
            'method' => $_SERVER['REQUEST_METHOD'] ?? ''
        ]);

        $this->sendJsonResponse($response, $statusCode);
    }

    /**
     * 发送JSON响应
     */
    protected function sendJsonResponse(array $data, int $statusCode = 200): void
    {
        // 设置响应头
        header('Content-Type: application/json');
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: DENY');
        header('X-XSS-Protection: 1; mode=block');
        
        // CORS 头
        if (isset($this->config['cors']['enabled']) && $this->config['cors']['enabled']) {
            $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
            $allowedOrigins = $this->config['cors']['allowed_origins'] ?? ['*'];
            
            if (in_array('*', $allowedOrigins) || in_array($origin, $allowedOrigins)) {
                header("Access-Control-Allow-Origin: {$origin}");
            }
            
            header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
            header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
            header('Access-Control-Allow-Credentials: true');
        }
        
        http_response_code($statusCode);
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    /**
     * 处理OPTIONS预检请求
     */
    protected function handleOptionsRequest(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            $this->sendJsonResponse(['message' => 'OK'], 200);
        }
    }

    /**
     * 分页参数处理
     */
    protected function getPaginationParams(Request $request): array
    {
        $queryParams = $request->getQueryParams();
        
        return [
            'page' => max(1, (int) ($queryParams['page'] ?? 1)),
            'limit' => min(100, max(1, (int) ($queryParams['limit'] ?? 20))),
            'offset' => 0
        ];
    }

    /**
     * 构建分页响应
     */
    protected function buildPaginatedResponse(array $data, int $total, array $pagination): array
    {
        $page = $pagination['page'];
        $limit = $pagination['limit'];
        $totalPages = ceil($total / $limit);

        return [
            'data' => $data,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $limit,
                'total' => $total,
                'total_pages' => $totalPages,
                'has_next' => $page < $totalPages,
                'has_prev' => $page > 1
            ]
        ];
    }

    /**
     * 获取当前用户
     */
    protected function getCurrentUser(): ?array
    {
        return $this->currentUser;
    }

    /**
     * 检查权限
     */
    protected function hasPermission(string $permission): bool
    {
        if (!$this->currentUser) {
            return false;
        }

        $userPermissions = $this->currentUser['permissions'] ?? [];
        return in_array($permission, $userPermissions) || 
               $this->currentUser['role'] === 'admin';
    }

    /**
     * 获取数据库连接
     */
    protected function getDatabase(): \PDO
    {
        try {
            if (isset($this->config['database']['engine']) && $this->config['database']['engine'] === 'sqlite') {
                // SQLite 数据库
                $dbPath = $this->config['database']['path'];
                $dsn = "sqlite:" . $dbPath;
                
                // 确保数据库目录存在
                $dbDir = dirname($dbPath);
                if (!is_dir($dbDir)) {
                    mkdir($dbDir, 0755, true);
                }
                
                $pdo = new \PDO($dsn, null, null, [
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                    \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC
                ]);
                
                // 创建基本表结构（如果不存在）
                $this->createTables($pdo);
                
            } else {
                // MySQL 数据库
                $dsn = "mysql:host={$this->config['database']['host']};dbname={$this->config['database']['name']};charset=utf8mb4";
                $pdo = new \PDO(
                    $dsn,
                    $this->config['database']['user'],
                    $this->config['database']['password'],
                    [
                        \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                        \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                        \PDO::ATTR_EMULATE_PREPARES => false
                    ]
                );
            }
            
            // 记录数据库连接
            if (method_exists($this->monitor, 'recordDatabaseConnection')) {
                $this->monitor->recordDatabaseConnection();
            }
            
            return $pdo;
            
        } catch (\PDOException $e) {
            $this->monitor->logError('Database connection failed', [
                'error' => $e->getMessage()
            ]);
            throw new Exception('Database connection failed');
        }
    }

    /**
     * 构建成功响应数组 (不直接发送)
     */
    protected function success($data = null, string $message = 'Success', int $statusCode = 200): array
    {
        $response = [
            'success' => true,
            'message' => $message,
            'timestamp' => date('c'),
            'execution_time' => round((microtime(true) - $this->requestStartTime) * 1000, 2) . 'ms'
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        // 记录成功响应
        $this->monitor->recordApiResponse($statusCode, strlen(json_encode($response)));

        return $response;
    }

    /**
     * 构建错误响应数组 (不直接发送)
     */
    protected function error(string $message, $details = null, int $statusCode = 400): array
    {
        $response = [
            'success' => false,
            'error' => $message,
            'timestamp' => date('c'),
            'execution_time' => round((microtime(true) - $this->requestStartTime) * 1000, 2) . 'ms'
        ];

        if ($details !== null) {
            $response['details'] = $details;
        }

        // 记录错误响应
        $this->monitor->recordApiResponse($statusCode, strlen(json_encode($response)));

        return $response;
    }

    /**
     * 创建基本数据库表结构
     */
    private function createTables(\PDO $pdo): void
    {
        // 用户表
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                email VARCHAR(255) UNIQUE NOT NULL,
                password VARCHAR(255) NOT NULL,
                username VARCHAR(255),
                name VARCHAR(255),
                role VARCHAR(50) DEFAULT 'user',
                status VARCHAR(20) DEFAULT 'active',
                avatar VARCHAR(255),
                permissions TEXT DEFAULT '[]',
                last_login_at DATETIME,
                login_count INTEGER DEFAULT 0,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                deleted_at DATETIME NULL
            )
        ");
        
        // 聊天会话表
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS chat_sessions (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NOT NULL,
                title VARCHAR(255),
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id)
            )
        ");
        
        // 聊天消息表
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS chat_messages (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                session_id INTEGER NOT NULL,
                role VARCHAR(20) NOT NULL,
                content TEXT NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (session_id) REFERENCES chat_sessions(id)
            )
        ");
        
        // 插入默认测试用户（如果不存在）
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
        $stmt->execute(['test@alingai.com']);
        if ($stmt->fetchColumn() == 0) {
            $hashedPassword = password_hash('test123456', PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("INSERT INTO users (email, password, username, name, role, status) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute(['test@alingai.com', $hashedPassword, 'testuser', 'Test User', 'user', 'active']);
        }
    }

    /**
     * 析构函数 - 记录请求完成时间
     */
    public function __destruct()
    {
        $executionTime = microtime(true) - $this->requestStartTime;
        $this->monitor->recordRequestCompletion($executionTime);
    }

    /**
     * 创建默认日志记录器
     */
    private function createDefaultLogger(): LoggerInterface
    {
        return new class implements LoggerInterface {
            public function emergency($message, array $context = []): void {}
            public function alert($message, array $context = []): void {}
            public function critical($message, array $context = []): void {}
            public function error($message, array $context = []): void {}
            public function warning($message, array $context = []): void {}
            public function notice($message, array $context = []): void {}
            public function info($message, array $context = []): void {}
            public function debug($message, array $context = []): void {}
            public function log($level, $message, array $context = []): void {}
        };
    }
    
    /**
     * 记录API响应 (新增方法，修复签名不匹配问题)
     */
    protected function recordApiResponse(array $responseData, ?Request $request = null): void
    {
        $this->monitor->recordApiResponse([
            'endpoint' => $_SERVER['REQUEST_URI'] ?? '',
            'status_code' => $responseData['status_code'] ?? 200,
            'processing_time' => microtime(true) - $this->requestStartTime,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * 记录API调用日志
     */
    protected function logApiCall(Request $request, string $action, array $context = []): void
    {
        $this->logger->info("API调用: {$action}", array_merge([
            'method' => $request->getMethod(),
            'path' => $request->getUri()->getPath(),
            'ip' => $request->getServerParams()['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $request->getHeaderLine('User-Agent')
        ], $context));
    }

    /**
     * 记录API错误
     */
    protected function logApiError(Request $request, string $action, \Throwable $error): void
    {
        $this->logger->error("API Error: {$action}", [
            'error' => $error->getMessage(),
            'trace' => $error->getTraceAsString(),
            'controller' => get_class($this),
            'uri' => $request->getUri()->getPath(),
            'method' => $request->getMethod()
        ]);
    }
}
