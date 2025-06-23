<?php

declare(strict_types=1);

namespace AlingAi\Controllers\Api;

use AlingAi\Core\Database\DatabaseManager;
use Exception;
use PDO;

/**
 * MonitorApiController
 * 
 * 为AI-3D全景API监控中心提供API端点
 * 
 * @version 1.0.0
 * @author AlingAi Team
 */
class MonitorApiController
{
    private $db;

    public function __construct()
    {
        try {
            // 使用单例模式获取数据库连接
            $this->db = DatabaseManager::getInstance()->getConnection();
        } catch (Exception $e) {
            error_log("MonitorApiController: 数据库连接失败: " . $e->getMessage());
            // 即使数据库连接失败，也要保证控制器能被实例化
            $this->db = null;
        }
    }

    /**
     * 用于测试监控端点的简单方法
     */
    public function test()
    {
        return ['status' => 'success', 'message' => '监控API工作正常'];
    }

    /**
     * 获取API调用列表(分页)
     * 支持按路径、方法、状态码、用户和日期范围进行过滤
     */
    public function getApiCalls($params)
    {
        if (!$this->db) {
            return $this->errorResponse('数据库服务不可用', 503);
        }

        $page = isset($params['page']) ? (int)$params['page'] : 1;
        $limit = isset($params['limit']) ? (int)$params['limit'] : 20;
        $offset = ($page - 1) * $limit;

        $sql = "SELECT id, request_id, path, method, status_code, user_id, user_type, ip_address, processing_time, created_at FROM api_calls WHERE 1=1";
        $countSql = "SELECT COUNT(*) FROM api_calls WHERE 1=1";
        $queryParams = [];

        // 添加过滤器
        if (!empty($params['path'])) {
            $sql .= " AND path LIKE :path";
            $countSql .= " AND path LIKE :path";
            $queryParams[':path'] = '%' . $params['path'] . '%';
        }
        if (!empty($params['method'])) {
            $sql .= " AND method = :method";
            $countSql .= " AND method = :method";
            $queryParams[':method'] = $params['method'];
        }
        if (!empty($params['status_code'])) {
            $sql .= " AND status_code = :status_code";
            $countSql .= " AND status_code = :status_code";
            $queryParams[':status_code'] = (int)$params['status_code'];
        }
        if (!empty($params['user_id'])) {
            $sql .= " AND user_id = :user_id";
            $countSql .= " AND user_id = :user_id";
            $queryParams[':user_id'] = (int)$params['user_id'];
        }
        if (!empty($params['start_date'])) {
            $sql .= " AND created_at >= :start_date";
            $countSql .= " AND created_at >= :start_date";
            $queryParams[':start_date'] = $params['start_date'];
        }
        if (!empty($params['end_date'])) {
            $sql .= " AND created_at <= :end_date";
            $countSql .= " AND created_at <= :end_date";
            $queryParams[':end_date'] = $params['end_date'];
        }
        
        $sql .= " ORDER BY created_at DESC LIMIT :limit OFFSET :offset";

        try {
            // 获取总数
            $countStmt = $this->db->prepare($countSql);
            $countStmt->execute($queryParams);
            $total = $countStmt->fetchColumn();

            // 获取数据
            $stmt = $this->db->prepare($sql);
            // 绑定查询参数
            foreach ($queryParams as $key => &$val) {
                // 根据类型确定绑定方式
                if(is_int($val)) {
                    $stmt->bindParam($key, $val, PDO::PARAM_INT);
                } else {
                    $stmt->bindParam($key, $val, PDO::PARAM_STR);
                }
            }
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'success' => true,
                'data' => $data,
                'pagination' => [
                    'total' => (int)$total,
                    'page' => $page,
                    'limit' => $limit,
                    'total_pages' => ceil($total / $limit)
                ]
            ];
        } catch (Exception $e) {
            error_log("getApiCalls错误: " . $e->getMessage());
            return $this->errorResponse('获取API调用记录失败');
        }
    }
    
    /**
     * 获取单个API调用的详细信息
     */
    public function getApiCall($params)
    {
        if (!$this->db) {
            return $this->errorResponse('数据库服务不可用', 503);
        }

        $id = $params['id'] ?? null;
        if (!$id) {
            return $this->errorResponse('缺少API调用ID', 400);
        }

        try {
            // 获取主调用数据
            $stmt = $this->db->prepare("SELECT * FROM api_calls WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $call = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$call) {
                return $this->errorResponse('未找到API调用记录', 404);
            }

            // 获取性能指标
            $perfStmt = $this->db->prepare("SELECT * FROM api_performance_metrics WHERE api_call_id = :id");
            $perfStmt->execute([':id' => $id]);
            $performance = $perfStmt->fetch(PDO::FETCH_ASSOC);
            
            // 获取安全事件
            $secStmt = $this->db->prepare("SELECT * FROM api_security_events WHERE api_call_id = :id");
            $secStmt->execute([':id' => $id]);
            $security = $secStmt->fetchAll(PDO::FETCH_ASSOC);

            // 解码JSON字段
            $call['request_data'] = json_decode($call['request_data'], true);
            $call['response_data'] = json_decode($call['response_data'], true);
            $call['device_info'] = json_decode($call['device_info'], true);

            foreach($security as &$event) {
                $event['details'] = json_decode($event['details'], true);
            }

            return [
                'success' => true,
                'data' => [
                    'call' => $call,
                    'performance' => $performance ?: null,
                    'security_events' => $security ?: []
                ]
            ];
        } catch (Exception $e) {
            error_log("getApiCall错误: " . $e->getMessage());
            return $this->errorResponse('获取API调用详情失败');
        }
    }
    
    /**
     * 为主仪表板提供聚合统计信息
     */
    public function getDashboardData($params)
    {
         if (!$this->db) {
            return $this->errorResponse('数据库服务不可用', 503);
        }

        try {
            $timeRange = $params['time_range'] ?? '24h';
            switch($timeRange) {
                case '1h':
                    $startDate = date('Y-m-d H:i:s', strtotime('-1 hour'));
                    break;
                case '7d':
                    $startDate = date('Y-m-d H:i:s', strtotime('-7 days'));
                    break;
                default:
                    $startDate = date('Y-m-d H:i:s', strtotime('-24 hours'));
            }
            $endDate = date('Y-m-d H:i:s');
            
            $dbType = $this->db->getAttribute(PDO::ATTR_DRIVER_NAME);
            $dateGroupFormat = $dbType == 'mysql' ? "DATE_FORMAT(created_at, '%Y-%m-%d %H:00:00')" : "strftime('%Y-%m-%d %H:00:00', created_at)";

            // 总调用次数
            $totalCallsStmt = $this->db->prepare("SELECT COUNT(*) FROM api_calls WHERE created_at BETWEEN :start_date AND :end_date");
            $totalCallsStmt->execute([':start_date' => $startDate, ':end_date' => $endDate]);
            $totalCalls = $totalCallsStmt->fetchColumn();

            // 错误率
            $errorCallsStmt = $this->db->prepare("SELECT COUNT(*) FROM api_calls WHERE status_code >= 400 AND created_at BETWEEN :start_date AND :end_date");
            $errorCallsStmt->execute([':start_date' => $startDate, ':end_date' => $endDate]);
            $errorCalls = $errorCallsStmt->fetchColumn();
            $errorRate = $totalCalls > 0 ? ($errorCalls / $totalCalls) * 100 : 0;
            
            // 平均响应时间
            $avgTimeStmt = $this->db->prepare("SELECT AVG(processing_time) FROM api_calls WHERE created_at BETWEEN :start_date AND :end_date");
            $avgTimeStmt->execute([':start_date' => $startDate, ':end_date' => $endDate]);
            $avgResponseTime = $avgTimeStmt->fetchColumn();
            
            // 安全事件
            $securityEventsStmt = $this->db->prepare("SELECT COUNT(*) FROM api_security_events WHERE created_at BETWEEN :start_date AND :end_date");
            $securityEventsStmt->execute([':start_date' => $startDate, ':end_date' => $endDate]);
            $securityEvents = $securityEventsStmt->fetchColumn();
            
            // 每小时调用次数图表
            $callsByHourStmt = $this->db->prepare("
                SELECT {$dateGroupFormat} as hour, COUNT(*) as count 
                FROM api_calls 
                WHERE created_at BETWEEN :start_date AND :end_date
                GROUP BY hour
                ORDER BY hour ASC
            ");
            $callsByHourStmt->execute([':start_date' => $startDate, ':end_date' => $endDate]);
            $callsByHour = $callsByHourStmt->fetchAll(PDO::FETCH_ASSOC);

            // 按调用次数排名前5的端点
            $topEndpointsStmt = $this->db->prepare("
                SELECT path, COUNT(*) as count
                FROM api_calls
                WHERE created_at BETWEEN :start_date AND :end_date
                GROUP BY path
                ORDER BY count DESC
                LIMIT 5
            ");
            $topEndpointsStmt->execute([':start_date' => $startDate, ':end_date' => $endDate]);
            $topEndpoints = $topEndpointsStmt->fetchAll(PDO::FETCH_ASSOC);
            
            return [
                'success' => true,
                'data' => [
                    'summary' => [
                        'total_calls' => (int)$totalCalls,
                        'error_rate' => round($errorRate, 2),
                        'avg_response_time_ms' => round(($avgResponseTime ?: 0) * 1000, 2),
                        'security_events' => (int)$securityEvents
                    ],
                    'calls_by_hour' => $callsByHour,
                    'top_endpoints' => $topEndpoints,
                ]
            ];
            
        } catch (Exception $e) {
            error_log("getDashboardData错误: " . $e->getMessage());
            return $this->errorResponse('获取仪表板数据失败');
        }
    }
    
    /**
     * 获取安全事件列表
     */
    public function getSecurityEvents($params)
    {
        if (!$this->db) {
            return $this->errorResponse('数据库服务不可用', 503);
        }
        
        $page = isset($params['page']) ? (int)$params['page'] : 1;
        $limit = isset($params['limit']) ? (int)$params['limit'] : 20;
        $offset = ($page - 1) * $limit;
        
        $sql = "SELECT * FROM api_security_events ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
        $countSql = "SELECT COUNT(*) FROM api_security_events";
        
        try {
            $countStmt = $this->db->query($countSql);
            $total = $countStmt->fetchColumn();

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach($data as &$event) {
                $event['details'] = json_decode($event['details'], true);
            }

            return [
                'success' => true,
                'data' => $data,
                'pagination' => [
                    'total' => (int)$total,
                    'page' => $page,
                    'limit' => $limit,
                    'total_pages' => ceil($total / $limit)
                ]
            ];
        } catch (Exception $e) {
            error_log("getSecurityEvents错误: " . $e->getMessage());
            return $this->errorResponse('获取安全事件失败');
        }
    }
    
    /**
     * 返回错误响应
     */
    private function errorResponse($message, $code = 500)
    {
        http_response_code($code);
        return [
            'success' => false,
            'error' => [
                'code' => $code,
                'message' => $message
            ]
        ];
    }
}
