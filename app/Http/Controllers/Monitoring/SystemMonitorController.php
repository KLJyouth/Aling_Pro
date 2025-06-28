<?php

namespace App\Http\Controllers\Monitoring;

use App\Http\Controllers\Controller;
use App\Services\Monitoring\SystemMonitorService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class SystemMonitorController extends Controller
{
    /**
     * 系统监控服务
     * 
     * @var SystemMonitorService
     */
    protected $systemMonitorService;

    /**
     * 构造函数
     * 
     * @param SystemMonitorService $systemMonitorService 系统监控服务
     */
    public function __construct(SystemMonitorService $systemMonitorService)
    {
        $this->systemMonitorService = $systemMonitorService;
    }

    /**
     * 获取系统性能指标
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function getPerformanceMetrics(Request $request): JsonResponse
    {
        try {
            $metrics = $this->systemMonitorService->getPerformanceMetrics();
            
            return response()->json([
                'success' => true,
                'data' => $metrics
            ]);
        } catch (\Exception $e) {
            Log::error('获取系统性能指标失败: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'success' => false,
                'message' => '获取系统性能指标失败: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 获取应用性能指标
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function getApplicationMetrics(Request $request): JsonResponse
    {
        try {
            $metrics = $this->systemMonitorService->getApplicationMetrics();
            
            return response()->json([
                'success' => true,
                'data' => $metrics
            ]);
        } catch (\Exception $e) {
            Log::error('获取应用性能指标失败: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'success' => false,
                'message' => '获取应用性能指标失败: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 获取系统健康状态
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function getHealthStatus(Request $request): JsonResponse
    {
        try {
            $status = $this->systemMonitorService->getHealthStatus();
            
            $httpStatus = 200;
            if (isset($status['overall']['status'])) {
                if ($status['overall']['status'] === 'critical') {
                    $httpStatus = 503; // Service Unavailable
                } elseif ($status['overall']['status'] === 'degraded') {
                    $httpStatus = 207; // Multi-Status
                }
            }
            
            return response()->json([
                'success' => true,
                'data' => $status
            ], $httpStatus);
        } catch (\Exception $e) {
            Log::error('获取系统健康状态失败: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'success' => false,
                'message' => '获取系统健康状态失败: ' . $e->getMessage()
            ], 500);
        }
    }
} 