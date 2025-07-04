<?php

namespace App\Exceptions;

use Exception;

class MCPException extends Exception
{
    /**
     * 构造函数
     *
     * @param string $message 错误消息
     * @param int $code 错误代码
     * @param \Throwable|null $previous 上一个异常
     */
    public function __construct($message = "MCP服务错误", $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    /**
     * 将异常转换为字符串
     *
     * @return string
     */
    public function __toString()
    {
        return __CLASS__ . ": [{$this->code}]: {$this->message}";
    }
}
