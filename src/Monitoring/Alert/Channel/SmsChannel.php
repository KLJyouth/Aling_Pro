<?php
namespace AlingAi\Monitoring\Alert\Channel;

use Psr\Log\LoggerInterface;

/**
 * 短信告警通道 - 通过短信发送告警
 */
class SmsChannel implements AlertChannelInterface
{
    /**
     * @var array 配置
     */
    private $config;
    
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * 构造函数
     */
    public function __construct(array $config, LoggerInterface $logger)
    {
        $this->config = $config;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function send(array $alert): bool
    {
        $recipients = $this->getRecipients($alert);
        
        if (empty($recipients)) {
            $this->logger->warning("没有配置短信告警接收者", [
                'alert_id' => $alert['id'],
            ]);
            
            return false;
        }
        
        $message = $this->formatMessage($alert);
        
        return $this->sendSms($recipients, $message, $alert);
    }

    /**
     * {@inheritdoc}
     */
    public function sendResolution(array $alert): bool
    {
        $recipients = $this->getRecipients($alert);
        
        if (empty($recipients)) {
            return false;
        }
        
        $message = $this->formatResolutionMessage($alert);
        
        return $this->sendSms($recipients, $message, $alert);
    }

    /**
     * 获取接收者列表
     */
    private function getRecipients(array $alert): array
    {
        $recipients = $this->config['recipients'] ?? [];
        
        // 根据告警严重级别获取特定接收者
        $severity = $alert['severity'];
        if (isset($this->config['severity_recipients'][$severity])) {
            $recipients = array_merge($recipients, $this->config['severity_recipients'][$severity]);
        }
        
        return array_unique($recipients);
    }

    /**
     * 格式化短信内容
     */
    private function formatMessage(array $alert): string
    {
        $prefix = $this->config['message_prefix'] ?? '[AlingAi监控]';
        $severityMap = [
            'low' => '低',
            'medium' => '中',
            'high' => '高',
            'critical' => '严重',
        ];
        
        $severityText = $severityMap[$alert['severity']] ?? $alert['severity'];
        $time = date('m-d H:i', $alert['timestamp']);
        
        return "{$prefix}[{$severityText}] {$alert['title']} - {$alert['message']} ({$time})";
    }

    /**
     * 格式化告警解决短信内容
     */
    private function formatResolutionMessage(array $alert): string
    {
        $prefix = $this->config['message_prefix'] ?? '[AlingAi监控]';
        $time = date('m-d H:i', $alert['resolved_at'] ?? time());
        
        return "{$prefix}[已解决] {$alert['title']} ({$time})";
    }

    /**
     * 发送短信
     */
    private function sendSms(array $recipients, string $message, array $alert): bool
    {
        $smsProvider = $this->config['provider'] ?? 'default';
        $apiKey = $this->config['api_key'] ?? '';
        $apiSecret = $this->config['api_secret'] ?? '';
        
        if (empty($apiKey) || empty($apiSecret)) {
            $this->logger->error("短信服务缺少API凭证", [
                'alert_id' => $alert['id'],
            ]);
            
            return false;
        }
        
        $success = true;
        
        foreach ($recipients as $phoneNumber) {
            try {
                // 实际发送短信的代码，根据不同提供商实现
                // 这里仅作日志记录示例
                $this->logger->info("发送短信告警", [
                    'alert_id' => $alert['id'],
                    'recipient' => $phoneNumber,
                    'provider' => $smsProvider,
                ]);
                
                // 实际发送逻辑根据短信服务商API进行实现
                // 示例: $result = $this->sendViaSmsProvider($phoneNumber, $message, $smsProvider, $apiKey, $apiSecret);
                $result = true; // 假设发送成功
                
                if (!$result) {
                    $this->logger->error("短信发送失败", [
                        'alert_id' => $alert['id'],
                        'recipient' => $phoneNumber,
                    ]);
                    
                    $success = false;
                }
            } catch (\Exception $e) {
                $this->logger->error("短信发送异常", [
                    'alert_id' => $alert['id'],
                    'recipient' => $phoneNumber,
                    'error' => $e->getMessage(),
                ]);
                
                $success = false;
            }
        }
        
        return $success;
    }
} 