<?php

namespace AlingAi\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
use Psr\Log\LoggerInterface;

class EmailService
{
    private PHPMailer $mailer;
    private LoggerInterface $logger;
    private array $config;
    private array $templates;
    
    public function __construct(LoggerInterface $logger, array $config = [])
    {
        $this->logger = $logger;        $this->config = array_merge([
            'host' => $_ENV['MAIL_HOST'] ?? 'localhost',
            'port' => (int) ($_ENV['MAIL_PORT'] ?? 587),
            'username' => $_ENV['MAIL_USERNAME'] ?? '',
            'password' => $_ENV['MAIL_PASSWORD'] ?? '',
            'encryption' => $_ENV['MAIL_ENCRYPTION'] ?? 'tls',
            'from_email' => $_ENV['MAIL_FROM_ADDRESS'] ?? 'noreply@example.com',
            'from_name' => $_ENV['MAIL_FROM_NAME'] ?? 'AlingAi Pro',
            'debug' => ($_ENV['MAIL_DEBUG'] ?? 'false') === 'true',
            'timeout' => 30,
            'charset' => 'UTF-8'
        ], $config);
        
        $this->initializeMailer();
        $this->loadTemplates();
    }
    
    private function initializeMailer(): void
    {
        $this->mailer = new PHPMailer(true);
        
        try {
            // 服务器设置
            $this->mailer->isSMTP();
            $this->mailer->Host = $this->config['host'];
            $this->mailer->SMTPAuth = !empty($this->config['username']);
            $this->mailer->Username = $this->config['username'];
            $this->mailer->Password = $this->config['password'];
            $this->mailer->Port = $this->config['port'];
            $this->mailer->Timeout = $this->config['timeout'];
            
            // 加密设置
            if ($this->config['encryption'] === 'ssl') {
                $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            } elseif ($this->config['encryption'] === 'tls') {
                $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            }
            
            // 字符集设置
            $this->mailer->CharSet = $this->config['charset'];
            
            // 调试设置
            if ($this->config['debug']) {
                $this->mailer->SMTPDebug = SMTP::DEBUG_SERVER;
                $this->mailer->Debugoutput = function($str, $level) {
                    $this->logger->debug("PHPMailer: {$str}");
                };
            }
            
            // 默认发件人
            $this->mailer->setFrom($this->config['from_email'], $this->config['from_name']);
            
        } catch (Exception $e) {
            $this->logger->error('Email service initialization failed: ' . $e->getMessage());
            throw new \RuntimeException('Failed to initialize email service: ' . $e->getMessage());
        }
    }
    
    private function loadTemplates(): void
    {
        $this->templates = [
            'verification' => [
                'subject' => '验证您的邮箱地址',
                'template' => 'verification.html'
            ],
            'password_reset' => [
                'subject' => '重置密码',
                'template' => 'password_reset.html'
            ],
            'welcome' => [
                'subject' => '欢迎使用AlingAi Pro',
                'template' => 'welcome.html'
            ],
            'notification' => [
                'subject' => '系统通知',
                'template' => 'notification.html'
            ],
            'account_locked' => [
                'subject' => '账户安全提醒',
                'template' => 'account_locked.html'
            ],
            'login_alert' => [
                'subject' => '登录提醒',
                'template' => 'login_alert.html'
            ]
        ];
    }
    
