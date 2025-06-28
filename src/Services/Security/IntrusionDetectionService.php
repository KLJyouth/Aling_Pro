<?php

namespace AlingAi\Services\Security;

use AlingAi\Utils\Logger;
use AlingAi\Utils\Database;

/**
 * 入侵检测服务
 * 提供系统入侵检测、分析和报警功能
 *
 * @package AlingAi\Services\Security
 */
class IntrusionDetectionService
{
    /**
     * 数据库连接
     *
     * @var Database
     */
    protected $db;
    
    /**
     * 日志记录器
     *
     * @var Logger
     */
    protected $logger;
    
    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->db = new Database();
        $this->logger = new Logger('intrusion_detection');
    }
    
    /**
     * 获取最近的入侵尝试
     * 
     * @param int $limit 限制数量
     * @return array 入侵尝试列表
     */
    public function getRecentAttempts($limit = 100)
    {
        try {
            $attempts = $this->db->query(
                "SELECT * FROM intrusion_attempts ORDER BY detected_at DESC LIMIT ?",
                [$limit]
            )->fetchAll();
            
            return $attempts;
        } catch (\Exception $e) {
            $this->logger->error('获取最近入侵尝试失败', ['error' => $e->getMessage()]);
            return [];
        }
    }
    
    /**
     * 记录入侵尝试
     * 
     * @param array $data 入侵尝试数据
     * @return bool 是否成功
     */
    public function logAttempt($data)
    {
        try {
            // 确保必要字段存在
            $requiredFields = ['type', 'source_ip', 'target', 'details'];
            foreach ($requiredFields as $field) {
                if (!isset($data[$field])) {
                    throw new \Exception('缺少必要字段: ' . $field);
                }
            }
            
            // 准备入侵尝试数据
            $attemptData = [
                'attempt_id' => $this->generateAttemptId(),
                'type' => $data['type'],
                'source_ip' => $data['source_ip'],
                'target' => $data['target'],
                'details' => is_array($data['details']) ? json_encode($data['details']) : $data['details'],
                'severity' => $data['severity'] ?? 'medium',
                'status' => $data['status'] ?? 'detected',
                'detected_at' => time(),
                'resolved_at' => null,
                'resolved_by' => null
            ];
            
            // 保存到数据库
            $this->db->insert('intrusion_attempts', $attemptData);
            
            // 记录日志
            $this->logger->warning('检测到入侵尝试', [
                'attempt_id' => $attemptData['attempt_id'],
                'type' => $attemptData['type'],
                'source_ip' => $attemptData['source_ip'],
                'severity' => $attemptData['severity']
            ]);
            
            // 如果严重程度高，触发警报
            if (in_array($attemptData['severity'], ['critical', 'high'])) {
                $this->triggerAlert($attemptData);
            }
            
            return true;
        } catch (\Exception $e) {
            $this->logger->error('记录入侵尝试失败', ['error' => $e->getMessage()]);
            return false;
        }
    }
    
    /**
     * 分析请求是否为入侵尝试
     * 
     * @param array $request 请求数据
     * @return array|null 入侵尝试数据，如果不是入侵则返回null
     */
    public function analyzeRequest($request)
    {
        // 检查SQL注入尝试
        $sqlInjection = $this->detectSqlInjection($request);
        if ($sqlInjection) {
            return $sqlInjection;
        }
        
        // 检查XSS尝试
        $xss = $this->detectXss($request);
        if ($xss) {
            return $xss;
        }
        
        // 检查路径遍历尝试
        $pathTraversal = $this->detectPathTraversal($request);
        if ($pathTraversal) {
            return $pathTraversal;
        }
        
        // 检查命令注入尝试
        $commandInjection = $this->detectCommandInjection($request);
        if ($commandInjection) {
            return $commandInjection;
        }
        
        // 检查暴力破解尝试
        $bruteForce = $this->detectBruteForce($request);
        if ($bruteForce) {
            return $bruteForce;
        }
        
        return null;
    }
    
    /**
     * 解决入侵尝试
     * 
     * @param string $attemptId 入侵尝试ID
     * @param string $resolvedBy 解决者
     * @param string $resolution 解决方案
     * @return bool 是否成功
     */
    public function resolveAttempt($attemptId, $resolvedBy, $resolution = '')
    {
        try {
            // 更新入侵尝试状态
            $this->db->update('intrusion_attempts', [
                'status' => 'resolved',
                'resolved_at' => time(),
                'resolved_by' => $resolvedBy,
                'resolution' => $resolution
            ], ['attempt_id' => $attemptId]);
            
            $this->logger->info('入侵尝试已解决', [
                'attempt_id' => $attemptId,
                'resolved_by' => $resolvedBy
            ]);
            
            return true;
        } catch (\Exception $e) {
            $this->logger->error('解决入侵尝试失败', [
                'attempt_id' => $attemptId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * 获取入侵尝试统计
     * 
     * @param int $days 天数
     * @return array 统计数据
     */
    public function getStatistics($days = 30)
    {
        try {
            $cutoffTime = time() - ($days * 86400);
            
            // 获取总数
            $total = $this->db->query(
                "SELECT COUNT(*) as count FROM intrusion_attempts WHERE detected_at > ?",
                [$cutoffTime]
            )->fetch()['count'];
            
            // 按类型统计
            $byType = $this->db->query(
                "SELECT type, COUNT(*) as count FROM intrusion_attempts WHERE detected_at > ? GROUP BY type",
                [$cutoffTime]
            )->fetchAll();
            
            // 按严重程度统计
            $bySeverity = $this->db->query(
                "SELECT severity, COUNT(*) as count FROM intrusion_attempts WHERE detected_at > ? GROUP BY severity",
                [$cutoffTime]
            )->fetchAll();
            
            // 按状态统计
            $byStatus = $this->db->query(
                "SELECT status, COUNT(*) as count FROM intrusion_attempts WHERE detected_at > ? GROUP BY status",
                [$cutoffTime]
            )->fetchAll();
            
            return [
                'total' => $total,
                'by_type' => $this->formatStatistics($byType),
                'by_severity' => $this->formatStatistics($bySeverity),
                'by_status' => $this->formatStatistics($byStatus)
            ];
        } catch (\Exception $e) {
            $this->logger->error('获取入侵尝试统计失败', ['error' => $e->getMessage()]);
            return [
                'total' => 0,
                'by_type' => [],
                'by_severity' => [],
                'by_status' => []
            ];
        }
    }
    
    /**
     * 生成唯一入侵尝试ID
     * 
     * @return string 入侵尝试ID
     */
    protected function generateAttemptId()
    {
        return 'attempt_' . uniqid() . '_' . time();
    }
    
    /**
     * 触发警报
     * 
     * @param array $attemptData 入侵尝试数据
     */
    protected function triggerAlert($attemptData)
    {
        try {
            // 准备警报数据
            $alertData = [
                'alert_id' => 'alert_' . uniqid() . '_' . time(),
                'type' => 'intrusion_attempt',
                'source' => 'intrusion_detection_service',
                'severity' => $attemptData['severity'],
                'title' => '检测到可能的入侵尝试: ' . $attemptData['type'],
                'description' => '来自 ' . $attemptData['source_ip'] . ' 的可能入侵尝试，目标: ' . $attemptData['target'],
                'details' => json_encode([
                    'attempt_id' => $attemptData['attempt_id'],
                    'type' => $attemptData['type'],
                    'source_ip' => $attemptData['source_ip'],
                    'target' => $attemptData['target'],
                    'details' => $attemptData['details']
                ]),
                'created_at' => time(),
                'acknowledged_at' => null,
                'acknowledged_by' => null,
                'resolved_at' => null,
                'resolved_by' => null
            ];
            
            // 保存警报
            $this->db->insert('alerts', $alertData);
            
            // 记录日志
            $this->logger->alert('已触发入侵尝试警报', [
                'alert_id' => $alertData['alert_id'],
                'attempt_id' => $attemptData['attempt_id'],
                'severity' => $alertData['severity']
            ]);
            
            // 发送通知
            $this->sendAlertNotification($alertData);
        } catch (\Exception $e) {
            $this->logger->error('触发警报失败', ['error' => $e->getMessage()]);
        }
    }
    
    /**
     * 发送警报通知
     * 
     * @param array $alertData 警报数据
     */
    protected function sendAlertNotification($alertData)
    {
        // 在实际应用中，这里应该发送邮件、短信或其他通知
        // 这里使用简化的实现
        $this->logger->info('发送警报通知', [
            'alert_id' => $alertData['alert_id'],
            'title' => $alertData['title']
        ]);
    }
    
    /**
     * 格式化统计数据
     * 
     * @param array $statistics 统计数据
     * @return array 格式化后的统计数据
     */
    protected function formatStatistics($statistics)
    {
        $result = [];
        foreach ($statistics as $item) {
            $key = array_keys($item)[0];
            $result[$item[$key]] = $item['count'];
        }
        return $result;
    }
    
    /**
     * 检测SQL注入尝试
     * 
     * @param array $request 请求数据
     * @return array|null 入侵尝试数据，如果不是入侵则返回null
     */
    protected function detectSqlInjection($request)
    {
        // 在实际应用中，这里应该使用更复杂的检测逻辑
        // 这里使用简化的实现
        
        $sqlInjectionPatterns = [
            '/(\s|^)(SELECT|INSERT|UPDATE|DELETE|DROP|ALTER|UNION)(\s|$)/i',
            '/(\s|^)(OR|AND)(\s+)([\'"]?\w+[\'"]?\s*=\s*[\'"]?\w+[\'"]?)/i',
            '/(\s|^)(--)/i',
            '/(\s|^)(\/\*.*\*\/)/i'
        ];
        
        foreach ($request as $key => $value) {
            if (!is_string($value)) {
                continue;
            }
            
            foreach ($sqlInjectionPatterns as $pattern) {
                if (preg_match($pattern, $value)) {
                    return [
                        'type' => 'sql_injection',
                        'source_ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                        'target' => $key,
                        'details' => [
                            'parameter' => $key,
                            'value' => $value,
                            'pattern' => $pattern
                        ],
                        'severity' => 'high',
                        'status' => 'detected'
                    ];
                }
            }
        }
        
        return null;
    }
    
    /**
     * 检测XSS尝试
     * 
     * @param array $request 请求数据
     * @return array|null 入侵尝试数据，如果不是入侵则返回null
     */
    protected function detectXss($request)
    {
        // 在实际应用中，这里应该使用更复杂的检测逻辑
        // 这里使用简化的实现
        
        $xssPatterns = [
            '/<script[^>]*>.*?<\/script>/is',
            '/<[^>]*on\w+\s*=\s*["\'][^"\']*["\'][^>]*>/i',
            '/<[^>]*javascript:[^>]*>/i'
        ];
        
        foreach ($request as $key => $value) {
            if (!is_string($value)) {
                continue;
            }
            
            foreach ($xssPatterns as $pattern) {
                if (preg_match($pattern, $value)) {
                    return [
                        'type' => 'xss',
                        'source_ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                        'target' => $key,
                        'details' => [
                            'parameter' => $key,
                            'value' => $value,
                            'pattern' => $pattern
                        ],
                        'severity' => 'high',
                        'status' => 'detected'
                    ];
                }
            }
        }
        
        return null;
    }
    
    /**
     * 检测路径遍历尝试
     * 
     * @param array $request 请求数据
     * @return array|null 入侵尝试数据，如果不是入侵则返回null
     */
    protected function detectPathTraversal($request)
    {
        // 在实际应用中，这里应该使用更复杂的检测逻辑
        // 这里使用简化的实现
        
        $pathTraversalPatterns = [
            '/(\.\.[\/\\\\])+/i',
            '/[\/\\\\]etc[\/\\\\]passwd/i',
            '/[\/\\\\]windows[\/\\\\]win.ini/i'
        ];
        
        foreach ($request as $key => $value) {
            if (!is_string($value)) {
                continue;
            }
            
            foreach ($pathTraversalPatterns as $pattern) {
                if (preg_match($pattern, $value)) {
                    return [
                        'type' => 'path_traversal',
                        'source_ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                        'target' => $key,
                        'details' => [
                            'parameter' => $key,
                            'value' => $value,
                            'pattern' => $pattern
                        ],
                        'severity' => 'high',
                        'status' => 'detected'
                    ];
                }
            }
        }
        
        return null;
    }
    
    /**
     * 检测命令注入尝试
     * 
     * @param array $request 请求数据
     * @return array|null 入侵尝试数据，如果不是入侵则返回null
     */
    protected function detectCommandInjection($request)
    {
        // 在实际应用中，这里应该使用更复杂的检测逻辑
        // 这里使用简化的实现
        
        $commandInjectionPatterns = [
            '/(\||;|&|`|\$\(|\$\{)/i',
            '/(wget|curl|bash|sh|nc|netcat)\s/i'
        ];
        
        foreach ($request as $key => $value) {
            if (!is_string($value)) {
                continue;
            }
            
            foreach ($commandInjectionPatterns as $pattern) {
                if (preg_match($pattern, $value)) {
                    return [
                        'type' => 'command_injection',
                        'source_ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                        'target' => $key,
                        'details' => [
                            'parameter' => $key,
                            'value' => $value,
                            'pattern' => $pattern
                        ],
                        'severity' => 'critical',
                        'status' => 'detected'
                    ];
                }
            }
        }
        
        return null;
    }
    
    /**
     * 检测暴力破解尝试
     * 
     * @param array $request 请求数据
     * @return array|null 入侵尝试数据，如果不是入侵则返回null
     */
    protected function detectBruteForce($request)
    {
        // 在实际应用中，这里应该检查登录失败次数等
        // 这里使用简化的实现
        
        // 检查是否是登录请求
        if (isset($request['action']) && $request['action'] === 'login') {
            $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
            
            // 检查最近的失败次数
            $failedAttempts = $this->getRecentFailedAttempts($ip);
            
            if ($failedAttempts >= 5) {
                return [
                    'type' => 'brute_force',
                    'source_ip' => $ip,
                    'target' => 'login',
                    'details' => [
                        'failed_attempts' => $failedAttempts,
                        'username' => $request['username'] ?? 'unknown'
                    ],
                    'severity' => 'medium',
                    'status' => 'detected'
                ];
            }
        }
        
        return null;
    }
    
    /**
     * 获取最近的失败尝试次数
     * 
     * @param string $ip IP地址
     * @param int $minutes 分钟数
     * @return int 失败次数
     */
    protected function getRecentFailedAttempts($ip, $minutes = 30)
    {
        try {
            $cutoffTime = time() - ($minutes * 60);
            
            $count = $this->db->query(
                "SELECT COUNT(*) as count FROM login_attempts WHERE ip = ? AND success = 0 AND attempt_time > ?",
                [$ip, $cutoffTime]
            )->fetch()['count'];
            
            return $count;
        } catch (\Exception $e) {
            $this->logger->error('获取失败尝试次数失败', ['error' => $e->getMessage()]);
            return 0;
        }
    }
} 