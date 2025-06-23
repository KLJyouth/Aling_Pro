<?php
/**
 * 文件名：PerformanceMonitor.php
 * 功能描述：性能监控工具 - 用于监控和分析代码性能
 * 创建时间：2025-01-XX
 * 最后修改：2025-02-XX
 * 版本：1.1.0
 *
 * @package AlingAi\Utils
 * @author AlingAi Team
 * @license MIT
 */

declare(strict_types=1);

namespace AlingAi\Utils;

use Exception;
use InvalidArgumentException;
use AlingAi\Core\Logger\LoggerInterface;

/**
 * 性能监控工具
 *
 * 用于监控和分析代码性能，记录执行时间、内存使用情况和CPU使用率
 */
class PerformanceMonitor
{
    /**
     * @var LoggerInterface 日志记录器
     */
    private ?LoggerInterface $logger;

    /**
     * @var array 计时器数据
     */
    private array $timers = [];

    /**
     * @var array 内存使用数据
     */
    private array $memoryUsage = [];

    /**
     * @var array CPU使用数据
     */
    private array $cpuUsage = [];

    /**
     * @var array 性能统计数据
     */
    private array $stats = [];

    /**
     * @var bool 是否启用监控
     */
    private bool $enabled;

    /**
     * @var bool 是否启用CPU监控
     */
    private bool $cpuMonitoringEnabled;

    /**
     * 构造函数
     *
     * @param LoggerInterface|null $logger 日志记录器
     * @param bool $enabled 是否启用监控
     * @param bool $cpuMonitoringEnabled 是否启用CPU监控
     */
    public function __construct(?LoggerInterface $logger = null, bool $enabled = true, bool $cpuMonitoringEnabled = true)
    {
        $this->logger = $logger;
        $this->enabled = $enabled;
        $this->cpuMonitoringEnabled = $cpuMonitoringEnabled && $this->isCpuMonitoringAvailable();
    }

    /**
     * 开始计时
     *
     * @param string $name 计时器名称
     * @return void
     */
    public function start(string $name): void
    {
        if (!$this->enabled) {
            return;
        }

        if (isset($this->timers[$name]) && $this->timers[$name]['running']) {
            if ($this->logger) {
                $this->logger->warning("Timer '{$name}' is already running");
            }
            return;
        }

        $this->timers[$name] = [
            'start' => microtime(true),
            'end' => null,
            'running' => true,
            'duration' => 0
        ];

        $this->memoryUsage[$name] = [
            'start' => memory_get_usage(true),
            'end' => null,
            'peak' => 0,
            'usage' => 0
        ];

        if ($this->cpuMonitoringEnabled) {
            $this->cpuUsage[$name] = [
                'start' => $this->getCpuUsage(),
                'end' => null,
                'usage' => 0
            ];
        }

        if ($this->logger) {
            $this->logger->debug("Timer '{$name}' started");
        }
    }

    /**
     * 结束计时
     *
     * @param string $name 计时器名称
     * @return void
     */
    public function end(string $name): void
    {
        if (!$this->enabled) {
            return;
        }

        if (!isset($this->timers[$name])) {
            if ($this->logger) {
                $this->logger->warning("Timer '{$name}' does not exist");
            }
            return;
        }

        if (!$this->timers[$name]['running']) {
            if ($this->logger) {
                $this->logger->warning("Timer '{$name}' is not running");
            }
            return;
        }

        $this->timers[$name]['end'] = microtime(true);
        $this->timers[$name]['running'] = false;
        $this->timers[$name]['duration'] = $this->timers[$name]['end'] - $this->timers[$name]['start'];

        $this->memoryUsage[$name]['end'] = memory_get_usage(true);
        $this->memoryUsage[$name]['peak'] = memory_get_peak_usage(true);
        $this->memoryUsage[$name]['usage'] = $this->memoryUsage[$name]['end'] - $this->memoryUsage[$name]['start'];

        if ($this->cpuMonitoringEnabled && isset($this->cpuUsage[$name])) {
            $this->cpuUsage[$name]['end'] = $this->getCpuUsage();
            $this->cpuUsage[$name]['usage'] = $this->calculateCpuUsage(
                $this->cpuUsage[$name]['start'],
                $this->cpuUsage[$name]['end'],
                $this->timers[$name]['duration']
            );
        }

        // 更新统计数据
        $this->updateStats($name);

        if ($this->logger) {
            $logData = [
                'duration' => $this->timers[$name]['duration'],
                'memory_usage' => $this->formatBytes($this->memoryUsage[$name]['usage']),
                'peak_memory' => $this->formatBytes($this->memoryUsage[$name]['peak'])
            ];

            if ($this->cpuMonitoringEnabled && isset($this->cpuUsage[$name])) {
                $logData['cpu_usage'] = $this->cpuUsage[$name]['usage'] . '%';
            }

            $this->logger->debug("Timer '{$name}' ended", $logData);
        }
    }

