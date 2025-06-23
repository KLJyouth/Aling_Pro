<?php

declare(strict_types=1);

namespace AlingAi\Controllers\Api;

use Exception;

/**
 * 简化的API控制器基类
 */
abstract class SimpleBaseApiController
{
    protected float $requestStartTime;
    protected ?array $currentUser = null;

    public function __construct()
    {
        $this->requestStartTime = microtime(true);
    }

    /**
     * 发送成功响应
     */
    protected function sendSuccessResponse(array $data = [], string $message = 'Success', int $statusCode = 200): array
    {
        return [
            'success' => true,
            'message' => $message,
            'data' => $data,
            'timestamp' => date('Y-m-d H:i:s'),
            'status_code' => $statusCode
        ];
    }

    /**
     * 发送错误响应
     */
    protected function sendErrorResponse(string $message = 'Error', int $statusCode = 400, array $details = []): array
    {
        return [
            'success' => false,
            'message' => $message,
            'status_code' => $statusCode,
            'timestamp' => date('Y-m-d H:i:s'),
            'details' => $details
        ];
    }

    /**
     * 获取请求数据
     */
    protected function getRequestData(): array
    {
        $data = [];
        
        // GET参数
        $data = array_merge($data, $_GET);
        
        // POST数据
        if ($_SERVER['REQUEST_METHOD'] === 'POST' || $_SERVER['REQUEST_METHOD'] === 'PUT') {
            $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
            
            if (strpos($contentType, 'application/json') !== false) {
                $input = file_get_contents('php://input');
                $jsonData = json_decode($input, true);
                if ($jsonData) {
                    $data = array_merge($data, $jsonData);
                }
            } else {
                $data = array_merge($data, $_POST);
            }
        }
        
        return $data;
    }

    /**
     * 验证必需参数
     */
    protected function validateRequired(array $data, array $required): bool
    {
        foreach ($required as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                return false;
            }
        }
        return true;
    }
}
