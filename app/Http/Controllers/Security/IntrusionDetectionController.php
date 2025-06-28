<?php

namespace App\Http\Controllers\Security;

use App\Http\Controllers\Controller;
use App\Services\Security\IntrusionDetectionService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class IntrusionDetectionController extends Controller
{
    /**
     * 入侵检测服务
     * 
     * @var IntrusionDetectionService
     */
    protected $intrusionDetectionService;

    /**
     * 构造函数
     * 
     * @param IntrusionDetectionService $intrusionDetectionService 入侵检测服务
     */
    public function __construct(IntrusionDetectionService $intrusionDetectionService)
    {
        $this->intrusionDetectionService = $intrusionDetectionService;
    }

    /**
     * 获取入侵尝试记录
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function getIntrusionAttempts(Request $request): JsonResponse
    {
        try {
            $page = $request->input('page', 1);
            $perPage = $request->input('per_page', 10);
            $filters = $request->input('filters', []);
            
            $attempts = $this->intrusionDetectionService->getIntrusionAttempts($page, $perPage, $filters);
            
            return response()->json([
                'success' => true,
                'data' => $attempts
            ]);
        } catch (\Exception $e) {
            Log::error('获取入侵尝试记录失败: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'success' => false,
                'message' => '获取入侵尝试记录失败: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * 获取入侵尝试详情
     * 
     * @param Request $request
     * @param string $attemptId 尝试ID
     * @return JsonResponse
     */
    public function getIntrusionDetail(Request $request, string $attemptId): JsonResponse
    {
        try {
            $detail = $this->intrusionDetectionService->getIntrusionDetail($attemptId);
            
            return response()->json([
                'success' => true,
                'data' => $detail
            ]);
        } catch (\Exception $e) {
            Log::error('获取入侵尝试详情失败: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'success' => false,
                'message' => '获取入侵尝试详情失败: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * 解决入侵尝试
     * 
     * @param Request $request
     * @param string $attemptId 尝试ID
     * @return JsonResponse
     */
    public function resolveIntrusion(Request $request, string $attemptId): JsonResponse
    {
        try {
            $action = $request->input('action', 'block');
            $notes = $request->input('notes', '');
            
            $result = $this->intrusionDetectionService->resolveIntrusion($attemptId, $action, $notes);
            
            return response()->json([
                'success' => true,
                'data' => $result
            ]);
        } catch (\Exception $e) {
            Log::error('解决入侵尝试失败: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'success' => false,
                'message' => '解决入侵尝试失败: ' . $e->getMessage()
            ], 500);
        }
    }
} 