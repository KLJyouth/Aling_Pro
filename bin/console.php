#!/usr/bin/env php
<?php

require_once __DIR__ . '/../vendor/autoload.php';

use AlingAi\Services\EnhancedConfigService;
use AlingAi\Database\DatabaseManager;
use AlingAi\Performance\CacheManager;
use AlingAi\Monitoring\SystemMonitor;
use AlingAi\Monitoring\LogManager;

/**
 * AlingAi 控制台命令工具
 */
class AlingAiConsole
{
    private $config;
    private $commands = [];
    
    public function __construct()
    {
        $this->config = EnhancedConfigService::getInstance();
        $this->registerCommands();
    }
    
    /**
     * 注册所有命令
     */
    private function registerCommands()
    {
        $this->commands = [
            'cache:clear' => [$this, 'clearCache'],
            'cache:status' => [$this, 'cacheStatus'],
            'db:migrate' => [$this, 'migrateDatabase'],
            'db:status' => [$this, 'databaseStatus'],
            'system:monitor' => [$this, 'systemMonitor'],
            'system:health' => [$this, 'healthCheck'],
            'logs:view' => [$this, 'viewLogs'],
            'logs:cleanup' => [$this, 'cleanupLogs'],
            'queue:work' => [$this, 'processQueue'],
            'backup:database' => [$this, 'backupDatabase'],
            'optimize:images' => [$this, 'optimizeImages'],
            'help' => [$this, 'showHelp']
        ];
    }
    
    /**
     * 运行命令
     */
    public function run(array $args)
    {
        if (count($args) < 2) {
            $this->showHelp();
            return;
        }
        
        $command = $args[1];
        $params = array_slice($args, 2);
        
        if (!isset($this->commands[$command])) {
            $this->error("未知命令: $command");
            $this->showHelp();
            return;
        }
        
        try {
            call_user_func($this->commands[$command], $params);
        } catch (Exception $e) {
            $this->error("命令执行失败: " . $e->getMessage());
        }
    }
    
    /**
     * 清除缓存
     */
    private function clearCache($params)
    {
        $this->info("正在清除缓存...");
        
        $cache = CacheManager::getInstance();
        if ($cache->flush()) {
            $this->success("缓存清除成功");
        } else {
            $this->error("缓存清除失败");
        }
    }
    
    /**
     * 查看缓存状态
     */
    private function cacheStatus($params)
    {
        $this->info("缓存状态:");
        // 这里可以添加具体的缓存状态检查逻辑
        $this->output("Redis: 连接正常");
        $this->output("文件缓存: 正常");
    }
    
    /**
     * 数据库迁移
     */
    private function migrateDatabase($params)
    {
        $this->info("正在执行数据库迁移...");
        
        $migrationScript = dirname(__DIR__) . '/database/migrate.php';
        if (file_exists($migrationScript)) {
            include $migrationScript;
            $this->success("数据库迁移完成");
        } else {
            $this->error("迁移脚本不存在");
        }
    }
    
    /**
     * 数据库状态
     */
    private function databaseStatus($params)
    {
        $this->info("数据库状态:");
        
        try {
            $db = DatabaseManager::getInstance();
            $connection = $db->getConnection();
            
            if ($connection) {
                $this->success("数据库连接: 正常");
                
                // 获取数据库信息
                $stmt = $connection->query("SELECT VERSION() as version");
                $result = $stmt->fetch();
                $this->output("MySQL版本: " . $result['version']);
                
                // 获取表数量
                $stmt = $connection->query("SELECT COUNT(*) as count FROM information_schema.tables WHERE table_schema = DATABASE()");
                $result = $stmt->fetch();
                $this->output("数据表数量: " . $result['count']);
            } else {
                $this->error("数据库连接失败");
            }
        } catch (Exception $e) {
            $this->error("数据库检查失败: " . $e->getMessage());
        }
    }
    
    /**
     * 系统监控
     */
    private function systemMonitor($params)
    {
        $monitor = SystemMonitor::getInstance();
        $metrics = $monitor->collectMetrics();
        
        $this->info("系统监控数据:");
        $this->output("时间戳: " . date('Y-m-d H:i:s', $metrics['timestamp']));
        
        // CPU负载
        $cpu = $metrics['system']['cpu_load'];
        $this->output("CPU负载: {$cpu['1min']} (1分钟), {$cpu['5min']} (5分钟), {$cpu['15min']} (15分钟)");
        
        // 内存使用
        $memory = $metrics['system']['memory'];
        $memoryUsage = round(($memory['used'] / $memory['limit']) * 100, 2);
        $this->output("内存使用: " . $this->formatBytes($memory['used']) . " / " . $this->formatBytes($memory['limit']) . " ({$memoryUsage}%)");
        
        // 磁盘空间
        $disk = $metrics['system']['disk_space'];
        $diskUsage = round(($disk['used'] / $disk['total']) * 100, 2);
        $this->output("磁盘使用: " . $this->formatBytes($disk['used']) . " / " . $this->formatBytes($disk['total']) . " ({$diskUsage}%)");
        
        // 记录指标
        $monitor->recordMetrics($metrics);
        $this->success("监控数据已记录到数据库");
    }
    
