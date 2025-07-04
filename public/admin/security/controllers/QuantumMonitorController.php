<?php
/**
 * 量子加密监控控制器 - 处理量子加密监控相关请求
 * @version 1.0.0
 * @author AlingAi Team
 */

namespace AlingAi\Admin\Security\Controllers;

use AlingAi\Admin\Security\QuantumEncryptionMonitor;
use PDO;
use Exception;

class QuantumMonitorController
{
    private $db;
    private $quantumMonitor;
    
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
        
        // 初始化量子加密监控器
        $this->quantumMonitor = new QuantumEncryptionMonitor($this->db);
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
                
            case 'status':
                $this->getStatus();
                break;
                
            case 'alerts':
                $this->getAlerts();
                break;
                
            case 'stats':
                $this->getStats();
                break;
                
            default:
                $this->respondWithError('未知操作', 404);
        }
    }
    
    /**
     * 显示量子加密监控仪表盘
     */
    private function showDashboard()
    {
        $data = [
            'encryption_status' => $this->quantumMonitor->getEncryptionStatus(),
            'key_status' => $this->quantumMonitor->getQuantumKeyStatus(),
            'recent_alerts' => $this->quantumMonitor->getRecentAlerts(),
            'usage_stats' => $this->quantumMonitor->getUsageStatistics('day')
        ];
        
        // 加载仪表盘视图
        include dirname(__DIR__) . '/views/quantum_dashboard.php';
    }
    
    /**
     * 执行量子加密系统扫描
     */
    private function performScan()
    {
        $results = $this->quantumMonitor->scanQuantumEncryptionSystem();
        $this->respondWithJson($results);
    }
    
    /**
     * 获取量子加密系统状态
     */
    private function getStatus()
    {
        $data = [
            'encryption_status' => $this->quantumMonitor->getEncryptionStatus(),
            'key_status' => $this->quantumMonitor->getQuantumKeyStatus()
        ];
        
        $this->respondWithJson($data);
    }
    
    /**
     * 获取量子加密警报
     */
    private function getAlerts()
    {
        $limit = isset($_GET['limit']) ? max(1, min(100, (int)$_GET['limit'])) : 10;
        $alerts = $this->quantumMonitor->getRecentAlerts($limit);
        
        $this->respondWithJson(['alerts' => $alerts]);
    }
    
    /**
     * 获取量子加密使用统计
     */
    private function getStats()
    {
        $period = $_GET['period'] ?? 'day';
        $validPeriods = ['day', 'week', 'month', 'all'];
        
        if (!in_array($period, $validPeriods)) {
            $period = 'day';
        }
        
        $stats = $this->quantumMonitor->getUsageStatistics($period);
        
        $this->respondWithJson([
            'period' => $period,
            'stats' => $stats
        ]);
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
