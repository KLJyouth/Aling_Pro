<?php

declare(strict_types=1);

namespace AlingAi\Services;

/**
 * 简化的诊断服务类
 * 
 * 专门用于系统诊断，避免复杂的依赖链
 * 
 * @package AlingAi\Services
 * @version 1.0.0
 */
class SimpleDiagnosticsService
{
    private array $config;

    public function __construct()
    {
        $this->config = $this->loadConfig();
    }

    /**
     * 加载配置
     */
    private function loadConfig(): array
    {
        return [
            'database' => [
                'host' => getenv('DB_HOST') ?: 'localhost',
                'port' => getenv('DB_PORT') ?: '3306',
                'name' => getenv('DB_NAME') ?: 'alingai_pro',
                'user' => getenv('DB_USER') ?: 'root',
                'password' => getenv('DB_PASSWORD') ?: '',
            ],
            'redis' => [
                'host' => getenv('REDIS_HOST') ?: '127.0.0.1',
                'port' => getenv('REDIS_PORT') ?: 6379,
                'password' => getenv('REDIS_PASSWORD') ?: null,
            ],
            'websocket' => [
                'host' => getenv('WEBSOCKET_HOST') ?: 'localhost',
                'port' => getenv('WEBSOCKET_PORT') ?: 8080,
            ]
        ];
    }

    /**
     * 执行系统诊断
     */
    public function runDiagnostics(): array
    {
        $results = [
            'timestamp' => date('Y-m-d H:i:s'),
            'overall_status' => 'healthy',
            'categories' => []
        ];

        // 后端诊断
        $results['categories']['backend'] = $this->checkBackend();
        
        // WebSocket 诊断
        $results['categories']['websocket'] = $this->checkWebSocket();
        
        // 前端诊断
        $results['categories']['frontend'] = $this->checkFrontend();
        
        // 性能诊断
        $results['categories']['performance'] = $this->checkPerformance();

        // 确定总体状态
        $allPassed = true;
        foreach ($results['categories'] as $category) {
            if ($category['status'] !== 'healthy') {
                $allPassed = false;
                break;
            }
        }
        
        $results['overall_status'] = $allPassed ? 'healthy' : 'warning';

        return $results;
    }

    /**
     * 检查后端服务
     */
    private function checkBackend(): array
    {
        $tests = [];
        $passed = 0;
        $total = 0;

        // PHP 版本检查
        $total++;
        $phpVersion = PHP_VERSION;
        $isPhpOk = version_compare($phpVersion, '8.0', '>=');
        $tests[] = [
            'name' => 'PHP版本检查',
            'status' => $isPhpOk ? 'pass' : 'fail',
            'message' => "PHP版本: {$phpVersion}",
            'details' => $isPhpOk ? '版本符合要求' : '需要PHP 8.0或更高版本'
        ];
        if ($isPhpOk) $passed++;

        // 数据库连接检查
        $total++;
        $dbResult = $this->testDatabaseConnection();
        $tests[] = $dbResult;
        if ($dbResult['status'] === 'pass') $passed++;

        // 缓存系统检查
        $total++;
        $cacheResult = $this->testCache();
        $tests[] = $cacheResult;
        if ($cacheResult['status'] === 'pass') $passed++;

        // 文件权限检查
        $total++;
        $permResult = $this->testFilePermissions();
        $tests[] = $permResult;
        if ($permResult['status'] === 'pass') $passed++;

        return [
            'name' => '后端服务',
            'status' => $passed === $total ? 'healthy' : 'warning',
            'passed' => $passed,
            'total' => $total,
            'tests' => $tests
        ];
    }

    /**
     * 检查WebSocket服务
     */
    private function checkWebSocket(): array
    {
        $tests = [];
        $passed = 0;
        $total = 0;

        // WebSocket端口检查
        $total++;
        $wsResult = $this->testWebSocketPort();
        $tests[] = $wsResult;
        if ($wsResult['status'] === 'pass') $passed++;

        return [
            'name' => 'WebSocket服务',
            'status' => $passed === $total ? 'healthy' : 'warning',
            'passed' => $passed,
            'total' => $total,
            'tests' => $tests
        ];
    }

    /**
     * 检查前端资源
     */
    private function checkFrontend(): array
    {
        $tests = [];
        $passed = 0;
        $total = 0;

        // 静态资源检查
        $total++;
        $staticResult = $this->testStaticResources();
        $tests[] = $staticResult;
        if ($staticResult['status'] === 'pass') $passed++;

        // API端点检查
        $total++;
        $apiResult = $this->testApiEndpoints();
        $tests[] = $apiResult;
        if ($apiResult['status'] === 'pass') $passed++;

        return [
            'name' => '前端资源',
            'status' => $passed === $total ? 'healthy' : 'warning',
            'passed' => $passed,
            'total' => $total,
            'tests' => $tests
        ];
    }