    /**
     * 发送邮件
     */
    public function send(
        $to,
        string $subject,
        string $body,
        array $options = []
    ): bool {
        try {
            // 重置收件人
            $this->mailer->clearAddresses();
            $this->mailer->clearAttachments();
            $this->mailer->clearCustomHeaders();
            
            // 设置收件人
            if (is_array($to)) {
                foreach ($to as $email => $name) {
                    if (is_numeric($email)) {
                        $this->mailer->addAddress($name);
                    } else {
                        $this->mailer->addAddress($email, $name);
                    }
                }
            } else {
                $this->mailer->addAddress($to);
            }
            
            // 设置抄送
            if (!empty($options['cc'])) {
                if (is_array($options['cc'])) {
                    foreach ($options['cc'] as $email => $name) {
                        if (is_numeric($email)) {
                            $this->mailer->addCC($name);
                        } else {
                            $this->mailer->addCC($email, $name);
                        }
                    }
                } else {
                    $this->mailer->addCC($options['cc']);
                }
            }
            
            // 设置密送
            if (!empty($options['bcc'])) {
                if (is_array($options['bcc'])) {
                    foreach ($options['bcc'] as $email => $name) {
                        if (is_numeric($email)) {
                            $this->mailer->addBCC($name);
                        } else {
                            $this->mailer->addBCC($email, $name);
                        }
                    }
                } else {
                    $this->mailer->addBCC($options['bcc']);
                }
            }
            
            // 设置回复地址
            if (!empty($options['reply_to'])) {
                if (is_array($options['reply_to'])) {
                    $this->mailer->addReplyTo($options['reply_to']['email'], $options['reply_to']['name'] ?? '');
                } else {
                    $this->mailer->addReplyTo($options['reply_to']);
                }
            }
            
            // 设置邮件内容
            $this->mailer->Subject = $subject;
            
            if (!empty($options['is_html']) || strpos($body, '<') !== false) {
                $this->mailer->isHTML(true);
                $this->mailer->Body = $body;
                
                // 生成纯文本版本
                if (empty($options['alt_body'])) {
                    $this->mailer->AltBody = strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $body));
                } else {
                    $this->mailer->AltBody = $options['alt_body'];
                }
            } else {
                $this->mailer->isHTML(false);
                $this->mailer->Body = $body;
            }
            
            // 添加附件
            if (!empty($options['attachments'])) {
                foreach ($options['attachments'] as $attachment) {
                    if (is_string($attachment)) {
                        $this->mailer->addAttachment($attachment);
                    } elseif (is_array($attachment)) {
                        $this->mailer->addAttachment(
                            $attachment['path'],
                            $attachment['name'] ?? '',
                            $attachment['encoding'] ?? 'base64',
                            $attachment['type'] ?? ''
                        );
                    }
                }
            }
            
            // 添加自定义头
            if (!empty($options['headers'])) {
                foreach ($options['headers'] as $name => $value) {
                    $this->mailer->addCustomHeader($name, $value);
                }
            }
            
            // 设置优先级
            if (!empty($options['priority'])) {
                $this->mailer->Priority = $options['priority'];
            }
            
            // 发送邮件
            $result = $this->mailer->send();
            
            if ($result) {
                $this->logger->info('Email sent successfully', [
                    'to' => $to,
                    'subject' => $subject,
                    'message_id' => $this->mailer->getLastMessageID()
                ]);
            }
            
