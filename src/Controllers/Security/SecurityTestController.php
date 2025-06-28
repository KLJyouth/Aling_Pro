<?php

namespace AlingAi\Controllers\Security;

use AlingAi\Services\Security\SecurityTestService;
use AlingAi\Services\Security\VulnerabilityScanner;
use AlingAi\Services\Security\IntrusionDetectionService;
use AlingAi\Utils\ResponseHelper;
use AlingAi\Utils\RequestValidator;

/**
 * 安全测试控制器
 * 处理安全测试和漏洞扫描相关的API请求
 *
 * @package AlingAi\Controllers\Security
 */
class SecurityTestController
{
    /**
     * 安全测试服务
     *
     * @var SecurityTestService
     */
    protected $securityTestService;
    
    /**
     * 漏洞扫描服务
     *
     * @var VulnerabilityScanner
     */
    protected $vulnerabilityScanner;
    
    /**
     * 入侵检测服务
     *
     * @var IntrusionDetectionService
     */
    protected $intrusionDetectionService;
    
    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->securityTestService = new SecurityTestService();
        $this->vulnerabilityScanner = new VulnerabilityScanner();
        $this->intrusionDetectionService = new IntrusionDetectionService();
    }
    
    /**
     * 运行安全测试
     * 
     * @param array $request 请求数据
     * @return array 响应数据
     */
    public function runTests($request)
    {
        // 验证请求参数
        $validator = new RequestValidator($request);
        $validator->optional(['test_type', 'target']);
        
        if ($validator->fails()) {
            return ResponseHelper::error('请求参数无效', $validator->errors(), 400);
        }
        
        try {
            // 确定测试类型
            $testType = isset($request['test_type']) ? $request['test_type'] : 'all';
            
            // 运行测试
            $results = $this->securityTestService->runTests($testType, $request);
            
            // 记录测试结果
            $this->logTestResults($results, $request['user_id'] ?? null);
            
            return ResponseHelper::success([
                'test_results' => $results,
                'timestamp' => time(),
                'test_type' => $testType,
                'status' => $this->getOverallStatus($results)
            ]);
        } catch (\Exception $e) {
            return ResponseHelper::error('运行安全测试失败: ' . $e->getMessage(), null, 500);
        }
    }
    
    /**
     * 获取安全状态
     * 
     * @param array $request 请求数据
     * @return array 响应数据
     */
    public function getSecurityStatus($request)
    {
        try {
            // 获取系统安全状态
            $status = $this->securityTestService->getSecurityStatus();
            
            // 获取最近的漏洞扫描结果
            $vulnerabilities = $this->vulnerabilityScanner->getRecentVulnerabilities();
            
            // 获取入侵检测结果
            $intrusionAttempts = $this->intrusionDetectionService->getRecentAttempts();
            
            return ResponseHelper::success([
                'security_status' => $status,
                'vulnerabilities' => [
                    'count' => count($vulnerabilities),
                    'critical' => $this->countBySeverity($vulnerabilities, 'critical'),
                    'high' => $this->countBySeverity($vulnerabilities, 'high'),
                    'medium' => $this->countBySeverity($vulnerabilities, 'medium'),
                    'low' => $this->countBySeverity($vulnerabilities, 'low'),
                    'recent' => array_slice($vulnerabilities, 0, 5) // 最近5个漏洞
                ],
                'intrusion_attempts' => [
                    'count' => count($intrusionAttempts),
                    'blocked' => $this->countByStatus($intrusionAttempts, 'blocked'),
                    'detected' => $this->countByStatus($intrusionAttempts, 'detected'),
                    'recent' => array_slice($intrusionAttempts, 0, 5) // 最近5次入侵尝试
                ],
                'last_scan_time' => $this->securityTestService->getLastScanTime(),
                'timestamp' => time()
            ]);
        } catch (\Exception $e) {
            return ResponseHelper::error('获取安全状态失败: ' . $e->getMessage(), null, 500);
        }
    }
    
    /**
     * 扫描漏洞
     * 
     * @param array $request 请求数据
     * @return array 响应数据
     */
    public function scanVulnerabilities($request)
    {
        // 验证请求参数
        $validator = new RequestValidator($request);
        $validator->optional(['scan_type', 'target']);
        
        if ($validator->fails()) {
            return ResponseHelper::error('请求参数无效', $validator->errors(), 400);
        }
        
        try {
            // 确定扫描类型
            $scanType = isset($request['scan_type']) ? $request['scan_type'] : 'full';
            $target = isset($request['target']) ? $request['target'] : null;
            
            // 启动扫描
            $scanId = $this->vulnerabilityScanner->startScan($scanType, $target);
            
            return ResponseHelper::success([
                'scan_id' => $scanId,
                'scan_type' => $scanType,
                'target' => $target,
                'status' => 'started',
                'timestamp' => time(),
                'estimated_completion_time' => time() + $this->getEstimatedScanTime($scanType)
            ]);
        } catch (\Exception $e) {
            return ResponseHelper::error('启动漏洞扫描失败: ' . $e->getMessage(), null, 500);
        }
    }
    
    /**
     * 获取漏洞扫描结果
     * 
     * @param array $request 请求数据
     * @return array 响应数据
     */
    public function getScanResults($request)
    {
        // 验证请求参数
        $validator = new RequestValidator($request);
        $validator->required(['scan_id']);
        
        if ($validator->fails()) {
            return ResponseHelper::error('请求参数无效', $validator->errors(), 400);
        }
        
        try {
            // 获取扫描结果
            $results = $this->vulnerabilityScanner->getScanResults($request['scan_id']);
            
            if (!$results) {
                return ResponseHelper::error('扫描结果不存在', null, 404);
            }
            
            return ResponseHelper::success([
                'scan_id' => $request['scan_id'],
                'status' => $results['status'],
                'progress' => $results['progress'],
                'vulnerabilities' => $results['vulnerabilities'],
                'summary' => [
                    'total' => count($results['vulnerabilities']),
                    'critical' => $this->countBySeverity($results['vulnerabilities'], 'critical'),
                    'high' => $this->countBySeverity($results['vulnerabilities'], 'high'),
                    'medium' => $this->countBySeverity($results['vulnerabilities'], 'medium'),
                    'low' => $this->countBySeverity($results['vulnerabilities'], 'low')
                ],
                'timestamp' => time()
            ]);
        } catch (\Exception $e) {
            return ResponseHelper::error('获取扫描结果失败: ' . $e->getMessage(), null, 500);
        }
    }
    
    /**
     * 获取安全报告
     * 
     * @param array $request 请求数据
     * @return array 响应数据
     */
    public function getSecurityReport($request)
    {
        // 验证请求参数
        $validator = new RequestValidator($request);
        $validator->optional(['report_type', 'start_date', 'end_date']);
        
        if ($validator->fails()) {
            return ResponseHelper::error('请求参数无效', $validator->errors(), 400);
        }
        
        try {
            // 确定报告类型
            $reportType = isset($request['report_type']) ? $request['report_type'] : 'summary';
            $startDate = isset($request['start_date']) ? $request['start_date'] : date('Y-m-d', strtotime('-30 days'));
            $endDate = isset($request['end_date']) ? $request['end_date'] : date('Y-m-d');
            
            // 生成报告
            $report = $this->securityTestService->generateReport($reportType, $startDate, $endDate);
            
            return ResponseHelper::success([
                'report' => $report,
                'report_type' => $reportType,
                'period' => [
                    'start_date' => $startDate,
                    'end_date' => $endDate
                ],
                'timestamp' => time()
            ]);
        } catch (\Exception $e) {
            return ResponseHelper::error('生成安全报告失败: ' . $e->getMessage(), null, 500);
        }
    }
    
    /**
     * 获取安全建议
     * 
     * @param array $request 请求数据
     * @return array 响应数据
     */
    public function getSecurityRecommendations($request)
    {
        try {
            // 获取安全建议
            $recommendations = $this->securityTestService->getSecurityRecommendations();
            
            return ResponseHelper::success([
                'recommendations' => $recommendations,
                'count' => count($recommendations),
                'timestamp' => time()
            ]);
        } catch (\Exception $e) {
            return ResponseHelper::error('获取安全建议失败: ' . $e->getMessage(), null, 500);
        }
    }
    
    /**
     * 记录测试结果
     * 
     * @param array $results 测试结果
     * @param int|null $userId 用户ID
     * @return void
     */
    protected function logTestResults($results, $userId = null)
    {
        try {
            // 记录测试结果
            $logData = [
                'results' => $results,
                'user_id' => $userId,
                'timestamp' => time(),
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null
            ];
            
            // 保存日志
            // 这里可以使用日志服务保存测试结果
        } catch (\Exception $e) {
            // 记录日志失败不应影响主要功能
            error_log('记录安全测试结果失败: ' . $e->getMessage());
        }
    }
    
    /**
     * 获取整体状态
     * 
     * @param array $results 测试结果
     * @return string 整体状态
     */
    protected function getOverallStatus($results)
    {
        // 检查是否有严重或高风险问题
        foreach ($results as $result) {
            if (isset($result['severity']) && ($result['severity'] === 'critical' || $result['severity'] === 'high')) {
                return 'critical';
            }
        }
        
        // 检查是否有中等风险问题
        foreach ($results as $result) {
            if (isset($result['severity']) && $result['severity'] === 'medium') {
                return 'warning';
            }
        }
        
        // 检查是否有低风险问题
        foreach ($results as $result) {
            if (isset($result['severity']) && $result['severity'] === 'low') {
                return 'notice';
            }
        }
        
        return 'secure';
    }
    
    /**
     * 按严重程度计数
     * 
     * @param array $items 项目列表
     * @param string $severity 严重程度
     * @return int 计数
     */
    protected function countBySeverity($items, $severity)
    {
        $count = 0;
        foreach ($items as $item) {
            if (isset($item['severity']) && $item['severity'] === $severity) {
                $count++;
            }
        }
        return $count;
    }
    
    /**
     * 按状态计数
     * 
     * @param array $items 项目列表
     * @param string $status 状态
     * @return int 计数
     */
    protected function countByStatus($items, $status)
    {
        $count = 0;
        foreach ($items as $item) {
            if (isset($item['status']) && $item['status'] === $status) {
                $count++;
            }
        }
        return $count;
    }
    
    /**
     * 获取预计扫描时间（秒）
     * 
     * @param string $scanType 扫描类型
     * @return int 预计时间（秒）
     */
    protected function getEstimatedScanTime($scanType)
    {
        switch ($scanType) {
            case 'quick':
                return 300; // 5分钟
            case 'targeted':
                return 600; // 10分钟
            case 'full':
                return 1800; // 30分钟
            case 'deep':
                return 3600; // 60分钟
            default:
                return 1800; // 默认30分钟
        }
    }
}
