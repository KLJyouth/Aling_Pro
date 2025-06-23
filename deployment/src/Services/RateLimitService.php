<?php
/**
 * 速率限制服务类
 * 提供API请求速率限制功能，防止滥用和过载
 * 
 * @package AlingAi\Services
 * @version 2.0.0
 * @author AlingAi Team
 * @created 2024-12-19
 */

declare(strict_types=1);

namespace AlingAi\Services;

use Monolog\Logger;
use AlingAi\Exceptions\RateLimitExceededException;

class RateLimitService
{
    private CacheService $cache;
    private Logger $logger;
    
    // 默认限制配置
    private array $defaultLimits = [
        'api' => [
            'requests' => 100,
            'window' => 3600, // 1小时
            'burst' => 10,    // 突发请求数
            'burst_window' => 60 // 突发窗口（秒）
        ],
        'auth' => [
            'requests' => 10,
            'window' => 300, // 5分钟
            'burst' => 3,
            'burst_window' => 60
        ],
        'chat' => [
            'requests' => 50,
            'window' => 3600,
            'burst' => 5,
            'burst_window' => 60
        ],
        'upload' => [
            'requests' => 20,
            'window' => 3600,
            'burst' => 3,
            'burst_window' => 300
        ],
        'admin' => [
            'requests' => 1000,
            'window' => 3600,
            'burst' => 50,
            'burst_window' => 60
        ]
    ];
    
    // 用户级别限制配置
    private array $userLevelLimits = [
        'free' => [
            'multiplier' => 1.0,
            'daily_requests' => 1000,
            'monthly_requests' => 10000
        ],
        'pro' => [
            'multiplier' => 5.0,
            'daily_requests' => 10000,
            'monthly_requests' => 100000
        ],
        'enterprise' => [
            'multiplier' => 20.0,
            'daily_requests' => 100000,
            'monthly_requests' => 1000000
        ],
        'admin' => [
            'multiplier' => 100.0,
            'daily_requests' => -1, // 无限制
            'monthly_requests' => -1
        ]
    ];
    
    public function __construct(CacheService $cache, Logger $logger)
    {
        $this->cache = $cache;
        $this->logger = $logger;
    }
      /**
     * 检查速率限制
     * 
     * @param string $identifier 标识符（通常是用户ID或IP地址）
     * @param string $action 操作类型（api, auth, chat, upload等）
     * @param array $options 选项配置
     * @return array 返回限制信息
     * @throws RateLimitExceededException
     */
    public function checkLimit(string $identifier, string $action = 'api', array $options = []): array
    {
        $userLevel = $options['user_level'] ?? 'free';
        $customLimits = $options['custom_limits'] ?? null;
        
        // 获取限制配置
        $limits = $this->getLimits($action, $userLevel, $customLimits);
        
        // 检查标准限制
        $this->checkStandardLimit($identifier, $action, $limits);
        
        // 检查突发限制
        $this->checkBurstLimit($identifier, $action, $limits);
        
        // 检查每日/每月限制
        if ($userLevel !== 'admin') {
            $this->checkDailyLimit($identifier, $userLevel);
            $this->checkMonthlyLimit($identifier, $userLevel);
        }
        
        // 记录请求
        $this->recordRequest($identifier, $action);
        
        // 返回当前状态
        return $this->getLimitStatus($identifier, $action, $limits);
    }
    
    /**
     * 简化的限制检查方法（与旧版本兼容）
     * 
     * @param string $identifier 标识符
     * @param string $action 操作类型
     * @param int $window 时间窗口（秒）
     * @param int $maxRequests 最大请求数
     * @return bool 是否允许请求
     */
    public function allow(string $identifier, string $action = 'api', int $window = 3600, int $maxRequests = 100): bool
    {
        try {
            $customLimits = [
                'requests' => $maxRequests,
                'window' => $window,
                'burst' => min(10, (int)($maxRequests * 0.1)),
                'burst_window' => 60
            ];
            
            $this->checkLimit($identifier, $action, ['custom_limits' => $customLimits]);
            return true;
        } catch (RateLimitExceededException $e) {
            return false;
        }
    }
    
