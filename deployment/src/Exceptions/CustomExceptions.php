<?php
/**
 * AlingAi Pro - 自定义异常类
 * 
 * @package AlingAi\Pro\Exceptions
 * @version 2.0.0
 * @author AlingAi Team
 * @created 2024-12-19
 */

declare(strict_types=1);

namespace AlingAi\Exceptions;

class UnauthorizedAccessException extends \Exception
{
    public function __construct(string $message = "未授权访问", int $code = 401, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}

class ForbiddenException extends \Exception
{
    public function __construct(string $message = "禁止访问", int $code = 403, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}

class NotFoundException extends \Exception
{
    public function __construct(string $message = "资源不存在", int $code = 404, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}

class ValidationException extends \Exception
{
    protected array $errors;
    
    public function __construct(array $errors = [], string $message = "验证失败", int $code = 422, \Throwable $previous = null)
    {
        $this->errors = $errors;
        parent::__construct($message, $code, $previous);
    }
    
    public function getErrors(): array
    {
        return $this->errors;
    }
}

class RateLimitException extends \Exception
{
    public function __construct(string $message = "请求过于频繁", int $code = 429, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}

class RateLimitExceededException extends \Exception
{
    protected array $limitInfo;
    
    public function __construct(string $message = "速率限制超出", int $code = 429, array $limitInfo = [], \Throwable $previous = null)
    {
        $this->limitInfo = $limitInfo;
        parent::__construct($message, $code, $previous);
    }
    
    public function getLimitInfo(): array
    {
        return $this->limitInfo;
    }
}

class DatabaseException extends \Exception
{
    public function __construct(string $message = "数据库错误", int $code = 500, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}

class ConfigurationException extends \Exception
{
    public function __construct(string $message = "配置错误", int $code = 500, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}

class SecurityException extends \Exception
{
    public function __construct(string $message = "安全违规", int $code = 403, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}

class FileException extends \Exception
{
    public function __construct(string $message = "文件操作失败", int $code = 500, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}

class EmailException extends \Exception
{
    public function __construct(string $message = "邮件服务异常", int $code = 500, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}

class ModelNotFoundException extends \Exception
{
    public function __construct(string $message = "模型未找到", int $code = 404, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
