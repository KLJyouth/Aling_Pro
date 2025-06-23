<?php
/**
 * 告警处理脚本
 */
class AlertManager
{
    private $config;
    
    public function __construct()
    {
        $this->config = [
            'email_smtp' => 'smtp.gmail.com',
            'email_port' => 587,
            'email_user' => 'aoteman2024@gmail.com',
            'email_pass' => 'your_app_password',
            'recipients' => ['admin@alingai.com']
        ];
    }
    
    public function processAlerts()
    {
        $alertsFile = './logs/alerts.log';
        if (!file_exists($alertsFile)) return;
        
        $alerts = file($alertsFile, FILE_IGNORE_NEW_LINES);
        $unprocessedAlerts = [];
        
        foreach ($alerts as $alertLine) {
            $alert = json_decode($alertLine, true);
            if ($alert && !isset($alert['processed'])) {
                $this->sendAlert($alert);
                $alert['processed'] = true;
                $unprocessedAlerts[] = json_encode($alert);
            }
        }
        
        // 更新已处理的告警
        if (!empty($unprocessedAlerts)) {
            file_put_contents($alertsFile, implode("\n", $unprocessedAlerts) . "\n");
        }
    }
    
    private function sendAlert($alert)
    {
        $subject = "AlingAi Pro 系统告警 - " . $alert['type'];
        $message = "告警时间: " . date('Y-m-d H:i:s', $alert['timestamp']) . "\n";
        $message .= "告警级别: " . $alert['type'] . "\n";
        $message .= "告警信息: " . $alert['message'] . "\n";
        
        // 发送邮件告警
        $this->sendEmailAlert($subject, $message);
        
        // 记录告警发送日志
        error_log("Alert sent: " . json_encode($alert));
    }
    
    private function sendEmailAlert($subject, $message)
    {
        // 简单的邮件发送实现
        $headers = "From: {$this->config['email_user']}\r\n";
        $headers .= "Reply-To: {$this->config['email_user']}\r\n";
        
        foreach ($this->config['recipients'] as $recipient) {
            mail($recipient, $subject, $message, $headers);
        }
    }
}

// 执行告警处理
$alertManager = new AlertManager();
$alertManager->processAlerts();
?>