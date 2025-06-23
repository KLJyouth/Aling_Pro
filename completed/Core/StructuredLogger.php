<?php

declare(strict_types=1);

namespace AlingAi\Pro\Core;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Formatter\JsonFormatter;
use Monolog\Processor\UidProcessor;
use Monolog\Processor\WebProcessor;
use Monolog\Processor\MemoryUsageProcessor;
use Monolog\Processor\ProcessIdProcessor;

/**
 * 结构化日志系统
 * 
 * 提供统一的日志记录接口和配置
 */
/**
 * LoggerFactory 类
 *
 * @package AlingAi\Pro\Core
 */
class LoggerFactory
{
    private array $config;
    private array $loggers = [];

    /**


     * __construct 方法


     *


     * @param array $config


     * @return void


     */


    public function __construct(array $config = [])
    {
        $this->config = array_merge([
            'default_channel' => 'app',
            'log_level' => LogLevel::INFO,
            'log_path' => __DIR__ . '/../../storage/logs',
            'max_files' => 30,
            'enable_json' => true,
            'enable_console' => false,
            'channels' => [
                'app' => [
                    'level' => LogLevel::INFO,
                    'file' => 'app.log'
                ],
                'api' => [
                    'level' => LogLevel::INFO,
                    'file' => 'api.log'
                ],
                'auth' => [
                    'level' => LogLevel::INFO,
                    'file' => 'auth.log'
                ],
                'database' => [
                    'level' => LogLevel::WARNING,
                    'file' => 'database.log'
                ],
                'cache' => [
                    'level' => LogLevel::INFO,
                    'file' => 'cache.log'
                ],
                'security' => [
                    'level' => LogLevel::WARNING,
                    'file' => 'security.log'
                ],
                'performance' => [
                    'level' => LogLevel::INFO,
                    'file' => 'performance.log'
                ]
            ]
        ], $config);

        // 确保日志目录存在
        if (!is_dir($this->config['log_path'])) {
            mkdir($this->config['log_path'], 0755, true);
        }
    }

    /**
     * 获取指定通道的日志器
     */
    /**

     * getLogger 方法

     *

     * @param string $channel

     * @return void

     */

    public function getLogger(string $channel = null): LoggerInterface
    {
        $channel = $channel ?: $this->config['default_channel'];
        
        if (!isset($this->loggers[$channel])) {
            $this->loggers[$channel] = $this->createLogger($channel);
        }

        return $this->loggers[$channel];
    }

    /**
     * 创建日志器
     */
    /**

     * createLogger 方法

     *

     * @param string $channel

     * @return void

     */

    private function createLogger(string $channel): Logger
    {
        $channelConfig = $this->config['channels'][$channel] ?? $this->config['channels']['app'];
        $logger = new Logger($channel);

        // 添加文件处理器
        $this->addFileHandler($logger, $channel, $channelConfig);

        // 添加控制台处理器（如果启用）
        if ($this->config['enable_console']) {
            $this->addConsoleHandler($logger, $channelConfig);
        }

        // 添加处理器
        $this->addProcessors($logger);

        return $logger;
    }

    /**
     * 添加文件处理器
     */
    /**

     * addFileHandler 方法

     *

     * @param Logger $logger

     * @param string $channel

     * @param array $config

     * @return void

     */

    private function addFileHandler(Logger $logger, string $channel, array $config): void
    {
        $filename = $this->config['log_path'] . '/' . $config['file'];
        
        $handler = new RotatingFileHandler(
            $filename,
            $this->config['max_files'],
            $config['level']
        );

        if ($this->config['enable_json']) {
            $handler->setFormatter(new JsonFormatter());
        }

        $logger->pushHandler($handler);
    }

    /**
     * 添加控制台处理器
     */
    /**

     * addConsoleHandler 方法

     *

     * @param Logger $logger

     * @param array $config

     * @return void

     */

    private function addConsoleHandler(Logger $logger, array $config): void
    {
        $handler = new StreamHandler('php://stdout', $config['level']);
        $logger->pushHandler($handler);
    }

    /**
     * 添加处理器
     */
    /**

     * addProcessors 方法

     *

     * @param Logger $logger

     * @return void

     */

