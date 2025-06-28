<?php

namespace App\Http\Controllers\Security;

use App\Http\Controllers\Controller;
use App\Services\Security\SecurityTestService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class SecurityTestController extends Controller
{
    /**
     * 安全测试服务
     * 
     * @var SecurityTestService
     */
    protected $securityTestService;

    /**
     * 构造函数
     * 
     * @param SecurityTestService $securityTestService 安全测试服务
     */
    public function __construct(SecurityTestService $securityTestService)
    {
        $this->securityTestService = $securityTestService;
    }

    /**
     * 运行安全测试
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function runTest(Request $request): JsonResponse
    {
        try {
            $testType = $request->input('test_type', 'full');
            $testParams = $request->input('params', []);
            
            $result = $this->securityTestService->runTest($testType, $testParams);
            
            return response()->json([
                'success' => true,
                'data' => $result
            ]);
        } catch (\Exception $e) {
            Log::error('安全测试失败: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'success' => false,
                'message' => '安全测试失败: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * 获取测试状态
     * 
     * @param Request $request
     * @param string $testId 测试ID
     * @return JsonResponse
     */
    public function getTestStatus(Request $request, string $testId): JsonResponse
    {
        try {
            $status = $this->securityTestService->getTestStatus($testId);
            
            return response()->json([
                'success' => true,
                'data' => $status
            ]);
        } catch (\Exception $e) {
            Log::error('获取测试状态失败: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'success' => false,
                'message' => '获取测试状态失败: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * 获取测试历史记录
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function getTestHistory(Request $request): JsonResponse
    {
        try {
            $page = $request->input('page', 1);
            $perPage = $request->input('per_page', 10);
            $filters = $request->input('filters', []);
            
            $history = $this->securityTestService->getTestHistory($page, $perPage, $filters);
            
            return response()->json([
                'success' => true,
                'data' => $history
            ]);
        } catch (\Exception $e) {
            Log::error('获取测试历史记录失败: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'success' => false,
                'message' => '获取测试历史记录失败: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 获取测试报告
     * 
     * @param Request $request
     * @param string $testId 测试ID
     * @return JsonResponse
     */
    public function getTestReport(Request $request, string $testId): JsonResponse
    {
        try {
            $format = $request->input('format', 'json');
            $report = $this->securityTestService->getTestReport($testId, $format);
            
            return response()->json([
                'success' => true,
                'data' => $report
            ]);
        } catch (\Exception $e) {
            Log::error('获取测试报告失败: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'success' => false,
                'message' => '获取测试报告失败: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * 取消测试
     * 
     * @param Request $request
     * @param string $testId 测试ID
     * @return JsonResponse
     */
    public function cancelTest(Request $request, string $testId): JsonResponse
    {
        try {
            $result = $this->securityTestService->cancelTest($testId);
            
            return response()->json([
                'success' => true,
                'data' => $result
            ]);
        } catch (\Exception $e) {
            Log::error('取消测试失败: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'success' => false,
                'message' => '取消测试失败: ' . $e->getMessage()
            ], 500);
        }
    }
} 