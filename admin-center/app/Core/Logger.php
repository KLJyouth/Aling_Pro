<?php
namespace App\Core;

/**
 * 日志管理类
 * 负责记录和管理系统日志
 */
class Logger
{
    /**
     * 日志级别常量
     */
    const DEBUG     = 100;
    const INFO      = 200;
    const NOTICE    = 250;
    const WARNING   = 300;
    const ERROR     = 400;
    const CRITICAL  = 500;
    const ALERT     = 550;
    const EMERGENCY = 600;
    
    /**
     * 日志级别名称映射
     * @var array
     */
    private static $levels = [
        self::DEBUG     => 'DEBUG',
        self::INFO      => 'INFO',
        self::NOTICE    => 'NOTICE',
        self::WARNING   => 'WARNING',
        self::ERROR     => 'ERROR',
        self::CRITICAL  => 'CRITICAL',
        self::ALERT     => 'ALERT',
        self::EMERGENCY => 'EMERGENCY',
    ];
    
    /**
     * 日志目录
     * @var string
     */
    private static $logPath = '';
    
    /**
     * 日志文件格式
     * @var string
     */
    private static $logFormat = 'Y-m-d';
    
    /**
     * 日志条目格式
     * @var string
     */
    private static $entryFormat = '[%datetime%] %level_name%: %message% %context% %extra%';
    
    /**
     * 日志级别
     * @var int
     */
    private static $minLevel = self::DEBUG;
    
    /**
     * 日志级别
     * @var int
     */
    private static $level = self::DEBUG;
    
    /**
     * 日志格式
     * @var string
     */
    private static $format = '[%datetime%] %level_name%: %message% %context%';
    
    /**
     * 日期格式
     * @var string
     */
    private static $dateFormat = 'Y-m-d H:i:s';
    
    /**
     * 最大日志文件数
     * @var int
     */
    private static $maxFiles = 30;
    
    /**
     * 日志通道
     * @var array
     */
    private static $channels = [];
    
    /**
     * 初始化日志系统
     * @param array $config 日志配置
     * @return void
     */
    public static function init(array $config)
    {
        // 设置日志目录
        self::$logPath = $config['path'] ?? BASE_PATH . '/storage/logs';
        
        // 设置日志级别
        self::$level = self::parseLevel($config['level'] ?? 'debug');
        
        // 设置日志格式
        self::$format = $config['format'] ?? '[%datetime%] %level_name%: %message% %context%';
        self::$dateFormat = $config['date_format'] ?? 'Y-m-d H:i:s';
        
        // 设置最大日志文件数
        self::$maxFiles = $config['max_files'] ?? 30;
        
        // 设置日志通道
        if (isset($config['channels']) && is_array($config['channels'])) {
            self::$channels = $config['channels'];
        }
        
        // 确保日志目录存在
        self::ensureLogDirectoryExists();
    }
    
    /**
     * 确保日志目录存在
     * @return void
     */
    private static function ensureLogDirectoryExists()
    {
        try {
            // 检查日志目录是否存在
            if (!is_dir(self::$logPath)) {
                // 获取目录的父级目录
                $parentDir = dirname(self::$logPath);
                
                // 确保父目录存在
                if (!is_dir($parentDir)) {
                    // 尝试创建父目录
                    if (!@mkdir($parentDir, 0755, true)) {
                        error_log("无法创建日志父目录: " . $parentDir);
                        return;
                    }
                }
                
                // 尝试创建日志目录
                if (!@mkdir(self::$logPath, 0755, true)) {
                    error_log("无法创建日志目录: " . self::$logPath);
                    return;
                }
            }
            
            // 如果目录存在但不可写，记录错误
            if (is_dir(self::$logPath) && !is_writable(self::$logPath)) {
                error_log("日志目录不可写: " . self::$logPath);
                return;
            }
            
            // 确保通道日志目录存在
            foreach (self::$channels as $channel => $config) {
                $channelPath = self::$logPath . '/' . $channel;
                if (!is_dir($channelPath)) {
                    if (!@mkdir($channelPath, 0755, true)) {
                        error_log("无法创建通道日志目录: " . $channelPath);
                    }
                }
            }
        } catch (\Exception $e) {
            // 记录异常到系统日志
            error_log("创建日志目录时发生异常: " . $e->getMessage());
        }
    }
    
    /**
     * 记录调试日志
     * @param string $message 日志消息
     * @param array $context 上下文数据
     * @return bool 是否成功
     */
    public static function debug($message, array $context = [])
    {
        return self::log(self::DEBUG, $message, $context);
    }
    
