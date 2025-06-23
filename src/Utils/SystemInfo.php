<?php

namespace AlingAi\Utils;

/**
 * 系统信息工具类
 * 提供系统性能监控和信息获取功能
 */
class SystemInfo
{
    /**
     * 获取系统基本信息
     */
    public static function getSystemInfo(): array
    {
        return [
            'php_version' => PHP_VERSION,
            'php_sapi' => PHP_SAPI,
            'os' => PHP_OS,
            'architecture' => php_uname('m'),
            'hostname' => php_uname('n'),
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'unknown',
            'document_root' => $_SERVER['DOCUMENT_ROOT'] ?? '',
            'server_time' => date('Y-m-d H:i:s'),
            'timezone' => date_default_timezone_get(),
            'max_execution_time' => ini_get('max_execution_time'),
            'memory_limit' => ini_get('memory_limit'),
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'post_max_size' => ini_get('post_max_size')
        ];
    }
    
    /**
     * 获取内存使用情况
     */
    public static function getMemoryUsage(): array
    {
        return [
            'current_usage' => memory_get_usage(true),
            'current_usage_formatted' => self::formatBytes(memory_get_usage(true)),
            'peak_usage' => memory_get_peak_usage(true),
            'peak_usage_formatted' => self::formatBytes(memory_get_peak_usage(true)),
            'memory_limit' => self::parseBytes(ini_get('memory_limit')),
            'memory_limit_formatted' => ini_get('memory_limit'),
            'usage_percentage' => round((memory_get_usage(true) / self::parseBytes(ini_get('memory_limit'))) * 100, 2)
        ];
    }
    
    /**
     * 获取CPU使用率 (Linux)
     */
    public static function getCpuUsage(): array
    {
        if (PHP_OS_FAMILY !== 'Linux') {
            return ['error' => 'CPU监控仅支持Linux系统'];
        }
        
        $loadAvg = sys_getloadavg();
        
        return [
            'load_1min' => $loadAvg[0] ?? 0,
            'load_5min' => $loadAvg[1] ?? 0,
            'load_15min' => $loadAvg[2] ?? 0,
            'cpu_count' => self::getCpuCount()
        ];
    }
    
    /**
     * 获取磁盘使用情况
     */
    public static function getDiskUsage(string $path = '/'): array
    {
        $totalBytes = disk_total_space($path);
        $freeBytes = disk_free_space($path);
        $usedBytes = $totalBytes - $freeBytes;
        
        return [
            'path' => $path,
            'total_space' => $totalBytes,
            'total_space_formatted' => self::formatBytes($totalBytes),
            'free_space' => $freeBytes,
            'free_space_formatted' => self::formatBytes($freeBytes),
            'used_space' => $usedBytes,
            'used_space_formatted' => self::formatBytes($usedBytes),
            'usage_percentage' => $totalBytes > 0 ? round(($usedBytes / $totalBytes) * 100, 2) : 0
        ];
    }
    