    /**
     * 获取限制配置
     */
    private function getLimits(string $action, string $userLevel, ?array $customLimits): array
    {
        $baseLimits = $customLimits ?? $this->defaultLimits[$action] ?? $this->defaultLimits['api'];
        $userConfig = $this->userLevelLimits[$userLevel] ?? $this->userLevelLimits['free'];
        
        // 应用用户级别倍数
        return [
            'requests' => (int)($baseLimits['requests'] * $userConfig['multiplier']),
            'window' => $baseLimits['window'],
            'burst' => (int)($baseLimits['burst'] * $userConfig['multiplier']),
            'burst_window' => $baseLimits['burst_window']
        ];
    }
    
    /**
     * 检查标准限制
     */
    private function checkStandardLimit(string $identifier, string $action, array $limits): void
    {
        $key = "rate_limit:{$action}:{$identifier}";
        $requests = $this->cache->get($key, 0);
        
        if ($requests >= $limits['requests']) {
            $this->logger->warning('标准速率限制超出', [
                'identifier' => $identifier,
                'action' => $action,
                'requests' => $requests,
                'limit' => $limits['requests']
            ]);
            
            throw new RateLimitExceededException(
                "标准速率限制超出。最大 {$limits['requests']} 请求每 {$limits['window']} 秒",
                429,
                [
                    'limit' => $limits['requests'],
                    'window' => $limits['window'],
                    'current' => $requests,
                    'reset_time' => time() + $this->cache->getTtl($key)
                ]
            );
        }
    }
    
    /**
     * 检查突发限制
     */
    private function checkBurstLimit(string $identifier, string $action, array $limits): void
    {
        $key = "rate_limit:burst:{$action}:{$identifier}";
        $burstRequests = $this->cache->get($key, 0);
        
        if ($burstRequests >= $limits['burst']) {
            $this->logger->warning('突发速率限制超出', [
                'identifier' => $identifier,
                'action' => $action,
                'burst_requests' => $burstRequests,
                'burst_limit' => $limits['burst']
            ]);
            
            throw new RateLimitExceededException(
                "突发速率限制超出。最大 {$limits['burst']} 请求每 {$limits['burst_window']} 秒",
                429,
                [
                    'burst_limit' => $limits['burst'],
                    'burst_window' => $limits['burst_window'],
                    'current' => $burstRequests,
                    'reset_time' => time() + $this->cache->getTtl($key)
                ]
            );
        }
    }
    
    /**
     * 检查每日限制
     */
    private function checkDailyLimit(string $identifier, string $userLevel): void
    {
        $userConfig = $this->userLevelLimits[$userLevel];
        
        if ($userConfig['daily_requests'] === -1) {
            return; // 无限制
        }
        
        $key = "rate_limit:daily:{$identifier}:" . date('Y-m-d');
        $dailyRequests = $this->cache->get($key, 0);
        
        if ($dailyRequests >= $userConfig['daily_requests']) {
            $this->logger->warning('每日速率限制超出', [
                'identifier' => $identifier,
                'user_level' => $userLevel,
                'daily_requests' => $dailyRequests,
                'daily_limit' => $userConfig['daily_requests']
            ]);
            
            throw new RateLimitExceededException(
                "每日请求限制超出。最大 {$userConfig['daily_requests']} 请求每天",
                429,
                [
                    'daily_limit' => $userConfig['daily_requests'],
                    'current' => $dailyRequests,
                    'reset_time' => strtotime('tomorrow')
                ]
            );
        }
    }
    
    /**
     * 检查每月限制
     */
    private function checkMonthlyLimit(string $identifier, string $userLevel): void
    {
        $userConfig = $this->userLevelLimits[$userLevel];
        
        if ($userConfig['monthly_requests'] === -1) {
            return; // 无限制
        }
        
        $key = "rate_limit:monthly:{$identifier}:" . date('Y-m');
        $monthlyRequests = $this->cache->get($key, 0);
        
        if ($monthlyRequests >= $userConfig['monthly_requests']) {
            $this->logger->warning('每月速率限制超出', [
                'identifier' => $identifier,
                'user_level' => $userLevel,
                'monthly_requests' => $monthlyRequests,
                'monthly_limit' => $userConfig['monthly_requests']
            ]);
            
            throw new RateLimitExceededException(
                "每月请求限制超出。最大 {$userConfig['monthly_requests']} 请求每月",
                429,
                [
                    'monthly_limit' => $userConfig['monthly_requests'],
                    'current' => $monthlyRequests,
                    'reset_time' => strtotime('first day of next month')
                ]
            );
        }
    }
    
