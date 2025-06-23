<?php

declare(strict_types=1);

namespace AlingAi\Monitoring;

use PDO;
use Psr\Log\LoggerInterface;
use AlingAi\Security\SecurityAnalyzer;
use AlingAi\Utils\DeviceDetector;
use AlingAi\Utils\IPGeolocator;

/**
 * API监控器
 * 负责监控API调用、记录详细信息、分析安全风险
 */
class ApiMonitor
{
    private PDO $pdo;
    private LoggerInterface $logger;
    private SecurityAnalyzer $securityAnalyzer;
    private DeviceDetector $deviceDetector;
    private IPGeolocator $ipGeolocator;
    private array $config;
    
    public function __construct(
        PDO $pdo,
        LoggerInterface $logger,
        SecurityAnalyzer $securityAnalyzer,
        DeviceDetector $deviceDetector,
        IPGeolocator $ipGeolocator,
        array $config = []
    ) {
        $this->pdo = $pdo;
        $this->logger = $logger;
        $this->securityAnalyzer = $securityAnalyzer;
        $this->deviceDetector = $deviceDetector;
        $this->ipGeolocator = $ipGeolocator;
        $this->config = array_merge([
            'enable_monitoring' => true,
            'log_request_body' => false,
            'log_response_body' => false,
            'max_log_size' => 10000,
            'retention_days' => 30,
            'alert_thresholds' => [
                'error_rate' => 0.05,
                'response_time' => 5000,
                'concurrent_requests' => 100
            ]
        ], $config);
        
        $this->initializeTables();
    }
    
