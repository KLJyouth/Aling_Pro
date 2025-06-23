<?php
/**
 * AlingAi Pro - 基础控制器
 * 提供所有控制器的基础功能和通用方法
 * 
 * @package AlingAi\Pro\Controllers
 * @version 2.0.0
 * @author AlingAi Team
 * @created 2024-12-19
 */

declare(strict_types=1);

namespace AlingAi\Controllers;

use AlingAi\Services\{DatabaseServiceInterface, CacheService};
use AlingAi\Utils\Logger;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

abstract class BaseController
{
    protected DatabaseServiceInterface $db;
    protected CacheService $cache;
    protected $logger;
    protected $view;
    
    public function __construct(
        DatabaseServiceInterface $db,
        CacheService $cache
    ) {
        $this->db = $db;
        $this->cache = $cache;
        $this->logger = new Logger();
        $this->view = new \stdClass(); // 临时占位，稍后实现模板引擎
    }
      /**
     * 创建JSON响应
     */
    protected function jsonResponse(
        ResponseInterface $response,
        array $data,
        int $status = 200,
        array $headers = []
    ): ResponseInterface {
        $payload = json_encode([
            'success' => true,
            'data' => $data,
            'timestamp' => date('c')
        ]);
        
        $response->getBody()->write($payload);
        $response = $response->withStatus($status)->withHeader('Content-Type', 'application/json');
        
        foreach ($headers as $name => $value) {
            $response = $response->withHeader($name, $value);
        }
          return $response;
    }
      /**
     * 获取请求的JSON数据
     */
    protected function getJsonData($request): array
    {
        if (is_array($request)) {
            return $request;
        }
        
        // 如果是PSR-7请求对象
        if (method_exists($request, 'getBody')) {
            $body = $request->getBody()->getContents();
            return json_decode($body, true) ?: [];
        }
        
        // 如果是其他类型的请求
        if (method_exists($request, 'getParsedBody')) {
            return $request->getParsedBody() ?: [];
        }
        
        // 直接从php://input读取
        $input = file_get_contents('php://input');
        return json_decode($input, true) ?: [];
    }
      /**
     * 创建成功响应 (PSR-7)
     */
    protected function successResponse(
        \Psr\Http\Message\ResponseInterface $response,
        $data = null,
        string $message = 'Success',
        int $status = 200
    ): \Psr\Http\Message\ResponseInterface {
        $responseData = [
            'success' => true,
            'message' => $message,
            'timestamp' => date('c')
        ];
        
        if ($data !== null) {
            $responseData['data'] = $data;
        }
        
        $json = json_encode($responseData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $response->getBody()->write($json);
        
        return $response
            ->withHeader('Content-Type', 'application/json; charset=utf-8')
            ->withStatus($status);
    }
    
    /**
     * 创建错误响应 (PSR-7)
     */
    protected function errorResponse(
        \Psr\Http\Message\ResponseInterface $response,
        string $message = 'Error',
        int $status = 400,
        $details = null
    ): \Psr\Http\Message\ResponseInterface {
        $responseData = [
            'success' => false,
            'error' => $message,
            'timestamp' => date('c')
        ];
        
        if ($details !== null) {
            $responseData['details'] = $details;
        }
        
        $json = json_encode($responseData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $response->getBody()->write($json);
        
        return $response
            ->withHeader('Content-Type', 'application/json; charset=utf-8')
            ->withStatus($status);
    }
    
    /**
     * 验证请求参数
     */
    protected function validateRequest(array $data, array $rules): array
    {
        $errors = [];
        
        foreach ($rules as $field => $rule) {
            $value = $data[$field] ?? null;
            
            if (strpos($rule, 'required') !== false && empty($value)) {
                $errors[$field] = "字段 {$field} 是必需的";
                continue;
            }
            
            if (!empty($value)) {
                if (strpos($rule, 'email') !== false && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $errors[$field] = "字段 {$field} 必须是有效的邮箱地址";
                }
                
                if (preg_match('/min:(\d+)/', $rule, $matches)) {
                    $min = (int)$matches[1];
                    if (strlen($value) < $min) {
                        $errors[$field] = "字段 {$field} 最少需要 {$min} 个字符";
                    }
                }
                
                if (preg_match('/max:(\d+)/', $rule, $matches)) {
                    $max = (int)$matches[1];
                    if (strlen($value) > $max) {
                        $errors[$field] = "字段 {$field} 最多允许 {$max} 个字符";
                    }
                }
            }
        }
        
        return $errors;
    }
    
    /**
     * 获取客户端IP地址
     */
    protected function getClientIp($request = null): string
    {
        if (isset($_SERVER['HTTP_CF_CONNECTING_IP'])) {
            return $_SERVER['HTTP_CF_CONNECTING_IP'];
        }
        
        $headers = [
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR'
        ];
        
        foreach ($headers as $header) {
            if (!empty($_SERVER[$header])) {
                $ip = $_SERVER[$header];
                if (strpos($ip, ',') !== false) {
                    $ip = trim(explode(',', $ip)[0]);
                }
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    }
    
    /**
     * 获取用户代理
     */
    protected function getUserAgent($request = null): string
    {
        return $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
    }
    
    /**
     * 记录操作日志
     */
    protected function logAction(string $action, array $details = []): void
    {
        $this->logger->info($action, array_merge($details, [
            'controller' => static::class,
            'timestamp' => date('c')
        ]));
    }
      /**
     * 处理异常
     */
    protected function handleException(\Exception $e, ResponseInterface $response): ResponseInterface
    {
        $this->logger->error('控制器异常', [
            'controller' => static::class,
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]);
        
        if ($e instanceof \InvalidArgumentException) {
            return $this->errorResponse($response, $e->getMessage(), 400);
        } else {
            return $this->errorResponse($response, '服务器内部错误', 500);
        }
    }
    
    /**
     * 获取请求体数据
     */
    protected function getRequestData($request = null): array
    {
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            return $_POST ?: [];
        }
        
        return is_array($data) ? $data : [];
    }
    
    /**
     * 获取查询参数
     */
    protected function getQueryParams($request = null): array
    {
        return $_GET ?: [];
    }
    
    /**
     * 获取当前认证用户
     */
    protected function getCurrentUser($request = null): ?array
    {
        // 从会话或JWT中获取用户信息
        if (isset($_SESSION['auth_user'])) {
            return $_SESSION['auth_user'];
        }
        return null;
    }
    
    /**
     * 检查用户权限
     */
    protected function hasPermission(string $permission, $request = null): bool
    {
        $user = $this->getCurrentUser($request);
        if (!$user) {
            return false;
        }
        
        $userPermissions = $user['permissions'] ?? [];
        return in_array($permission, $userPermissions) || in_array('*', $userPermissions);
    }
    
    /**
     * 验证必需参数
     */
    protected function validateRequired(array $data, array $required): array
    {
        $missing = [];
        foreach ($required as $field) {
            if (!isset($data[$field]) || $data[$field] === '' || $data[$field] === null) {
                $missing[] = $field;
            }
        }
        return $missing;
    }
    
    /**
     * 分页处理
     */
    protected function getPaginationParams($request = null): array
    {
        $queryParams = $this->getQueryParams($request);
        
        $page = max(1, (int) ($queryParams['page'] ?? 1));
        $limit = max(1, min(100, (int) ($queryParams['limit'] ?? 20)));
        $offset = ($page - 1) * $limit;
        
        return [
            'page' => $page,
            'limit' => $limit,
            'offset' => $offset
        ];
    }
      /**
     * 创建分页响应
     */
    protected function paginatedResponse(
        ResponseInterface $response,
        array $data,
        int $total,
        array $pagination
    ): ResponseInterface {
        $totalPages = ceil($total / $pagination['limit']);
        
        $responseData = [
            'items' => $data,
            'pagination' => [
                'current_page' => $pagination['page'],
                'per_page' => $pagination['limit'],
                'total' => $total,
                'total_pages' => $totalPages,
                'has_next' => $pagination['page'] < $totalPages,
                'has_prev' => $pagination['page'] > 1
            ]
        ];
        
        return $this->successResponse($response, $responseData);
    }
      /**
     * 渲染HTML页面
     */
    protected function renderPage(
        ResponseInterface $response,
        string $template,
        array $data = []
    ): ResponseInterface {
        try {
            // 构建模板文件路径 - 修复路径问题
            $templatePath = dirname(__DIR__, 2) . '/public/' . $template;
            
            // 检查模板是否存在
            if (!file_exists($templatePath)) {
                $this->logger->error("Template not found: {$templatePath}");
                return $this->renderErrorPage($response, 404, '页面未找到');
            }
            
            // 读取模板内容
            $content = file_get_contents($templatePath);
            
            // 简单的模板变量替换
            foreach ($data as $key => $value) {
                if (is_string($value) || is_numeric($value)) {
                    $content = str_replace('{{' . $key . '}}', (string)$value, $content);
                } elseif (is_array($value) || is_object($value)) {
                    $content = str_replace('{{' . $key . '}}', json_encode($value), $content);
                }
            }
            
            // 替换默认变量
            $content = str_replace('{{app_name}}', 'AlingAi Pro', $content);
            $content = str_replace('{{version}}', '2.0.0', $content);
            $content = str_replace('{{timestamp}}', date('c'), $content);
            
            $response->getBody()->write($content);
            return $response->withHeader('Content-Type', 'text/html; charset=utf-8');
            
        } catch (\Exception $e) {
            $this->logger->error('Template rendering error: ' . $e->getMessage());
            return $this->renderErrorPage($response, 500, '模板渲染错误');
        }
    }
    
    /**
     * 渲染错误页面
     */
    protected function renderErrorPage(
        ResponseInterface $response,
        int $code,
        string $message
    ): ResponseInterface {
        $errorHtml = "<!DOCTYPE html>
<html lang=\"zh-CN\">
<head>
    <meta charset=\"UTF-8\">
    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">
    <title>错误 {$code} - AlingAi Pro</title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; padding: 50px; background: #f5f5f5; }
        .error-container { max-width: 500px; margin: 0 auto; background: white; padding: 40px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .error-code { font-size: 48px; color: #e74c3c; margin-bottom: 20px; }
        .error-message { font-size: 18px; color: #333; margin-bottom: 30px; }
        .back-link { display: inline-block; background: #3498db; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; }
        .back-link:hover { background: #2980b9; }
    </style>
</head>
<body>
    <div class=\"error-container\">
        <div class=\"error-code\">{$code}</div>
        <div class=\"error-message\">{$message}</div>
        <a href=\"/\" class=\"back-link\">返回首页</a>
    </div>
</body>
</html>";
        
        $response->getBody()->write($errorHtml);
        return $response->withStatus($code)->withHeader('Content-Type', 'text/html; charset=utf-8');
    }
    
    /**
     * 重定向响应
     */
    protected function redirect(ResponseInterface $response, string $url, int $status = 302): ResponseInterface
    {
        return $response->withStatus($status)->withHeader('Location', $url);
    }
}