    private function addProcessors(Logger $logger): void
    {
        $logger->pushProcessor(new UidProcessor());
        $logger->pushProcessor(new WebProcessor());
        $logger->pushProcessor(new MemoryUsageProcessor());
        $logger->pushProcessor(new ProcessIdProcessor());
        
        // 添加自定义处理器
        $logger->pushProcessor(function ($record) {
            $record['extra']['app'] = 'AlingAi_Pro';
            $record['extra']['version'] = '1.0.0';
            $record['extra']['timestamp'] = microtime(true);
            return $record;
        });
    }
}

/**
 * 结构化日志记录器
 * 
 * 提供便捷的日志记录方法
 */
class StructuredLogger
{
    private LoggerInterface $logger;
    private array $defaultContext;

    /**


     * __construct 方法


     *


     * @param LoggerInterface $logger


     * @param array $defaultContext


     * @return void


     */


    public function __construct(LoggerInterface $logger, array $defaultContext = [])
    {
        $this->logger = $logger;
        $this->defaultContext = $defaultContext;
    }

    /**
     * 记录API请求
     */
    /**

     * logApiRequest 方法

     *

     * @param string $method

     * @param string $path

     * @param array $params

     * @param float $responseTime

     * @param int $statusCode

     * @param string $userId

     * @return void

     */

    public function logApiRequest(
        string $method,
        string $path,
        array $params = [],
        float $responseTime = null,
        int $statusCode = null,
        string $userId = null
    ): void {
        $context = array_merge($this->defaultContext, [
            'type' => 'api_request',
            'http_method' => $method,
            'path' => $path,
            'params' => $this->sanitizeParams($params),
            'response_time_ms' => $responseTime ? round($responseTime * 1000, 2) : null,
            'status_code' => $statusCode,
            'user_id' => $userId
        ]);

        $level = $this->getLogLevelFromStatusCode($statusCode);
        $message = "API {$method} {$path}";
        
        if ($statusCode) {
            $message .= " - {$statusCode}";
        }
        
        if ($responseTime) {
            $message .= " (" . round($responseTime * 1000, 2) . "ms)";
        }

        $this->logger->log($level, $message, $context);
    }

    /**
     * 记录用户行为
     */
    /**

     * logUserAction 方法

     *

     * @param string $action

     * @param string $userId

     * @param array $data

     * @param string $ip

     * @return void

     */

    public function logUserAction(
        string $action,
        string $userId,
        array $data = [],
        string $ip = null
    ): void {
        $context = array_merge($this->defaultContext, [
            'type' => 'user_action',
            'action' => $action,
            'user_id' => $userId,
            'data' => $data,
            'ip_address' => $ip
        ]);

        $this->logger->info("用户行为: {$action}", $context);
    }

    /**
     * 记录安全事件
     */
    /**

     * logSecurityEvent 方法

     *

     * @param string $event

     * @param string $severity

     * @param array $details

     * @param string $ip

     * @param string $userId

     * @return void

     */

    public function logSecurityEvent(
        string $event,
        string $severity = 'medium',
        array $details = [],
        string $ip = null,
        string $userId = null
    ): void {
        $context = array_merge($this->defaultContext, [
            'type' => 'security_event',
            'event' => $event,
            'severity' => $severity,
            'details' => $details,
            'ip_address' => $ip,
            'user_id' => $userId
        ]);

        $level = $this->getLogLevelFromSeverity($severity);
        $this->logger->log($level, "安全事件: {$event}", $context);
    }

    /**
     * 记录数据库操作
     */
    /**

     * logDatabaseQuery 方法

     *

     * @param string $query

     * @param array $bindings

     * @param float $executionTime

     * @param string $connection

     * @return void

     */

    public function logDatabaseQuery(
        string $query,
        array $bindings = [],
        float $executionTime = null,
        string $connection = 'default'
    ): void {
        $context = array_merge($this->defaultContext, [
            'type' => 'database_query',
            'query' => $query,
            'bindings' => $bindings,
            'execution_time_ms' => $executionTime ? round($executionTime * 1000, 2) : null,
            'connection' => $connection
        ]);

        $level = LogLevel::DEBUG;
        if ($executionTime && $executionTime > 1.0) { // 慢查询
            $level = LogLevel::WARNING;
        }

        $message = "数据库查询";
        if ($executionTime) {
            $message .= " (" . round($executionTime * 1000, 2) . "ms)";
        }

        $this->logger->log($level, $message, $context);
    }

