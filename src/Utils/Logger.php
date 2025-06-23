<?php
/**
 * AlingAi Pro - 日志工具类
 * 
 * @package AlingAi\Pro\Utils
 * @version 2.0.0
 * @author AlingAi Team
 * @created 2024-12-19
 */

declare(strict_types=1);

namespace AlingAi\Utils;

class Logger
{
    private static string $logPath = '';
    private static string $logLevel = 'INFO';
    
    /**
     * 设置日志路径
     */
    public static function setLogPath(string $path): void
    {
        self::$logPath = $path;
        
        // 确保日志目录存在
        $dir = dirname($path);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
    }
    
    /**
     * 设置日志级别
     */
    public static function setLogLevel(string $level): void
    {
        self::$logLevel = strtoupper($level);
    }
    
    /**
     * 记录信息日志
     */
    public static function info(string $message, array $context = []): void
    {
        self::log('INFO', $message, $context);
    }
    
    /**
     * 记录错误日志
     */
    public static function error(string $message, array $context = []): void
    {
        self::log('ERROR', $message, $context);
    }
    
    /**
     * 记录警告日志
     */
    public static function warning(string $message, array $context = []): void
    {
        self::log('WARNING', $message, $context);
    }
    
    /**
     * 记录调试日志
     */
    public static function debug(string $message, array $context = []): void
    {
        self::log('DEBUG', $message, $context);
    }
    
    /**
     * 记录日志
     */
    private static function log(string $level, string $message, array $context = []): void
    {
        // 根据日志级别过滤
        $levels = ['DEBUG' => 1, 'INFO' => 2, 'WARNING' => 3, 'ERROR' => 4];
        if ($levels[$level] < $levels[self::$logLevel]) {
            return;
        }
        
        $timestamp = date('Y-m-d H:i:s');
        $contextStr = !empty($context) ? ' | Context: ' . json_encode($context, JSON_UNESCAPED_UNICODE) : '';
        
        $logEntry = "[{$timestamp}] [{$level}] {$message}{$contextStr}" . PHP_EOL;
        
        // 写入文件
        if (self::$logPath) {
            file_put_contents(self::$logPath, $logEntry, FILE_APPEND | LOCK_EX);
        }
        
        // 同时输出到错误日志
        error_log($logEntry);
    }
}
