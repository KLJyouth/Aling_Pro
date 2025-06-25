<?php
/**
 * AlingAi Pro 5.0 - 系统自动优化�?
 * 自动执行性能优化、缓存管理和系统维护任务
 */

require_once __DIR__ . '/../vendor/autoload.php';

class SystemOptimizer {
    private $basePath;
    private $optimizations = [];
    private $results = [];
    
    public function __construct() {
        $this->basePath = dirname(__DIR__];
        $this->initializeOptimizations(];
    }
    
    /**
     * 初始化优化任�?
     */
    private function initializeOptimizations() {
        $this->optimizations = [
            'composer_optimize' => [
                'name' => 'Composer自动加载优化',
                'priority' => 'high',
                'enabled' => true
            ], 
            'cache_warmup' => [
                'name' => '缓存预热',
                'priority' => 'high',
                'enabled' => true
            ], 
            'log_cleanup' => [
                'name' => '日志清理',
                'priority' => 'medium',
                'enabled' => true
            ], 
            'storage_optimization' => [
                'name' => '存储优化',
                'priority' => 'medium',
                'enabled' => true
            ], 
            'database_optimization' => [
                'name' => '数据库优�?,
                'priority' => 'low',
                'enabled' => false
            ], 
            'security_scan' => [
                'name' => '安全扫描',
                'priority' => 'medium',
                'enabled' => true
            ]
        ];
    }
    
    /**
     * 运行所有优�?
     */
    public function runAllOptimizations() {
        echo "🚀 AlingAi Pro 5.0 - 系统自动优化器\n";
        echo str_repeat("=", 60) . "\n\n";
        
        foreach ($this->optimizations as $key => $config) {
            if (!$config['enabled']) {
                continue;
            }
            
            echo "🔧 执行: {$config['name']} (优先�? {$config['priority']})\n";
            echo str_repeat("-", 40) . "\n";
            
            $startTime = microtime(true];
            $result = $this->runOptimization($key];
            $duration = round((microtime(true) - $startTime) * 1000, 2];
            
            $this->results[$key] = [
                'success' => $result['success'], 
                'message' => $result['message'], 
                'duration' => $duration,
                'details' => $result['details'] ?? []
            ];
            
            $status = $result['success'] ? '�?成功' : '�?失败';
            echo "$status - {$result['message']} ({$duration}ms)\n\n";
        }
        
        $this->showSummary(];
    }
    