    /**
     * 记录请求
     */
    private function recordRequest(string $identifier, string $action): void
    {
        $now = time();
          // 记录标准窗口
        $limits = $this->defaultLimits[$action] ?? $this->defaultLimits['api'];
        $key = "rate_limit:{$action}:{$identifier}";
        $this->cache->incrementWithTtl($key, 1, $limits['window']);
        
        // 记录突发窗口
        $burstKey = "rate_limit:burst:{$action}:{$identifier}";
        $this->cache->incrementWithTtl($burstKey, 1, $limits['burst_window']);
        
        // 记录每日
        $dailyKey = "rate_limit:daily:{$identifier}:" . date('Y-m-d');
        $this->cache->incrementWithTtl($dailyKey, 1, 86400); // 24小时
        
        // 记录每月
        $monthlyKey = "rate_limit:monthly:{$identifier}:" . date('Y-m');
        $this->cache->incrementWithTtl($monthlyKey, 1, 2678400); // 31天
        
        // 记录请求历史（用于分析）
        $this->recordRequestHistory($identifier, $action, $now);
    }
    
    /**
     * 记录请求历史
     */
    private function recordRequestHistory(string $identifier, string $action, int $timestamp): void
    {
        $historyKey = "rate_limit:history:{$identifier}";
        $history = $this->cache->get($historyKey, []);
        
        // 只保留最近1小时的历史
        $cutoff = $timestamp - 3600;
        $history = array_filter($history, fn($record) => $record['timestamp'] > $cutoff);
        
        // 添加新记录
        $history[] = [
            'action' => $action,
            'timestamp' => $timestamp
        ];
        
        // 限制历史记录数量
        if (count($history) > 1000) {
            $history = array_slice($history, -1000);
        }
        
        $this->cache->set($historyKey, $history, 3600);
    }
    
    /**
     * 获取限制状态
     */
    public function getLimitStatus(string $identifier, string $action, array $limits = null): array
    {
        if ($limits === null) {
            $limits = $this->defaultLimits[$action] ?? $this->defaultLimits['api'];
        }
        
        $key = "rate_limit:{$action}:{$identifier}";
        $burstKey = "rate_limit:burst:{$action}:{$identifier}";
        $dailyKey = "rate_limit:daily:{$identifier}:" . date('Y-m-d');
        $monthlyKey = "rate_limit:monthly:{$identifier}:" . date('Y-m');
        
        $current = $this->cache->get($key, 0);
        $burstCurrent = $this->cache->get($burstKey, 0);
        $dailyCurrent = $this->cache->get($dailyKey, 0);
        $monthlyCurrent = $this->cache->get($monthlyKey, 0);
        
        return [
            'action' => $action,
            'identifier' => $identifier,
            'standard' => [
                'limit' => $limits['requests'],
                'current' => $current,
                'remaining' => max(0, $limits['requests'] - $current),
                'reset_time' => time() + ($this->cache->getTtl($key) ?? $limits['window'])
            ],
            'burst' => [
                'limit' => $limits['burst'],
                'current' => $burstCurrent,
                'remaining' => max(0, $limits['burst'] - $burstCurrent),
                'reset_time' => time() + ($this->cache->getTtl($burstKey) ?? $limits['burst_window'])
            ],
            'daily' => [
                'current' => $dailyCurrent,
                'reset_time' => strtotime('tomorrow')
            ],
            'monthly' => [
                'current' => $monthlyCurrent,
                'reset_time' => strtotime('first day of next month')
            ]
        ];
    }
    
