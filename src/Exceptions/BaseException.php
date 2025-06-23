<?php

declare(strict_types=1);

namespace AlingAi\Pro\Exceptions;

use Exception;
use Throwable;

/**
 * 基础异常类
 * 
 * 所有自定义异常的基类
 */
abstract class BaseException extends Exception
{
    protected int $statusCode = 500;
    protected string $errorCode = 'UNKNOWN_ERROR';
    protected array $details = [];

    public function __construct(
        string $message = '',
        int $code = 0,
        ?Throwable $previous = null,
        array $details = []
    ) {
        parent::__construct($message, $code, $previous);
        $this->details = $details;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getErrorCode(): string
    {
        return $this->errorCode;
    }

    public function getDetails(): array
    {
        return $this->details;
    }

    public function hasDetails(): bool
    {
        return !empty($this->details);
    }
}

/**
 * 验证异常
 */
class ValidationException extends BaseException
{
    protected int $statusCode = 422;
    protected string $errorCode = 'VALIDATION_ERROR';

    public function __construct(array $errors, string $message = '数据验证失败')
    {
        parent::__construct($message, 0, null, ['errors' => $errors]);
    }

    public function getErrors(): array
    {
        return $this->details['errors'] ?? [];
    }
}

/**
 * 认证异常
 */
class AuthenticationException extends BaseException
{
    protected int $statusCode = 401;
    protected string $errorCode = 'AUTHENTICATION_ERROR';

    public function __construct(string $message = '认证失败')
    {
        parent::__construct($message);
    }
}

/**
 * 授权异常
 */
class AuthorizationException extends BaseException
{
    protected int $statusCode = 403;
    protected string $errorCode = 'AUTHORIZATION_ERROR';

    public function __construct(string $message = '权限不足')
    {
        parent::__construct($message);
    }
}

/**
 * 资源不存在异常
 */
class NotFoundException extends BaseException
{
    protected int $statusCode = 404;
    protected string $errorCode = 'NOT_FOUND';

    public function __construct(string $resource = '资源', int $id = null)
    {
        $message = $id ? "{$resource}不存在 (ID: {$id})" : "{$resource}不存在";
        parent::__construct($message);
    }
}

/**
 * 业务逻辑异常
 */
class BusinessException extends BaseException
{
    protected int $statusCode = 400;
    protected string $errorCode = 'BUSINESS_ERROR';

    public function __construct(string $message, string $errorCode = null)
    {
        parent::__construct($message);
        if ($errorCode) {
            $this->errorCode = $errorCode;
        }
    }
}

/**
 * 限流异常
 */
class RateLimitException extends BaseException
{
    protected int $statusCode = 429;
    protected string $errorCode = 'RATE_LIMIT_EXCEEDED';

    public function __construct(int $retryAfter = 60)
    {
        parent::__construct('请求过于频繁，请稍后再试', 0, null, [
            'retry_after' => $retryAfter
        ]);
    }

    public function getRetryAfter(): int
    {
        return $this->details['retry_after'] ?? 60;
    }
}

/**
 * 服务不可用异常
 */
class ServiceUnavailableException extends BaseException
{
    protected int $statusCode = 503;
    protected string $errorCode = 'SERVICE_UNAVAILABLE';

    public function __construct(string $service = '服务')
    {
        parent::__construct("{$service}暂时不可用");
    }
}

/**
 * 数据库异常
 */
class DatabaseException extends BaseException
{
    protected int $statusCode = 500;
    protected string $errorCode = 'DATABASE_ERROR';

    public function __construct(string $message = '数据库操作失败', ?Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}

/**
 * 外部API异常
 */
class ExternalApiException extends BaseException
{
    protected int $statusCode = 502;
    protected string $errorCode = 'EXTERNAL_API_ERROR';

    public function __construct(string $api, int $statusCode, string $message = '')
    {
        $errorMessage = $message ?: "外部API调用失败: {$api}";
        parent::__construct($errorMessage, 0, null, [
            'api' => $api,
            'api_status_code' => $statusCode
        ]);
    }
}

/**
 * 配置错误异常
 */
class ConfigurationException extends BaseException
{
    protected int $statusCode = 500;
    protected string $errorCode = 'CONFIGURATION_ERROR';

    public function __construct(string $config, string $message = '')
    {
        $errorMessage = $message ?: "配置错误: {$config}";
        parent::__construct($errorMessage);
    }
}
