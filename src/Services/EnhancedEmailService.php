<?php

namespace AlingAi\Services;

use AlingAi\Config\EnhancedConfig;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

/**
 * 增强邮件服务
 * 支持SMTP发送、告警邮件、模板邮件等
 */
class EnhancedEmailService
{
    private static $instance = null;
    private $config;
    private $mailer;
    private $dbService;

    private function __construct()
    {
        $this->config = EnhancedConfig::getInstance();
        $this->dbService = EnhancedDatabaseService::getInstance();
        $this->initializeMailer();
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * 初始化邮件发送器
     */
    private function initializeMailer(): void
    {
        $this->mailer = new PHPMailer(true);

        try {
            $mailConfig = $this->config->get('mail');

            // 服务器设置
            $this->mailer->isSMTP();
            $this->mailer->Host = $mailConfig['smtp']['host'];
            $this->mailer->SMTPAuth = true;
            $this->mailer->Username = $mailConfig['smtp']['user'];
            $this->mailer->Password = $mailConfig['smtp']['password'];
            $this->mailer->Port = $mailConfig['smtp']['port'];

            // 安全设置
            if ($mailConfig['smtp']['secure'] === 'SSL') {
                $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            } else {
                $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            }

            // 字符集设置
            $this->mailer->CharSet = 'UTF-8';
            $this->mailer->Encoding = 'base64';

            // 发件人设置
            $this->mailer->setFrom($mailConfig['from']['address'], $mailConfig['from']['name']);

            // 调试模式（开发环境）
            if ($this->config->isDevelopment()) {
                $this->mailer->SMTPDebug = SMTP::DEBUG_SERVER;
            }

        } catch (Exception $e) {
            throw new \Exception('邮件服务初始化失败: ' . $e->getMessage());
        }
    }

    /**
     * 发送邮件（主要接口）
     */
    public function sendEmail(string $to, string $subject, string $body, array $options = []): bool
    {
        try {
            // 重置收件人
            $this->mailer->clearAddresses();
            $this->mailer->clearAttachments();
            $this->mailer->clearReplyTos();

            // 设置收件人
            $this->mailer->addAddress($to);

            // 设置抄送
            if (!empty($options['cc'])) {
                foreach ((array)$options['cc'] as $cc) {
                    $this->mailer->addCC($cc);
                }
            }

            // 设置密送
            if (!empty($options['bcc'])) {
                foreach ((array)$options['bcc'] as $bcc) {
                    $this->mailer->addBCC($bcc);
                }
            }

            // 设置回复地址
            if (!empty($options['reply_to'])) {
                $this->mailer->addReplyTo($options['reply_to']);
            }

            // 设置邮件内容
            $this->mailer->isHTML(true);
            $this->mailer->Subject = $subject;
            $this->mailer->Body = $this->wrapEmailTemplate($subject, $body);

            // 设置纯文本版本
            $this->mailer->AltBody = strip_tags($body);

            // 添加附件
            if (!empty($options['attachments'])) {
                foreach ($options['attachments'] as $attachment) {
                    if (is_array($attachment)) {
                        $this->mailer->addAttachment($attachment['path'], $attachment['name'] ?? '');
                    } else {
                        $this->mailer->addAttachment($attachment);
                    }
                }
            }

            // 发送邮件
            $result = $this->mailer->send();

            // 记录发送日志
            $this->logEmailSent($to, $subject, $result);

            return $result;

        } catch (Exception $e) {
            $this->logEmailError('邮件发送失败', $e->getMessage(), [
                'to' => $to,
                'subject' => $subject
            ]);
            return false;
        }
    }

    /**
     * 发送联系表单邮件
     */
    public function sendContactForm(array $formData): bool
    {
        $subject = '新的联系表单消息 - ' . ($formData['subject'] ?? '无主题');
        
        $body = $this->renderContactTemplate($formData);
        
        $adminEmail = $this->config->get('mail.alert_email');
        
        $result = $this->sendEmail($adminEmail, $subject, $body);

        // 发送确认邮件给用户
        if ($result && !empty($formData['email'])) {
            $this->sendContactConfirmation($formData);
        }

        return $result;
    }

    /**
     * 发送联系确认邮件
     */
    private function sendContactConfirmation(array $formData): bool
    {
        $subject = '感谢您的联系 - AlingAi Pro';
        
        $body = $this->renderContactConfirmationTemplate($formData);
        
        return $this->sendEmail($formData['email'], $subject, $body);
    }

    /**
     * 发送告警邮件
     */
    public function sendAlert(string $subject, string $message): bool
    {
        $alertEmail = $this->config->get('mail.alert_email');
        
        $body = $this->renderAlertTemplate($subject, $message);
        
        return $this->sendEmail($alertEmail, '[告警] ' . $subject, $body, [
            'priority' => 'high'
        ]);
    }

    /**
     * 发送系统通知邮件
     */
    public function sendSystemNotification(string $to, string $subject, string $message, array $data = []): bool
    {
        $body = $this->renderNotificationTemplate($subject, $message, $data);
        
        return $this->sendEmail($to, $subject, $body);
    }

    /**
     * 批量发送邮件
     */
    public function sendBulkEmails(array $recipients, string $subject, string $body): array
    {
        $results = [];
        
        foreach ($recipients as $recipient) {
            $email = is_array($recipient) ? $recipient['email'] : $recipient;
            $name = is_array($recipient) ? ($recipient['name'] ?? '') : '';
            
            $personalizedBody = $body;
            if ($name) {
                $personalizedBody = str_replace('{{name}}', $name, $personalizedBody);
            }
            
            $results[$email] = $this->sendEmail($email, $subject, $personalizedBody);
            
            // 避免邮件服务器限制，每封邮件间隔一段时间
            usleep(500000); // 0.5秒
        }
        
        return $results;
    }

    /**
     * 渲染联系表单模板
     */
    private function renderContactTemplate(array $data): string
    {
        return "
        <h2>新的联系表单消息</h2>
        <table style='border-collapse: collapse; width: 100%;'>
            <tr>
                <td style='border: 1px solid #ddd; padding: 8px; font-weight: bold;'>姓名:</td>
                <td style='border: 1px solid #ddd; padding: 8px;'>" . htmlspecialchars($data['name'] ?? '') . "</td>
            </tr>
            <tr>
                <td style='border: 1px solid #ddd; padding: 8px; font-weight: bold;'>邮箱:</td>
                <td style='border: 1px solid #ddd; padding: 8px;'>" . htmlspecialchars($data['email'] ?? '') . "</td>
            </tr>
            <tr>
                <td style='border: 1px solid #ddd; padding: 8px; font-weight: bold;'>电话:</td>
                <td style='border: 1px solid #ddd; padding: 8px;'>" . htmlspecialchars($data['phone'] ?? '') . "</td>
            </tr>
            <tr>
                <td style='border: 1px solid #ddd; padding: 8px; font-weight: bold;'>主题:</td>
                <td style='border: 1px solid #ddd; padding: 8px;'>" . htmlspecialchars($data['subject'] ?? '') . "</td>
            </tr>
            <tr>
                <td style='border: 1px solid #ddd; padding: 8px; font-weight: bold; vertical-align: top;'>消息内容:</td>
                <td style='border: 1px solid #ddd; padding: 8px;'>" . nl2br(htmlspecialchars($data['message'] ?? '')) . "</td>
            </tr>
            <tr>
                <td style='border: 1px solid #ddd; padding: 8px; font-weight: bold;'>提交时间:</td>
                <td style='border: 1px solid #ddd; padding: 8px;'>" . date('Y-m-d H:i:s') . "</td>
            </tr>
            <tr>
                <td style='border: 1px solid #ddd; padding: 8px; font-weight: bold;'>IP地址:</td>
                <td style='border: 1px solid #ddd; padding: 8px;'>" . ($_SERVER['REMOTE_ADDR'] ?? 'unknown') . "</td>
            </tr>
        </table>
        ";
    }

    /**
     * 渲染联系确认模板
     */
    private function renderContactConfirmationTemplate(array $data): string
    {
        return "
        <h2>感谢您的联系</h2>
        <p>亲爱的 " . htmlspecialchars($data['name'] ?? '用户') . "，</p>
        <p>我们已经收到您的联系信息，我们的团队会在24小时内回复您。</p>
        
        <h3>您提交的信息:</h3>
        <table style='border-collapse: collapse; width: 100%;'>
            <tr>
                <td style='border: 1px solid #ddd; padding: 8px; font-weight: bold;'>主题:</td>
                <td style='border: 1px solid #ddd; padding: 8px;'>" . htmlspecialchars($data['subject'] ?? '') . "</td>
            </tr>
            <tr>
                <td style='border: 1px solid #ddd; padding: 8px; font-weight: bold;'>消息内容:</td>
                <td style='border: 1px solid #ddd; padding: 8px;'>" . nl2br(htmlspecialchars($data['message'] ?? '')) . "</td>
            </tr>
            <tr>
                <td style='border: 1px solid #ddd; padding: 8px; font-weight: bold;'>提交时间:</td>
                <td style='border: 1px solid #ddd; padding: 8px;'>" . date('Y-m-d H:i:s') . "</td>
            </tr>
        </table>
        
        <p>如果您有任何疑问，请随时联系我们。</p>
        <p>祝好！<br>AlingAi Pro 团队</p>
        ";
    }

    /**
     * 渲染告警模板
     */
    private function renderAlertTemplate(string $subject, string $message): string
    {
        $alertLevel = $this->getAlertLevel($message);
        $alertColor = $this->getAlertColor($alertLevel);
        
        return "
        <div style='border: 2px solid {$alertColor}; padding: 20px; margin: 10px 0; background-color: #f9f9f9;'>
            <h2 style='color: {$alertColor}; margin-top: 0;'>🚨 系统告警</h2>
            <table style='border-collapse: collapse; width: 100%;'>
                <tr>
                    <td style='border: 1px solid #ddd; padding: 8px; font-weight: bold;'>告警级别:</td>
                    <td style='border: 1px solid #ddd; padding: 8px; color: {$alertColor}; font-weight: bold;'>" . strtoupper($alertLevel) . "</td>
                </tr>
                <tr>
                    <td style='border: 1px solid #ddd; padding: 8px; font-weight: bold;'>告警主题:</td>
                    <td style='border: 1px solid #ddd; padding: 8px;'>" . htmlspecialchars($subject) . "</td>
                </tr>
                <tr>
                    <td style='border: 1px solid #ddd; padding: 8px; font-weight: bold;'>服务器:</td>
                    <td style='border: 1px solid #ddd; padding: 8px;'>" . gethostname() . "</td>
                </tr>
                <tr>
                    <td style='border: 1px solid #ddd; padding: 8px; font-weight: bold;'>告警时间:</td>
                    <td style='border: 1px solid #ddd; padding: 8px;'>" . date('Y-m-d H:i:s') . "</td>
                </tr>
                <tr>
                    <td style='border: 1px solid #ddd; padding: 8px; font-weight: bold; vertical-align: top;'>详细信息:</td>
                    <td style='border: 1px solid #ddd; padding: 8px;'>" . nl2br(htmlspecialchars($message)) . "</td>
                </tr>
            </table>
        </div>
        <p><strong>请立即处理此告警！</strong></p>
        ";
    }

    /**
     * 渲染通知模板
     */
    private function renderNotificationTemplate(string $subject, string $message, array $data = []): string
    {
        $content = "
        <h2>" . htmlspecialchars($subject) . "</h2>
        <p>" . nl2br(htmlspecialchars($message)) . "</p>
        ";
        
        if (!empty($data)) {
            $content .= "<h3>详细信息:</h3><table style='border-collapse: collapse; width: 100%;'>";
            foreach ($data as $key => $value) {
                $content .= "
                <tr>
                    <td style='border: 1px solid #ddd; padding: 8px; font-weight: bold;'>" . htmlspecialchars($key) . ":</td>
                    <td style='border: 1px solid #ddd; padding: 8px;'>" . htmlspecialchars($value) . "</td>
                </tr>";
            }
            $content .= "</table>";
        }
        
        return $content;
    }

    /**
     * 包装邮件模板
     */
    private function wrapEmailTemplate(string $subject, string $body): string
    {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>" . htmlspecialchars($subject) . "</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { text-align: center; margin-bottom: 30px; }
                .footer { text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd; color: #666; font-size: 12px; }
                table { border-collapse: collapse; width: 100%; margin: 15px 0; }
                td { padding: 8px; border: 1px solid #ddd; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1 style='color: #2c3e50; margin: 0;'>AlingAi Pro</h1>
                    <p style='margin: 5px 0 0 0; color: #7f8c8d;'>智能AI助手平台</p>
                </div>
                
                <div class='content'>
                    {$body}
                </div>
                
                <div class='footer'>
                    <p>此邮件由 AlingAi Pro 系统自动发送，请勿直接回复。</p>
                    <p>如有疑问，请联系我们：admin@gxggm.com</p>
                    <p>&copy; " . date('Y') . " AlingAi Pro. All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }

    /**
     * 获取告警级别
     */
    private function getAlertLevel(string $message): string
    {
        if (stripos($message, 'critical') !== false || stripos($message, '临界') !== false) {
            return 'critical';
        } elseif (stripos($message, 'warning') !== false || stripos($message, '告警') !== false) {
            return 'warning';
        } else {
            return 'info';
        }
    }

    /**
     * 获取告警颜色
     */
    private function getAlertColor(string $level): string
    {
        switch ($level) {
            case 'critical':
                return '#e74c3c';
            case 'warning':
                return '#f39c12';
            default:
                return '#3498db';
        }
    }

    /**
     * 记录邮件发送日志
     */
    private function logEmailSent(string $to, string $subject, bool $success): void
    {
        try {
            $this->dbService->insert('email_logs', [
                'recipient' => $to,
                'subject' => $subject,
                'status' => $success ? 'sent' : 'failed',
                'sent_at' => date('Y-m-d H:i:s'),
                'created_at' => date('Y-m-d H:i:s')
            ]);
        } catch (\Exception $e) {
            // 记录失败不影响主流程
            error_log('记录邮件日志失败: ' . $e->getMessage());
        }
    }

    /**
     * 记录邮件错误
     */
    private function logEmailError(string $message, string $error, array $context = []): void
    {
        $logMessage = sprintf(
            '[%s] %s: %s',
            date('Y-m-d H:i:s'),
            $message,
            $error
        );
        
        if (!empty($context)) {
            $logMessage .= ' Context: ' . json_encode($context);
        }
        
        error_log($logMessage);
    }

    /**
     * 测试邮件配置
     */
    public function testEmailConfiguration(): array
    {
        try {
            $testEmail = $this->config->get('mail.alert_email');
            $result = $this->sendEmail(
                $testEmail,
                'AlingAi Pro 邮件配置测试',
                '这是一封测试邮件，用于验证邮件配置是否正确。如果您收到此邮件，说明邮件服务配置成功。'
            );

            return [
                'success' => $result,
                'message' => $result ? '邮件配置测试成功' : '邮件配置测试失败',
                'timestamp' => date('Y-m-d H:i:s')
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => '邮件配置测试失败: ' . $e->getMessage(),
                'timestamp' => date('Y-m-d H:i:s')
            ];
        }
    }

    /**
     * 获取邮件统计
     */
    public function getEmailStats(string $period = 'today'): array
    {
        try {
            $whereClause = '';
            switch ($period) {
                case 'today':
                    $whereClause = 'WHERE DATE(sent_at) = CURDATE()';
                    break;
                case 'week':
                    $whereClause = 'WHERE sent_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)';
                    break;
                case 'month':
                    $whereClause = 'WHERE sent_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)';
                    break;
            }

            $stats = $this->dbService->fetchOne("
                SELECT 
                    COUNT(*) as total_emails,
                    COUNT(CASE WHEN status = 'sent' THEN 1 END) as sent_count,
                    COUNT(CASE WHEN status = 'failed' THEN 1 END) as failed_count
                FROM email_logs 
                {$whereClause}
            ");

            return $stats ?: [
                'total_emails' => 0,
                'sent_count' => 0,
                'failed_count' => 0
            ];

        } catch (\Exception $e) {
            $this->logEmailError('获取邮件统计失败', $e->getMessage());
            return [];
        }
    }

    /**
     * 获取服务状态（API兼容方法）
     */
    public function getStatus(): array
    {
        try {
            // 尝试连接SMTP服务器测试状态
            $testResult = $this->testEmailConfiguration();
            
            return [
                'connected' => $testResult['success'],
                'smtp_host' => $this->config->get('mail.smtp.host'),
                'smtp_port' => $this->config->get('mail.smtp.port'),
                'smtp_secure' => $this->config->get('mail.smtp.secure'),
                'from_address' => $this->config->get('mail.from.address'),
                'last_test' => $testResult['timestamp'],
                'status' => $testResult['success'] ? 'healthy' : 'error'
            ];
        } catch (\Exception $e) {
            return [
                'connected' => false,
                'error' => $e->getMessage(),
                'status' => 'error'
            ];
        }
    }

    /**
     * 发送测试邮件（API兼容方法）
     */
    public function sendTestEmail(string $email): array
    {
        return $this->testEmailConfiguration();
    }

    /**
     * 获取统计信息（API兼容方法）
     */
    public function getStatistics(string $period = 'today'): array
    {
        return $this->getEmailStats($period);
    }
}
