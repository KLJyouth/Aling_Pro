<?php

declare(strict_types=1);

namespace AlingAi\Pro\Core;

use Psr\Log\LoggerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use AlingAi\Pro\Exceptions\BaseException;
use Throwable;

/**
 * 全局错误处理器
 * 
 * 统一处理系统中的所有异常和错误
 * 
 * @package AlingAi\Pro\Core
 */
/**
 * ErrorHandler 类
 *
 * @package AlingAi\Pro\Core
 */
class ErrorHandler
{
    private LoggerInterface $logger;
    private bool $debug;
    private array $config;

    /**


     * __construct 方法


     *


     * @param LoggerInterface $logger


     * @param array $config


     * @return void


     */


    public function __construct(LoggerInterface $logger, array $config = [])
    {
        $this->logger = $logger;
        $this->debug = $config['debug'] ?? false;
        $this->config = $config;
    }

    /**
     * 处理异常
     */
    /**

     * handleException 方法

     *

     * @param Throwable $exception

     * @param ServerRequestInterface $request

     * @return void

     */

    public function handleException(
        Throwable $exception, 
        ServerRequestInterface $request
    ): ResponseInterface {
        // 记录错误日志
        $this->logException($exception, $request);

        // 根据异常类型返回适当的响应
        if ($exception instanceof BaseException) {
            return $this->handleCustomException($exception, $request);
        }

        return $this->handleGenericException($exception, $request);
    }

    /**
     * 处理自定义异常
     */
    /**

     * handleCustomException 方法

     *

     * @param BaseException $exception

     * @param ServerRequestInterface $request

     * @return void

     */

    private function handleCustomException(
        BaseException $exception, 
        ServerRequestInterface $request
    ): ResponseInterface {
        $statusCode = $exception->getStatusCode();
        $response = [
            'success' => false,
            'code' => $statusCode,
            'message' => $exception->getMessage(),
            'error_code' => $exception->getErrorCode(),
            'meta' => $this->getErrorMeta($request)
        ];

        // 添加详细错误信息（如果有）
        if ($exception->hasDetails()) {
            $response['details'] = $exception->getDetails();
        }

        // 调试模式下添加更多信息
        if ($this->debug) {
            $response['debug'] = [
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString()
            ];
        }

        return $this->createJsonResponse($response, $statusCode);
    }

    /**
     * 处理通用异常
     */
    /**

     * handleGenericException 方法

     *

     * @param Throwable $exception

     * @param ServerRequestInterface $request

     * @return void

     */

    private function handleGenericException(
        Throwable $exception, 
        ServerRequestInterface $request
    ): ResponseInterface {
        $statusCode = $this->getStatusCodeFromException($exception);
        $message = $this->debug ? $exception->getMessage() : '服务器内部错误';

        $response = [
            'success' => false,
            'code' => $statusCode,
            'message' => $message,
            'meta' => $this->getErrorMeta($request)
        ];

        if ($this->debug) {
            $response['debug'] = [
                'exception' => get_class($exception),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString()
            ];
        }

        return $this->createJsonResponse($response, $statusCode);
    }

    /**
     * 记录异常日志
     */
    /**

     * logException 方法

     *

     * @param Throwable $exception

     * @param ServerRequestInterface $request

     * @return void

     */

    private function logException(Throwable $exception, ServerRequestInterface $request): void
    {
        $context = [
            'exception' => get_class($exception),
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'request' => [
                'method' => $request->getMethod(),
                'uri' => (string) $request->getUri(),
                'headers' => $this->sanitizeHeaders($request->getHeaders()),
                'user_agent' => $request->getHeaderLine('User-Agent'),
                'ip' => $this->getClientIp($request)
            ]
        ];

        // 根据异常类型选择不同的日志级别
        if ($exception instanceof BaseException) {
            $level = $this->getLogLevelFromException($exception);
        } else {
            $level = 'error';
        }

        $this->logger->log($level, $exception->getMessage(), $context);
    }

    /**
     * 从异常类型推断状态码
     */
    /**

     * getStatusCodeFromException 方法

     *

     * @param Throwable $exception

     * @return void

     */

    private function getStatusCodeFromException(Throwable $exception): int
    {
        return match (get_class($exception)) {
            'InvalidArgumentException' => 400,
            'UnauthorizedException' => 401,
            'ForbiddenException' => 403,
            'NotFoundException' => 404,
            'MethodNotAllowedException' => 405,
            'ConflictException' => 409,
            'ValidationException' => 422,
            'TooManyRequestsException' => 429,
            default => 500
        };
    }

    /**
     * 从自定义异常获取日志级别
     */
    /**

     * getLogLevelFromException 方法

     *

     * @param BaseException $exception

     * @return void

     */

