<?php
declare(strict_types=1);

/**
 * 文件名：FileLogger.php
 * 功能描述：文件日志记录器 - 将日志记录到文件
 * 创建时间：2025-01-XX
 * 最后修改：2025-01-XX
 * 版本：1.0.0
 *
 * @package AlingAi\Core\Logger
 * @author AlingAi Team
 * @license MIT
 */

namespace AlingAi\Core\Logger;

use Exception;
use InvalidArgumentException;

/**
 * 文件日志记录器
 *
 * 实现将日志记录到文件的功能
 */
class FileLogger implements LoggerInterface
{
    /**
     * 日志级别
     */
    private const LEVEL_EMERGENCY = 'emergency';
    private const LEVEL_ALERT = 'alert';
    private const LEVEL_CRITICAL = 'critical';
    private const LEVEL_ERROR = 'error';
    private const LEVEL_WARNING = 'warning';
    private const LEVEL_NOTICE = 'notice';
    private const LEVEL_INFO = 'info';
    private const LEVEL_DEBUG = 'debug';


    /**
     * 日志级别映射
     */
    private const LEVEL_MAP = [
        self::LEVEL_EMERGENCY => 0,
        self::LEVEL_ALERT => 1,
        self::LEVEL_CRITICAL => 2,
        self::LEVEL_ERROR => 3,
        self::LEVEL_WARNING => 4,
        self::LEVEL_NOTICE => 5,
        self::LEVEL_INFO => 6,
        self::LEVEL_DEBUG => 7
    ];

    /**
     * 日志文件路径
     */
    private string $logFilePath;

    /**
     * 最低日志级别
     */
    private string $minLevel;

    /**
     * 日志格式
     */
    private string $format;

    /**
     * 是否添加日志前缀
     */
    private bool $usePrefix;
    
    /**
     * 构造函数
     *
     * @param string $logFilePath 日志文件路径
     * @param string $minLevel 最低日志级别
     * @param string $format 日志格式
     * @param bool $usePrefix 是否添加日志前缀
     */
    public function __construct(
        string $logFilePath, 
        string $minLevel = self::LEVEL_DEBUG, 
        string $format = '[%datetime%] [%level%] %message% %context%', 
        bool $usePrefix = true
    ) {
        if (!in_array($minLevel, array_keys(self::LEVEL_MAP))) {
            throw new InvalidArgumentException("Invalid log level: $minLevel");
        }
        
        $this->logFilePath = $logFilePath;
        $this->minLevel = $minLevel;
        $this->format = $format;
        $this->usePrefix = $usePrefix;
        
        // 确保日志目录存在
        $logDir = dirname($logFilePath);
        if (!is_dir($logDir) && !mkdir($logDir, 0777, true)) {
            throw new Exception("Unable to create log directory: $logDir");
        }
    }
    
    /**
     * 写入日志
     *
     * @param string $level 日志级别
     * @param string $message 日志消息
     * @param array $context 上下文数据
     * @return void
     */
    private function writeLog(string $level, string $message, array $context = []): void
    {
        // 检查日志级别
        if (self::LEVEL_MAP[$level] > self::LEVEL_MAP[$this->minLevel]) {
            return;
        }
        
        // 格式化日志
        $log = $this->formatLog($level, $message, $context);
        
        // 写入日志
        try {
            file_put_contents($this->logFilePath, $log . PHP_EOL, FILE_APPEND);
        } catch (Exception $e) {
            // 日志写入失败，无法记录错误
        }
    }
    
    /**
     * 格式化日志
     *
     * @param string $level 日志级别
     * @param string $message 日志消息
     * @param array $context 上下文数据
     * @return string 格式化后的日志
     */
    private function formatLog(string $level, string $message, array $context = []): string
    {
        $log = $this->format;
        $log = str_replace('%datetime%', date('Y-m-d H:i:s'), $log);
        $log = str_replace('%level%', strtoupper($level), $log);
        $log = str_replace('%message%', $message, $log);
        $log = str_replace('%context%', $this->formatContext($context), $log);
        
        // 添加前缀
        if ($this->usePrefix) {
            $prefix = sprintf('[%s]', uniqid());
            $log = $prefix . ' ' . $log;
        }
        
        return $log;
    }
    
    /**
     * 格式化上下文数据
     *
     * @param array $context 上下文数据
     * @return string 格式化后的上下文
     */
    private function formatContext(array $context): string
    {
        if (empty($context)) {
            return '';
        }
        
        // 将上下文转换为JSON
        try {
            return json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        } catch (Exception $e) {
            return '{"error": "Unable to encode context"}';
        }
    }
    
    /**
     * {@inheritdoc}
     */
    public function emergency(string $message, array $context = []): void
    {
        $this->writeLog(self::LEVEL_EMERGENCY, $message, $context);
    }
    
    /**
     * {@inheritdoc}
     */
    public function alert(string $message, array $context = []): void
    {
        $this->writeLog(self::LEVEL_ALERT, $message, $context);
    }
    
    /**
     * {@inheritdoc}
     */
    public function critical(string $message, array $context = []): void
    {
        $this->writeLog(self::LEVEL_CRITICAL, $message, $context);
    }
    
    /**
     * {@inheritdoc}
     */
    public function error(string $message, array $context = []): void
    {
        $this->writeLog(self::LEVEL_ERROR, $message, $context);
    }
    
    /**
     * {@inheritdoc}
     */
    public function warning(string $message, array $context = []): void
    {
        $this->writeLog(self::LEVEL_WARNING, $message, $context);
    }
    
    /**
     * {@inheritdoc}
     */
    public function notice(string $message, array $context = []): void
    {
        $this->writeLog(self::LEVEL_NOTICE, $message, $context);
    }
    
    /**
     * {@inheritdoc}
     */
    public function info(string $message, array $context = []): void
    {
        $this->writeLog(self::LEVEL_INFO, $message, $context);
    }
    
    /**
     * {@inheritdoc}
     */
    public function debug(string $message, array $context = []): void
    {
        $this->writeLog(self::LEVEL_DEBUG, $message, $context);
    }
    
    /**
     * {@inheritdoc}
     */
    public function log($level, string $message, array $context = []): void
    {
        if (!is_string($level) || !in_array($level, array_keys(self::LEVEL_MAP))) {
            throw new InvalidArgumentException("Invalid log level: " . var_export($level, true));
        }
        
        $this->writeLog($level, $message, $context);
    }
}

