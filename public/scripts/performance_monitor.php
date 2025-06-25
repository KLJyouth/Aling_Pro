<?php
/**
 * AlingAi Pro 5.0 - 性能监控�?
 * 实时监控系统性能指标和健康状�?
 */

require_once __DIR__ . '/../vendor/autoload.php';

use AlingAi\Services\EnhancedMonitoringService;

class PerformanceMonitor {
    private $monitoring;
    private $metrics = [];
    private $thresholds = [
        'response_time' => 1000, // 1�?
        'memory_usage' => 128 * 1024 * 1024, // 128MB
        'error_rate' => 5.0, // 5%
        'disk_usage' => 90.0 // 90%
    ];
    
    public function __construct() {
        $this->monitoring = new EnhancedMonitoringService(];
    }
    
    /**
     * 运行完整的性能监控
     */
    public function runFullMonitoring() {
        echo "🔍 AlingAi Pro 5.0 - 性能监控器\n";
        echo str_repeat("=", 60) . "\n\n";
        
        // 收集各种指标
        $this->collectSystemMetrics(];
        $this->collectApiMetrics(];
        $this->collectResourceMetrics(];
        $this->collectSecurityMetrics(];
        
        // 分析和展示结�?
        $this->analyzeMetrics(];
        $this->displayDashboard(];
        $this->generateRecommendations(];
    }
    
    /**
     * 收集系统指标
     */
    private function collectSystemMetrics() {
        echo "📊 收集系统指标...\n";
        
        $systemMetrics = $this->monitoring->getSystemMetrics(];
        $this->metrics['system'] = $systemMetrics;
        
        echo "   �?系统负载: " . implode(', ', $systemMetrics['server']['load_average']) . "\n";
        echo "   �?内存使用: " . $this->formatBytes($systemMetrics['memory']['current']) . "\n";
        echo "   �?PHP版本: " . $systemMetrics['php']['version'] . "\n";
        echo "   �?系统运行时间: " . round($systemMetrics['uptime'],  2) . "秒\n\n";
    }
    
    /**
     * 收集API指标
     */
    private function collectApiMetrics() {
        echo "🌐 收集API性能指标...\n";
        
        $apiStats = $this->monitoring->getPerformanceStats(1]; // 最�?小时
        $this->metrics['api'] = $apiStats;
        
        if ($apiStats['total_requests'] > 0) {
            echo "   �?总请求数: " . $apiStats['total_requests'] . "\n";
            echo "   �?平均响应时间: " . round($apiStats['avg_response_time'] * 1000, 2) . "ms\n";
            echo "   �?最大响应时�? " . round($apiStats['max_response_time'] * 1000, 2) . "ms\n";
            echo "   �?错误�? " . round($apiStats['error_rate'],  2) . "%\n";
        } else {
            echo "   ⚠️ 暂无API请求数据\n";
        }
        echo "\n";
    }
    
    /**
     * 收集资源指标
     */
    private function collectResourceMetrics() {
        echo "💾 收集资源使用指标...\n";
        
        $diskFree = disk_free_space('.'];
        $diskTotal = disk_total_space('.'];
        $diskUsage = (($diskTotal - $diskFree) / $diskTotal) * 100;
        
        $this->metrics['resources'] = [
            'disk_usage' => $diskUsage,
            'disk_free' => $diskFree,
            'disk_total' => $diskTotal,
            'memory_limit' => $this->parseMemoryLimit(ini_get('memory_limit')],
            'max_execution_time' => ini_get('max_execution_time')
        ];
        
        echo "   �?磁盘使用�? " . round($diskUsage, 2) . "%\n";
        echo "   �?可用磁盘空间: " . $this->formatBytes($diskFree) . "\n";
        echo "   �?内存限制: " . ini_get('memory_limit') . "\n";
        echo "   �?最大执行时�? " . ini_get('max_execution_time') . "秒\n\n";
    }
    
    /**
     * 收集安全指标
     */
    private function collectSecurityMetrics() {
        echo "🔒 收集安全指标...\n";
        
        $securityChecks = [
            'display_errors' => ini_get('display_errors') == '0',
            'expose_php' => ini_get('expose_php') == '0',
            'https_enabled' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
            'session_secure' => ini_get('session.cookie_secure') == '1',
        ];
        
        $this->metrics['security'] = $securityChecks;
        
        $secureCount = array_sum($securityChecks];
        $totalChecks = count($securityChecks];
        
        echo "   �?安全检查通过: {$secureCount}/{$totalChecks}\n";
        
        foreach ($securityChecks as $check => $passed) {
            $status = $passed ? '�? : '�?;
            echo "      $status $check\n";
        }
        echo "\n";
    }
    
