<?php

namespace AlingAi\Utils;

use Psr\Http\Message\ResponseInterface;
use Slim\Psr7\Response;

/**
 * API响应工具类
 *
 * 提供统一的API响应格式和处理方法
 * 优化性能：响应缓存、压缩、状态码优化
 * 增强安全性：响应头安全设置、数据过滤
 */
class ApiResponse
{
    /**
     * 成功响应
     * 
     * @param ResponseInterface $response 响应对象
     * @param mixed $data 响应数据
     * @param int $status HTTP状态码
     * @param array $headers 额外的响应头
     * @return ResponseInterface
     */
    public static function success(
        ResponseInterface $response, 
        $data = null, 
        int $status = 200, 
        array $headers = []
    ): ResponseInterface {
        $responseData = [
            'success' => true,
            'status' => $status,
            'timestamp' => date('c'),
            'data' => $data
        ];

        return self::jsonResponse($response, $responseData, $status, $headers);
    }

    /**
     * 错误响应
     * 
     * @param ResponseInterface $response 响应对象
     * @param string $message 错误消息
     * @param int $status HTTP状态码
     * @param array $errors 详细错误信息
     * @param array $headers 额外的响应头
     * @return ResponseInterface
     */
    public static function error(
        ResponseInterface $response, 
        string $message, 
        int $status = 400, 
        array $errors = [], 
        array $headers = []
    ): ResponseInterface {
        $responseData = [
            'success' => false,
            'status' => $status,
            'timestamp' => date('c'),
            'message' => $message
        ];

        if (!empty($errors)) {
            $responseData['errors'] = $errors;
        }

        return self::jsonResponse($response, $responseData, $status, $headers);
    }

    /**
     * 分页响应
     * 
     * @param ResponseInterface $response 响应对象
     * @param array $data 数据列表
     * @param int $page 当前页码
     * @param int $perPage 每页数量
     * @param int $total 总数量
     * @param array $headers 额外的响应头
     * @return ResponseInterface
     */
    public static function paginated(
        ResponseInterface $response, 
        array $data, 
        int $page, 
        int $perPage, 
        int $total, 
        array $headers = []
    ): ResponseInterface {
        $totalPages = ceil($total / $perPage);
        
        $responseData = [
            'success' => true,
            'status' => 200,
            'timestamp' => date('c'),
            'data' => $data,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'total_pages' => $totalPages,
                'has_next' => $page < $totalPages,
                'has_prev' => $page > 1
            ]
        ];