            return $result;
            
        } catch (Exception $e) {
            $this->logger->error('Failed to send email', [
                'to' => $to,
                'subject' => $subject,
                'error' => $e->getMessage()
            ]);
            
            return false;
        }
    }
    
    /**
     * 使用模板发送邮件
     */
    public function sendTemplate(
        $to,
        string $template,
        array $data = [],
        array $options = []
    ): bool {
        if (!isset($this->templates[$template])) {
            $this->logger->error("Email template not found: {$template}");
            return false;
        }
        
        $templateConfig = $this->templates[$template];
        $subject = $options['subject'] ?? $templateConfig['subject'];
        
        // 渲染模板
        $body = $this->renderTemplate($templateConfig['template'], $data);
        
        if ($body === null) {
            return false;
        }
        
        return $this->send($to, $subject, $body, array_merge($options, ['is_html' => true]));
    }
    
    /**
     * 发送验证邮件
     */
    public function sendVerificationEmail(string $email, string $token, array $user = []): bool
    {
        $verificationUrl = $this->config['app_url'] . "/verify-email?token={$token}";
        
        $data = array_merge($user, [
            'verification_url' => $verificationUrl,
            'token' => $token,
            'app_name' => $this->config['from_name'],
            'app_url' => $this->config['app_url'] ?? '',
            'expires_in' => '24小时'
        ]);
        
        return $this->sendTemplate($email, 'verification', $data);
    }
    
    /**
     * 发送密码重置邮件
     */
    public function sendPasswordResetEmail(string $email, string $token, array $user = []): bool
    {
        $resetUrl = $this->config['app_url'] . "/reset-password?token={$token}";
        
        $data = array_merge($user, [
            'reset_url' => $resetUrl,
            'token' => $token,
            'app_name' => $this->config['from_name'],
            'app_url' => $this->config['app_url'] ?? '',
            'expires_in' => '1小时'
        ]);
        
        return $this->sendTemplate($email, 'password_reset', $data);
    }
    
    /**
     * 发送欢迎邮件
     */
    public function sendWelcomeEmail(string $email, array $user = []): bool
    {
        $data = array_merge($user, [
            'app_name' => $this->config['from_name'],
            'app_url' => $this->config['app_url'] ?? '',
            'support_email' => $this->config['support_email'] ?? $this->config['from_email']
        ]);
        
        return $this->sendTemplate($email, 'welcome', $data);
    }
    
    /**
     * 发送通知邮件
     */
    public function sendNotification(
        $to,
        string $title,
        string $message,
        array $data = []
    ): bool {
        $data = array_merge($data, [
            'title' => $title,
            'message' => $message,
            'app_name' => $this->config['from_name'],
            'app_url' => $this->config['app_url'] ?? ''
        ]);
        
        return $this->sendTemplate($to, 'notification', $data, ['subject' => $title]);
    }
    
    /**
     * 发送登录提醒邮件
     */
    public function sendLoginAlert(string $email, array $loginInfo = []): bool
    {
        $data = array_merge($loginInfo, [
            'app_name' => $this->config['from_name'],
            'timestamp' => date('Y-m-d H:i:s'),
            'app_url' => $this->config['app_url'] ?? ''
        ]);
        
        return $this->sendTemplate($email, 'login_alert', $data);
    }
    
    /**
     * 渲染邮件模板
     */
    private function renderTemplate(string $templateFile, array $data): ?string
    {
        $templatePath = __DIR__ . "/../../resources/email_templates/{$templateFile}";
        
        if (!file_exists($templatePath)) {
            // 如果模板文件不存在，使用默认模板
            return $this->renderDefaultTemplate($data);
        }
        
        try {
            $template = file_get_contents($templatePath);
            
            // 简单的模板变量替换
            foreach ($data as $key => $value) {
                if (is_scalar($value)) {
                    $template = str_replace("{{$key}}", htmlspecialchars((string)$value), $template);
                }
            }
            
            // 移除未替换的变量
            $template = preg_replace('/\{\{[^}]+\}\}/', '', $template);
            
            return $template;
            
        } catch (\Exception $e) {
            $this->logger->error("Failed to render email template: {$templateFile}", [
                'error' => $e->getMessage()
            ]);
            
            return $this->renderDefaultTemplate($data);
        }
    }
    
    /**
     * 渲染默认模板
     */
    private function renderDefaultTemplate(array $data): string
    {
        $content = $data['message'] ?? $data['content'] ?? '邮件内容';
        $title = $data['title'] ?? $data['subject'] ?? '通知';
        $appName = $data['app_name'] ?? $this->config['from_name'];
        
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>{$title}</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #f8f9fa; padding: 20px; text-align: center; }
                .content { padding: 20px; }
                .footer { background: #f8f9fa; padding: 20px; text-align: center; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>{$appName}</h1>
                </div>
                <div class='content'>
                    <h2>{$title}</h2>
                    <p>{$content}</p>
                </div>
                <div class='footer'>
                    <p>&copy; " . date('Y') . " {$appName}. All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }
    
    /**
     * 批量发送邮件
     */
    public function sendBulk(array $recipients, string $subject, string $body, array $options = []): array
    {
        $results = [];
        $batchSize = $options['batch_size'] ?? 10;
        $delay = $options['delay'] ?? 1; // 秒
        
        $batches = array_chunk($recipients, $batchSize, true);
        
        foreach ($batches as $batch) {
            foreach ($batch as $email => $name) {
                $to = is_numeric($email) ? $name : [$email => $name];
                $results[$email] = $this->send($to, $subject, $body, $options);
                
                if ($delay > 0) {
                    sleep($delay);
                }
            }
        }
        
        return $results;
    }
    
    /**
     * 测试邮件连接
     */
    public function testConnection(): bool
    {
        try {
            $this->mailer->smtpConnect();
            $this->mailer->smtpClose();
            return true;
        } catch (Exception $e) {
            $this->logger->error('Email connection test failed: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * 获取邮件发送统计
     */
    public function getStats(): array
    {
        return [
            'host' => $this->config['host'],
            'port' => $this->config['port'],
            'encryption' => $this->config['encryption'],
            'from_email' => $this->config['from_email'],
            'from_name' => $this->config['from_name'],
            'templates' => array_keys($this->templates)
        ];
    }
}