    /**
     * 获取执行时间
     *
     * @param string $name 计时器名称
     * @return float 执行时间（秒）
     */
    public function getDuration(string $name): float
    {
        if (!isset($this->timers[$name])) {
            return 0;
        }

        if ($this->timers[$name]['running']) {
            return microtime(true) - $this->timers[$name]['start'];
        }

        return $this->timers[$name]['duration'];
    }

    /**
     * 获取内存使用量
     *
     * @param string $name 计时器名称
     * @return int 内存使用量（字节）
     */
    public function getMemoryUsage(string $name): int
    {
        if (!isset($this->memoryUsage[$name])) {
            return 0;
        }

        if ($this->timers[$name]['running']) {
            return memory_get_usage(true) - $this->memoryUsage[$name]['start'];
        }

        return $this->memoryUsage[$name]['usage'];
    }

    /**
     * 获取峰值内存使用量
     *
     * @param string $name 计时器名称
     * @return int 峰值内存使用量（字节）
     */
    public function getPeakMemoryUsage(string $name): int
    {
        if (!isset($this->memoryUsage[$name])) {
            return 0;
        }

        return $this->memoryUsage[$name]['peak'];
    }

    /**
     * 获取CPU使用率
     *
     * @param string $name 计时器名称
     * @return float CPU使用率（百分比）
     */
    public function getCpuUsagePercent(string $name): float
    {
        if (!$this->cpuMonitoringEnabled || !isset($this->cpuUsage[$name])) {
            return 0;
        }

        if ($this->timers[$name]['running']) {
            $currentCpu = $this->getCpuUsage();
            $duration = microtime(true) - $this->timers[$name]['start'];
            return $this->calculateCpuUsage($this->cpuUsage[$name]['start'], $currentCpu, $duration);
        }

        return $this->cpuUsage[$name]['usage'];
    }

    /**
     * 更新统计数据
     *
     * @param string $name 计时器名称
     * @return void
     */
    private function updateStats(string $name): void
    {
        if (!isset($this->stats[$name])) {
            $this->stats[$name] = [
                'count' => 0,
                'total_duration' => 0,
                'min_duration' => PHP_FLOAT_MAX,
                'max_duration' => 0,
                'avg_duration' => 0,
                'total_memory' => 0,
                'peak_memory' => 0,
                'avg_cpu_usage' => 0,
                'total_cpu_usage' => 0
            ];
        }

        $duration = $this->timers[$name]['duration'];
        $memory = $this->memoryUsage[$name]['usage'];
        $peak = $this->memoryUsage[$name]['peak'];
        $cpuUsage = $this->cpuMonitoringEnabled && isset($this->cpuUsage[$name]) ? $this->cpuUsage[$name]['usage'] : 0;

        $this->stats[$name]['count']++;
        $this->stats[$name]['total_duration'] += $duration;
        $this->stats[$name]['min_duration'] = min($this->stats[$name]['min_duration'], $duration);
        $this->stats[$name]['max_duration'] = max($this->stats[$name]['max_duration'], $duration);
        $this->stats[$name]['avg_duration'] = $this->stats[$name]['total_duration'] / $this->stats[$name]['count'];
        $this->stats[$name]['total_memory'] += $memory;
        $this->stats[$name]['peak_memory'] = max($this->stats[$name]['peak_memory'], $peak);
        $this->stats[$name]['total_cpu_usage'] += $cpuUsage;
        $this->stats[$name]['avg_cpu_usage'] = $this->stats[$name]['total_cpu_usage'] / $this->stats[$name]['count'];
    }

    /**
     * 获取性能统计数据
     *
     * @return array 性能统计数据
     */
    public function getStats(): array
    {
        $formattedStats = [];

        foreach ($this->stats as $name => $stat) {
            $formattedStats[$name] = [
                'count' => $stat['count'],
                'total_duration' => round($stat['total_duration'], 6),
                'min_duration' => round($stat['min_duration'], 6),
                'max_duration' => round($stat['max_duration'], 6),
                'avg_duration' => round($stat['avg_duration'], 6),
                'total_memory' => $this->formatBytes($stat['total_memory']),
                'peak_memory' => $this->formatBytes($stat['peak_memory'])
            ];

            if ($this->cpuMonitoringEnabled) {
                $formattedStats[$name]['avg_cpu_usage'] = round($stat['avg_cpu_usage'], 2) . '%';
            }
        }

        return $formattedStats;
    }

    /**
     * 获取原始性能统计数据
     *
     * @return array 原始性能统计数据
     */
    public function getRawStats(): array
    {
        return $this->stats;
    }

