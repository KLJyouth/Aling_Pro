<?php

declare(strict_types=1);

namespace AlingAi\Pro\Monitoring;

use Psr\Log\LoggerInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Exception;
use Throwable;

/**
 * 错误追踪系统
 * 
 * 收集、分析和追踪系统错误
 * 
 * @package AlingAi\Pro\Monitoring
 */
class ErrorTracker
{
    private LoggerInterface $logger;
    private array $config;
    private array $errorContext = [];

    public function __construct(LoggerInterface $logger, array $config = [])
    {
        $this->logger = $logger;
        $this->config = array_merge([
            'enabled' => true,
            'sample_rate' => 1.0,
            'max_trace_depth' => 20,
            'ignore_exceptions' => [
                'Illuminate\Http\Exceptions\HttpResponseException',
                'Illuminate\Validation\ValidationException',
            ],
            'sentry_dsn' => null,
            'webhook_url' => null,
        ], $config);
    }

    /**
     * 捕获异常
     */
    public function captureException(Throwable $exception, array $context = []): string
    {
        if (!$this->config['enabled'] || !$this->shouldCapture($exception)) {
            return '';
        }

        $errorId = $this->generateErrorId();
        
        $errorData = [
            'id' => $errorId,
            'exception' => get_class($exception),
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'code' => $exception->getCode(),
            'trace' => $this->formatStackTrace($exception),
            'context' => array_merge($this->getSystemContext(), $context),
            'timestamp' => time(),
            'severity' => $this->determineSeverity($exception),
        ];

        // 存储错误数据
        $this->storeError($errorData);
        
        // 记录日志
        $this->logger->error('Exception captured', $errorData);
        
        // 发送到外部服务
        $this->sendToExternalServices($errorData);
        
        // 更新错误统计
        $this->updateErrorStatistics($errorData);

        return $errorId;
    }

    /**
     * 捕获消息
     */
    public function captureMessage(string $message, string $level = 'info', array $context = []): string
    {
        if (!$this->config['enabled']) {
            return '';
        }

        $messageId = $this->generateErrorId();
        
        $messageData = [
            'id' => $messageId,
            'message' => $message,
            'level' => $level,
            'context' => array_merge($this->getSystemContext(), $context),
            'timestamp' => time(),
        ];

        // 存储消息数据
        $this->storeMessage($messageData);
        
        // 记录日志
        $this->logger->log($level, 'Message captured', $messageData);

        return $messageId;
    }

    /**
     * 获取错误详情
     */
    public function getError(string $errorId): ?array
    {
        return Cache::get("error_tracker:error:{$errorId}");
    }

    /**
     * 获取错误列表
     */
    public function getErrors(int $limit = 50, int $offset = 0): array
    {
        $errorIds = Cache::get('error_tracker:error_list', []);
        $paginatedIds = array_slice($errorIds, $offset, $limit);
        
        $errors = [];
        foreach ($paginatedIds as $errorId) {
            $error = $this->getError($errorId);
            if ($error) {
                $errors[] = $error;
            }
        }

        return $errors;
    }

    /**
     * 获取错误统计
     */
    public function getErrorStatistics(int $hours = 24): array
    {
        $stats = [
            'total_errors' => 0,
            'errors_by_hour' => [],
            'errors_by_type' => [],
            'errors_by_severity' => [],
            'top_errors' => [],
        ];

        $startTime = time() - ($hours * 3600);
        
        for ($i = 0; $i < $hours; $i++) {
            $hourKey = date('Y-m-d H:00:00', $startTime + ($i * 3600));
            $stats['errors_by_hour'][$hourKey] = Cache::get("error_tracker:hourly:{$hourKey}", 0);
            $stats['total_errors'] += $stats['errors_by_hour'][$hourKey];
        }

        // 按类型统计
        $stats['errors_by_type'] = Cache::get('error_tracker:by_type', []);
        
        // 按严重程度统计
        $stats['errors_by_severity'] = Cache::get('error_tracker:by_severity', []);
        
        // 热门错误
        $stats['top_errors'] = Cache::get('error_tracker:top_errors', []);

        return $stats;
    }

    /**
     * 清理旧错误
     */
    public function cleanupOldErrors(int $daysToKeep = 30): int
    {
        $cutoffTime = time() - ($daysToKeep * 24 * 3600);
        $errorIds = Cache::get('error_tracker:error_list', []);
        $cleanedCount = 0;

        foreach ($errorIds as $index => $errorId) {
            $error = $this->getError($errorId);
            if ($error && $error['timestamp'] < $cutoffTime) {
                Cache::forget("error_tracker:error:{$errorId}");
                unset($errorIds[$index]);
                $cleanedCount++;
            }
        }

        // 更新错误列表
        Cache::put('error_tracker:error_list', array_values($errorIds), 86400 * $daysToKeep);

        return $cleanedCount;
    }

    /**
     * 判断是否应该捕获异常
     */
    private function shouldCapture(Throwable $exception): bool
    {
        // 检查采样率
        if (mt_rand() / mt_getrandmax() > $this->config['sample_rate']) {
            return false;
        }

        // 检查忽略列表
        $exceptionClass = get_class($exception);
        return !in_array($exceptionClass, $this->config['ignore_exceptions'], true);
    }

    /**
     * 生成错误ID
     */
    private function generateErrorId(): string
    {
        return uniqid('err_', true);
    }

