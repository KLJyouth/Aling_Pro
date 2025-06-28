<?php

namespace App\Http\Controllers\Security;

use App\Http\Controllers\Controller;
use App\Models\Security\SecurityTestResult;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class SecurityTestResultController extends Controller
{
    /**
     * 获取所有测试结果
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $page = $request->input('page', 1);
            $perPage = $request->input('per_page', 10);
            $testType = $request->input('test_type');
            $status = $request->input('status');
            
            $query = SecurityTestResult::query();
            
            if ($testType) {
                $query->where('test_type', $testType);
            }
            
            if ($status) {
                $query->where('status', $status);
            }
            
            $results = $query->orderBy('created_at', 'desc')
                ->paginate($perPage, ['*'], 'page', $page);
            
            return response()->json([
                'success' => true,
                'data' => $results
            ]);
        } catch (\Exception $e) {
            Log::error('获取测试结果列表失败: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'success' => false,
                'message' => '获取测试结果列表失败: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * 获取特定测试结果详情
     * 
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function show(Request $request, int $id): JsonResponse
    {
        try {
            $result = SecurityTestResult::findOrFail($id);
            
            return response()->json([
                'success' => true,
                'data' => $result
            ]);
        } catch (\Exception $e) {
            Log::error('获取测试结果详情失败: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'success' => false,
                'message' => '获取测试结果详情失败: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * 获取测试结果报告
     * 
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function getReport(Request $request, int $id): JsonResponse
    {
        try {
            $result = SecurityTestResult::findOrFail($id);
            $format = $request->input('format', 'json');
            
            // 根据格式生成报告
            $report = $this->generateReport($result, $format);
            
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
     * 生成测试报告
     * 
     * @param SecurityTestResult $result 测试结果
     * @param string $format 报告格式
     * @return array
     */
    private function generateReport(SecurityTestResult $result, string $format): array
    {
        $report = [];
        
        // 报告摘要
        $report['summary'] = [
            'test_id' => $result->id,
            'test_type' => $result->test_type,
            'status' => $result->status,
            'start_time' => $result->created_at,
            'end_time' => $result->updated_at,
            'duration' => $result->created_at->diffInSeconds($result->updated_at),
            'issues_count' => [
                'critical' => $this->countIssuesBySeverity($result, 'critical'),
                'high' => $this->countIssuesBySeverity($result, 'high'),
                'medium' => $this->countIssuesBySeverity($result, 'medium'),
                'low' => $this->countIssuesBySeverity($result, 'low')
            ],
            'pass_rate' => $this->calculatePassRate($result)
        ];
        
        // 详细结果
        $report['details'] = $result->results;
        
        // 根据不同格式处理报告
        if ($format === 'pdf') {
            // 在实际项目中，这里会生成PDF格式的报告
            $report['pdf_url'] = '/api/security/test-results/' . $result->id . '/download-pdf';
        } elseif ($format === 'csv') {
            // 在实际项目中，这里会生成CSV格式的报告
            $report['csv_url'] = '/api/security/test-results/' . $result->id . '/download-csv';
        }
        
        return $report;
    }
    
    /**
     * 统计特定严重级别的问题数量
     * 
     * @param SecurityTestResult $result 测试结果
     * @param string $severity 严重级别
     * @return int
     */
    private function countIssuesBySeverity(SecurityTestResult $result, string $severity): int
    {
        $count = 0;
        $results = $result->results;
        
        if (isset($results['issues']) && is_array($results['issues'])) {
            foreach ($results['issues'] as $issue) {
                if (isset($issue['severity']) && $issue['severity'] === $severity) {
                    $count++;
                }
            }
        }
        
        return $count;
    }
    
    /**
     * 计算测试通过率
     * 
     * @param SecurityTestResult $result 测试结果
     * @return float
     */
    private function calculatePassRate(SecurityTestResult $result): float
    {
        $results = $result->results;
        
        if (!isset($results['tests_total']) || $results['tests_total'] === 0) {
            return 0;
        }
        
        $passedTests = $results['tests_total'] - ($results['tests_failed'] ?? 0);
        return round(($passedTests / $results['tests_total']) * 100, 2);
    }
} 