    /**
     * 重置性能监控数据
     *
     * @return void
     */
    public function reset(): void
    {
        $this->timers = [];
        $this->memoryUsage = [];
        $this->cpuUsage = [];
        $this->stats = [];

        if ($this->logger) {
            $this->logger->debug('Performance monitor reset');
        }
    }

    /**
     * 格式化字节数为可读字符串
     *
     * @param int $bytes 字节数
     * @param int $precision 精度
     * @return string 格式化后的字符串
     */
    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
        
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= (1 << (10 * $pow));
        
        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    /**
     * 启用性能监控
     *
     * @return void
     */
    public function enable(): void
    {
        $this->enabled = true;

        if ($this->logger) {
            $this->logger->debug('Performance monitoring enabled');
        }
    }

    /**
     * 禁用性能监控
     *
     * @return void
     */
    public function disable(): void
    {
        $this->enabled = false;

        if ($this->logger) {
            $this->logger->debug('Performance monitoring disabled');
        }
    }

    /**
     * 检查性能监控是否启用
     *
     * @return bool 是否启用
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * 启用CPU监控
     *
     * @return void
     */
    public function enableCpuMonitoring(): void
    {
        if ($this->isCpuMonitoringAvailable()) {
            $this->cpuMonitoringEnabled = true;

            if ($this->logger) {
                $this->logger->debug('CPU monitoring enabled');
            }
        } else {
            if ($this->logger) {
                $this->logger->warning('CPU monitoring is not available on this system');
            }
        }
    }

    /**
     * 禁用CPU监控
     *
     * @return void
     */
    public function disableCpuMonitoring(): void
    {
        $this->cpuMonitoringEnabled = false;

        if ($this->logger) {
            $this->logger->debug('CPU monitoring disabled');
        }
    }

    /**
     * 检查CPU监控是否可用
     *
     * @return bool 是否可用
     */
    private function isCpuMonitoringAvailable(): bool
    {
        return function_exists('sys_getloadavg') || 
               (PHP_OS !== 'WIN' && is_readable('/proc/stat'));
    }

    /**
     * 获取当前CPU使用情况
     *
     * @return array|float CPU使用情况
     */
    private function getCpuUsage()
    {
        // 尝试使用系统负载平均值
        if (function_exists('sys_getloadavg')) {
            $load = sys_getloadavg();
            return $load[0]; // 1分钟负载
        }

        // 尝试读取/proc/stat（仅适用于Linux系统）
        if (PHP_OS !== 'WIN' && is_readable('/proc/stat')) {
            $stats = file_get_contents('/proc/stat');
            if ($stats !== false) {
                $stats = explode("\n", $stats);
                $cpuStats = explode(' ', trim(preg_replace('/\s+/', ' ', $stats[0])));
                
                // 格式：cpu user nice system idle iowait irq softirq steal guest guest_nice
                return [
                    'user' => $cpuStats[1] ?? 0,
                    'nice' => $cpuStats[2] ?? 0,
                    'system' => $cpuStats[3] ?? 0,
                    'idle' => $cpuStats[4] ?? 0,
                    'iowait' => $cpuStats[5] ?? 0,
                    'irq' => $cpuStats[6] ?? 0,
                    'softirq' => $cpuStats[7] ?? 0,
                    'steal' => $cpuStats[8] ?? 0,
                    'guest' => $cpuStats[9] ?? 0,
                    'guest_nice' => $cpuStats[10] ?? 0
                ];
            }
        }

        return 0;
    }

    /**
     * 计算CPU使用率
     *
     * @param mixed $start 开始时的CPU使用情况
     * @param mixed $end 结束时的CPU使用情况
     * @param float $duration 持续时间
     * @return float CPU使用率（百分比）
     */
    private function calculateCpuUsage($start, $end, float $duration): float
    {
        // 如果使用的是系统负载平均值
        if (is_numeric($start) && is_numeric($end)) {
            return round(($start + $end) / 2, 2);
        }

        // 如果使用的是/proc/stat数据
        if (is_array($start) && is_array($end)) {
            $startUser = $start['user'] + $start['nice'] + $start['guest'] + $start['guest_nice'];
            $startSystem = $start['system'] + $start['irq'] + $start['softirq'];
            $startIdle = $start['idle'] + $start['iowait'];
            $startSteal = $start['steal'];
            $startTotal = $startUser + $startSystem + $startIdle + $startSteal;

            $endUser = $end['user'] + $end['nice'] + $end['guest'] + $end['guest_nice'];
            $endSystem = $end['system'] + $end['irq'] + $end['softirq'];
            $endIdle = $end['idle'] + $end['iowait'];
            $endSteal = $end['steal'];
            $endTotal = $endUser + $endSystem + $endIdle + $endSteal;

            $diffTotal = $endTotal - $startTotal;
            if ($diffTotal === 0) {
                return 0;
            }

            $diffIdle = $endIdle - $startIdle;
            $cpuUsage = (1 - ($diffIdle / $diffTotal)) * 100;
            return round($cpuUsage, 2);
        }

        return 0;
    }