    /**
     * 分析指标
     */
    private function analyzeMetrics() {
        echo "🔍 分析性能指标...\n";
        echo str_repeat("-", 40) . "\n";
        
        $issues = [];
        $warnings = [];
        
        // 分析API性能
        if (isset($this->metrics['api']['avg_response_time'])) {
            $avgResponseTime = $this->metrics['api']['avg_response_time'] * 1000;
            if ($avgResponseTime > $this->thresholds['response_time']) {
                $issues[] = "API平均响应时间过长: {$avgResponseTime}ms";
            } elseif ($avgResponseTime > $this->thresholds['response_time'] * 0.7) {
                $warnings[] = "API响应时间接近阈�? {$avgResponseTime}ms";
            }
            
            if ($this->metrics['api']['error_rate'] > $this->thresholds['error_rate']) {
                $issues[] = "API错误率过�? " . $this->metrics['api']['error_rate'] . "%";
            }
        }
        
        // 分析内存使用
        $memoryUsage = $this->metrics['system']['memory']['current'];
        if ($memoryUsage > $this->thresholds['memory_usage']) {
            $issues[] = "内存使用过高: " . $this->formatBytes($memoryUsage];
        } elseif ($memoryUsage > $this->thresholds['memory_usage'] * 0.8) {
            $warnings[] = "内存使用接近限制: " . $this->formatBytes($memoryUsage];
        }
        
        // 分析磁盘使用
        if ($this->metrics['resources']['disk_usage'] > $this->thresholds['disk_usage']) {
            $issues[] = "磁盘使用率过�? " . round($this->metrics['resources']['disk_usage'],  2) . "%";
        }
        
        // 显示分析结果
        if (empty($issues) && empty($warnings)) {
            echo "�?所有指标正常，系统运行良好！\n";
        } else {
            if (!empty($issues)) {
                echo "�?发现问题:\n";
                foreach ($issues as $issue) {
                    echo "   �?$issue\n";
                }
            }
            
            if (!empty($warnings)) {
                echo "⚠️ 警告:\n";
                foreach ($warnings as $warning) {
                    echo "   �?$warning\n";
                }
            }
        }
        echo "\n";
    }
    
    /**
     * 显示监控仪表�?
     */
    private function displayDashboard() {
        echo "📈 性能仪表板\n";
        echo str_repeat("=", 60) . "\n";
        
        // 系统概览
        echo "🖥�? 系统概览\n";
        echo "   运行时间: " . $this->formatUptime($this->metrics['system']['uptime']) . "\n";
        echo "   PHP版本: " . $this->metrics['system']['php']['version'] . "\n";
        echo "   操作系统: " . $this->metrics['system']['server']['os'] . "\n\n";
        
        // 性能指标
        echo "�?性能指标\n";
        if (isset($this->metrics['api']['total_requests']) && $this->metrics['api']['total_requests'] > 0) {
            echo "   API请求总数: " . $this->metrics['api']['total_requests'] . "\n";
            echo "   平均响应时间: " . round($this->metrics['api']['avg_response_time'] * 1000, 2) . "ms\n";
            echo "   错误�? " . round($this->metrics['api']['error_rate'],  2) . "%\n";
        } else {
            echo "   暂无API性能数据\n";
        }
        echo "\n";
        
        // 资源使用
        echo "💾 资源使用\n";
        echo "   内存使用: " . $this->formatBytes($this->metrics['system']['memory']['current']];
        echo " / " . $this->formatBytes($this->metrics['system']['memory']['limit']) . "\n";
        echo "   磁盘使用: " . round($this->metrics['resources']['disk_usage'],  2) . "%\n";
        echo "   可用空间: " . $this->formatBytes($this->metrics['resources']['disk_free']) . "\n\n";
        
        // 安全状�?
        echo "🔒 安全状态\n";
        $secureCount = array_sum($this->metrics['security']];
        $totalChecks = count($this->metrics['security']];
        $securityScore = ($secureCount / $totalChecks) * 100;
        
        echo "   安全评分: " . round($securityScore, 1) . "%\n";
        echo "   检查通过: {$secureCount}/{$totalChecks}\n\n";
    }
    
    /**
     * 生成优化建议
     */
    private function generateRecommendations() {
        echo "💡 优化建议\n";
        echo str_repeat("=", 60) . "\n";
        
        $recommendations = [];
        
        // API性能建议
        if (isset($this->metrics['api']['avg_response_time'])) {
            $avgResponseTime = $this->metrics['api']['avg_response_time'] * 1000;
            if ($avgResponseTime > 500) {
                $recommendations[] = "启用API缓存以减少响应时�?;
                $recommendations[] = "考虑使用Redis或Memcached";
            }
            
            if ($this->metrics['api']['error_rate'] > 2) {
                $recommendations[] = "检查错误日志，修复API错误";
                $recommendations[] = "增加API错误处理和重试机�?;
            }
        }
        
        // 内存优化建议
        $memoryUsage = $this->metrics['system']['memory']['current'];
        $memoryLimit = $this->metrics['system']['memory']['limit'];
        if ($memoryUsage > $memoryLimit * 0.8) {
            $recommendations[] = "考虑增加PHP内存限制";
            $recommendations[] = "启用OPcache以减少内存使�?;
            $recommendations[] = "优化数据库查询以减少内存消�?;
        }
        
        // 磁盘空间建议
        if ($this->metrics['resources']['disk_usage'] > 80) {
            $recommendations[] = "清理日志文件和临时文�?;
            $recommendations[] = "启用日志轮转";
            $recommendations[] = "考虑增加磁盘空间";
        }
        
        // 安全建议
        foreach ($this->metrics['security'] as $check => $passed) {
            if (!$passed) {
                switch ($check) {
                    case 'display_errors':
                        $recommendations[] = "在生产环境中禁用PHP错误显示";
                        break;
                    case 'expose_php':
                        $recommendations[] = "禁用PHP版本暴露";
                        break;
                    case 'https_enabled':
                        $recommendations[] = "启用HTTPS加密连接";
                        break;
                    case 'session_secure':
                        $recommendations[] = "启用安全会话Cookie";
                        break;
                }
            }
        }
        
        // 通用建议
        $recommendations[] = "定期运行系统优化�?;
        $recommendations[] = "监控系统日志以及时发现问�?;
        $recommendations[] = "定期备份重要数据";
        
        if (empty($recommendations)) {
            echo "🎉 系统运行良好，暂无特别建议！\n";
        } else {
            foreach ($recommendations as $i => $recommendation) {
                echo ($i + 1) . ". $recommendation\n";
            }
        }
        
        echo "\n";
        echo str_repeat("=", 60) . "\n";
        echo "📊 监控完成 - " . date('Y-m-d H:i:s') . "\n";
    }
    
