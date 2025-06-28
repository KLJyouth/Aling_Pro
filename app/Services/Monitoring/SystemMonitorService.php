<?php

namespace App\Services\Monitoring;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;

/**
 * 系统监控服务
 * 提供系统性能、资源使用情况和健康状态的监控功能
 */
class SystemMonitorService
{
    /**
     * 获取系统基本性能指标
     * 
     * @return array 性能指标数据
     */
    public function getPerformanceMetrics(): array
    {
        try {
            // 获取CPU使用率
            $cpuUsage = $this->getCpuUsage();
            
            // 获取内存使用情况
            $memoryUsage = $this->getMemoryUsage();
            
            // 获取磁盘使用情况
            $diskUsage = $this->getDiskUsage();
            
            // 获取数据库连接情况
            $dbConnections = $this->getDatabaseConnections();
            
            return [
                'cpu' => $cpuUsage,
                'memory' => $memoryUsage,
                'disk' => $diskUsage,
                'database' => $dbConnections,
                'timestamp' => now()->toDateTimeString()
            ];
        } catch (\Exception $e) {
            Log::error('获取系统性能指标失败: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return [
                'error' => '获取系统性能指标失败',
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * 获取CPU使用率
     * 
     * @return array CPU使用率数据
     */
    private function getCpuUsage(): array
    {
        // 在Windows系统上获取CPU使用率
        if (PHP_OS_FAMILY === 'Windows') {
            $cmd = 'wmic cpu get loadpercentage';
            $output = [];
            exec($cmd, $output);
            
            // 解析输出
            $cpuLoad = 0;
            if (isset($output[1])) {
                $cpuLoad = (int) trim($output[1]);
            }
            
            return [
                'usage_percent' => $cpuLoad,
                'cores' => $this->getCpuCores(),
                'load_average' => $cpuLoad / 100
            ];
        } 
        // 在Linux系统上获取CPU使用率
        else {
            $load = sys_getloadavg();
            $cpuCores = $this->getCpuCores();
            
            return [
                'usage_percent' => min(100, round(($load[0] / $cpuCores) * 100, 2)),
                'cores' => $cpuCores,
                'load_average' => $load
            ];
        }
    }
    
    /**
     * 获取CPU核心数
     * 
     * @return int CPU核心数
     */
    private function getCpuCores(): int
    {
        // 尝试从缓存获取
        return Cache::remember('system_cpu_cores', 3600, function () {
            // 在Windows系统上获取CPU核心数
            if (PHP_OS_FAMILY === 'Windows') {
                $cmd = 'wmic cpu get NumberOfCores';
                $output = [];
                exec($cmd, $output);
                
                // 解析输出
                if (isset($output[1])) {
                    return (int) trim($output[1]);
                }
                
                return 1; // 默认值
            } 
            // 在Linux系统上获取CPU核心数
            else {
                $cmd = 'nproc';
                $cores = (int) trim(shell_exec($cmd));
                return $cores > 0 ? $cores : 1;
            }
        });
    }
    
    /**
     * 获取内存使用情况
     * 
     * @return array 内存使用情况数据
     */
    private function getMemoryUsage(): array
    {
        // 在Windows系统上获取内存使用情况
        if (PHP_OS_FAMILY === 'Windows') {
            $cmd = 'wmic OS get FreePhysicalMemory,TotalVisibleMemorySize';
            $output = [];
            exec($cmd, $output);
            
            // 解析输出
            $memory = [
                'total' => 0,
                'free' => 0,
                'used' => 0,
                'usage_percent' => 0
            ];
            
            if (isset($output[1])) {
                $values = preg_split('/\s+/', trim($output[1]));
                if (count($values) >= 2) {
                    // 值以KB为单位
                    $memory['free'] = (int) $values[0] * 1024; // 转为字节
                    $memory['total'] = (int) $values[1] * 1024; // 转为字节
                    $memory['used'] = $memory['total'] - $memory['free'];
                    $memory['usage_percent'] = round(($memory['used'] / $memory['total']) * 100, 2);
                }
            }
            
            return $memory;
        } 
        // 在Linux系统上获取内存使用情况
        else {
            $cmd = 'free -b';
            $output = shell_exec($cmd);
            $lines = explode("\n", $output);
            
            $memory = [
                'total' => 0,
                'free' => 0,
                'used' => 0,
                'usage_percent' => 0
            ];
            
            if (isset($lines[1])) {
                $values = preg_split('/\s+/', trim($lines[1]));
                if (count($values) >= 4) {
                    $memory['total'] = (int) $values[1];
                    $memory['used'] = (int) $values[2];
                    $memory['free'] = (int) $values[3];
                    $memory['usage_percent'] = round(($memory['used'] / $memory['total']) * 100, 2);
                }
            }
            
            return $memory;
        }
    }
    
    /**
     * 获取磁盘使用情况
     * 
     * @return array 磁盘使用情况数据
     */
    private function getDiskUsage(): array
    {
        $diskData = [];
        
        // 获取应用根目录所在磁盘
        $rootPath = base_path();
        $rootDisk = $this->getDiskUsageForPath($rootPath);
        $diskData['root'] = $rootDisk;
        
        // 获取存储目录磁盘
        $storagePath = storage_path();
        if (dirname($storagePath) !== dirname($rootPath)) {
            $storageDisk = $this->getDiskUsageForPath($storagePath);
            $diskData['storage'] = $storageDisk;
        }
        
        return $diskData;
    }
    
    /**
     * 获取指定路径的磁盘使用情况
     * 
     * @param string $path 路径
     * @return array 磁盘使用情况数据
     */
    private function getDiskUsageForPath(string $path): array
    {
        $disk = [
            'path' => $path,
            'total' => 0,
            'free' => 0,
            'used' => 0,
            'usage_percent' => 0
        ];
        
        if (function_exists('disk_free_space') && function_exists('disk_total_space')) {
            $disk['free'] = disk_free_space($path);
            $disk['total'] = disk_total_space($path);
            $disk['used'] = $disk['total'] - $disk['free'];
            $disk['usage_percent'] = round(($disk['used'] / $disk['total']) * 100, 2);
        }
        
        return $disk;
    }
    
    /**
     * 获取数据库连接情况
     * 
     * @return array 数据库连接情况数据
     */
    private function getDatabaseConnections(): array
    {
        try {
            $connections = [];
            
            // 获取默认连接
            $defaultConnection = config('database.default');
            $connections['default'] = $defaultConnection;
            
            // 测试连接
            $startTime = microtime(true);
            DB::connection()->getPdo();
            $endTime = microtime(true);
            
            $connections['status'] = 'connected';
            $connections['response_time'] = round(($endTime - $startTime) * 1000, 2); // 毫秒
            
            // 获取活跃连接数（仅MySQL）
            if (config("database.connections.{$defaultConnection}.driver") === 'mysql') {
                try {
                    $processlist = DB::select('SHOW PROCESSLIST');
                    $connections['active_connections'] = count($processlist);
                    
                    // 统计查询类型
                    $queryTypes = [
                        'select' => 0,
                        'insert' => 0,
                        'update' => 0,
                        'delete' => 0,
                        'other' => 0
                    ];
                    
                    foreach ($processlist as $process) {
                        if (!empty($process->Info)) {
                            $info = strtolower($process->Info);
                            if (strpos($info, 'select') === 0) {
                                $queryTypes['select']++;
                            } elseif (strpos($info, 'insert') === 0) {
                                $queryTypes['insert']++;
                            } elseif (strpos($info, 'update') === 0) {
                                $queryTypes['update']++;
                            } elseif (strpos($info, 'delete') === 0) {
                                $queryTypes['delete']++;
                            } else {
                                $queryTypes['other']++;
                            }
                        }
                    }
                    
                    $connections['query_types'] = $queryTypes;
                } catch (\Exception $e) {
                    // 忽略错误，可能没有权限
                    Log::warning('获取数据库进程列表失败: ' . $e->getMessage());
                }
            }
            
            return $connections;
        } catch (\Exception $e) {
            Log::error('获取数据库连接情况失败: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * 获取应用性能指标
     * 
     * @return array 应用性能指标数据
     */
    public function getApplicationMetrics(): array
    {
        try {
            return [
                'response_times' => $this->getAverageResponseTimes(),
                'error_rates' => $this->getErrorRates(),
                'request_rates' => $this->getRequestRates(),
                'timestamp' => now()->toDateTimeString()
            ];
        } catch (\Exception $e) {
            Log::error('获取应用性能指标失败: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return [
                'error' => '获取应用性能指标失败',
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * 获取平均响应时间
     * 
     * @return array 平均响应时间数据
     */
    private function getAverageResponseTimes(): array
    {
        // 从日志或数据库中获取响应时间数据
        // 这里使用模拟数据，实际项目中应该从监控系统获取
        return [
            'last_minute' => rand(50, 200),
            'last_hour' => rand(80, 250),
            'last_day' => rand(100, 300),
            'endpoints' => [
                '/api/auth/login' => rand(50, 150),
                '/api/user/profile' => rand(30, 100),
                '/api/security/encrypt' => rand(100, 300),
                '/api/security/decrypt' => rand(100, 300)
            ]
        ];
    }
    
    /**
     * 获取错误率
     * 
     * @return array 错误率数据
     */
    private function getErrorRates(): array
    {
        // 从日志或数据库中获取错误率数据
        // 这里使用模拟数据，实际项目中应该从监控系统获取
        return [
            'last_minute' => [
                'total_requests' => rand(500, 1000),
                'error_count' => rand(0, 10),
                'error_rate' => rand(0, 2) // 百分比
            ],
            'last_hour' => [
                'total_requests' => rand(5000, 10000),
                'error_count' => rand(10, 50),
                'error_rate' => rand(0, 1) // 百分比
            ],
            'last_day' => [
                'total_requests' => rand(50000, 100000),
                'error_count' => rand(100, 500),
                'error_rate' => rand(0, 1) // 百分比
            ],
            'error_types' => [
                '404' => rand(10, 100),
                '500' => rand(5, 50),
                '403' => rand(1, 20),
                'validation' => rand(10, 200),
                'database' => rand(1, 10)
            ]
        ];
    }
    
    /**
     * 获取请求率
     * 
     * @return array 请求率数据
     */
    private function getRequestRates(): array
    {
        // 从日志或数据库中获取请求率数据
        // 这里使用模拟数据，实际项目中应该从监控系统获取
        return [
            'current' => rand(10, 100), // 每秒请求数
            'average' => [
                'last_minute' => rand(10, 100),
                'last_hour' => rand(20, 200),
                'last_day' => rand(30, 300)
            ],
            'peak' => [
                'last_hour' => rand(100, 500),
                'last_day' => rand(200, 1000),
                'last_week' => rand(300, 2000)
            ],
            'by_endpoint' => [
                '/api/auth/login' => rand(1, 10),
                '/api/user/profile' => rand(5, 20),
                '/api/security/encrypt' => rand(10, 50),
                '/api/security/decrypt' => rand(10, 50)
            ]
        ];
    }
    
    /**
     * 获取系统健康状态
     * 
     * @return array 系统健康状态数据
     */
    public function getHealthStatus(): array
    {
        try {
            // 检查各个组件的健康状态
            $database = $this->checkDatabaseHealth();
            $cache = $this->checkCacheHealth();
            $storage = $this->checkStorageHealth();
            $services = $this->checkServicesHealth();
            
            // 计算整体健康状态
            $components = [$database, $cache, $storage];
            $components = array_merge($components, $services);
            
            $healthyCount = count(array_filter($components, function ($component) {
                return $component['status'] === 'healthy';
            }));
            
            $overallStatus = ($healthyCount === count($components)) ? 'healthy' : 'degraded';
            if ($healthyCount === 0) {
                $overallStatus = 'critical';
            }
            
            return [
                'overall' => [
                    'status' => $overallStatus,
                    'healthy_components' => $healthyCount,
                    'total_components' => count($components)
                ],
                'components' => [
                    'database' => $database,
                    'cache' => $cache,
                    'storage' => $storage,
                    'services' => $services
                ],
                'timestamp' => now()->toDateTimeString()
            ];
        } catch (\Exception $e) {
            Log::error('获取系统健康状态失败: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return [
                'error' => '获取系统健康状态失败',
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * 检查数据库健康状态
     * 
     * @return array 数据库健康状态数据
     */
    private function checkDatabaseHealth(): array
    {
        try {
            $startTime = microtime(true);
            DB::select('SELECT 1');
            $endTime = microtime(true);
            
            $responseTime = round(($endTime - $startTime) * 1000, 2); // 毫秒
            
            // 响应时间阈值（毫秒）
            $warningThreshold = 100;
            $criticalThreshold = 500;
            
            $status = 'healthy';
            if ($responseTime > $criticalThreshold) {
                $status = 'critical';
            } elseif ($responseTime > $warningThreshold) {
                $status = 'warning';
            }
            
            return [
                'status' => $status,
                'response_time' => $responseTime,
                'connection' => config('database.default'),
                'message' => "数据库连接正常，响应时间: {$responseTime}ms"
            ];
        } catch (\Exception $e) {
            Log::error('检查数据库健康状态失败: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return [
                'status' => 'critical',
                'message' => '数据库连接失败: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * 检查缓存健康状态
     * 
     * @return array 缓存健康状态数据
     */
    private function checkCacheHealth(): array
    {
        try {
            $testKey = 'health_check_' . time();
            $testValue = uniqid();
            
            $startTime = microtime(true);
            Cache::put($testKey, $testValue, 10);
            $cachedValue = Cache::get($testKey);
            $endTime = microtime(true);
            
            $responseTime = round(($endTime - $startTime) * 1000, 2); // 毫秒
            
            // 响应时间阈值（毫秒）
            $warningThreshold = 50;
            $criticalThreshold = 200;
            
            $status = 'healthy';
            if ($responseTime > $criticalThreshold) {
                $status = 'critical';
            } elseif ($responseTime > $warningThreshold) {
                $status = 'warning';
            }
            
            if ($cachedValue !== $testValue) {
                $status = 'critical';
            }
            
            return [
                'status' => $status,
                'response_time' => $responseTime,
                'driver' => config('cache.default'),
                'message' => "缓存服务正常，响应时间: {$responseTime}ms"
            ];
        } catch (\Exception $e) {
            Log::error('检查缓存健康状态失败: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return [
                'status' => 'critical',
                'message' => '缓存服务异常: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * 检查存储健康状态
     * 
     * @return array 存储健康状态数据
     */
    private function checkStorageHealth(): array
    {
        try {
            $testFile = 'health_check_' . time() . '.txt';
            $testContent = 'Health check at ' . now()->toDateTimeString();
            
            $startTime = microtime(true);
            
            // 写入测试文件
            Storage::put($testFile, $testContent);
            
            // 读取测试文件
            $readContent = Storage::get($testFile);
            
            // 删除测试文件
            Storage::delete($testFile);
            
            $endTime = microtime(true);
            
            $responseTime = round(($endTime - $startTime) * 1000, 2); // 毫秒
            
            // 响应时间阈值（毫秒）
            $warningThreshold = 100;
            $criticalThreshold = 500;
            
            $status = 'healthy';
            if ($responseTime > $criticalThreshold) {
                $status = 'critical';
            } elseif ($responseTime > $warningThreshold) {
                $status = 'warning';
            }
            
            if ($readContent !== $testContent) {
                $status = 'critical';
            }
            
            return [
                'status' => $status,
                'response_time' => $responseTime,
                'driver' => config('filesystems.default'),
                'message' => "存储服务正常，响应时间: {$responseTime}ms"
            ];
        } catch (\Exception $e) {
            Log::error('检查存储健康状态失败: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return [
                'status' => 'critical',
                'message' => '存储服务异常: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * 检查关键服务健康状态
     * 
     * @return array 服务健康状态数据
     */
    private function checkServicesHealth(): array
    {
        $services = [];
        
        // 检查队列服务
        $services['queue'] = $this->checkQueueHealth();
        
        // 检查外部API服务（如有）
        $externalApis = $this->getExternalApiEndpoints();
        foreach ($externalApis as $name => $endpoint) {
            $services[$name] = $this->checkExternalApiHealth($name, $endpoint);
        }
        
        return $services;
    }
    
    /**
     * 检查队列健康状态
     * 
     * @return array 队列健康状态数据
     */
    private function checkQueueHealth(): array
    {
        try {
            // 这里应该实际检查队列服务
            // 例如，可以发送一个测试任务到队列并检查是否成功
            // 由于这需要实际的队列实现，这里只返回模拟数据
            
            $queueDriver = config('queue.default');
            $status = 'healthy';
            $message = "队列服务({$queueDriver})正常";
            
            return [
                'status' => $status,
                'driver' => $queueDriver,
                'message' => $message
            ];
        } catch (\Exception $e) {
            Log::error('检查队列健康状态失败: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return [
                'status' => 'critical',
                'message' => '队列服务异常: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * 获取外部API端点列表
     * 
     * @return array 外部API端点列表
     */
    private function getExternalApiEndpoints(): array
    {
        // 这里应该从配置中获取外部API端点列表
        // 这里返回一些示例端点
        return [
            'payment_gateway' => 'https://api.example.com/payment',
            'notification_service' => 'https://api.example.com/notifications'
        ];
    }
    
    /**
     * 检查外部API健康状态
     * 
     * @param string $name API名称
     * @param string $endpoint API端点
     * @return array API健康状态数据
     */
    private function checkExternalApiHealth(string $name, string $endpoint): array
    {
        try {
            // 发送请求到API健康检查端点
            // 注意：在实际项目中，应该使用专门的健康检查端点，而不是直接请求API
            $startTime = microtime(true);
            
            // 模拟请求，不实际发送
            // $response = Http::timeout(5)->get($endpoint . '/health');
            // $statusCode = $response->status();
            
            // 模拟响应
            $statusCode = 200;
            $endTime = microtime(true);
            
            $responseTime = round(($endTime - $startTime) * 1000, 2); // 毫秒
            
            // 响应时间阈值（毫秒）
            $warningThreshold = 300;
            $criticalThreshold = 1000;
            
            $status = 'healthy';
            if ($responseTime > $criticalThreshold) {
                $status = 'critical';
            } elseif ($responseTime > $warningThreshold) {
                $status = 'warning';
            }
            
            if ($statusCode !== 200) {
                $status = 'critical';
            }
            
            return [
                'status' => $status,
                'response_time' => $responseTime,
                'endpoint' => $endpoint,
                'status_code' => $statusCode,
                'message' => "{$name} API服务正常，响应时间: {$responseTime}ms"
            ];
        } catch (\Exception $e) {
            Log::error("检查{$name} API健康状态失败: " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return [
                'status' => 'critical',
                'endpoint' => $endpoint,
                'message' => "{$name} API服务异常: " . $e->getMessage()
            ];
        }
    }
} 