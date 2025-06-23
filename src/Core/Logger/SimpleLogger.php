<?php
/**
 * 简单的日志记录类，替代Monolog
 * 
 * @package AlingAi\Core\Logger
 */

declare(strict_types=1);

namespace AlingAi\Core\Logger;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

/**
 * SimpleLogger类，提供与Monolog\Logger兼容的接口
 */
class SimpleLogger implements LoggerInterface
{
    private string $name;
    private string $logFile;
    private array $handlers = [];
    private array $processors = [];
    
    /**
     * 构造函数
     *
     * @param string $name 日志记录器名称
     */
    public function __construct(string $name = 'app')
    {
        $this->name = $name;
        $logDir = __DIR__ . '/../../../storage/logs';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        $this->logFile = $logDir . '/app-' . date('Y-m-d') . '.log';
    }
    
    /**
     * 添加日志处理器
     *
     * @param mixed $handler 处理器对象
     * @return self
     */
    public function pushHandler($handler): self
    {
        array_unshift($this->handlers, $handler);
        return $this;
    }
    
    /**
     * 添加日志处理器（底部）
     *
     * @param mixed $handler 处理器对象
     * @return self
     */
    public function pushProcessor($processor): self
    {
        array_unshift($this->processors, $processor);
        return $this;
    }
    
    /**
     * 获取所有处理器
     *
     * @return array
     */
    public function getHandlers(): array
    {
        return $this->handlers;
    }
    
    /**
     * 获取所有处理器
     *
     * @return array
     */
    public function getProcessors(): array
    {
        return $this->processors;
    }
    
    /**
     * 记录调试信息
     *
     * @param string $message 日志消息
     * @param array $context 上下文数据
     * @return void
     */
    public function debug($message, array $context = []): void
    {
        $this->log(LogLevel::DEBUG, $message, $context);
    }
    
    /**
     * 记录信息
     *
     * @param string $message 日志消息
     * @param array $context 上下文数据
     * @return void
     */
    public function info($message, array $context = []): void
    {
        $this->log(LogLevel::INFO, $message, $context);
    }
    
    /**
     * 记录通知
     *
     * @param string $message 日志消息
     * @param array $context 上下文数据
     * @return void
     */
    public function notice($message, array $context = []): void
    {
        $this->log(LogLevel::NOTICE, $message, $context);
    }
    
    /**
     * 记录警告
     *
     * @param string $message 日志消息
     * @param array $context 上下文数据
     * @return void
     */
    public function warning($message, array $context = []): void
    {
        $this->log(LogLevel::WARNING, $message, $context);
    }
    
    /**
     * 记录错误
     *
     * @param string $message 日志消息
     * @param array $context 上下文数据
     * @return void
     */
    public function error($message, array $context = []): void
    {
        $this->log(LogLevel::ERROR, $message, $context);
    }
    
    /**
     * 记录严重错误
     *
     * @param string $message 日志消息
     * @param array $context 上下文数据
     * @return void
     */
    public function critical($message, array $context = []): void
    {
        $this->log(LogLevel::CRITICAL, $message, $context);
    }
    
    /**
     * 记录警报
     *
     * @param string $message 日志消息
     * @param array $context 上下文数据
     * @return void
     */
    public function alert($message, array $context = []): void
    {
        $this->log(LogLevel::ALERT, $message, $context);
    }
    
    /**
     * 记录紧急情况
     *
     * @param string $message 日志消息
     * @param array $context 上下文数据
     * @return void
     */
    public function emergency($message, array $context = []): void
    {
        $this->log(LogLevel::EMERGENCY, $message, $context);
    }
    
    /**
     * 记录日志
     *
     * @param string $level 日志级别
     * @param string|mixed $message 日志消息
     * @param array $context 上下文数据
     * @return void
     */
    public function log($level, $message, array $context = []): void
    {
        // 将非字符串消息转换为字符串
        if (!is_string($message)) {
            $message = $this->convertToString($message);
        }
        
        $timestamp = date('Y-m-d H:i:s');
        $contextStr = !empty($context) ? ' ' . json_encode($context, JSON_UNESCAPED_UNICODE) : '';
        $logMessage = "[{$timestamp}] [{$this->name}] [{$level}] {$message}{$contextStr}" . PHP_EOL;
        
        file_put_contents($this->logFile, $logMessage, FILE_APPEND);
        
        // 如果有处理器，调用处理器
        foreach ($this->handlers as $handler) {
            if (method_exists($handler, 'handle')) {
                $record = [
                    'message' => $message,
                    'context' => $context,
                    'level' => $level,
                    'level_name' => $level,
                    'channel' => $this->name,
                    'datetime' => new \DateTime(),
                    'extra' => [],
                ];
                
                // 应用处理器
                foreach ($this->processors as $processor) {
                    if (is_callable($processor)) {
                        $record = call_user_func($processor, $record);
                    }
                }
                
                $handler->handle($record);
            }
        }
    }
    
    /**
     * 将非字符串消息转换为字符串
     * 
     * @param mixed $message
     * @return string
     */
    private function convertToString($message): string
    {
        if (is_array($message) || is_object($message)) {
            return json_encode($message, JSON_UNESCAPED_UNICODE);
        }
        
        return (string) $message;
    }
    
    /**
     * 获取日志名称
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }
    
    /**
     * 设置日志文件路径
     *
     * @param string $path 文件路径
     * @return self
     */
    public function setLogFile(string $path): self
    {
        $this->logFile = $path;
        return $this;
    }
    
    /**
     * 获取日志文件路径
     *
     * @return string
     */
    public function getLogFile(): string
    {
        return $this->logFile;
    }
} 