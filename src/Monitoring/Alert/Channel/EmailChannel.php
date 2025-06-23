<?php
namespace AlingAi\Monitoring\Alert\Channel;

use Psr\Log\LoggerInterface;

/**
 * 邮件告警通道 - 通过邮件发送告警
 */
class EmailChannel implements AlertChannelInterface
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
            $this->logger->warning("没有配置邮件告警接收者", [
                'alert_id' => $alert['id'],
            ]);
            
            return false;
        }
        
        $subject = $this->formatSubject($alert);
        $body = $this->formatBody($alert);
        
        return $this->sendEmail($recipients, $subject, $body, $alert);
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
        
        $subject = "[已解决] " . $this->formatSubject($alert);
        $body = $this->formatResolutionBody($alert);
        
        return $this->sendEmail($recipients, $subject, $body, $alert);
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
        
        // 根据告警标签获取特定接收者
        if (isset($alert['metadata']['tags'])) {
            foreach ($alert['metadata']['tags'] as $tag) {
                if (isset($this->config['tag_recipients'][$tag])) {
                    $recipients = array_merge($recipients, $this->config['tag_recipients'][$tag]);
                }
            }
        }
        
        return array_unique($recipients);
    }

    /**
     * 格式化邮件主题
     */
    private function formatSubject(array $alert): string
    {
        $prefix = $this->config['subject_prefix'] ?? '[AlingAi监控告警]';
        
        // 添加严重级别标识
        $severityMap = [
            'low' => '低',
            'medium' => '中',
            'high' => '高',
            'critical' => '严重',
        ];
        
        $severityText = $severityMap[$alert['severity']] ?? $alert['severity'];
        
        return "{$prefix} [{$severityText}] {$alert['title']}";
    }

    /**
     * 格式化邮件正文
     */
    private function formatBody(array $alert): string
    {
        $time = date('Y-m-d H:i:s', $alert['timestamp']);
        
        $body = <<<EOT
<html>
<head>
  <style>
    body { font-family: Arial, sans-serif; margin: 0; padding: 20px; color: #333; }
    .alert-box { border: 1px solid #ddd; border-radius: 5px; padding: 15px; margin-bottom: 20px; }
    .alert-critical { border-left: 5px solid #d9534f; }
    .alert-high { border-left: 5px solid #f0ad4e; }
    .alert-medium { border-left: 5px solid #5bc0de; }
    .alert-low { border-left: 5px solid #5cb85c; }
    .alert-header { margin-bottom: 15px; padding-bottom: 10px; border-bottom: 1px solid #eee; }
    .alert-title { margin: 0; font-size: 18px; font-weight: bold; }
    .alert-time { color: #777; font-size: 13px; margin-top: 5px; }
    .alert-body { line-height: 1.5; }
    .alert-message { margin-bottom: 15px; }
    .alert-metadata { background: #f8f8f8; padding: 10px; border-radius: 4px; font-family: monospace; white-space: pre-wrap; }
  </style>
</head>
<body>
  <div class="alert-box alert-{$alert['severity']}">
    <div class="alert-header">
      <h2 class="alert-title">{$alert['title']}</h2>
      <div class="alert-time">告警时间: {$time}</div>
    </div>
    <div class="alert-body">
      <div class="alert-message">{$alert['message']}</div>
      <div class="alert-metadata">{$this->formatMetadata($alert['metadata'])}</div>
    </div>
  </div>
  <div>
    <p>此邮件由AlingAi监控系统自动发送，请勿直接回复。</p>
  </div>
</body>
</html>
EOT;
        
        return $body;
    }

    /**
     * 格式化告警解决邮件正文
     */
    private function formatResolutionBody(array $alert): string
    {
        $time = date('Y-m-d H:i:s', $alert['timestamp']);
        $resolvedTime = date('Y-m-d H:i:s', $alert['resolved_at'] ?? time());
        
        $body = <<<EOT
<html>
<head>
  <style>
    body { font-family: Arial, sans-serif; margin: 0; padding: 20px; color: #333; }
    .alert-box { border: 1px solid #ddd; border-radius: 5px; padding: 15px; margin-bottom: 20px; }
    .alert-resolved { border-left: 5px solid #5cb85c; }
    .alert-header { margin-bottom: 15px; padding-bottom: 10px; border-bottom: 1px solid #eee; }
    .alert-title { margin: 0; font-size: 18px; font-weight: bold; }
    .alert-time { color: #777; font-size: 13px; margin-top: 5px; }
    .alert-body { line-height: 1.5; }
    .alert-message { margin-bottom: 15px; }
    .alert-metadata { background: #f8f8f8; padding: 10px; border-radius: 4px; font-family: monospace; white-space: pre-wrap; }
  </style>
</head>
<body>
  <div class="alert-box alert-resolved">
    <div class="alert-header">
      <h2 class="alert-title">[已解决] {$alert['title']}</h2>
      <div class="alert-time">告警时间: {$time}</div>
      <div class="alert-time">解决时间: {$resolvedTime}</div>
    </div>
    <div class="alert-body">
      <div class="alert-message">{$alert['message']}</div>
      <div class="alert-metadata">{$this->formatMetadata($alert['metadata'])}</div>
    </div>
  </div>
  <div>
    <p>此邮件由AlingAi监控系统自动发送，请勿直接回复。</p>
  </div>
</body>
</html>
EOT;
        
        return $body;
    }

    /**
     * 格式化元数据
     */
    private function formatMetadata(array $metadata): string
    {
        return json_encode($metadata, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }

    /**
     * 发送邮件
     */
    private function sendEmail(array $recipients, string $subject, string $body, array $alert): bool
    {
        try {
            // 检查是否配置了SMTP服务器
            if (!isset($this->config['smtp_host']) || !isset($this->config['smtp_port'])) {
                $this->logger->error("邮件通道未配置SMTP服务器信息");
                return false;
            }
            
            $headers = [
                'MIME-Version: 1.0',
                'Content-type: text/html; charset=utf-8',
                'From: ' . ($this->config['from_email'] ?? 'alingai-monitor@example.com'),
            ];
            
            // 这里使用PHP的mail()函数发送邮件
            // 在实际环境中，应该使用更可靠的邮件库，如PHPMailer或Symfony Mailer
            foreach ($recipients as $recipient) {
                mail($recipient, $subject, $body, implode("\r\n", $headers));
            }
            
            $this->logger->info("邮件告警已发送", [
                'alert_id' => $alert['id'],
                'recipients' => count($recipients),
            ]);
            
            return true;
        } catch (\Exception $e) {
            $this->logger->error("发送邮件告警失败", [
                'alert_id' => $alert['id'],
                'error' => $e->getMessage(),
            ]);
            
            return false;
        }
    }
} 