    /**
     * 运行单个优化任务
     */
    private function runOptimization($type) {
        try {
            switch ($type) {
                case 'composer_optimize':
                    return $this->optimizeComposer(];
                case 'cache_warmup':
                    return $this->warmupCache(];
                case 'log_cleanup':
                    return $this->cleanupLogs(];
                case 'storage_optimization':
                    return $this->optimizeStorage(];
                case 'database_optimization':
                    return $this->optimizeDatabase(];
                case 'security_scan':
                    return $this->securityScan(];
                default:
                    return ['success' => false, 'message' => '未知的优化任�?];
            }
        } catch (Exception $e) {
            return ['success' => false, 'message' => '执行错误: ' . $e->getMessage()];
        }
    }
    
    /**
     * Composer自动加载优化
     */
    private function optimizeComposer() {
        $commands = [
            'composer dump-autoload --optimize --no-dev --classmap-authoritative'
        ];
        
        $output = [];
        $success = true;
        
        foreach ($commands as $command) {
            $fullCommand = "cd {$this->basePath} && $command 2>&1";
            $commandOutput = shell_exec($fullCommand];
            $output[] = trim($commandOutput];
            
            if (strpos($commandOutput, 'error') !== false || strpos($commandOutput, 'Error') !== false) {
                $success = false;
            }
        }
        
        return [
            'success' => $success,
            'message' => $success ? 'Composer自动加载已优�? : 'Composer优化失败',
            'details' => $output
        ];
    }
    
    /**
     * 缓存预热
     */
    private function warmupCache() {
        $endpoints = [
            'http://localhost:8000/api/',
            'http://localhost:8000/api/system/status',
            'http://localhost:8000/api/system/info',
            'http://localhost:8000/api/system/health'
        ];
        
        $warmedUp = 0;
        $failed = 0;
        
        foreach ($endpoints as $endpoint) {
            $context = stream_context_create([
                'http' => [
                    'timeout' => 5,
                    'method' => 'GET'
                ]
            ]];
            
            $result = @file_get_contents($endpoint, false, $context];
            
            if ($result !== false) {
                $warmedUp++;
            } else {
                $failed++;
            }
        }
        
        return [
            'success' => $failed < count($endpoints],
            'message' => "预热�?{$warmedUp} 个端点，{$failed} 个失�?,
            'details' => ['warmed_up' => $warmedUp, 'failed' => $failed]
        ];
    }
    
    /**
     * 日志清理
     */
    private function cleanupLogs() {
        $logDirs = [
            $this->basePath . '/storage/logs',
            $this->basePath . '/logs'
        ];
        
        $cleaned = 0;
        $totalSize = 0;
        
        foreach ($logDirs as $dir) {
            if (!is_dir($dir)) {
                continue;
            }
            
            $files = glob($dir . '/*.log'];
            
            foreach ($files as $file) {
                if (filemtime($file) < strtotime('-7 days')) {
                    $size = filesize($file];
                    if (unlink($file)) {
                        $cleaned++;
                        $totalSize += $size;
                    }
                }
            }
        }
        
        return [
            'success' => true,
            'message' => "清理�?{$cleaned} 个日志文件，释放 " . $this->formatBytes($totalSize],
            'details' => ['files_cleaned' => $cleaned, 'size_freed' => $totalSize]
        ];
    }
    
    /**
     * 存储优化
     */
    private function optimizeStorage() {
        $storageDirs = [
            $this->basePath . '/storage/cache',
            $this->basePath . '/storage/tmp',
            $this->basePath . '/tmp'
        ];
        
        $optimizations = [];
        
        foreach ($storageDirs as $dir) {
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true];
                $optimizations[] = "创建目录: $dir";
            }
            
            // 清理临时文件
            $tempFiles = glob($dir . '/tmp_*'];
            $cleaned = 0;
            
            foreach ($tempFiles as $file) {
                if (filemtime($file) < strtotime('-1 hour')) {
                    if (unlink($file)) {
                        $cleaned++;
                    }
                }
            }
            
            if ($cleaned > 0) {
                $optimizations[] = "清理 {$cleaned} 个临时文件从 $dir";
            }
        }
        
