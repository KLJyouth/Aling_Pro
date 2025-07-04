<?php
/**
 * API安全监控控制器 - 处理API安全监控相关请求
 * @version 1.0.0
 * @author AlingAi Team
 */

namespace AlingAi\Admin\Security\Controllers;

use AlingAi\Admin\Security\ApiSecurityMonitor;
use PDO;
use Exception;

class ApiMonitorController
{
    private $db;
    private $apiMonitor;
    
    /**
     * 构造函数
     */
    public function __construct()
    {
        // 初始化数据库连接
        try {
            $dbPath = dirname(dirname(dirname(dirname(__DIR__)))) . '/storage/database/admin.sqlite';
            $dbDir = dirname($dbPath);
            
            if (!is_dir($dbDir)) {
                mkdir($dbDir, 0755, true);
            }
            
            $this->db = new PDO("sqlite:{$dbPath}");
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (Exception $e) {
            error_log('数据库初始化失败: ' . $e->getMessage());
            $this->db = null;
        }
        
        // 初始化API安全监控器
        $this->apiMonitor = new ApiSecurityMonitor($this->db);
    }
    
    /**
     * 处理请求
     */
    public function handleRequest()
    {
        // 处理各种请求
        $action = $_GET['action'] ?? 'dashboard';
        
        switch ($action) {
            case 'dashboard':
                $this->showDashboard();
                break;
                
            case 'scan':
                $this->performScan();
                break;
                
            case 'endpoints':
                $this->getEndpoints();
                break;
                
            case 'threats':
                $this->getThreats();
                break;
                
            case 'vulnerabilities':
                $this->getVulnerabilities();
                break;
                
            case 'stats':
                $this->getStats();
                break;
                
            case 'add_endpoint':
                $this->addEndpoint();
                break;
                
            case 'fix_vulnerability':
                $this->fixVulnerability();
                break;
                
            default:
                $this->respondWithError('未知操作', 404);
        }
    }
    
    /**
     * 显示API安全监控仪表盘
     */
    private function showDashboard()
    {
        $data = [
            'endpoints' => $this->apiMonitor->getApiEndpoints(),
            'threats' => $this->apiMonitor->getApiThreats(10),
            'vulnerabilities' => $this->apiMonitor->getApiVulnerabilities(),
            'stats' => $this->apiMonitor->getApiAccessStatistics('day')
        ];
        
        // 加载仪表盘视图
        include dirname(__DIR__) . '/views/api_dashboard.php';
    }
    
    /**
     * 执行API安全扫描
     */
    private function performScan()
    {
        $results = $this->apiMonitor->scanApiSecurity();
        $this->respondWithJson($results);
    }
    
    /**
     * 获取API端点
     */
    private function getEndpoints()
    {
        $category = $_GET['category'] ?? null;
        $endpoints = $this->apiMonitor->getApiEndpoints($category);
        
        $this->respondWithJson(['endpoints' => $endpoints]);
    }
    
    /**
     * 获取API威胁
     */
    private function getThreats()
    {
        $limit = isset($_GET['limit']) ? max(1, min(100, (int)$_GET['limit'])) : 50;
        $threats = $this->apiMonitor->getApiThreats($limit);
        
        $this->respondWithJson(['threats' => $threats]);
    }
    
    /**
     * 获取API漏洞
     */
    private function getVulnerabilities()
    {
        $status = $_GET['status'] ?? 'open';
        $vulnerabilities = $this->apiMonitor->getApiVulnerabilities($status);
        
        $this->respondWithJson(['vulnerabilities' => $vulnerabilities]);
    }
    
    /**
     * 获取API访问统计
     */
    private function getStats()
    {
        $period = $_GET['period'] ?? 'day';
        $validPeriods = ['day', 'week', 'month', 'all'];
        
        if (!in_array($period, $validPeriods)) {
            $period = 'day';
        }
        
        $stats = $this->apiMonitor->getApiAccessStatistics($period);
        
        $this->respondWithJson([
            'period' => $period,
            'stats' => $stats
        ]);
    }
    
    /**
     * 添加API端点
     */
    private function addEndpoint()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->respondWithError('必须使用POST请求', 405);
            return;
        }
        
        $endpoint = $_POST['endpoint'] ?? '';
        $method = $_POST['method'] ?? 'GET';
        $category = $_POST['category'] ?? 'user';
        $description = $_POST['description'] ?? '';
        $authRequired = isset($_POST['auth_required']) ? (bool)$_POST['auth_required'] : true;
        $rateLimited = isset($_POST['rate_limited']) ? (bool)$_POST['rate_limited'] : true;
        
        if (empty($endpoint)) {
            $this->respondWithError('端点不能为空');
            return;
        }
        
        $validMethods = ['GET', 'POST', 'PUT', 'DELETE', 'PATCH'];
        if (!in_array($method, $validMethods)) {
            $this->respondWithError('无效的HTTP方法');
            return;
        }
        
        $validCategories = ['system', 'local', 'user', 'external'];
        if (!in_array($category, $validCategories)) {
            $this->respondWithError('无效的API类别');
            return;
        }
        
        $endpointData = [
            'endpoint' => $endpoint,
            'method' => $method,
            'category' => $category,
            'description' => $description,
            'authentication_required' => $authRequired,
            'rate_limited' => $rateLimited
        ];
        
        if ($this->apiMonitor->addApiEndpoint($endpointData)) {
            $this->respondWithJson([
                'success' => true,
                'message' => 'API端点添加成功',
                'endpoint' => $endpointData
            ]);
        } else {
            $this->respondWithError('添加API端点失败');
        }
    }
    
    /**
     * 修复API漏洞
     */
    private function fixVulnerability()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->respondWithError('必须使用POST请求', 405);
            return;
        }
        
        $vulnerabilityId = isset($_POST['vulnerability_id']) ? (int)$_POST['vulnerability_id'] : 0;
        
        if ($vulnerabilityId <= 0) {
            $this->respondWithError('无效的漏洞ID');
            return;
        }
        
        if ($this->apiMonitor->fixApiVulnerability($vulnerabilityId)) {
            $this->respondWithJson([
                'success' => true,
                'message' => '漏洞已标记为已修复',
                'vulnerability_id' => $vulnerabilityId
            ]);
        } else {
            $this->respondWithError('修复漏洞失败');
        }
    }
    
    /**
     * 以JSON格式响应
     */
    private function respondWithJson(array $data, int $statusCode = 200)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    /**
     * 返回错误响应
     */
    private function respondWithError(string $message, int $statusCode = 400)
    {
        $this->respondWithJson([
            'error' => true,
            'message' => $message
        ], $statusCode);
    }
}
