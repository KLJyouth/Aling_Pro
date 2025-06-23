<?php
namespace AlingAi\Security\Exceptions;

/**
 * 加密异常类
 * 
 * 处理加密过程中的异常情况
 * 
 * @package AlingAi\Security\Exceptions
 * @version 6.0.0
 */
class EncryptionException extends \Exception
{
    /**
     * 构造函数
     * 
     * @param string $message 错误消息
     * @param int $code 错误代码
     * @param \Throwable $previous 前一个异常
     */
    public function __construct(string $message = "", int $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