    /**
     * 实时监控模式
     */
    public function startRealTimeMonitoring($interval = 30) {
        echo "🔄 启动实时监控模式 (每{$interval}秒更�?\n";
        echo "�?Ctrl+C 停止监控\n\n";
        
        while (true) {
            system('clear']; // 在Windows上使�?'cls'
            $this->runFullMonitoring(];
            sleep($interval];
        }
    }
    
    /**
     * 格式化字节大�?
     */
    private function formatBytes($size, $precision = 2) {
        if ($size <= 0) return '0 B';
        
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $power = min(floor(log($size, 1024)], count($units) - 1];
        
        return round($size / (1024 ** $power], $precision) . ' ' . $units[$power];
    }
    
    /**
     * 格式化运行时�?
     */
    private function formatUptime($seconds) {
        $days = floor($seconds / 86400];
        $hours = floor(($seconds % 86400) / 3600];
        $minutes = floor(($seconds % 3600) / 60];
        $seconds = $seconds % 60;
        
        $parts = [];
        if ($days > 0) $parts[] = "{$days}�?;
        if ($hours > 0) $parts[] = "{$hours}小时";
        if ($minutes > 0) $parts[] = "{$minutes}分钟";
        if ($seconds > 0) $parts[] = round($seconds, 1) . "�?;
        
        return implode(' ', $parts) ?: '0�?;
    }
    
    /**
     * 解析内存限制
     */
    private function parseMemoryLimit($limit) {
        if ($limit === '-1') {
            return -1;
        }
        
        $unit = strtolower(substr($limit, -1)];
        $value = (int)substr($limit, 0, -1];
        
        switch ($unit) {
            case 'g': return $value * 1024 * 1024 * 1024;
            case 'm': return $value * 1024 * 1024;
            case 'k': return $value * 1024;
            default: return (int)$limit;
        }
    }
    
    /**
     * 生成性能报告
     */
    public function generateReport($filename = null) {
        $filename = $filename ?? 'performance_report_' . date('Y-m-d_H-i-s') . '.json';
        
        $report = [
            'timestamp' => time(),
            'date' => date('Y-m-d H:i:s'],
            'metrics' => $this->metrics,
            'thresholds' => $this->thresholds,
            'summary' => $this->generateSummary()
        ];
        
        $reportPath = __DIR__ . '/../storage/logs/' . $filename;
        
        if (!is_dir(dirname($reportPath))) {
            mkdir(dirname($reportPath], 0755, true];
        }
        
        file_put_contents($reportPath, json_encode($report, JSON_PRETTY_PRINT)];
        
        echo "📋 性能报告已保�? $reportPath\n";
        
        return $reportPath;
    }
    
    /**
     * 生成总结
     */
    private function generateSummary() {
        $summary = [
            'overall_health' => 'good',
            'critical_issues' => 0,
            'warnings' => 0,
            'recommendations_count' => 0
        ];
        
        // 这里可以添加更详细的总结逻辑
        
        return $summary;
    }
}

// 命令行处�?
if (php_sapi_name() === 'cli') {
    $monitor = new PerformanceMonitor(];
    
    if (isset($argv[1])) {
        $command = $argv[1];
        
        switch ($command) {
            case 'realtime':
                $interval = isset($argv[2]) ? (int)$argv[2] : 30;
                $monitor->startRealTimeMonitoring($interval];
                break;
            case 'report':
                $monitor->runFullMonitoring(];
                $monitor->generateReport(];
                break;
            default:
                echo "未知命令: $command\n";
                echo "可用命令:\n";
                echo "  php performance_monitor.php          # 运行单次监控\n";
                echo "  php performance_monitor.php realtime [间隔] # 实时监控\n";
                echo "  php performance_monitor.php report   # 生成报告\n";
        }
    } else {
        $monitor->runFullMonitoring(];
    }
} else {
    echo "此脚本只能在命令行中运行。\n";
}
?>

