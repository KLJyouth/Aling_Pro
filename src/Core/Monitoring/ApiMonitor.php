<?php

namespace AlingAi\Core\Monitoring;

use PDO;
use Exception;
use DateTime;
use DateTimeZone;
use AlingAi\Core\Database\DatabaseManager;
use AlingAi\Core\Security\SecurityAnalyzer;
use AlingAi\Core\Utils\DeviceDetector;
use AlingAi\Core\Utils\IPGeolocator;

/**
 * API监控系统
 * 
 * 全局API监控和记录系统，用于跟踪所有API调用、性能指标和安全事件
 * 支持3D可视化展示和实时监控
 * 
 * @version 1.0.0
 * @author AlingAi Team
 */
class ApiMonitor
{
    /**
     * @var PDO 数据库连接
     */
    private $db;
    
    /**
     * @var string 请求ID
     */
    private $requestId;
    
    /**
     * @var float 请求开始时间
     */
    private $startTime;
    
    /**
     * @var array 存储请求数据
     */
    private $requestData;
    
    /**
     * @var array 存储响应数据
     */
    private $responseData;
    
    /**
     * @var SecurityAnalyzer 安全分析器
     */
    private $securityAnalyzer;
    
    /**
     * @var DeviceDetector 设备检测器
     */
    private $deviceDetector;
    
    /**
     * @var IPGeolocator IP位置定位器
     */
    private $ipGeolocator;
    
    /**
     * @var float 内存使用基线(字节)
     */
    private $memoryBaseline;
    
    /**
     * @var int 数据库查询计数
     */
    private $dbQueryCount = 0;
    
    /**
     * @var float 数据库查询时间
     */
    private $dbQueryTime = 0;
    
    /**
     * @var int 缓存命中计数
     */
    private $cacheHitCount = 0;
    
    /**
     * @var int 缓存未命中计数
     */
    private $cacheMissCount = 0;
    
    /**
     * @var array 存储当前请求的完整信息
     */
    private $currentCall = [];
    
    /**
     * @var bool 是否已经记录
     */
    private $isLogged = false;
    
    /**
     * 构造函数
     */
    public function __construct()
    {
        // 生成唯一请求ID
        $this->requestId = $this->generateRequestId();
        
        // 记录开始时间
        $this->startTime = microtime(true);
        
        // 记录基线内存使用
        $this->memoryBaseline = memory_get_usage();
        
        // 初始化数据库连接
        try {
            $dbManager = new DatabaseManager();
            $this->db = $dbManager->getConnection();
        } catch (Exception $e) {
            // 无法连接数据库时也能正常工作
            error_log("API监控: 无法连接到数据库: " . $e->getMessage());
        }
        
        // 初始化组件
        $this->securityAnalyzer = new SecurityAnalyzer();
        $this->deviceDetector = new DeviceDetector();
        $this->ipGeolocator = new IPGeolocator();
        
        // 初始化请求数据
        $this->initRequestData();
    }
    
    /**
     * 初始化请求数据
     */
    private function initRequestData()
    {
        // 获取请求方法和路径
        $method = $_SERVER['REQUEST_METHOD'] ?? 'UNKNOWN';
        $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        
        // 获取IP地址
        $ipAddress = $this->getClientIp();
        
        // 获取请求头
        $headers = $this->getRequestHeaders();
        
        // 解析设备信息
        $deviceInfo = $this->deviceDetector->detect($headers['user_agent'] ?? '');
        
        // 获取位置信息
        $geoInfo = $this->ipGeolocator->locate($ipAddress);
        
        // 初始化当前调用信息
        $this->currentCall = [
            'request_id' => $this->requestId,
            'path' => $path,
            'method' => $method,
            'status_code' => 200, // 默认值，会在记录响应时更新
            'user_id' => null, // 会在身份验证后更新
            'user_type' => null,
            'ip_address' => $ipAddress,
            'user_agent' => $headers['user_agent'] ?? null,
            'referer' => $headers['referer'] ?? null,
            'request_data' => null, // 会在请求结束时更新
            'response_data' => null, // 会在请求结束时更新
            'error_message' => null,
            'processing_time' => 0, // 会在请求结束时更新
            'request_size' => 0, // 会在请求结束时更新
            'response_size' => 0, // 会在请求结束时更新
            'is_encrypted' => 0,
            'encryption_type' => null,
            'is_authenticated' => 0,
            'auth_type' => null,
            'session_id' => session_id() ?: null,
            'device_info' => json_encode($deviceInfo['device'] ?? [], JSON_UNESCAPED_UNICODE),
            'device_type' => $deviceInfo['device_type'] ?? null,
            'os_info' => $deviceInfo['os'] ?? null,
            'browser_info' => $deviceInfo['browser'] ?? null,
            'country' => $geoInfo['country'] ?? null,
            'region' => $geoInfo['region'] ?? null,
            'city' => $geoInfo['city'] ?? null
        ];
    }
    
