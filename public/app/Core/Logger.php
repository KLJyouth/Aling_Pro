<?php
/**
 * 日志类
 * 
 * 负责记录应用程序日志
 * 
 * @package App\Core
 */

namespace App\Core;

class Logger
{
    /**
     * 日志配置
     * 
     * @var array
     */
    private static $config = [
        "path" => "",
        "level" => "debug"
    ];
    
    /**
     * 日志级别
     * 
     * @var array
     */
    private static $levels = [
        "debug" => 0,
        "info" => 1,
        "notice" => 2,
        "warning" => 3,
        "error" => 4,
        "critical" => 5,
        "alert" => 6,
        "emergency" => 7
    ];
    
    /**
     * 初始化日志系统
     * 
     * @param array $config 日志配置
     * @return void
     */
    public static function init(array $config)
    {
        self::$config = array_merge(self::$config, $config);
        
        // 确保日志目录存在
        if (!empty(self::$config["path"]) && !is_dir(self::$config["path"])) {
            mkdir(self::$config["path"], 0755, true);
        }
    }
    
    /**
     * 记录日志
     * 
     * @param string $level 日志级别
     * @param string $message 日志消息
     * @param array $context 上下文数据
     * @return void
     */
    public static function log($level, $message, array $context = [])
    {
        // 检查日志级别
        if (!isset(self::$levels[$level]) || self::$levels[$level] < self::$levels[self::$config["level"]]) {
            return;
        }
        
        // 格式化日志消息
        $logMessage = self::formatMessage($level, $message, $context);
        
        // 写入日志
        self::write($logMessage);
    }
    
    /**
     * 格式化日志消息
     * 
     * @param string $level 日志级别
     * @param string $message 日志消息
     * @param array $context 上下文数据
     * @return string
     */
    private static function formatMessage($level, $message, array $context = [])
    {
        $date = date("Y-m-d H:i:s");
        $levelUpper = strtoupper($level);
        
        // 替换消息中的占位符
        if (!empty($context)) {
            foreach ($context as $key => $value) {
                if (is_array($value) || is_object($value)) {
                    $value = json_encode($value);
                }
                $message = str_replace("{".$key."}", $value, $message);
            }
        }
        
        return "[{$date}] [{$levelUpper}] {$message}" . PHP_EOL;
    }
    
    /**
     * 写入日志
     * 
     * @param string $message 格式化后的日志消息
     * @return void
     */
    private static function write($message)
    {
        if (empty(self::$config["path"])) {
            // 如果没有配置日志路径，使用PHP错误日志
            error_log($message);
            return;
        }
        
        $logFile = self::$config["path"] . "/app-" . date("Y-m-d") . ".log";
        file_put_contents($logFile, $message, FILE_APPEND);
    }
    
    /**
     * 记录调试级别日志
     * 
     * @param string $message 日志消息
     * @param array $context 上下文数据
     * @return void
     */
    public static function debug($message, array $context = [])
    {
        self::log("debug", $message, $context);
    }
    
    /**
     * 记录信息级别日志
     * 
     * @param string $message 日志消息
     * @param array $context 上下文数据
     * @return void
     */
    public static function info($message, array $context = [])
    {
        self::log("info", $message, $context);
    }
    
    /**
     * 记录警告级别日志
     * 
     * @param string $message 日志消息
     * @param array $context 上下文数据
     * @return void
     */
    public static function warning($message, array $context = [])
    {
        self::log("warning", $message, $context);
    }
    
    /**
     * 记录错误级别日志
     * 
     * @param string $message 日志消息
     * @param array $context 上下文数据
     * @return void
     */
    public static function error($message, array $context = [])
    {
        self::log("error", $message, $context);
    }
}