    /**
     * 重置用户限制
     */
    public function resetUserLimits(string $identifier, string $action = null): bool
    {
        try {
            if ($action) {
                // 重置特定操作的限制
                $keys = [
                    "rate_limit:{$action}:{$identifier}",
                    "rate_limit:burst:{$action}:{$identifier}"
                ];
            } else {
                // 重置所有限制
                $keys = [];
                foreach (array_keys($this->defaultLimits) as $act) {
                    $keys[] = "rate_limit:{$act}:{$identifier}";
                    $keys[] = "rate_limit:burst:{$act}:{$identifier}";
                }
                $keys[] = "rate_limit:daily:{$identifier}:" . date('Y-m-d');
                $keys[] = "rate_limit:monthly:{$identifier}:" . date('Y-m');
                $keys[] = "rate_limit:history:{$identifier}";
            }
            
            foreach ($keys as $key) {
                $this->cache->delete($key);
            }
            
            $this->logger->info('重置用户速率限制', [
                'identifier' => $identifier,
                'action' => $action,
                'keys_reset' => count($keys)
            ]);
            
            return true;
        } catch (\Exception $e) {
            $this->logger->error('重置速率限制失败', [
                'identifier' => $identifier,
                'action' => $action,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * 获取用户请求统计
     */
    public function getUserStats(string $identifier): array
    {
        $stats = [];
        
        foreach (array_keys($this->defaultLimits) as $action) {
            $stats[$action] = $this->getLimitStatus($identifier, $action);
        }
        
        // 获取请求历史
        $historyKey = "rate_limit:history:{$identifier}";
        $history = $this->cache->get($historyKey, []);
        
        // 统计最近1小时的请求分布
        $hourlyStats = [];
        $now = time();
        for ($i = 0; $i < 60; $i++) {
            $minute = $now - ($i * 60);
            $minuteKey = date('H:i', $minute);
            $hourlyStats[$minuteKey] = 0;
        }
        
        foreach ($history as $record) {
            $minute = date('H:i', $record['timestamp']);
            if (isset($hourlyStats[$minute])) {
                $hourlyStats[$minute]++;
            }
        }
        
        $stats['history'] = [
            'total_requests_last_hour' => count($history),
            'hourly_distribution' => array_reverse($hourlyStats, true),
            'actions_distribution' => array_count_values(array_column($history, 'action'))
        ];
        
        return $stats;
    }
    
    /**
     * 检查IP地址限制
     */
    public function checkIpLimit(string $ip, string $action = 'api'): array
    {
        // IP限制通常比用户限制更严格
        $ipLimits = [
            'requests' => 50,
            'window' => 3600,
            'burst' => 5,
            'burst_window' => 60
        ];
        
        return $this->checkLimit("ip:{$ip}", $action, ['custom_limits' => $ipLimits]);
    }
    
    /**
     * 获取当前活跃的限制
     */
    public function getActiveLimits(): array
    {
        $pattern = "rate_limit:*";
        $keys = $this->cache->getKeys($pattern);
        
        $active = [];
        foreach ($keys as $key) {
            $parts = explode(':', $key);
            if (count($parts) >= 3) {
                $type = $parts[1];
                $action = $parts[2];
                $identifier = $parts[3] ?? 'unknown';
                
                $active[] = [
                    'type' => $type,
                    'action' => $action,
                    'identifier' => $identifier,
                    'current_requests' => $this->cache->get($key, 0),
                    'ttl' => $this->cache->getTtl($key)
                ];
            }
        }
        
        return $active;
    }
    
    /**
     * 清理过期的限制记录
     */
    public function cleanupExpiredLimits(): int
    {
        $pattern = "rate_limit:*";
        $keys = $this->cache->getKeys($pattern);
        $cleaned = 0;
        
        foreach ($keys as $key) {
            $ttl = $this->cache->getTtl($key);
            if ($ttl <= 0) {
                $this->cache->delete($key);
                $cleaned++;
            }
        }
        
        $this->logger->info('清理过期速率限制记录', ['cleaned_records' => $cleaned]);
        
        return $cleaned;
    }
    
    /**
     * 设置自定义限制
     */
    public function setCustomLimit(string $identifier, string $action, array $limits): bool
    {
        try {
            $key = "rate_limit:custom:{$action}:{$identifier}";
            $this->cache->set($key, $limits, 86400); // 24小时有效
            
            $this->logger->info('设置自定义速率限制', [
                'identifier' => $identifier,
                'action' => $action,
                'limits' => $limits
            ]);
            
            return true;
        } catch (\Exception $e) {
            $this->logger->error('设置自定义速率限制失败', [
                'identifier' => $identifier,
                'action' => $action,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * 获取自定义限制
     */
    public function getCustomLimit(string $identifier, string $action): ?array
    {
        $key = "rate_limit:custom:{$action}:{$identifier}";
        return $this->cache->get($key);
    }
}