    /**
     * 获取网络信息
     */
    public static function getNetworkInfo(): array
    {
        return [
            'server_ip' => $_SERVER['SERVER_ADDR'] ?? 'unknown',
            'client_ip' => self::getClientIp(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'server_port' => $_SERVER['SERVER_PORT'] ?? 80,
            'https' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
            'request_method' => $_SERVER['REQUEST_METHOD'] ?? 'GET',
            'request_uri' => $_SERVER['REQUEST_URI'] ?? '/'
        ];
    }
    
    /**
     * 获取PHP扩展信息
     */
    public static function getPhpExtensions(): array
    {
        $loadedExtensions = get_loaded_extensions();
        sort($loadedExtensions);
        
        $importantExtensions = [
            'mysql', 'mysqli', 'pdo', 'pdo_mysql',
            'redis', 'memcached', 'gd', 'curl', 'json',
            'mbstring', 'openssl', 'zip', 'xml'
        ];
        
        $extensionStatus = [];
        foreach ($importantExtensions as $extension) {
            $extensionStatus[$extension] = extension_loaded($extension);
        }
        
        return [
            'total_count' => count($loadedExtensions),
            'loaded_extensions' => $loadedExtensions,
            'important_extensions' => $extensionStatus
        ];
    }
    
    /**
     * 获取数据库连接信息
     */
    public static function getDatabaseInfo(): array
    {
        try {
            $config = require __DIR__ . '/../../config/database.php';
            
            $pdo = new \PDO(
                "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']}",
                $config['username'],
                $config['password'],
                [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]
            );
            
            $version = $pdo->query('SELECT VERSION()')->fetchColumn();
            $status = $pdo->query('SHOW STATUS')->fetchAll(\PDO::FETCH_KEY_PAIR);
            
            return [
                'connected' => true,
                'host' => $config['host'],
                'port' => $config['port'],
                'database' => $config['database'],
                'version' => $version,
                'uptime' => $status['Uptime'] ?? 0,
                'connections' => $status['Connections'] ?? 0,
                'max_connections' => $status['max_connections'] ?? 0,
                'threads_connected' => $status['Threads_connected'] ?? 0
            ];
        } catch (\Exception $e) {
            return [
                'connected' => false,
                'error' => $e->getMessage()
            ];
        }
    }    /**
     * 获取Redis连接信息
     */
    public static function getRedisInfo(): array
    {
        if (!extension_loaded('redis')) {
            return ['error' => 'Redis扩展未安装'];
        }
        
        try {
            $config = require __DIR__ . '/../../config/cache.php';
            
            // 使用字符串方式实例化Redis类避免类型检查错误
            $redisClass = '\Redis';
            if (!class_exists($redisClass)) {
                return ['error' => 'Redis类不存在'];
            }
            
            $redis = new $redisClass();
            $redis->connect($config['redis']['host'], $config['redis']['port']);
            
            if (!empty($config['redis']['password'])) {
                $redis->auth($config['redis']['password']);
            }
            
            $info = $redis->info();
            
            return [
                'connected' => true,
                'host' => $config['redis']['host'],
                'port' => $config['redis']['port'],
                'version' => $info['redis_version'] ?? 'unknown',
                'uptime' => $info['uptime_in_seconds'] ?? 0,
                'connected_clients' => $info['connected_clients'] ?? 0,
                'used_memory' => $info['used_memory'] ?? 0,
                'used_memory_formatted' => self::formatBytes($info['used_memory'] ?? 0),
                'keyspace' => $redis->dbSize()
            ];
        } catch (\Exception $e) {
            return [
                'connected' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * 运行健康检查
     */
    public static function healthCheck(): array
    {
        $checks = [];
        
        // PHP版本检查
        $checks['php_version'] = [
            'status' => version_compare(PHP_VERSION, '7.4.0', '>=') ? 'pass' : 'fail',
            'message' => 'PHP版本: ' . PHP_VERSION,
            'required' => 'PHP 7.4+'
        ];
        
        // 内存检查
        $memoryUsage = self::getMemoryUsage();
        $checks['memory_usage'] = [
            'status' => $memoryUsage['usage_percentage'] < 80 ? 'pass' : 'warning',
            'message' => '内存使用率: ' . $memoryUsage['usage_percentage'] . '%',
            'current' => $memoryUsage['current_usage_formatted'],
            'limit' => $memoryUsage['memory_limit_formatted']
        ];
        
        // 磁盘空间检查
        $diskUsage = self::getDiskUsage();
        $checks['disk_space'] = [
            'status' => $diskUsage['usage_percentage'] < 90 ? 'pass' : 'warning',
            'message' => '磁盘使用率: ' . $diskUsage['usage_percentage'] . '%',
            'free_space' => $diskUsage['free_space_formatted']
        ];
        
        // 数据库连接检查
        $dbInfo = self::getDatabaseInfo();
        $checks['database'] = [
            'status' => $dbInfo['connected'] ? 'pass' : 'fail',
            'message' => $dbInfo['connected'] ? '数据库连接正常' : '数据库连接失败',
            'details' => $dbInfo
        ];
        
        // Redis连接检查
        $redisInfo = self::getRedisInfo();
        $checks['redis'] = [
            'status' => isset($redisInfo['connected']) && $redisInfo['connected'] ? 'pass' : 'warning',
            'message' => isset($redisInfo['connected']) && $redisInfo['connected'] ? 'Redis连接正常' : 'Redis连接失败',
            'details' => $redisInfo
        ];
        
        // 重要扩展检查
        $extensions = self::getPhpExtensions();
        $requiredExtensions = ['pdo', 'pdo_mysql', 'json', 'mbstring', 'openssl'];
        $missingExtensions = array_filter($requiredExtensions, function($ext) use ($extensions) {
            return !$extensions['important_extensions'][$ext];
        });
        
        $checks['php_extensions'] = [
            'status' => empty($missingExtensions) ? 'pass' : 'fail',
            'message' => empty($missingExtensions) ? '所有必需扩展已安装' : '缺少扩展: ' . implode(', ', $missingExtensions),
            'missing' => $missingExtensions
        ];
        
        // 计算总体状态
        $overallStatus = 'pass';
        foreach ($checks as $check) {
            if ($check['status'] === 'fail') {
                $overallStatus = 'fail';
                break;
            } elseif ($check['status'] === 'warning' && $overallStatus === 'pass') {
                $overallStatus = 'warning';
            }
        }
        
        return [
            'overall_status' => $overallStatus,
            'timestamp' => date('Y-m-d H:i:s'),
            'checks' => $checks
        ];
    }
    
    /**
     * 获取客户端IP地址
     */
    private static function getClientIp(): string
    {
        $headers = [
            'HTTP_CF_CONNECTING_IP',
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR'
        ];
        
        foreach ($headers as $header) {
            if (!empty($_SERVER[$header])) {
                $ip = $_SERVER[$header];
                if (strpos($ip, ',') !== false) {
                    $ip = trim(explode(',', $ip)[0]);
                }
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    }
    
    /**
     * 获取CPU核心数
     */
    private static function getCpuCount(): int
    {
        if (PHP_OS_FAMILY === 'Linux') {
            $cpuinfo = file_get_contents('/proc/cpuinfo');
            return substr_count($cpuinfo, 'processor');
        }
        
        return 1;
    }
    
    /**
     * 格式化字节数
     */
    private static function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
    
    /**
     * 解析字节数
     */
    private static function parseBytes(string $val): int
    {
        $val = trim($val);
        $last = strtolower($val[strlen($val) - 1]);
        $val = (int) $val;
        
        switch ($last) {
            case 'g':
                $val *= 1024;
            case 'm':
                $val *= 1024;
            case 'k':
                $val *= 1024;
        }
        
        return $val;
    }
}