    /**
     * 记录信息日志
     * @param string $message 日志消息
     * @param array $context 上下文数据
     * @return bool 是否成功
     */
    public static function info($message, array $context = [])
    {
        return self::log(self::INFO, $message, $context);
    }
    
    /**
     * 记录通知日志
     * @param string $message 日志消息
     * @param array $context 上下文数据
     * @return bool 是否成功
     */
    public static function notice($message, array $context = [])
    {
        return self::log(self::NOTICE, $message, $context);
    }
    
    /**
     * 记录警告日志
     * @param string $message 日志消息
     * @param array $context 上下文数据
     * @return bool 是否成功
     */
    public static function warning($message, array $context = [])
    {
        return self::log(self::WARNING, $message, $context);
    }
    
    /**
     * 记录错误日志
     * @param string $message 日志消息
     * @param array $context 上下文数据
     * @return bool 是否成功
     */
    public static function error($message, array $context = [])
    {
        return self::log(self::ERROR, $message, $context);
    }
    
    /**
     * 记录严重错误日志
     * @param string $message 日志消息
     * @param array $context 上下文数据
     * @return bool 是否成功
     */
    public static function critical($message, array $context = [])
    {
        return self::log(self::CRITICAL, $message, $context);
    }
    
    /**
     * 记录警报日志
     * @param string $message 日志消息
     * @param array $context 上下文数据
     * @return bool 是否成功
     */
    public static function alert($message, array $context = [])
    {
        return self::log(self::ALERT, $message, $context);
    }
    
    /**
     * 记录紧急日志
     * @param string $message 日志消息
     * @param array $context 上下文数据
     * @return bool 是否成功
     */
    public static function emergency($message, array $context = [])
    {
        return self::log(self::EMERGENCY, $message, $context);
    }
    