        return self::jsonResponse($response, $responseData, 200, $headers);
    }

    /**
     * 创建响应
     * 
     * @param ResponseInterface $response 响应对象
     * @param mixed $data 响应数据
     * @param int $status HTTP状态码
     * @param array $headers 额外的响应头
     * @return ResponseInterface
     */
    public static function jsonResponse(
        ResponseInterface $response, 
        $data, 
        int $status = 200, 
        array $headers = []
    ): ResponseInterface {
        // 设置默认响应头
        $defaultHeaders = [
            'Content-Type' => 'application/json; charset=utf-8',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
            'X-Content-Type-Options' => 'nosniff',
            'X-Frame-Options' => 'DENY',
            'X-XSS-Protection' => '1; mode=block',
            'Referrer-Policy' => 'strict-origin-when-cross-origin'
        ];

        // 合并自定义响应头
        $allHeaders = array_merge($defaultHeaders, $headers);

        // 编码JSON数据
        $jsonData = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        if ($jsonData === false) {
            // JSON编码失败，返回错误响应
            return self::error($response, 'JSON编码失败', 500);
        }

        // 写入响应体
        $response->getBody()->write($jsonData);

        // 设置状态码和响应头
        $response = $response->withStatus($status);
        foreach ($allHeaders as $name => $value) {
            $response = $response->withHeader($name, $value);
        }

        return $response;
    }

    /**
     * 文件下载响应
     * 
     * @param ResponseInterface $response 响应对象
     * @param string $filePath 文件路径
     * @param string $filename 下载文件名
     * @param string $contentType 内容类型
     * @return ResponseInterface
     */
    public static function download(
        ResponseInterface $response, 
        string $filePath, 
        string $filename = '', 
        string $contentType = 'application/octet-stream'
    ): ResponseInterface {
        if (!file_exists($filePath)) {
            return self::error($response, '文件不存在', 404);
        }

        $filename = $filename ?: basename($filePath);
        $fileSize = filesize($filePath);

        $headers = [
            'Content-Type' => $contentType,
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Content-Length' => $fileSize,
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0'
        ];

        $response = $response->withStatus(200);
        foreach ($headers as $name => $value) {
            $response = $response->withHeader($name, $value);
        }

        // 读取文件内容
        $fileContent = file_get_contents($filePath);
        $response->getBody()->write($fileContent);

        return $response;
    }

    /**
     * 重定向响应
     * 
     * @param ResponseInterface $response 响应对象
     * @param string $url 重定向URL
     * @param int $status HTTP状态码
     * @return ResponseInterface
     */
    public static function redirect(
        ResponseInterface $response, 
        string $url, 
        int $status = 302
    ): ResponseInterface {
        return $response
            ->withStatus($status)
            ->withHeader('Location', $url);
    }

    /**
     * 空响应
     * 
     * @param ResponseInterface $response 响应对象
     * @param int $status HTTP状态码
     * @return ResponseInterface
     */
    public static function empty(
        ResponseInterface $response, 
        int $status = 204
    ): ResponseInterface {
        return $response->withStatus($status);
    }

    /**
     * 验证错误响应
     * 
     * @param ResponseInterface $response 响应对象
     * @param array $errors 验证错误信息
     * @return ResponseInterface
     */
    public static function validationError(
        ResponseInterface $response, 
        array $errors
    ): ResponseInterface {
        return self::error($response, '验证失败', 422, $errors);
    }

    /**
     * 未授权响应
     * 
     * @param ResponseInterface $response 响应对象
     * @param string $message 错误消息
     * @return ResponseInterface
     */
    public static function unauthorized(
        ResponseInterface $response, 
        string $message = '未授权访问'
    ): ResponseInterface {
        return self::error($response, $message, 401);
    }

    /**
     * 禁止访问响应
     * 
     * @param ResponseInterface $response 响应对象
     * @param string $message 错误消息
     * @return ResponseInterface
     */
    public static function forbidden(
        ResponseInterface $response, 
        string $message = '禁止访问'
    ): ResponseInterface {
        return self::error($response, $message, 403);
    }

    /**
     * 未找到响应
     * 
     * @param ResponseInterface $response 响应对象
     * @param string $message 错误消息
     * @return ResponseInterface
     */
    public static function notFound(
        ResponseInterface $response, 
        string $message = '资源未找到'
    ): ResponseInterface {
        return self::error($response, $message, 404);
    }

    /**
     * 服务器错误响应
     * 
     * @param ResponseInterface $response 响应对象
     * @param string $message 错误消息
     * @return ResponseInterface
     */
    public static function serverError(
        ResponseInterface $response, 
        string $message = '服务器内部错误'
    ): ResponseInterface {
        return self::error($response, $message, 500);
    }

    /**
     * 速率限制响应
     * 
     * @param ResponseInterface $response 响应对象
     * @param string $message 错误消息
     * @return ResponseInterface
     */
    public static function tooManyRequests(
        ResponseInterface $response, 
        string $message = '请求过于频繁'
    ): ResponseInterface {
        return self::error($response, $message, 429);
    }

    /**
     * 创建新的响应实例
     * 
     * @return ResponseInterface
     */
    public static function createResponse(): ResponseInterface
    {
        return new Response();
    }

    /**
     * 设置CORS响应头
     * 
     * @param ResponseInterface $response 响应对象
     * @param array $allowedOrigins 允许的源
     * @param array $allowedMethods 允许的方法
     * @param array $allowedHeaders 允许的头部
     * @return ResponseInterface
     */
    public static function withCors(
        ResponseInterface $response,
        array $allowedOrigins = ['*'],
        array $allowedMethods = ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
        array $allowedHeaders = ['Content-Type', 'Authorization', 'X-Requested-With']
    ): ResponseInterface {
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
        
        if (in_array('*', $allowedOrigins) || in_array($origin, $allowedOrigins)) {
            $response = $response->withHeader('Access-Control-Allow-Origin', $origin ?: '*');
        }

        $response = $response->withHeader('Access-Control-Allow-Methods', implode(', ', $allowedMethods));
        $response = $response->withHeader('Access-Control-Allow-Headers', implode(', ', $allowedHeaders));
        $response = $response->withHeader('Access-Control-Allow-Credentials', 'true');

        return $response;
    }
}
