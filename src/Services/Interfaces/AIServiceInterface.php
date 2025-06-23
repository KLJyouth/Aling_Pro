<?php

declare(strict_types=1);

namespace AlingAi\Services\Interfaces;

/**
 * AI服务接口
 * 定义AI服务的标准接口，用于解耦AI调用逻辑
 */
interface AIServiceInterface
{
    /**
     * 获取AI完成响应
     * 
     * @param array $messages 消息数组，格式：[['role' => 'user', 'content' => '消息内容']]
     * @param array $options 可选参数
     * @return array 返回格式：['success' => true, 'data' => ['content' => 'AI响应'], 'usage' => [...]]
     * @throws \Exception 当AI调用失败时抛出异常
     */
    public function getCompletion(array $messages, array $options = []): array;

    /**
     * 健康检查
     * 
     * @return array 返回格式：['success' => true, 'status' => 'healthy', 'details' => [...]]
     */
    public function healthCheck(): array;

    /**
     * 获取服务配置信息
     * 
     * @return array 服务配置信息
     */
    public function getConfig(): array;

    /**
     * 设置服务配置
     * 
     * @param array $config 配置数组
     * @return void
     */
    public function setConfig(array $config): void;

    /**
     * 获取服务名称
     * 
     * @return string 服务名称
     */
    public function getServiceName(): string;

    /**
     * 检查服务是否可用
     * 
     * @return bool 服务是否可用
     */
    public function isAvailable(): bool;
} 