    /**
     * 导出性能报告到JSON文件
     *
     * @param string $filePath 文件路径
     * @return bool 是否成功
     */
    public function exportReportToJson(string $filePath): bool
    {
        $report = [
            'timestamp' => time(),
            'date' => date('Y-m-d H:i:s'),
            'stats' => $this->getRawStats(),
            'system_info' => [
                'php_version' => PHP_VERSION,
                'os' => PHP_OS,
                'memory_limit' => ini_get('memory_limit')
            ]
        ];

        $json = json_encode($report, JSON_PRETTY_PRINT);
        if ($json === false) {
            if ($this->logger) {
                $this->logger->error('Failed to encode performance report to JSON');
            }
            return false;
        }

        $result = file_put_contents($filePath, $json);
        if ($result === false) {
            if ($this->logger) {
                $this->logger->error("Failed to write performance report to file: {$filePath}");
            }
            return false;
        }

        if ($this->logger) {
            $this->logger->info("Performance report exported to: {$filePath}");
        }
        return true;
    }

    /**
     * 导出性能报告到HTML文件
     *
     * @param string $filePath 文件路径
     * @param string $title 报告标题
     * @return bool 是否成功
     */
    public function exportReportToHtml(string $filePath, string $title = 'Performance Report'): bool
    {
        $stats = $this->getStats();
        
        $html = "<!DOCTYPE html>\n";
        $html .= "<html lang=\"en\">\n";
        $html .= "<head>\n";
        $html .= "    <meta charset=\"UTF-8\">\n";
        $html .= "    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\n";
        $html .= "    <title>{$title}</title>\n";
        $html .= "    <style>\n";
        $html .= "        body { font-family: Arial, sans-serif; margin: 20px; }\n";
        $html .= "        h1 { color: #333; }\n";
        $html .= "        table { border-collapse: collapse; width: 100%; margin-top: 20px; }\n";
        $html .= "        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }\n";
        $html .= "        th { background-color: #f2f2f2; }\n";
        $html .= "        tr:nth-child(even) { background-color: #f9f9f9; }\n";
        $html .= "        .info { margin-bottom: 20px; }\n";
        $html .= "    </style>\n";
        $html .= "</head>\n";
        $html .= "<body>\n";
        $html .= "    <h1>{$title}</h1>\n";
        $html .= "    <div class=\"info\">\n";
        $html .= "        <p>Generated: " . date('Y-m-d H:i:s') . "</p>\n";
        $html .= "        <p>PHP Version: " . PHP_VERSION . "</p>\n";
        $html .= "        <p>OS: " . PHP_OS . "</p>\n";
        $html .= "        <p>Memory Limit: " . ini_get('memory_limit') . "</p>\n";
        $html .= "    </div>\n";
        $html .= "    <table>\n";
        $html .= "        <tr>\n";
        $html .= "            <th>Name</th>\n";
        $html .= "            <th>Count</th>\n";
        $html .= "            <th>Total Duration</th>\n";
        $html .= "            <th>Min Duration</th>\n";
        $html .= "            <th>Max Duration</th>\n";
        $html .= "            <th>Avg Duration</th>\n";
        $html .= "            <th>Peak Memory</th>\n";
        
        if ($this->cpuMonitoringEnabled) {
            $html .= "            <th>Avg CPU Usage</th>\n";
        }
        
        $html .= "        </tr>\n";
        
        foreach ($stats as $name => $stat) {
            $html .= "        <tr>\n";
            $html .= "            <td>{$name}</td>\n";
            $html .= "            <td>{$stat['count']}</td>\n";
            $html .= "            <td>{$stat['total_duration']} s</td>\n";
            $html .= "            <td>{$stat['min_duration']} s</td>\n";
            $html .= "            <td>{$stat['max_duration']} s</td>\n";
            $html .= "            <td>{$stat['avg_duration']} s</td>\n";
            $html .= "            <td>{$stat['peak_memory']}</td>\n";
            
            if ($this->cpuMonitoringEnabled && isset($stat['avg_cpu_usage'])) {
                $html .= "            <td>{$stat['avg_cpu_usage']}</td>\n";
            }
            
            $html .= "        </tr>\n";
        }
        
        $html .= "    </table>\n";
        $html .= "</body>\n";
        $html .= "</html>";
        
        $result = file_put_contents($filePath, $html);
        if ($result === false) {
            if ($this->logger) {
                $this->logger->error("Failed to write performance report to file: {$filePath}");
            }
            return false;
        }
        
        if ($this->logger) {
            $this->logger->info("Performance report exported to: {$filePath}");
        }
        return true;
    }
}

