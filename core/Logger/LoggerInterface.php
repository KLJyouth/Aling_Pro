<?php
declare(strict_types=1);

/**
 * 文件名：LoggerInterface.php
 * 功能描述：日志记录接口 - 定义日志记录的基本功能
 * 创建时间：2025-01-XX
 * 最后修改：2025-01-XX
 * 版本：1.0.0
 *
 * @package AlingAi\Core\Logger
 * @author AlingAi Team
 * @license MIT
 */

namespace AlingAi\Core\Logger;

/**
 * 日志记录接口
 *
 * 定义日志记录的基本功能，遵循PSR-3规范
 */
interface LoggerInterface
{

    /**
     * 记录系统不可用的情形
     *
     * @param string $message 日志消息
     * @param array $context 上下文数据
     * @return void
     */
    public function emergency(string $message, array $context = []): void;

    /**
     * 必须立即采取行动的情形
     *
     * 例如：整个网站宕机，数据库不可用等
     *
     * @param string $message 日志消息
     * @param array $context 上下文数据
     * @return void
     */
    public function alert(string $message, array $context = []): void;


    /**
     * 紧急情况
     *
     * 例如：应用组件不可用，意外异常等
     *
     * @param string $message 日志消息
     * @param array $context 上下文数据
     * @return void
     */
    public function critical(string $message, array $context = []): void;

    /**
     * 运行时错误，不需要立即处理
     *
     * @param string $message 日志消息
     * @param array $context 上下文数据
     * @return void
     */
    public function error(string $message, array $context = []): void;


    /**
     * 出现但不是错误的情况
     *
     * 例如：使用了已废弃的API，使用了不推荐的方法等
     *
     * @param string $message 日志消息
     * @param array $context 上下文数据
     * @return void
     */
    public function warning(string $message, array $context = []): void;

    /**
     * 普通但值得注意的事件
     *
     * @param string $message 日志消息
     * @param array $context 上下文数据
     * @return void
     */
    public function notice(string $message, array $context = []): void;

    /**
     * 感兴趣的事件
     *
     * 例如：用户登录，SQL日志等
     *
     * @param string $message 日志消息
     * @param array $context 上下文数据
     * @return void
     */
    public function info(string $message, array $context = []): void;


    /**
     * 详细的调试信息
     *
     * @param string $message 日志消息
     * @param array $context 上下文数据
     * @return void
     */
    public function debug(string $message, array $context = []): void;

    /**
     * 记录特定级别的日志
     *
     * @param mixed $level 日志级别
     * @param string $message 日志消息
     * @param array $context 上下文数据
     * @return void
     */
    public function log($level, string $message, array $context = []): void;
}