    /**
     * 生成唯一请求ID
     */
    private function generateRequestId(): string
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }
    
    /**
     * 获取客户端IP
     */
    private function getClientIp(): string
    {
        $ip = '';
        
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        }
        
        return $ip ?: 'unknown';
    }
    
    /**
     * 获取请求头
     */
    private function getRequestHeaders(): array
    {
        $headers = [];
        
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $name = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))));
                $headers[strtolower($name)] = $value;
            }
        }
        
        return $headers;
    }
    
    /**
     * 设置用户信息
     * 
     * @param int|null $userId 用户ID
     * @param string|null $userType 用户类型(user/admin/system)
     * @param string|null $authType 认证类型
     * @return self
     */
    public function setUserInfo(?int $userId, ?string $userType = 'user', ?string $authType = 'token'): self
    {
        $this->currentCall['user_id'] = $userId;
        $this->currentCall['user_type'] = $userType;
        
        if ($userId !== null) {
            $this->currentCall['is_authenticated'] = 1;
            $this->currentCall['auth_type'] = $authType;
        }
        
        return $this;
    }
    
    /**
     * 设置加密信息
     * 
     * @param bool $isEncrypted 是否加密
     * @param string|null $encryptionType 加密类型
     * @return self
     */
    public function setEncryptionInfo(bool $isEncrypted, ?string $encryptionType = null): self
    {
        $this->currentCall['is_encrypted'] = $isEncrypted ? 1 : 0;
        $this->currentCall['encryption_type'] = $encryptionType;
        
        return $this;
    }
    
    /**
     * 增加数据库查询计数
     * 
     * @param float $queryTime 查询时间(秒)
     * @return self
     */
    public function addDbQuery(float $queryTime): self
    {
        $this->dbQueryCount++;
        $this->dbQueryTime += $queryTime;
        
        return $this;
    }
    
    /**
     * 增加缓存命中计数
     * 
     * @return self
     */
    public function addCacheHit(): self
    {
        $this->cacheHitCount++;
        
        return $this;
    }
    
    /**
     * 增加缓存未命中计数
     * 
     * @return self
     */
    public function addCacheMiss(): self
    {
        $this->cacheMissCount++;
        
        return $this;
    }
    
    /**
     * 记录响应
     * 
     * @param int $statusCode HTTP状态码
     * @param array|string|null $responseData 响应数据
     * @param string|null $errorMessage 错误信息
     * @return self
     */
    public function logResponse(int $statusCode, $responseData = null, ?string $errorMessage = null): self
    {
        if ($this->isLogged) {
            return $this;
        }
        
        // 计算处理时间
        $processingTime = microtime(true) - $this->startTime;
        
        // 获取内存使用量
        $memoryUsage = memory_get_usage() - $this->memoryBaseline;
        
        // 准备请求数据(去除敏感信息)
        $requestData = $this->sanitizeData($_REQUEST);
        
        // 准备响应数据摘要(限制大小)
        $responseDataSummary = $this->createResponseSummary($responseData);
        
        // 更新当前调用信息
        $this->currentCall['status_code'] = $statusCode;
        $this->currentCall['request_data'] = json_encode($requestData, JSON_UNESCAPED_UNICODE);
        $this->currentCall['response_data'] = $responseDataSummary;
        $this->currentCall['error_message'] = $errorMessage;
        $this->currentCall['processing_time'] = $processingTime;
        $this->currentCall['request_size'] = isset($_SERVER['CONTENT_LENGTH']) ? (int)$_SERVER['CONTENT_LENGTH'] : 0;
        $this->currentCall['response_size'] = strlen($responseDataSummary);
        
        // 分析是否存在安全风险
        $securityIssues = $this->securityAnalyzer->analyze($requestData, $this->currentCall);
        
        // 保存到数据库
        try {
            if ($this->db) {
                // 保存API调用记录
                $callId = $this->saveApiCall($this->currentCall);
                
                // 保存性能指标
                $this->savePerformanceMetrics($callId, $memoryUsage);
                
                // 保存安全事件
                $this->saveSecurityEvents($callId, $securityIssues);
                
                // 更新API分析统计
                $this->updateApiAnalytics($this->currentCall);
            }
            
            $this->isLogged = true;
            
        } catch (Exception $e) {
            error_log("API监控: 保存数据失败: " . $e->getMessage());
        }
        
        return $this;
    }
    
    /**
     * 清理数据中的敏感信息
     * 
     * @param array $data 原始数据
     * @return array 清理后的数据
     */
    private function sanitizeData($data): array
    {
        if (!is_array($data)) {
            return [];
        }
        
        $sensitiveFields = ['password', 'token', 'secret', 'api_key', 'key', 'private', 'auth'];
        $sanitized = [];
        
        foreach ($data as $key => $value) {
            // 检查是否为敏感字段
            $isSensitive = false;
            foreach ($sensitiveFields as $field) {
                if (stripos($key, $field) !== false) {
                    $isSensitive = true;
                    break;
                }
            }
            
            if ($isSensitive) {
                $sanitized[$key] = '***REDACTED***';
            } elseif (is_array($value)) {
                $sanitized[$key] = $this->sanitizeData($value);
            } else {
                $sanitized[$key] = $value;
            }
        }
        
        return $sanitized;
    }
    
    /**
     * 创建响应数据摘要
     * 
     * @param mixed $responseData 响应数据
     * @return string 响应摘要的JSON字符串
     */
    private function createResponseSummary($responseData): string
    {
        // 如果为NULL返回空字符串
        if ($responseData === null) {
            return '';
        }
        
        // 转为数组处理
        if (!is_array($responseData)) {
            // 尝试解析JSON字符串
            if (is_string($responseData) && $this->isJson($responseData)) {
                $responseData = json_decode($responseData, true);
            } else {
                // 如果不是JSON字符串，返回简短的字符串描述
                $type = gettype($responseData);
                if ($type === 'string') {
                    return strlen($responseData) > 100 
                           ? substr($responseData, 0, 97) . '...' 
                           : $responseData;
                } else {
                    return "[$type]";
                }
            }
        }
        
        // 创建摘要(移除大型数据数组等)
        $summary = [];
        
        foreach ($responseData as $key => $value) {
            if (is_array($value) && count($value) > 5) {
                $summary[$key] = [
                    'type' => 'array',
                    'count' => count($value),
                    'sample' => array_slice($value, 0, 3)
                ];
            } elseif (is_string($value) && strlen($value) > 200) {
                $summary[$key] = substr($value, 0, 197) . '...';
            } else {
                $summary[$key] = $value;
            }
        }
        
        // 如果摘要太大，进一步简化
        $jsonSummary = json_encode($summary, JSON_UNESCAPED_UNICODE);
        if (strlen($jsonSummary) > 1000) {
            $keys = array_keys($responseData);
            return json_encode([
                'summary' => '响应数据过大，已省略',
                'keys' => $keys,
                'dataSize' => strlen(json_encode($responseData))
            ], JSON_UNESCAPED_UNICODE);
        }
        
        return $jsonSummary;
    }
    
    /**
     * 检查字符串是否为有效的JSON
     */
    private function isJson($string): bool
    {
        if (!is_string($string)) {
            return false;
        }
        
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }
    
    /**
     * 保存API调用记录到数据库
     */
    private function saveApiCall(array $callData): int
    {
        $sql = "INSERT INTO api_calls (
            request_id, path, method, status_code, user_id, user_type,
            ip_address, user_agent, referer, request_data, response_data, error_message,
            processing_time, request_size, response_size, is_encrypted, encryption_type,
            is_authenticated, auth_type, session_id, device_info, device_type,
            os_info, browser_info, country, region, city
        ) VALUES (
            :request_id, :path, :method, :status_code, :user_id, :user_type,
            :ip_address, :user_agent, :referer, :request_data, :response_data, :error_message,
            :processing_time, :request_size, :response_size, :is_encrypted, :encryption_type,
            :is_authenticated, :auth_type, :session_id, :device_info, :device_type,
            :os_info, :browser_info, :country, :region, :city
        )";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($callData);
        
        return $this->db->lastInsertId();
    }
    
    /**
     * 保存性能指标
     */
    private function savePerformanceMetrics(int $callId, float $memoryUsage): void
    {
        $cpuUsage = $this->getCpuUsage();
        
        $sql = "INSERT INTO api_performance_metrics (
            api_call_id, cpu_usage, memory_usage, db_query_count, 
            db_query_time, cache_hit_count, cache_miss_count
        ) VALUES (
            :api_call_id, :cpu_usage, :memory_usage, :db_query_count,
            :db_query_time, :cache_hit_count, :cache_miss_count
        )";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'api_call_id' => $callId,
            'cpu_usage' => $cpuUsage,
            'memory_usage' => $memoryUsage / 1024 / 1024, // 转为MB
            'db_query_count' => $this->dbQueryCount,
            'db_query_time' => $this->dbQueryTime,
            'cache_hit_count' => $this->cacheHitCount,
            'cache_miss_count' => $this->cacheMissCount
        ]);
    }
    
    /**
     * 保存安全事件
     */
    private function saveSecurityEvents(int $callId, array $securityIssues): void
    {
        if (empty($securityIssues)) {
            return;
        }
        
        foreach ($securityIssues as $issue) {
            $sql = "INSERT INTO api_security_events (
                api_call_id, event_type, severity, description, details
            ) VALUES (
                :api_call_id, :event_type, :severity, :description, :details
            )";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'api_call_id' => $callId,
                'event_type' => $issue['type'],
                'severity' => $issue['severity'],
                'description' => $issue['description'],
                'details' => json_encode($issue['details'] ?? null, JSON_UNESCAPED_UNICODE)
            ]);
        }
    }
    
    /**
     * 更新API分析统计
     */
    private function updateApiAnalytics(array $callData): void
    {
        $date = (new DateTime('now', new DateTimeZone('UTC')))->format('Y-m-d');
        $hour = (int)(new DateTime('now', new DateTimeZone('UTC')))->format('H');
        $path = $callData['path'];
        $isSuccess = $callData['status_code'] < 400;
        $processingTime = $callData['processing_time'];
        $dataSize = $callData['request_size'] + $callData['response_size'];
        
        // 检查记录是否存在
        $sql = "SELECT id, total_calls, success_calls, error_calls, 
                  avg_response_time, min_response_time, max_response_time, 
                  total_data_transferred
                FROM api_analytics 
                WHERE path = :path AND date = :date AND hour = :hour";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'path' => $path,
            'date' => $date,
            'hour' => $hour
        ]);
        
        $record = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($record) {
            // 更新现有记录
            $totalCalls = $record['total_calls'] + 1;
            $successCalls = $record['success_calls'] + ($isSuccess ? 1 : 0);
            $errorCalls = $record['error_calls'] + ($isSuccess ? 0 : 1);
            
            // 计算新的平均响应时间
            $avgResponseTime = (($record['avg_response_time'] * $record['total_calls']) + $processingTime) / $totalCalls;
            
            // 更新最小和最大响应时间
            $minResponseTime = min($record['min_response_time'], $processingTime);
            $maxResponseTime = max($record['max_response_time'], $processingTime);
            
            // 增加总数据传输量
            $totalDataTransferred = $record['total_data_transferred'] + $dataSize;
            
            $sql = "UPDATE api_analytics SET 
                    total_calls = :total_calls,
                    success_calls = :success_calls, 
                    error_calls = :error_calls,
                    avg_response_time = :avg_response_time,
                    min_response_time = :min_response_time,
                    max_response_time = :max_response_time,
                    total_data_transferred = :total_data_transferred,
                    updated_at = CURRENT_TIMESTAMP
                  WHERE id = :id";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'id' => $record['id'],
                'total_calls' => $totalCalls,
                'success_calls' => $successCalls,
                'error_calls' => $errorCalls,
                'avg_response_time' => $avgResponseTime,
                'min_response_time' => $minResponseTime,
                'max_response_time' => $maxResponseTime,
                'total_data_transferred' => $totalDataTransferred
            ]);
            
        } else {
            // 创建新记录
            $sql = "INSERT INTO api_analytics (
                    path, date, hour, total_calls, success_calls, error_calls,
                    avg_response_time, min_response_time, max_response_time, 
                    total_data_transferred, unique_users, unique_ips
                ) VALUES (
                    :path, :date, :hour, 1, :success_calls, :error_calls,
                    :processing_time, :processing_time, :processing_time,
                    :data_size, 1, 1
                )";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'path' => $path,
                'date' => $date,
                'hour' => $hour,
                'success_calls' => $isSuccess ? 1 : 0,
                'error_calls' => $isSuccess ? 0 : 1,
                'processing_time' => $processingTime,
                'data_size' => $dataSize
            ]);
        }
    }
    
    /**
     * 获取CPU使用率
     */
    private function getCpuUsage(): ?float
    {
        // 在Linux系统上获取CPU使用率
        if (function_exists('sys_getloadavg')) {
            $load = sys_getloadavg();
            return $load[0]; // 1分钟平均负载
        }
        
        return null;
    }
    
    /**
     * 获取请求ID
     */
    public function getRequestId(): string
    {
        return $this->requestId;
    }
} 