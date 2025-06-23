<?php
/**
 * AlingAi Pro - Illuminate 兼容异常类
 * 
 * @package AlingAi\Pro\Exceptions
 * @version 2.0.0
 * @author AlingAi Team
 * @created 2024-12-19
 */

declare(strict_types=1);

namespace Illuminate\Database\Eloquent;

class ModelNotFoundException extends \Exception
{
    public function __construct(string $message = "模型未找到", int $code = 404, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
