<?php

declare(strict_types=1);

namespace AlingAi\Controllers\Api;

use Exception;

/**
 * 简化的管理员API控制器
 */
class AdminApiController extends BaseApiController
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 测试端点
     */
    public function test(): array
    {
        return $this->sendSuccessResponse([
            'message' => 'Admin API is working',
            'timestamp' => date('Y-m-d H:i:s'),
            'version' => '1.0.0'
        ]);
    }

    /**
     * 获取系统状态
     */
    public function getSystemStatus(): array
    {
        try {
            if (!$this->requireAuth()) {
                return $this->sendErrorResponse('Unauthorized', 401);
            }

            $status = [
                'server_time' => date('Y-m-d H:i:s'),
                'php_version' => PHP_VERSION,
                'memory_usage' => memory_get_usage(true),
                'memory_peak' => memory_get_peak_usage(true),
                'disk_free' => disk_free_space('.'),
                'uptime' => time() - $_SERVER['REQUEST_TIME']
            ];

            return $this->sendSuccessResponse($status, 'System status retrieved');

        } catch (Exception $e) {
            return $this->sendErrorResponse('Failed to get system status: ' . $e->getMessage(), 500);
        }
    }
}