    /**
     * 格式化堆栈跟踪
     */
    private function formatStackTrace(Throwable $exception): array
    {
        $trace = $exception->getTrace();
        $formattedTrace = [];

        foreach (array_slice($trace, 0, $this->config['max_trace_depth']) as $frame) {
            $formattedTrace[] = [
                'file' => $frame['file'] ?? '[internal]',
                'line' => $frame['line'] ?? 0,
                'function' => $frame['function'] ?? '[unknown]',
                'class' => $frame['class'] ?? null,
                'type' => $frame['type'] ?? null,
            ];
        }

        return $formattedTrace;
    }

    /**
     * 获取系统上下文
     */
    private function getSystemContext(): array
    {
        return [
            'php_version' => PHP_VERSION,
            'memory_usage' => memory_get_usage(true),
            'memory_peak' => memory_get_peak_usage(true),
            'request_uri' => $_SERVER['REQUEST_URI'] ?? null,
            'request_method' => $_SERVER['REQUEST_METHOD'] ?? null,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            'timestamp' => time(),
            'timezone' => date_default_timezone_get(),
        ];
    }

    /**
     * 确定错误严重程度
     */
    private function determineSeverity(Throwable $exception): string
    {
        $exceptionClass = get_class($exception);
        
        if (strpos($exceptionClass, 'Fatal') !== false) {
            return 'fatal';
        }
        
        if (strpos($exceptionClass, 'Error') !== false) {
            return 'error';
        }
        
        if (strpos($exceptionClass, 'Warning') !== false) {
            return 'warning';
        }
        
        return 'info';
    }

    /**
     * 存储错误数据
     */
    private function storeError(array $errorData): void
    {
        // 存储错误详情
        Cache::put(
            "error_tracker:error:{$errorData['id']}", 
            $errorData, 
            86400 * 30 // 30天
        );

        // 更新错误列表
        $errorIds = Cache::get('error_tracker:error_list', []);
        array_unshift($errorIds, $errorData['id']);
        
        // 只保留最近1000个错误ID
        if (count($errorIds) > 1000) {
            $errorIds = array_slice($errorIds, 0, 1000);
        }
        
        Cache::put('error_tracker:error_list', $errorIds, 86400 * 30);
    }

    /**
     * 存储消息数据
     */
    private function storeMessage(array $messageData): void
    {
        Cache::put(
            "error_tracker:message:{$messageData['id']}", 
            $messageData, 
            86400 * 7 // 7天
        );
    }

    /**
     * 更新错误统计
     */
    private function updateErrorStatistics(array $errorData): void
    {
        $hourKey = date('Y-m-d H:00:00');
        
        // 每小时统计
        Cache::increment("error_tracker:hourly:{$hourKey}", 1, 3600);
        
        // 按类型统计
        $typeStats = Cache::get('error_tracker:by_type', []);
        $exceptionType = $errorData['exception'];
        $typeStats[$exceptionType] = ($typeStats[$exceptionType] ?? 0) + 1;
        Cache::put('error_tracker:by_type', $typeStats, 86400);
        
        // 按严重程度统计
        $severityStats = Cache::get('error_tracker:by_severity', []);
        $severity = $errorData['severity'];
        $severityStats[$severity] = ($severityStats[$severity] ?? 0) + 1;
        Cache::put('error_tracker:by_severity', $severityStats, 86400);
        
        // 热门错误
        $topErrors = Cache::get('error_tracker:top_errors', []);
        $errorKey = md5($errorData['exception'] . $errorData['file'] . $errorData['line']);
        $topErrors[$errorKey] = [
            'exception' => $errorData['exception'],
            'file' => $errorData['file'],
            'line' => $errorData['line'],
            'count' => ($topErrors[$errorKey]['count'] ?? 0) + 1,
            'last_seen' => time(),
        ];
        
        // 按计数排序，只保留前20个
        uasort($topErrors, fn($a, $b) => $b['count'] <=> $a['count']);
        $topErrors = array_slice($topErrors, 0, 20, true);
        
        Cache::put('error_tracker:top_errors', $topErrors, 86400);
    }

    /**
     * 发送到外部服务
     */
    private function sendToExternalServices(array $errorData): void
    {
        // 发送到 Sentry
        if ($this->config['sentry_dsn']) {
            $this->sendToSentry($errorData);
        }
        
        // 发送到 Webhook
        if ($this->config['webhook_url']) {
            $this->sendToWebhook($errorData);
        }
    }

    /**
     * 发送到 Sentry
     */
    private function sendToSentry(array $errorData): void
    {
        try {
            // 这里可以集成 Sentry SDK
            // \Sentry\captureException($exception);
        } catch (Exception $e) {
            $this->logger->warning('Failed to send error to Sentry', [
                'error' => $e->getMessage(),
                'original_error_id' => $errorData['id'],
            ]);
        }
    }

    /**
     * 发送到 Webhook
     */
    private function sendToWebhook(array $errorData): void
    {
        try {
            Http::timeout(5)->post($this->config['webhook_url'], [
                'error_id' => $errorData['id'],
                'exception' => $errorData['exception'],
                'message' => $errorData['message'],
                'file' => $errorData['file'],
                'line' => $errorData['line'],
                'severity' => $errorData['severity'],
                'timestamp' => $errorData['timestamp'],
            ]);
        } catch (Exception $e) {
            $this->logger->warning('Failed to send error to webhook', [
                'error' => $e->getMessage(),
                'original_error_id' => $errorData['id'],
            ]);
        }
    }
}