    /**
     * 检查系统性能
     */
    private function checkPerformance(): array
    {
        $tests = [];
        $passed = 0;
        $total = 0;

        // 内存使用检查
        $total++;
        $memoryResult = $this->testMemoryUsage();
        $tests[] = $memoryResult;
        if ($memoryResult['status'] === 'pass') $passed++;

        // 磁盘空间检查
        $total++;
        $diskResult = $this->testDiskSpace();
        $tests[] = $diskResult;
        if ($diskResult['status'] === 'pass') $passed++;

        // 响应时间检查
        $total++;
        $responseResult = $this->testResponseTime();
        $tests[] = $responseResult;
        if ($responseResult['status'] === 'pass') $passed++;

        return [
            'name' => '系统性能',
            'status' => $passed === $total ? 'healthy' : 'warning',
            'passed' => $passed,
            'total' => $total,
            'tests' => $tests
        ];
    }

    /**
     * 测试数据库连接
     */
    private function testDatabaseConnection(): array
    {
        try {
            $config = $this->config['database'];
            $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['name']};charset=utf8mb4";
            
            $pdo = new \PDO($dsn, $config['user'], $config['password'], [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                \PDO::ATTR_TIMEOUT => 5
            ]);
            
            $pdo->query('SELECT 1');
            
            return [
                'name' => '数据库连接',
                'status' => 'pass',
                'message' => '数据库连接正常',
                'details' => "连接到 {$config['host']}:{$config['port']}"
            ];
        } catch (\Exception $e) {
            return [
                'name' => '数据库连接',
                'status' => 'fail',
                'message' => '数据库连接失败',
                'details' => $e->getMessage()
            ];
        }
    }    /**
     * 测试缓存系统
     */
    private function testCache(): array
    {
        try {
            // 检查Redis扩展是否可用
            if (!class_exists('Redis')) {
                return [
                    'name' => '缓存系统',
                    'status' => 'fail',
                    'message' => 'Redis扩展未安装',
                    'details' => 'PHP Redis扩展未找到，无法连接到Redis服务器'
                ];
            }
            
            // 检查Redis连接
            $config = $this->config['redis'];
            $redis = new \Redis();
            $result = $redis->connect($config['host'], (int)$config['port'], 5);
            
            if (!$result) {
                throw new \Exception('无法连接到Redis服务器');
            }
            
            if ($config['password']) {
                $redis->auth($config['password']);
            }
            
            $redis->ping();
            $redis->close();
            
            return [
                'name' => '缓存系统',
                'status' => 'pass',
                'message' => 'Redis连接正常',
                'details' => "连接到 {$config['host']}:{$config['port']}"
            ];
        } catch (\Exception $e) {
            return [
                'name' => '缓存系统',
                'status' => 'fail',
                'message' => 'Redis连接失败',
                'details' => $e->getMessage()
            ];
        }
    }

    /**
     * 测试文件权限
     */
    private function testFilePermissions(): array
    {
        $paths = [
            dirname(__DIR__, 2) . '/storage',
            dirname(__DIR__, 2) . '/public',
            dirname(__DIR__, 2) . '/logs'
        ];

        $issues = [];
        foreach ($paths as $path) {
            if (!is_dir($path)) {
                $issues[] = "目录不存在: {$path}";
            } elseif (!is_writable($path)) {
                $issues[] = "目录不可写: {$path}";
            }
        }

        return [
            'name' => '文件权限',
            'status' => empty($issues) ? 'pass' : 'fail',
            'message' => empty($issues) ? '文件权限正常' : '文件权限问题',
            'details' => empty($issues) ? '所有目录权限正常' : implode(', ', $issues)
        ];
    }

    /**
     * 测试WebSocket端口
     */
    private function testWebSocketPort(): array
    {
        $config = $this->config['websocket'];
        $host = $config['host'];
        $port = (int)$config['port'];

        $connection = @fsockopen($host, $port, $errno, $errstr, 5);
        
        if ($connection) {
            fclose($connection);
            return [
                'name' => 'WebSocket端口',
                'status' => 'pass',
                'message' => 'WebSocket端口可访问',
                'details' => "端口 {$host}:{$port} 正常"
            ];
        }

        return [
            'name' => 'WebSocket端口',
            'status' => 'fail',
            'message' => 'WebSocket端口不可访问',
            'details' => "无法连接到 {$host}:{$port} - {$errstr}"
        ];
    }

    /**
     * 测试静态资源
     */
    private function testStaticResources(): array
    {
        $publicPath = dirname(__DIR__, 2) . '/public';
        $requiredFiles = [
            'index.html',
            'admin-enhanced.html',
            'css/style.css',
            'js/app.js'
        ];

        $missing = [];
        foreach ($requiredFiles as $file) {
            if (!file_exists($publicPath . '/' . $file)) {
                $missing[] = $file;
            }
        }

        return [
            'name' => '静态资源',
            'status' => empty($missing) ? 'pass' : 'fail',
            'message' => empty($missing) ? '静态资源完整' : '缺少静态资源',
            'details' => empty($missing) ? '所有必需文件存在' : '缺少: ' . implode(', ', $missing)
        ];
    }

    /**
     * 测试API端点
     */
    private function testApiEndpoints(): array
    {
        try {
            $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
            $protocol = isset($_SERVER['HTTPS']) ? 'https' : 'http';
            $baseUrl = "{$protocol}://{$host}";
            
            // 测试API状态端点
            $context = stream_context_create([
                'http' => [
                    'method' => 'GET',
                    'timeout' => 5,
                    'ignore_errors' => true
                ]
            ]);
            
            $response = @file_get_contents("{$baseUrl}/api/system/status", false, $context);
            
            if ($response !== false) {
                return [
                    'name' => 'API端点',
                    'status' => 'pass',
                    'message' => 'API端点可访问',
                    'details' => 'API响应正常'
                ];
            }
            
            return [
                'name' => 'API端点',
                'status' => 'fail',
                'message' => 'API端点不可访问',
                'details' => '无法获取API响应'
            ];
        } catch (\Exception $e) {
            return [
                'name' => 'API端点',
                'status' => 'fail',
                'message' => 'API测试失败',
                'details' => $e->getMessage()
            ];
        }
    }    /**
     * 测试内存使用
     */
    private function testMemoryUsage(): array
    {
        $memoryLimit = ini_get('memory_limit');
        $memoryUsage = memory_get_usage(true);
        $memoryPeak = memory_get_peak_usage(true);
        
        $limitBytes = $this->parseMemoryLimit($memoryLimit);
        $usagePercent = $limitBytes > 0 ? ($memoryUsage / $limitBytes) * 100 : 0;
        
        $status = $usagePercent < 80 ? 'pass' : 'fail';
        
        return [
            'name' => '内存使用',
            'status' => $status,
            'message' => "内存使用率: " . round($usagePercent, 2) . "%",
            'details' => "当前: " . $this->formatBytes((int)$memoryUsage) . 
                        ", 峰值: " . $this->formatBytes((int)$memoryPeak) . 
                        ", 限制: {$memoryLimit}"
        ];
    }    /**
     * 测试磁盘空间
     */
    private function testDiskSpace(): array
    {
        $path = dirname(__DIR__, 2);
        $freeBytes = disk_free_space($path);
        $totalBytes = disk_total_space($path);
        
        if ($freeBytes === false || $totalBytes === false) {
            return [
                'name' => '磁盘空间',
                'status' => 'fail',
                'message' => '无法获取磁盘信息',
                'details' => '磁盘空间检查失败'
            ];
        }
        
        $usedPercent = (($totalBytes - $freeBytes) / $totalBytes) * 100;
        $status = $usedPercent < 90 ? 'pass' : 'fail';
        
        return [
            'name' => '磁盘空间',
            'status' => $status,
            'message' => "磁盘使用率: " . round($usedPercent, 2) . "%",
            'details' => "可用: " . $this->formatBytes((int)$freeBytes) . 
                        ", 总计: " . $this->formatBytes((int)$totalBytes)
        ];
    }

    /**
     * 测试响应时间
     */
    private function testResponseTime(): array
    {
        $startTime = microtime(true);
        
        // 执行一个简单的操作来测试响应时间
        for ($i = 0; $i < 1000; $i++) {
            $dummy = md5((string)$i);
        }
        
        $endTime = microtime(true);
        $responseTime = ($endTime - $startTime) * 1000; // 转换为毫秒
        
        $status = $responseTime < 100 ? 'pass' : 'fail';
        
        return [
            'name' => '响应时间',
            'status' => $status,
            'message' => "响应时间: " . round($responseTime, 2) . "ms",
            'details' => $status === 'pass' ? '响应时间正常' : '响应时间过长'
        ];
    }

    /**
     * 解析内存限制
     */
    private function parseMemoryLimit(string $limit): int
    {
        if ($limit === '-1') {
            return 0; // 无限制
        }
        
        $unit = strtoupper(substr($limit, -1));
        $value = (int)substr($limit, 0, -1);
        
        switch ($unit) {
            case 'G':
                return $value * 1024 * 1024 * 1024;
            case 'M':
                return $value * 1024 * 1024;
            case 'K':
                return $value * 1024;
            default:
                return (int)$limit;
        }
    }

    /**
     * 格式化字节数
     */
    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $unitIndex = 0;
        
        while ($bytes >= 1024 && $unitIndex < count($units) - 1) {
            $bytes /= 1024;
            $unitIndex++;
        }
        
        return round($bytes, 2) . ' ' . $units[$unitIndex];
    }
}