    private function getLogLevelFromException(BaseException $exception): string
    {
        return match ($exception->getStatusCode()) {
            400, 422 => 'warning',  // 客户端错误
            401, 403 => 'notice',   // 认证/授权错误
            404 => 'info',          // 资源不存在
            429 => 'warning',       // 限流
            default => 'error'      // 服务器错误
        };
    }

    /**
     * 获取客户端IP
     */
    /**

     * getClientIp 方法

     *

     * @param ServerRequestInterface $request

     * @return void

     */

    private function getClientIp(ServerRequestInterface $request): string
    {
        $headers = [
            'HTTP_CF_CONNECTING_IP',     // Cloudflare
            'HTTP_X_FORWARDED_FOR',      // 代理
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_CLIENT_IP',
            'REMOTE_ADDR'
        ];

        foreach ($headers as $header) {
            $value = $request->getServerParams()[$header] ?? null;
            if (!empty($value) && $value !== 'unknown') {
                return explode(',', $value)[0];
            }
        }

        return 'unknown';
    }

    /**
     * 清理敏感的请求头
     */
    /**

     * sanitizeHeaders 方法

     *

     * @param array $headers

     * @return void

     */

    private function sanitizeHeaders(array $headers): array
    {
        $sensitiveHeaders = [
            'authorization',
            'cookie',
            'x-api-key',
            'x-auth-token'
        ];

        foreach ($sensitiveHeaders as $header) {
            if (isset($headers[$header])) {
                $headers[$header] = ['[REDACTED]'];
            }
        }

        return $headers;
    }

    /**
     * 获取错误响应的元数据
     */
    /**

     * getErrorMeta 方法

     *

     * @param ServerRequestInterface $request

     * @return void

     */

    private function getErrorMeta(ServerRequestInterface $request): array
    {
        return [
            'timestamp' => (new \DateTime())->format('c'),
            'request_id' => $this->generateRequestId($request),
            'path' => $request->getUri()->getPath(),
            'method' => $request->getMethod()
        ];
    }

    /**
     * 生成请求ID
     */
    /**

     * generateRequestId 方法

     *

     * @param ServerRequestInterface $request

     * @return void

     */

    private function generateRequestId(ServerRequestInterface $request): string
    {
        return $request->getHeaderLine('X-Request-ID') 
            ?: 'req_' . uniqid() . '_' . time();
    }

    /**
     * 创建JSON响应
     */
    /**

     * createJsonResponse 方法

     *

     * @param array $data

     * @param int $statusCode

     * @return void

     */

    private function createJsonResponse(array $data, int $statusCode): ResponseInterface
    {
        $response = new \Slim\Psr7\Response();
        $response->getBody()->write(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($statusCode);
    }

    /**
     * 处理PHP错误
     */
    /**

     * handleError 方法

     *

     * @param int $level

     * @param string $message

     * @param string $file

     * @param int $line

     * @return void

     */

    public function handleError(
        int $level,
        string $message,
        string $file = '',
        int $line = 0
    ): bool {
        if (!(error_reporting() & $level)) {
            return false;
        }

        $errorTypes = [
            E_ERROR => 'Error',
            E_WARNING => 'Warning',
            E_PARSE => 'Parse Error',
            E_NOTICE => 'Notice',
            E_CORE_ERROR => 'Core Error',
            E_CORE_WARNING => 'Core Warning',
            E_COMPILE_ERROR => 'Compile Error',
            E_COMPILE_WARNING => 'Compile Warning',
            E_USER_ERROR => 'User Error',
            E_USER_WARNING => 'User Warning',
            E_USER_NOTICE => 'User Notice',
            E_STRICT => 'Strict Standards',
            E_RECOVERABLE_ERROR => 'Recoverable Error',
            E_DEPRECATED => 'Deprecated',
            E_USER_DEPRECATED => 'User Deprecated'
        ];

        $errorType = $errorTypes[$level] ?? 'Unknown Error';

        $this->logger->error("PHP {$errorType}: {$message}", [
            'level' => $level,
            'file' => $file,
            'line' => $line,
            'error_type' => $errorType
        ]);

        return true;
    }

    /**
     * 处理致命错误
     */
    /**

     * handleFatalError 方法

     *

     * @return void

     */

    public function handleFatalError(): void
    {
        $error = error_get_last();
        if ($error && ($error['type'] & (E_ERROR | E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR))) {
            $this->logger->critical('PHP Fatal Error', [
                'message' => $error['message'],
                'file' => $error['file'],
                'line' => $error['line'],
                'type' => $error['type']
            ]);
        }
    }
}
