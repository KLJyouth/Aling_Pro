<?php

declare(strict_types=1);

namespace AlingAi\Core\Logger;

use Monolog\Logger as MonologLogger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Formatter\LineFormatter;
use Psr\Log\LoggerInterface;

/**
 * 日志工厂类
 * 创建和管理应用程序的日志记录器
 */
class LoggerFactory
{
    private static ?LoggerInterface $logger = null;

    /**
     * 创建应用程序日志记录器
     */
    public static function createLogger(): LoggerInterface
    {
        if (self::$logger !== null) {
            return self::$logger;
        }

        $logger = new MonologLogger('alingai');

        // 添加文件处理器
        $logPath = self::getLogPath();
        $fileHandler = new RotatingFileHandler($logPath, 30, MonologLogger::DEBUG);
        $fileHandler->setFormatter(self::createFormatter());
        $logger->pushHandler($fileHandler);

        // 添加错误日志处理器
        $errorLogPath = self::getErrorLogPath();
        $errorHandler = new StreamHandler($errorLogPath, MonologLogger::ERROR);
        $errorHandler->setFormatter(self::createFormatter());
        $logger->pushHandler($errorHandler);

        // 开发环境下添加控制台输出
        if (self::isDevelopment()) {
            $consoleHandler = new StreamHandler('php://stdout', MonologLogger::DEBUG);
            $consoleHandler->setFormatter(self::createFormatter());
            $logger->pushHandler($consoleHandler);
        }

        self::$logger = $logger;
        return $logger;
    }

    /**
     * 创建日志格式器
     */
    private static function createFormatter(): LineFormatter
    {
        $dateFormat = "Y-m-d H:i:s";
        $output = "[%datetime%] %channel%.%level_name%: %message% %context% %extra%\n";
        
        return new LineFormatter($output, $dateFormat, true, true);
    }

    /**
     * 获取日志文件路径
     */
    private static function getLogPath(): string
    {
        $logDir = dirname(__DIR__, 4) . '/storage/logs';
        
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        return $logDir . '/application.log';
    }

    /**
     * 获取错误日志文件路径
     */
    private static function getErrorLogPath(): string
    {
        $logDir = dirname(__DIR__, 4) . '/storage/logs';
        
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        return $logDir . '/error.log';
    }

    /**
     * 检查是否为开发环境
     */
    private static function isDevelopment(): bool
    {
        return ($_ENV['APP_ENV'] ?? 'production') === 'development';
    }

    /**
     * 创建特定模块的日志记录器
     */
    public static function createModuleLogger(string $module): LoggerInterface
    {
        $logger = new MonologLogger($module);

        $logPath = self::getLogPath();
        $fileHandler = new RotatingFileHandler($logPath, 30, MonologLogger::DEBUG);
        $fileHandler->setFormatter(self::createFormatter());
        $logger->pushHandler($fileHandler);

        return $logger;
    }

    /**
     * 创建API日志记录器
     */
    public static function createApiLogger(): LoggerInterface
    {
        return self::createModuleLogger('api');
    }

    /**
     * 创建数据库日志记录器
     */
    public static function createDatabaseLogger(): LoggerInterface
    {
        return self::createModuleLogger('database');
    }

    /**
     * 创建聊天日志记录器
     */
    public static function createChatLogger(): LoggerInterface
    {
        return self::createModuleLogger('chat');
    }
} 