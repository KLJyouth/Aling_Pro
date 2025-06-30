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
     * 初始化日志系统
     * @param array $config 日志配置
     * @return void
     */
    public static function init(array $config)
    {
        // 设置日志目录
        self::$logPath = $config['path'] ?? BASE_PATH . '/storage/logs';
        
        // 确保日志目录存在
        if (!is_dir(self::$logPath)) {
            mkdir(self::$logPath, 0755, true);
        }
        
        // 设置日志文件格式
        self::$logFormat = $config['format'] ?? 'Y-m-d';
        
        // 设置日志条目格式
        self::$entryFormat = $config['entry_format'] ?? '[%datetime%] %level_name%: %message% %context% %extra%';
        
        // 设置最低日志级别
        $levelName = strtoupper($config['level'] ?? 'debug');
        $level = array_search($levelName, array_map('strtoupper', self::$levels));
        self::$minLevel = $level !== false ? $level : self::DEBUG;
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
     * 记录日志
     * @param int $level 日志级别
     * @param string $message 日志消息
     * @param array $context 上下文数据
     * @return bool 是否成功
     */
    public static function log($level, $message, array $context = [])
    {
        // 检查日志级别
        if ($level < self::$minLevel) {
            return false;
        }
        
        // 获取日志级别名称
        $levelName = self::$levels[$level] ?? 'UNKNOWN';
        
        // 获取日志文件路径
        $logFile = self::getLogFile($level);
        
        // 格式化日志条目
        $entry = self::formatEntry($level, $levelName, $message, $context);
        
        // 写入日志文件
        return self::writeLog($logFile, $entry);
    }
    
    /**
     * 获取日志文件路径
     * @param int $level 日志级别
     * @return string 日志文件路径
     */
    private static function getLogFile($level)
    {
        // 根据日志级别获取日志文件名前缀
        $prefix = '';
        
        if ($level >= self::ERROR) {
            $prefix = 'error-';
        } elseif ($level >= self::WARNING) {
            $prefix = 'warning-';
        } elseif ($level >= self::INFO) {
            $prefix = 'info-';
        } else {
            $prefix = 'debug-';
        }
        
        // 生成日志文件名
        $date = date(self::$logFormat);
        $filename = $prefix . $date . '.log';
        
        return self::$logPath . '/' . $filename;
    }
    
    /**
     * 格式化日志条目
     * @param int $level 日志级别
     * @param string $levelName 日志级别名称
     * @param string $message 日志消息
     * @param array $context 上下文数据
     * @return string 格式化后的日志条目
     */
    private static function formatEntry($level, $levelName, $message, array $context = [])
    {
        // 准备替换变量
        $vars = [
            '%datetime%' => date('Y-m-d H:i:s'),
            '%level%' => $level,
            '%level_name%' => $levelName,
            '%message%' => $message
        ];
        
        // 处理上下文数据
        $contextStr = !empty($context) ? json_encode($context, JSON_UNESCAPED_UNICODE) : '';
        $vars['%context%'] = $contextStr;
        
        // 处理额外数据
        $extra = [
            'ip' => $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'uri' => $_SERVER['REQUEST_URI'] ?? 'unknown'
        ];
        $extraStr = !empty($extra) ? json_encode($extra, JSON_UNESCAPED_UNICODE) : '';
        $vars['%extra%'] = $extraStr;
        
        // 替换格式占位符
        $entry = strtr(self::$entryFormat, $vars);
        
        return $entry . PHP_EOL;
    }
    
    /**
     * 写入日志文件
     * @param string $logFile 日志文件路径
     * @param string $entry 日志条目
     * @return bool 是否成功
     */
    private static function writeLog($logFile, $entry)
    {
        try {
            $dir = dirname($logFile);
            
            // 确保日志目录存在
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            
            // 写入日志文件
            $result = file_put_contents($logFile, $entry, FILE_APPEND | LOCK_EX);
            
            return $result !== false;
        } catch (\Exception $e) {
            error_log('写入日志失败: ' . $e->getMessage());
            return false;
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
} 