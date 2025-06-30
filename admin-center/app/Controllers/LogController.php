<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\View;
use App\Core\Database;
use App\Core\Logger;
use App\Core\Security;

/**
 * 日志管理控制器
 * 负责处理系统日志相关请求
 */
class LogController extends Controller
{
    /**
     * 日志列表页面
     */
    public function index()
    {
        // 获取分页参数
        $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
        $perPage = 20;
        $offset = ($page - 1) * $perPage;
        
        // 获取筛选参数
        $level = isset($_GET['level']) ? $_GET['level'] : '';
        $startDate = isset($_GET['start_date']) ? $_GET['start_date'] : '';
        $endDate = isset($_GET['end_date']) ? $_GET['end_date'] : '';
        $search = isset($_GET['search']) ? $_GET['search'] : '';
        
        // 构建查询条件
        $conditions = [];
        $params = [];
        
        if (!empty($level)) {
            $conditions[] = "level = ?";
            $params[] = $level;
        }
        
        if (!empty($startDate)) {
            $conditions[] = "created_at >= ?";
            $params[] = $startDate . ' 00:00:00';
        }
        
        if (!empty($endDate)) {
            $conditions[] = "created_at <= ?";
            $params[] = $endDate . ' 23:59:59';
        }
        
        if (!empty($search)) {
            $conditions[] = "(message LIKE ? OR context LIKE ?)";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }
        
        $whereClause = !empty($conditions) ? "WHERE " . implode(" AND ", $conditions) : "";
        
        // 获取日志总数
        $db = Database::getInstance();
        $countSql = "SELECT COUNT(*) FROM logs {$whereClause}";
        $stmt = $db->prepare($countSql);
        $stmt->execute($params);
        $total = $stmt->fetchColumn();
        
        // 计算总页数
        $totalPages = ceil($total / $perPage);
        
        // 获取当前页的日志
        $sql = "SELECT * FROM logs {$whereClause} ORDER BY created_at DESC LIMIT {$perPage} OFFSET {$offset}";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $logs = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        // 获取日志级别统计
        $levelStats = $this->getLogLevelStats();
        
        // 渲染视图
        View::display('logs.index', [
            'pageTitle' => '系统日志 - IT运维中心',
            'pageHeader' => '系统日志',
            'currentPage' => 'logs',
            'breadcrumbs' => [
                '/admin' => '首页',
                '/admin/logs' => '系统日志'
            ],
            'logs' => $logs,
            'levelStats' => $levelStats,
            'pagination' => [
                'page' => $page,
                'perPage' => $perPage,
                'total' => $total,
                'totalPages' => $totalPages
            ],
            'filters' => [
                'level' => $level,
                'startDate' => $startDate,
                'endDate' => $endDate,
                'search' => $search
            ]
        ]);
    }
    
    /**
     * 查看日志详情
     */
    public function show($id)
    {
        // 获取日志详情
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM logs WHERE id = ?");
        $stmt->execute([$id]);
        $log = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if (!$log) {
            $_SESSION['flash_message'] = '日志不存在';
            $_SESSION['flash_message_type'] = 'danger';
            header('Location: /admin/logs');
            exit;
        }
        
        // 解析上下文
        $context = json_decode($log['context'], true) ?? [];
        
        // 渲染视图
        View::display('logs.show', [
            'pageTitle' => '日志详情 - IT运维中心',
            'pageHeader' => '日志详情',
            'currentPage' => 'logs',
            'breadcrumbs' => [
                '/admin' => '首页',
                '/admin/logs' => '系统日志',
                '/admin/logs/' . $id => '日志详情'
            ],
            'log' => $log,
            'context' => $context
        ]);
    }
    
    /**
     * 清除日志
     */
    public function clear()
    {
        // 验证CSRF令牌
        if (!Security::validateCsrfToken()) {
            $_SESSION['flash_message'] = '安全验证失败，请重试';
            $_SESSION['flash_message_type'] = 'danger';
            header('Location: /admin/logs');
            exit;
        }
        
        // 获取清除参数
        $type = $_POST['clear_type'] ?? 'all';
        $level = $_POST['level'] ?? '';
        $days = isset($_POST['days']) ? intval($_POST['days']) : 0;
        
        try {
            $db = Database::getInstance();
            
            switch ($type) {
                case 'level':
                    // 按级别清除
                    if (empty($level)) {
                        throw new \Exception('请选择日志级别');
                    }
                    
                    $stmt = $db->prepare("DELETE FROM logs WHERE level = ?");
                    $stmt->execute([$level]);
                    $count = $stmt->rowCount();
                    $message = "已清除 {$count} 条 {$level} 级别的日志";
                    break;
                    
                case 'days':
                    // 按天数清除
                    if ($days <= 0) {
                        throw new \Exception('请输入有效的天数');
                    }
                    
                    $date = date('Y-m-d H:i:s', strtotime("-{$days} days"));
                    $stmt = $db->prepare("DELETE FROM logs WHERE created_at < ?");
                    $stmt->execute([$date]);
                    $count = $stmt->rowCount();
                    $message = "已清除 {$count} 条 {$days} 天前的日志";
                    break;
                    
                case 'all':
                default:
                    // 清除所有日志
                    $stmt = $db->query("DELETE FROM logs");
                    $count = $stmt->rowCount();
                    $message = "已清除所有日志，共 {$count} 条";
                    break;
            }
            
            // 记录操作日志
            Logger::info('清除系统日志', [
                'type' => $type,
                'level' => $level,
                'days' => $days,
                'count' => $count,
                'user_id' => $_SESSION['user_id'] ?? 0
            ]);
            
            $_SESSION['flash_message'] = $message;
            $_SESSION['flash_message_type'] = 'success';
        } catch (\Exception $e) {
            $_SESSION['flash_message'] = '清除日志失败: ' . $e->getMessage();
            $_SESSION['flash_message_type'] = 'danger';
        }
        
        header('Location: /admin/logs');
        exit;
    }
    
    /**
     * 获取日志级别统计
     * 
     * @return array 日志级别统计
     */
    private function getLogLevelStats()
    {
        try {
            $db = Database::getInstance();
            $stmt = $db->query("SELECT level, COUNT(*) as count FROM logs GROUP BY level ORDER BY FIELD(level, 'critical', 'error', 'warning', 'info', 'debug')");
            $stats = $stmt->fetchAll(\PDO::FETCH_KEY_PAIR);
            
            // 确保所有级别都有数据
            $levels = ['critical', 'error', 'warning', 'info', 'debug'];
            foreach ($levels as $level) {
                if (!isset($stats[$level])) {
                    $stats[$level] = 0;
                }
            }
            
            return $stats;
        } catch (\Exception $e) {
            Logger::error('获取日志级别统计失败: ' . $e->getMessage());
            return [
                'critical' => 0,
                'error' => 0,
                'warning' => 0,
                'info' => 0,
                'debug' => 0
            ];
        }
    }
} 