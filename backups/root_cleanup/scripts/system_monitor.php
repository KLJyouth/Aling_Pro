<?php
/**
 * 系统监控脚本
 */
class SystemMetricsCollector
{
    public function collectMetrics()
    {
        return [
            'timestamp' => time(),
            'cpu' => $this->getCPUUsage(),
            'memory' => $this->getMemoryUsage(),
            'disk' => $this->getDiskUsage(),
            'network' => $this->getNetworkStats()
        ];
    }
      private function getCPUUsage()
    {
        if (PHP_OS_FAMILY === 'Windows') {
            // 生产环境兼容：shell_exec 可能被禁用
            if (function_exists('shell_exec')) {
                $output = shell_exec('wmic cpu get loadpercentage /value');
                if (preg_match('/LoadPercentage=(\d+)/', $output, $matches)) {
                    return (int)$matches[1];
                }
            }
        } else {
            $load = sys_getloadavg();
            return $load[0];
        }
        return 0;
    }
      private function getMemoryUsage()
    {
        if (PHP_OS_FAMILY === 'Windows') {
            // 生产环境兼容：shell_exec 可能被禁用
            if (function_exists('shell_exec')) {
                $output = shell_exec('wmic OS get TotalVisibleMemorySize,FreePhysicalMemory /value');
                if (preg_match('/TotalVisibleMemorySize=(\d+)/', $output, $total) &&
                    preg_match('/FreePhysicalMemory=(\d+)/', $output, $free)) {
                    $used = ($total[1] - $free[1]) / $total[1] * 100;
                    return round($used, 2);
                }
            }
        }
        return 0;
    }
    
    private function getDiskUsage()
    {
        $total = disk_total_space('.');
        $free = disk_free_space('.');
        return round(($total - $free) / $total * 100, 2);
    }
    
    private function getNetworkStats()
    {
        // 网络统计实现
        return ['bytes_in' => 0, 'bytes_out' => 0];
    }
}

// 定期收集指标
$collector = new SystemMetricsCollector();
$metrics = $collector->collectMetrics();

// 保存到日志文件
file_put_contents('./logs/system_metrics.log', 
    json_encode($metrics) . "\n", FILE_APPEND | LOCK_EX);

// 检查告警阈值
$alerts = [];
if ($metrics['cpu'] > 80) {
    $alerts[] = ['type' => 'WARNING', 'message' => "CPU usage high: {$metrics['cpu']}%"];
}
if ($metrics['memory'] > 85) {
    $alerts[] = ['type' => 'WARNING', 'message' => "Memory usage high: {$metrics['memory']}%"];
}
if ($metrics['disk'] > 90) {
    $alerts[] = ['type' => 'CRITICAL', 'message' => "Disk usage critical: {$metrics['disk']}%"];
}

// 发送告警
foreach ($alerts as $alert) {
    file_put_contents('./logs/alerts.log', 
        json_encode($alert + ['timestamp' => time()]) . "\n", FILE_APPEND | LOCK_EX);
}
?>