    /**
     * 记录API调用
     */
    public function recordApiCall(array $requestData, array $responseData = []): int
    {
        if (!$this->config['enable_monitoring']) {
            return 0;
        }
        
        try {
            $startTime = microtime(true);
            
            // 收集请求信息
            $requestInfo = $this->collectRequestInfo($requestData);
            
            // 收集设备信息
            $deviceInfo = $this->deviceDetector->detect($_SERVER['HTTP_USER_AGENT'] ?? '');
            
            // 收集地理位置信息
            $geoInfo = $this->ipGeolocator->locate($requestInfo['client_ip']);
            
            // 安全分析
            $securityInfo = $this->securityAnalyzer->analyzeRequest($requestData);
            
            // 性能指标
            $performanceInfo = $this->collectPerformanceInfo($startTime);
            
            // 保存到数据库
            $callId = $this->saveApiCall([
                'request_info' => $requestInfo,
                'device_info' => $deviceInfo,
                'geo_info' => $geoInfo,
                'security_info' => $securityInfo,
                'performance_info' => $performanceInfo,
                'response_info' => $responseData
            ]);
            
            // 记录日志
            $this->logger->info('API调用已记录', [
                'call_id' => $callId,
                'endpoint' => $requestInfo['endpoint'],
                'method' => $requestInfo['method'],
                'status_code' => $responseData['status_code'] ?? 0,
                'response_time' => $performanceInfo['response_time']
            ]);
            
            return $callId;
            
        } catch (\Exception $e) {
            $this->logger->error('记录API调用失败', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 0;
        }
    }
    
    /**
     * 收集请求信息
     */
    private function collectRequestInfo(array $requestData): array
    {
        $headers = $requestData['headers'] ?? [];
        $body = $requestData['body'] ?? '';
        
        return [
            'endpoint' => $requestData['endpoint'] ?? '',
            'method' => $requestData['method'] ?? 'GET',
            'client_ip' => $this->getClientIP(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'referer' => $_SERVER['HTTP_REFERER'] ?? '',
            'content_type' => $headers['Content-Type'] ?? '',
            'accept_language' => $headers['Accept-Language'] ?? '',
            'authorization' => $this->extractAuthInfo($headers),
            'request_size' => strlen($body),
            'query_params' => $requestData['query_params'] ?? [],
            'headers' => $this->sanitizeHeaders($headers),
            'body' => $this->config['log_request_body'] ? $this->sanitizeBody($body) : '',
            'timestamp' => date('Y-m-d H:i:s'),
            'session_id' => session_id() ?: '',
            'user_id' => $this->getCurrentUserId(),
            'api_version' => $requestData['api_version'] ?? 'v1',
            'request_id' => $requestData['request_id'] ?? uniqid()
        ];
    }
    
    /**
     * 收集性能信息
     */
    private function collectPerformanceInfo(float $startTime): array
    {
        $endTime = microtime(true);
        $responseTime = ($endTime - $startTime) * 1000; // 转换为毫秒
        
        return [
            'response_time' => round($responseTime, 2),
            'memory_usage' => memory_get_usage(true),
            'peak_memory' => memory_get_peak_usage(true),
            'cpu_usage' => $this->getCpuUsage(),
            'load_average' => function_exists('sys_getloadavg') ? sys_getloadavg() : [0, 0, 0],
            'concurrent_requests' => $this->getConcurrentRequests()
        ];
    }
    
    /**
     * 保存API调用记录
     */
    private function saveApiCall(array $data): int
    {
        $sql = "INSERT INTO api_call_logs (
            endpoint, method, client_ip, user_agent, referer, 
            content_type, accept_language, authorization, request_size,
            query_params, headers, body, timestamp, session_id, user_id,
            api_version, request_id, device_info, geo_info, security_info,
            performance_info, response_info, created_at
        ) VALUES (
            :endpoint, :method, :client_ip, :user_agent, :referer,
            :content_type, :accept_language, :authorization, :request_size,
            :query_params, :headers, :body, :timestamp, :session_id, :user_id,
            :api_version, :request_id, :device_info, :geo_info, :security_info,
            :performance_info, :response_info, NOW()
        )";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'endpoint' => $data['request_info']['endpoint'],
            'method' => $data['request_info']['method'],
            'client_ip' => $data['request_info']['client_ip'],
            'user_agent' => $data['request_info']['user_agent'],
            'referer' => $data['request_info']['referer'],
            'content_type' => $data['request_info']['content_type'],
            'accept_language' => $data['request_info']['accept_language'],
            'authorization' => $data['request_info']['authorization'],
            'request_size' => $data['request_info']['request_size'],
            'query_params' => json_encode($data['request_info']['query_params']),
            'headers' => json_encode($data['request_info']['headers']),
            'body' => $data['request_info']['body'],
            'timestamp' => $data['request_info']['timestamp'],
            'session_id' => $data['request_info']['session_id'],
            'user_id' => $data['request_info']['user_id'],
            'api_version' => $data['request_info']['api_version'],
            'request_id' => $data['request_info']['request_id'],
            'device_info' => json_encode($data['device_info']),
            'geo_info' => json_encode($data['geo_info']),
            'security_info' => json_encode($data['security_info']),
            'performance_info' => json_encode($data['performance_info']),
            'response_info' => json_encode($data['response_info'])
        ]);
        