    /**
     * 记录缓存操作
     */
    /**

     * logCacheOperation 方法

     *

     * @param string $operation

     * @param string $key

     * @param bool $hit

     * @param float $executionTime

     * @return void

     */

    public function logCacheOperation(
        string $operation,
        string $key,
        bool $hit = null,
        float $executionTime = null
    ): void {
        $context = array_merge($this->defaultContext, [
            'type' => 'cache_operation',
            'operation' => $operation,
            'key' => $key,
            'hit' => $hit,
            'execution_time_ms' => $executionTime ? round($executionTime * 1000, 2) : null
        ]);

        $message = "缓存{$operation}: {$key}";
        if ($hit !== null) {
            $message .= $hit ? ' (命中)' : ' (未命中)';
        }

        $this->logger->info($message, $context);
    }

    /**
     * 记录性能指标
     */
    /**

     * logPerformanceMetric 方法

     *

     * @param string $metric

     * @param mixed $value

     * @param string $unit

     * @param array $tags

     * @return void

     */

    public function logPerformanceMetric(
        string $metric,
        $value,
        string $unit = '',
        array $tags = []
    ): void {
        $context = array_merge($this->defaultContext, [
            'type' => 'performance_metric',
            'metric' => $metric,
            'value' => $value,
            'unit' => $unit,
            'tags' => $tags
        ]);

        $message = "性能指标 {$metric}: {$value}";
        if ($unit) {
            $message .= " {$unit}";
        }

        $this->logger->info($message, $context);
    }

    /**
     * 记录业务事件
     */
    /**

     * logBusinessEvent 方法

     *

     * @param string $event

     * @param array $data

     * @param string $userId

     * @return void

     */

    public function logBusinessEvent(
        string $event,
        array $data = [],
        string $userId = null
    ): void {
        $context = array_merge($this->defaultContext, [
            'type' => 'business_event',
            'event' => $event,
            'data' => $data,
            'user_id' => $userId
        ]);

        $this->logger->info("业务事件: {$event}", $context);
    }

    /**
     * 根据HTTP状态码获取日志级别
     */
    /**

     * getLogLevelFromStatusCode 方法

     *

     * @param int $statusCode

     * @return void

     */

    private function getLogLevelFromStatusCode(?int $statusCode): string
    {
        if (!$statusCode) {
            return LogLevel::INFO;
        }

        return match (true) {
            $statusCode >= 500 => LogLevel::ERROR,
            $statusCode >= 400 => LogLevel::WARNING,
            $statusCode >= 300 => LogLevel::INFO,
            default => LogLevel::INFO
        };
    }

    /**
     * 根据安全事件严重程度获取日志级别
     */
    /**

     * getLogLevelFromSeverity 方法

     *

     * @param string $severity

     * @return void

     */

    private function getLogLevelFromSeverity(string $severity): string
    {
        return match ($severity) {
            'critical' => LogLevel::CRITICAL,
            'high' => LogLevel::ERROR,
            'medium' => LogLevel::WARNING,
            'low' => LogLevel::INFO,
            default => LogLevel::WARNING
        };
    }

    /**
     * 清理敏感参数
     */
    /**

     * sanitizeParams 方法

     *

     * @param array $params

     * @return void

     */

    private function sanitizeParams(array $params): array
    {
        $sensitiveKeys = [
            'password', 'token', 'api_key', 'secret', 'authorization',
            'password_confirmation', 'old_password', 'new_password'
        ];

        foreach ($sensitiveKeys as $key) {
            if (isset($params[$key])) {
                $params[$key] = '[REDACTED]';
            }
        }

        return $params;
    }

    /**
     * 代理其他日志方法
     */
    /**

     * __call 方法

     *

     * @param string $method

     * @param array $arguments

     * @return void

     */

    public function __call(string $method, array $arguments)
    {
        if (method_exists($this->logger, $method)) {
            return call_user_func_array([$this->logger, $method], $arguments);
        }

        throw new \BadMethodCallException("方法 {$method} 不存在");
    }
}