        return [
            'success' => true,
            'message' => '存储结构已优�?,
            'details' => $optimizations
        ];
    }
    
    /**
     * 数据库优�?
     */
    private function optimizeDatabase() {
        // 这里可以添加数据库优化逻辑
        // 例如：分析表、优化索引、清理过期数据等
        
        return [
            'success' => true,
            'message' => '数据库优化功能待实现',
            'details' => []
        ];
    }
    
    /**
     * 安全扫描
     */
    private function securityScan() {
        $issues = [];
        
        // 检查敏感文件权�?
        $sensitiveFiles = [
            '.env',
            'config/database.php',
            'storage/logs'
        ];
        
        foreach ($sensitiveFiles as $file) {
            $fullPath = $this->basePath . '/' . $file;
            
            if (file_exists($fullPath)) {
                $perms = fileperms($fullPath];
                
                if (is_file($fullPath) && ($perms & 0044)) {
                    $issues[] = "文件 $file 权限过于宽松";
                }
            }
        }
        
        // 检查PHP配置
        $phpConfig = [
            'display_errors' => '应为 Off',
            'expose_php' => '应为 Off',
            'allow_url_fopen' => '建议�?Off'
        ];
        
        foreach ($phpConfig as $setting => $recommendation) {
            $value = ini_get($setting];
            if ($setting === 'display_errors' && $value) {
                $issues[] = "PHP配置 $setting $recommendation (当前: $value)";
            }
        }
        
        return [
            'success' => count($issues) === 0,
            'message' => count($issues) === 0 ? '未发现安全问�? : '发现 ' . count($issues) . ' 个安全问�?,
            'details' => $issues
        ];
    }
    
    /**
     * 显示总结
     */
    private function showSummary() {
        echo "📊 优化总结\n";
        echo str_repeat("=", 60) . "\n";
        
        $successful = 0;
        $failed = 0;
        $totalTime = 0;
        
        foreach ($this->results as $key => $result) {
            if ($result['success']) {
                $successful++;
            } else {
                $failed++;
            }
            $totalTime += $result['duration'];
        }
        
        echo "�?成功: $successful 项\n";
        echo "�?失败: $failed 项\n";
        echo "⏱️ 总耗时: " . round($totalTime, 2) . "ms\n\n";
        
        if ($failed > 0) {
            echo "�?失败的优化项�?\n";
            foreach ($this->results as $key => $result) {
                if (!$result['success']) {
                    echo "   - {$this->optimizations[$key]['name']}: {$result['message']}\n";
                }
            }
            echo "\n";
        }
        
        echo "🎯 建议:\n";
        if ($successful >= count($this->results) * 0.8) {
            echo "   �?系统优化良好！大部分任务成功完成。\n";
        } else {
            echo "   ⚠️ 系统需要关注，部分优化任务失败。\n";
        }
        
        echo "   🔄 建议定期运行此优化器保持系统性能。\n";
        echo "   📊 运行 'php scripts/performance_monitor.php' 监控性能。\n\n";
        
        echo str_repeat("=", 60) . "\n";
        echo "🚀 AlingAi Pro 5.0 系统优化完成！\n";
    }
    
    /**
     * 格式化字节大�?
     */
    private function formatBytes($size, $precision = 2) {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $size > 1024 && $i < count($units) - 1; $i++) {
            $size /= 1024;
        }
        
        return round($size, $precision) . ' ' . $units[$i];
    }
    
    /**
     * 运行特定优化
     */
    public function runSpecificOptimization($type) {
        if (!isset($this->optimizations[$type])) {
            echo "�?错误: 未知的优化类�?'$type'\n";
            return false;
        }
        
        $config = $this->optimizations[$type];
        echo "🔧 运行: {$config['name']}\n";
        
        $result = $this->runOptimization($type];
        $status = $result['success'] ? '�?成功' : '�?失败';
        
        echo "$status - {$result['message']}\n";
        
        return $result['success'];
    }
    
    /**
     * 显示可用的优化任�?
     */
    public function listOptimizations() {
        echo "📋 可用的优化任�?\n";
        echo str_repeat("-", 40) . "\n";
        
        foreach ($this->optimizations as $key => $config) {
            $status = $config['enabled'] ? '�? : '�?;
            echo "$status $key - {$config['name']} ({$config['priority']})\n";
        }
        
        echo "\n使用方法:\n";
        echo "php system_optimizer.php                    # 运行所有优化\n";
        echo "php system_optimizer.php [optimization]     # 运行特定优化\n";
        echo "php system_optimizer.php list               # 显示所有可用优化\n";
    }
}

// 命令行处�?
if (php_sapi_name() === 'cli') {
    $optimizer = new SystemOptimizer(];
    
    if (isset($argv[1])) {
        $command = $argv[1];
        
        if ($command === 'list') {
            $optimizer->listOptimizations(];
        } else {
            $optimizer->runSpecificOptimization($command];
        }
    } else {
        $optimizer->runAllOptimizations(];
    }
} else {
    echo "此脚本只能在命令行中运行。\n";
}
?>