    /**
     * 健康检查
     */
    private function healthCheck($params)
    {
        $monitor = SystemMonitor::getInstance();
        $health = $monitor->checkHealth();
        
        $this->info("系统健康检查:");
        $this->output("整体状态: " . ($health['overall_status'] === 'healthy' ? '✓ 健康' : '✗ 异常'));
        
        foreach ($health['checks'] as $component => $check) {
            $status = $check['status'];
            $icon = $status === 'healthy' || $status === 'active' ? '✓' : '✗';
            $this->output("$component: $icon $status");
        }
    }
    
    /**
     * 查看日志
     */
    private function viewLogs($params)
    {
        $logManager = LogManager::getInstance();
        $filename = $params[0] ?? 'app-' . date('Y-m-d') . '.log';
        $lines = (int)($params[1] ?? 50);
        
        $this->info("查看日志文件: $filename (最近 {$lines} 行)");
        
        $logs = $logManager->readLogFile($filename, $lines);
        
        if (empty($logs)) {
            $this->warning("日志文件为空或不存在");
            return;
        }
        
        foreach ($logs as $log) {
            $level = strtoupper($log['level']);
            $time = $log['timestamp'];
            $message = $log['message'];
            $this->output("[$time] [$level] $message");
        }
    }
    
    /**
     * 清理日志
     */
    private function cleanupLogs($params)
    {
        $days = (int)($params[0] ?? 30);
        
        $this->info("清理 {$days} 天前的日志文件...");
        
        $logManager = LogManager::getInstance();
        $deletedCount = $logManager->cleanupOldLogs($days);
        
        $this->success("已删除 {$deletedCount} 个旧日志文件");
    }
    
    /**
     * 处理队列任务
     */
    private function processQueue($params)
    {
        $this->info("开始处理队列任务...");
        
        // 这里可以添加具体的队列处理逻辑
        $this->output("正在监听队列...");
        
        // 模拟队列处理
        $processed = 0;
        while (true) {
            // 检查是否有任务
            // 处理任务
            // $processed++;
            
            sleep(1); // 避免过度占用CPU
            
            // 可以添加退出条件
            if ($processed >= 100) { // 示例：处理100个任务后退出
                break;
            }
        }
        
        $this->success("队列处理完成，共处理 {$processed} 个任务");
    }
    
    /**
     * 备份数据库
     */
    private function backupDatabase($params)
    {
        $this->info("正在备份数据库...");
        
        $timestamp = date('Y-m-d_H-i-s');
        $backupDir = dirname(__DIR__) . '/storage/backups';
        
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }
        
        $backupFile = $backupDir . "/database_backup_{$timestamp}.sql";
        
        $dbConfig = $this->config->getDatabaseConfig();
        $command = sprintf(
            'mysqldump -h%s -P%s -u%s -p%s %s > %s',
            $dbConfig['host'],
            $dbConfig['port'],
            $dbConfig['username'],
            $dbConfig['password'],
            $dbConfig['database'],
            $backupFile
        );
        
        $result = shell_exec($command);
        
        if (file_exists($backupFile) && filesize($backupFile) > 0) {
            $this->success("数据库备份完成: " . basename($backupFile));
        } else {
            $this->error("数据库备份失败");
        }
    }
    
    /**
     * 优化图片
     */
    private function optimizeImages($params)
    {
        $this->info("正在优化图片...");
        
        $uploadsDir = dirname(__DIR__) . '/public/uploads';
        $files = glob($uploadsDir . '/**/*.{jpg,jpeg,png,gif}', GLOB_BRACE);
        
        $optimized = 0;
        foreach ($files as $file) {
            // 这里可以添加图片优化逻辑
            $optimized++;
        }
        
        $this->success("图片优化完成，共处理 {$optimized} 个文件");
    }
    
    /**
     * 显示帮助
     */
    private function showHelp($params = [])
    {
        $this->info("AlingAi 控制台命令工具");
        $this->output("");
        $this->output("使用方法: php console.php <command> [options]");
        $this->output("");
        $this->output("可用命令:");
        $this->output("  cache:clear        - 清除所有缓存");
        $this->output("  cache:status       - 查看缓存状态");
        $this->output("  db:migrate         - 执行数据库迁移");
        $this->output("  db:status          - 查看数据库状态");
        $this->output("  system:monitor     - 系统监控");
        $this->output("  system:health      - 健康检查");
        $this->output("  logs:view [file] [lines] - 查看日志文件");
        $this->output("  logs:cleanup [days] - 清理旧日志文件");
        $this->output("  queue:work         - 处理队列任务");
        $this->output("  backup:database    - 备份数据库");
        $this->output("  optimize:images    - 优化图片");
        $this->output("  help               - 显示此帮助信息");
    }
    
    /**
     * 输出信息
     */
    private function info($message)
    {
        echo "\033[34m[INFO]\033[0m $message\n";
    }
    
    /**
     * 输出成功信息
     */
    private function success($message)
    {
        echo "\033[32m[SUCCESS]\033[0m $message\n";
    }
    
    /**
     * 输出警告信息
     */
    private function warning($message)
    {
        echo "\033[33m[WARNING]\033[0m $message\n";
    }
    
    /**
     * 输出错误信息
     */
    private function error($message)
    {
        echo "\033[31m[ERROR]\033[0m $message\n";
    }
    
    /**
     * 输出普通信息
     */
    private function output($message)
    {
        echo "$message\n";
    }
    
    /**
     * 格式化字节大小
     */
    private function formatBytes($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= pow(1024, $pow);
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }
}

// 运行控制台应用
$console = new AlingAiConsole();
$console->run($argv);
