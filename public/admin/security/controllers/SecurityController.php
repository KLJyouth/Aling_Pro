<?php
/**
 * 基本安全管理控制器 - 处理基本安全监控相关请求
 * @version 1.0.0
 * @author AlingAi Team
 */

namespace AlingAi\Admin\Security\Controllers;

use AlingAi\Admin\Security\SecurityManager;
use PDO;
use Exception;

class SecurityController
{
    private $db;
    private $securityManager;
    
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
        
        // 初始化安全管理器
        $this->securityManager = new SecurityManager($this->db);
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
                
            case 'events':
                $this->getEvents();
                break;
                
            case 'vulnerabilities':
                $this->getVulnerabilities();
                break;
                
            case 'threats':
                $this->getThreats();
                break;
                
            case 'stats':
                $this->getStats();
                break;
                
            case 'fix_vulnerability':
                $this->fixVulnerability();
                break;
                
            case 'block_ip':
                $this->blockIp();
                break;
                
            default:
                $this->respondWithError('未知操作', 404);
        }
    }
    
    /**
     * 显示安全监控仪表盘
     */
    private function showDashboard()
    {
        $data = [
            'security_events' => $this->securityManager->getSecurityEvents(10),
            'vulnerabilities' => $this->securityManager->getVulnerabilities('open', 5),
            'threats' => $this->securityManager->getThreats(5),
            'stats' => $this->securityManager->getSecurityStats('day')
        ];
        
        // 加载仪表盘视图
        include dirname(__DIR__) . '/views/dashboard.php';
    }
    
    /**
     * 执行安全扫描
     */
    private function performScan()
    {
        $results = $this->securityManager->scanSystem();
        $this->respondWithJson($results);
    }
    
    /**
     * 获取安全事件
     */
    private function getEvents()
    {
        $limit = isset($_GET['limit']) ? max(1, min(100, (int)$_GET['limit'])) : 50;
        $events = $this->securityManager->getSecurityEvents($limit);
        
        $this->respondWithJson(['events' => $events]);
    }
    
    /**
     * 获取漏洞
     */
    private function getVulnerabilities()
    {
        $status = $_GET['status'] ?? 'all';
        $limit = isset($_GET['limit']) ? max(1, min(100, (int)$_GET['limit'])) : 50;
        $vulnerabilities = $this->securityManager->getVulnerabilities($status, $limit);
        
        $this->respondWithJson(['vulnerabilities' => $vulnerabilities]);
    }
    
    /**
     * 获取威胁
     */
    private function getThreats()
    {
        $limit = isset($_GET['limit']) ? max(1, min(100, (int)$_GET['limit'])) : 50;
        $threats = $this->securityManager->getThreats($limit);
        
        $this->respondWithJson(['threats' => $threats]);
    }
    
    /**
     * 获取安全统计
     */
    private function getStats()
    {
        $period = $_GET['period'] ?? 'day';
        $validPeriods = ['day', 'week', 'month', 'all'];
        
        if (!in_array($period, $validPeriods)) {
            $period = 'day';
        }
        
        $stats = $this->securityManager->getSecurityStats($period);
        
        $this->respondWithJson([
            'period' => $period,
            'stats' => $stats
        ]);
    }
    
    /**
     * 修复漏洞
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
        
        if ($this->securityManager->fixVulnerability($vulnerabilityId)) {
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
     * 阻止IP
     */
    private function blockIp()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->respondWithError('必须使用POST请求', 405);
            return;
        }
        
        $ipAddress = $_POST['ip_address'] ?? '';
        $reason = $_POST['reason'] ?? '手动阻止';
        $expiresAt = $_POST['expires_at'] ?? null;
        
        if (empty($ipAddress) || !filter_var($ipAddress, FILTER_VALIDATE_IP)) {
            $this->respondWithError('无效的IP地址');
            return;
        }
        
        if ($this->securityManager->blockIp($ipAddress, $reason, $expiresAt)) {
            $this->respondWithJson([
                'success' => true,
                'message' => 'IP地址已添加到黑名单',
                'ip_address' => $ipAddress
            ]);
        } else {
            $this->respondWithError('添加IP到黑名单失败');
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