    /**
     * 写入日志
     * @param int $level 日志级别
     * @param string $message 日志消息
     * @param array $context 上下文数据
     * @param string|null $channel 日志通道，默认为null（使用默认通道）
     * @return bool 是否成功写入
     */
    public static function log($level, $message, array $context = [], $channel = null)
    {
        // 检查日志级别
        if ($level < self::$level) {
            return false;
        }
        
        // 确保日志目录存在
        if (!is_dir(self::$logPath) || !is_writable(self::$logPath)) {
            self::ensureLogDirectoryExists();
        }
        
        try {
            // 获取日志级别名称
            $levelName = self::getLevelName($level);
            
            // 格式化日志条目
            $entry = self::formatEntry($levelName, $message, $context);
            
            // 获取日志文件路径
            $logFile = self::getLogFile($levelName, $channel);
            
            // 写入日志
            if (file_put_contents($logFile, $entry . PHP_EOL, FILE_APPEND | LOCK_EX) === false) {
                error_log("无法写入日志文件: $logFile");
                return false;
            }
            
            // 日志轮转（如果需要）
            self::rotateLogFile($logFile);
            
            return true;
        } catch (\Exception $e) {
            error_log("写入日志失败: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * 获取日志文件路径
     * @param string $levelName 日志级别名称
     * @param string|null $channel 日志通道
     * @return string 日志文件路径
     */
    private static function getLogFile($levelName, $channel = null)
    {
        // 如果指定了通道，优先使用通道配置
        if ($channel !== null && isset(self::$channels[$channel])) {
            $channelConfig = self::$channels[$channel];
            $channelDir = self::$logPath . '/' . $channel;
            $fileName = $channelConfig['file'] ?? 'channel.log';
            
            // 确保通道目录存在
            if (!is_dir($channelDir)) {
                if (!@mkdir($channelDir, 0755, true)) {
                    // 如果无法创建通道目录，回退到默认日志目录
                    return self::$logPath . '/' . date('Y-m-d') . '.log';
                }
            }
            
            return $channelDir . '/' . $fileName;
        }
        
        // 否则根据日志级别确定日志文件
        $fileName = strtolower($levelName);
        
        // 按级别分类
        switch ($fileName) {
            case 'emergency':
            case 'alert':
            case 'critical':
            case 'error':
                return self::$logPath . '/error.log';
            case 'warning':
                return self::$logPath . '/warning.log';
            case 'notice':
            case 'info':
                return self::$logPath . '/info.log';
            case 'debug':
            default:
                return self::$logPath . '/debug.log';
        }
    }
    
    /**
     * 格式化日志条目
     * @param string $levelName 日志级别名称
     * @param string $message 日志消息
     * @param array $context 上下文数据
     * @return string 格式化的日志条目
     */
    private static function formatEntry($levelName, $message, array $context)
    {
        // 替换日志格式中的占位符
        $entry = self::$format;
        
        // 替换日期时间
        $entry = str_replace('%datetime%', date(self::$dateFormat), $entry);
        
        // 替换级别名称
        $entry = str_replace('%level_name%', $levelName, $entry);
        
        // 替换消息
        $entry = str_replace('%message%', $message, $entry);
        
        // 替换上下文
        if (!empty($context)) {
            $contextJson = self::jsonEncode($context);
            $entry = str_replace('%context%', $contextJson, $entry);
        } else {
            $entry = str_replace('%context%', '', $entry);
        }
        
        // 替换其他占位符
        $entry = str_replace('%extra%', '', $entry);
        
        return $entry;
    }
    
    /**
     * 日志文件轮转
     * @param string $logFile 日志文件路径
     * @return void
     */
    private static function rotateLogFile($logFile)
    {
        // 检查文件是否需要轮转
        if (!file_exists($logFile) || filesize($logFile) < 10485760) { // 10MB
            return;
        }
        
        try {
            $fileInfo = pathinfo($logFile);
            $baseName = $fileInfo['filename'];
            $extension = $fileInfo['extension'] ?? 'log';
            $dirName = $fileInfo['dirname'];
            
            // 查找现有的轮转日志文件
            $existingLogs = glob("$dirName/$baseName.*.{$extension}");
            
            // 对现有文件按数字排序
            usort($existingLogs, function($a, $b) use ($baseName, $extension) {
                preg_match("/$baseName\.(\d+)\.{$extension}/", $a, $matchesA);
                preg_match("/$baseName\.(\d+)\.{$extension}/", $b, $matchesB);
                
                $numA = isset($matchesA[1]) ? intval($matchesA[1]) : 0;
                $numB = isset($matchesB[1]) ? intval($matchesB[1]) : 0;
                
                return $numB - $numA; // 降序排序
            });
            
            // 轮转现有日志文件
            foreach ($existingLogs as $log) {
                preg_match("/$baseName\.(\d+)\.{$extension}/", $log, $matches);
                $index = intval($matches[1]);
                
                // 如果达到最大文件数，删除最旧的日志
                if ($index >= self::$maxFiles - 1) {
                    @unlink($log);
                    continue;
                }
                
                // 轮转日志文件
                $newIndex = $index + 1;
                $newFile = "$dirName/$baseName.$newIndex.$extension";
                @rename($log, $newFile);
            }
            
            // 将当前日志文件轮转为第1个备份
            $backupFile = "$dirName/$baseName.1.$extension";
            @rename($logFile, $backupFile);
            
            // 创建新的日志文件
            @touch($logFile);
            @chmod($logFile, 0644);
        } catch (\Exception $e) {
            error_log("日志轮转失败: " . $e->getMessage());
        }
    }
    
    /**
     * 将数据编码为JSON
     * @param mixed $data 要编码的数据
     * @return string JSON字符串
     */
    private static function jsonEncode($data)
    {
        try {
            $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            return $json ?: '';
        } catch (\Exception $e) {
            return '{"error": "无法编码为JSON"}';
        }
    }
    
    /**
     * 清理旧日志
     * @param int $days 保留天数
     * @return int 清理的文件数
     */
    public static function cleanOldLogs($days = 30)
    {
        $count = 0;
        
        try {
            // 计算截止日期
            $cutoff = time() - ($days * 86400);
            
            // 获取所有日志文件
            $files = glob(self::$logPath . '/*.log');
            
            foreach ($files as $file) {
                // 检查文件修改时间
                $mtime = filemtime($file);
                
                if ($mtime < $cutoff) {
                    // 删除旧文件
                    if (unlink($file)) {
                        $count++;
                    }
                }
            }
        } catch (\Exception $e) {
            error_log('清理日志失败: ' . $e->getMessage());
        }
        
        return $count;
    }
    
    /**
     * 获取日志级别名称
     * @param int $level 日志级别
     * @return string 日志级别名称
     */
    private static function getLevelName($level)
    {
        return self::$levels[$level] ?? 'UNKNOWN';
    }
    
    /**
     * 解析日志级别
     * @param string $level 日志级别名称
     * @return int 日志级别
     */
    private static function parseLevel($level)
    {
        $level = strtolower($level);
        
        switch ($level) {
            case 'emergency':
                return self::EMERGENCY;
            case 'alert':
                return self::ALERT;
            case 'critical':
                return self::CRITICAL;
            case 'error':
                return self::ERROR;
            case 'warning':
                return self::WARNING;
            case 'notice':
                return self::NOTICE;
            case 'info':
                return self::INFO;
            case 'debug':
            default:
                return self::DEBUG;
        }
    }
} 