        return (int) $this->pdo->lastInsertId();
    }
    
    /**
     * 获取API调用统计
     */
    public function getApiStats(string $period = '24h'): array
    {
        $timeFilter = $this->getTimeFilter($period);
        
        $sql = "SELECT 
            COUNT(*) as total_calls,
            COUNT(CASE WHEN JSON_EXTRACT(response_info, '$.status_code') >= 400 THEN 1 END) as error_calls,
            AVG(JSON_EXTRACT(performance_info, '$.response_time')) as avg_response_time,
            MAX(JSON_EXTRACT(performance_info, '$.response_time')) as max_response_time,
            COUNT(DISTINCT client_ip) as unique_ips,
            COUNT(DISTINCT user_id) as unique_users,
            COUNT(DISTINCT endpoint) as unique_endpoints
        FROM api_call_logs 
        WHERE created_at >= :start_time";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['start_time' => $timeFilter]);
        $stats = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // 计算错误率
        $stats['error_rate'] = $stats['total_calls'] > 0 ? 
            round($stats['error_calls'] / $stats['total_calls'], 4) : 0;
        
        // 获取热门端点
        $stats['top_endpoints'] = $this->getTopEndpoints($timeFilter);
        
        // 获取错误分布
        $stats['error_distribution'] = $this->getErrorDistribution($timeFilter);
        
        return $stats;
    }
    
    /**
     * 获取API调用列表
     */
    public function getApiCalls(int $page = 1, int $limit = 50, array $filters = []): array
    {
        $offset = ($page - 1) * $limit;
        $whereConditions = [];
        $params = [];
        
        // 构建过滤条件
        if (!empty($filters['endpoint'])) {
            $whereConditions[] = "endpoint LIKE :endpoint";
            $params['endpoint'] = '%' . $filters['endpoint'] . '%';
        }
        
        if (!empty($filters['method'])) {
            $whereConditions[] = "method = :method";
            $params['method'] = $filters['method'];
        }
        
        if (!empty($filters['status_code'])) {
            $whereConditions[] = "JSON_EXTRACT(response_info, '$.status_code') = :status_code";
            $params['status_code'] = $filters['status_code'];
        }
        
        if (!empty($filters['client_ip'])) {
            $whereConditions[] = "client_ip = :client_ip";
            $params['client_ip'] = $filters['client_ip'];
        }
        
        if (!empty($filters['date_from'])) {
            $whereConditions[] = "created_at >= :date_from";
            $params['date_from'] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $whereConditions[] = "created_at <= :date_to";
            $params['date_to'] = $filters['date_to'];
        }
        
        $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
        
        // 获取总数
        $countSql = "SELECT COUNT(*) FROM api_call_logs {$whereClause}";
        $countStmt = $this->pdo->prepare($countSql);
        $countStmt->execute($params);
        $total = $countStmt->fetchColumn();
        
        // 获取数据
        $sql = "SELECT * FROM api_call_logs {$whereClause} 
                ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }
        
        $stmt->execute();
        $calls = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return [
            'data' => $calls,
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'pages' => ceil($total / $limit)
            ]
        ];
    }
    
    /**
     * 获取单个API调用详情
     */
    public function getApiCallDetail(int $callId): ?array
    {
        $sql = "SELECT * FROM api_call_logs WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $callId]);
        
        $call = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$call) {
            return null;
        }
        
        // 解析JSON字段
        $call['query_params'] = json_decode($call['query_params'], true);
        $call['headers'] = json_decode($call['headers'], true);
        $call['device_info'] = json_decode($call['device_info'], true);
        $call['geo_info'] = json_decode($call['geo_info'], true);
        $call['security_info'] = json_decode($call['security_info'], true);
        $call['performance_info'] = json_decode($call['performance_info'], true);
        $call['response_info'] = json_decode($call['response_info'], true);
        
        return $call;
    }
    
    /**
     * 获取安全事件列表
     */
    public function getSecurityEvents(int $page = 1, int $limit = 50): array
    {
        $offset = ($page - 1) * $limit;
        
        $sql = "SELECT * FROM api_call_logs 
                WHERE JSON_EXTRACT(security_info, '$.risk_level') IN ('high', 'critical')
                ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // 解析安全信息
        foreach ($events as &$event) {
            $event['security_info'] = json_decode($event['security_info'], true);
        }
        
        return $events;
    }
    
    /**
     * 清理旧日志
     */
    public function cleanupOldLogs(): int
    {
        $retentionDays = $this->config['retention_days'];
        $cutoffDate = date('Y-m-d H:i:s', strtotime("-{$retentionDays} days"));
        
        $sql = "DELETE FROM api_call_logs WHERE created_at < :cutoff_date";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['cutoff_date' => $cutoffDate]);
        
        return $stmt->rowCount();
    }
    
    /**
     * 初始化数据库表
     */
    private function initializeTables(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS api_call_logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            endpoint VARCHAR(255) NOT NULL,
            method VARCHAR(10) NOT NULL,
            client_ip VARCHAR(45) NOT NULL,
            user_agent TEXT,
            referer TEXT,
            content_type VARCHAR(100),
            accept_language VARCHAR(100),
            authorization TEXT,
            request_size INT DEFAULT 0,
            query_params JSON,
            headers JSON,
            body LONGTEXT,
            timestamp DATETIME NOT NULL,
            session_id VARCHAR(255),
            user_id VARCHAR(255),
            api_version VARCHAR(10) DEFAULT 'v1',
            request_id VARCHAR(255),
            device_info JSON,
            geo_info JSON,
            security_info JSON,
            performance_info JSON,
            response_info JSON,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_endpoint (endpoint),
            INDEX idx_method (method),
            INDEX idx_client_ip (client_ip),
            INDEX idx_timestamp (timestamp),
            INDEX idx_user_id (user_id),
            INDEX idx_security_risk (security_info(100))
        )";
        
        $this->pdo->exec($sql);
    }
    
    /**
     * 获取客户端IP
     */
    private function getClientIP(): string
    {
        $ipKeys = ['HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'HTTP_CLIENT_IP', 'REMOTE_ADDR'];
        
        foreach ($ipKeys as $key) {
            if (!empty($_SERVER[$key])) {
                $ip = $_SERVER[$key];
                if (strpos($ip, ',') !== false) {
                    $ip = trim(explode(',', $ip)[0]);
                }
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
    
    /**
     * 提取认证信息
     */
    private function extractAuthInfo(array $headers): string
    {
        $authHeaders = ['Authorization', 'X-API-Key', 'X-Auth-Token'];
        
        foreach ($authHeaders as $header) {
            if (isset($headers[$header])) {
                return $headers[$header];
            }
        }
        
        return '';
    }
    
    /**
     * 清理请求头
     */
    private function sanitizeHeaders(array $headers): array
    {
        $sensitiveHeaders = ['authorization', 'cookie', 'x-api-key', 'x-auth-token'];
        $sanitized = [];
        
        foreach ($headers as $key => $value) {
            if (in_array(strtolower($key), $sensitiveHeaders)) {
                $sanitized[$key] = '***HIDDEN***';
            } else {
                $sanitized[$key] = $value;
            }
        }
        
        return $sanitized;
    }
    
    /**
     * 清理请求体
     */
    private function sanitizeBody(string $body): string
    {
        // 移除敏感信息
        $sensitivePatterns = [
            '/"password"\s*:\s*"[^"]*"/' => '"password": "***HIDDEN***"',
            '/"token"\s*:\s*"[^"]*"/' => '"token": "***HIDDEN***"',
            '/"api_key"\s*:\s*"[^"]*"/' => '"api_key": "***HIDDEN***"'
        ];
        
        return preg_replace(array_keys($sensitivePatterns), array_values($sensitivePatterns), $body);
    }
    
    /**
     * 获取当前用户ID
     */
    private function getCurrentUserId(): ?string
    {
        // 这里应该根据你的认证系统来获取用户ID
        return $_SESSION['user_id'] ?? null;
    }
    
    /**
     * 获取CPU使用率
     */
    private function getCpuUsage(): float
    {
        // 简化实现，实际应该使用更准确的方法
        return 0.0;
    }
    
    /**
     * 获取并发请求数
     */
    private function getConcurrentRequests(): int
    {
        // 简化实现，实际应该使用更准确的方法
        return 1;
    }
    
    /**
     * 获取时间过滤器
     */
    private function getTimeFilter(string $period): string
    {
        $filters = [
            '1h' => '1 hour ago',
            '24h' => '24 hours ago',
            '7d' => '7 days ago',
            '30d' => '30 days ago'
        ];
        
        $time = $filters[$period] ?? '24 hours ago';
        return date('Y-m-d H:i:s', strtotime($time));
    }
    
    /**
     * 获取热门端点
     */
    private function getTopEndpoints(string $timeFilter): array
    {
        $sql = "SELECT endpoint, COUNT(*) as call_count 
                FROM api_call_logs 
                WHERE created_at >= :start_time 
                GROUP BY endpoint 
                ORDER BY call_count DESC 
                LIMIT 10";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['start_time' => $timeFilter]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * 获取错误分布
     */
    private function getErrorDistribution(string $timeFilter): array
    {
        $sql = "SELECT 
                    JSON_EXTRACT(response_info, '$.status_code') as status_code,
                    COUNT(*) as count
                FROM api_call_logs 
                WHERE created_at >= :start_time 
                    AND JSON_EXTRACT(response_info, '$.status_code') >= 400
                GROUP BY JSON_EXTRACT(response_info, '$.status_code')
                ORDER BY count DESC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['start_time' => $timeFilter]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} 