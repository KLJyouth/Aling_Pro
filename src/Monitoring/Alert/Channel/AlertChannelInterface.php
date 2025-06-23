<?php
namespace AlingAi\Monitoring\Alert\Channel;

/**
 * 告警通道接口 - 所有告警通道必须实现此接口
 */
interface AlertChannelInterface
{
    /**
     * 发送告警
     *
     * @param array $alert 告警数据
     * @return bool 是否发送成功
     */
    public function send(array $alert): bool;
    
    /**
     * 发送告警解决通知
     *
     * @param array $alert 告警数据
     * @return bool 是否发送成功
     */
    public function sendResolution(array $alert): bool;
} 