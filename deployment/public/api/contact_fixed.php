<?php
/**
 * 联系表单处理端点
 * 处理来自网站联系表单的消息并发送邮件
 */

// 加载 composer autoloader
require_once __DIR__ . '/../../vendor/autoload.php';

// 引入PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// 加载配置
require_once __DIR__ . '/config.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// 处理预检请求
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// 只允许POST请求
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => '只允许POST请求']);
    exit();
}

try {
    // 获取POST数据
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        // 如果不是JSON，尝试获取表单数据
        $input = $_POST;
    }

    // 验证必填字段
    $required_fields = ['name', 'email', 'message'];
    $missing_fields = [];
    
    foreach ($required_fields as $field) {
        if (empty($input[$field])) {
            $missing_fields[] = $field;
        }
    }
    
    if (!empty($missing_fields)) {
        echo json_encode([
            'success' => false, 
            'message' => '缺少必填字段: ' . implode(', ', $missing_fields)
        ]);
        exit();
    }

    // 验证邮箱格式
    if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => '邮箱格式不正确']);
        exit();
    }

    // 清理输入数据
    $name = htmlspecialchars(trim($input['name']));
    $email = htmlspecialchars(trim($input['email']));
    $company = htmlspecialchars(trim($input['company'] ?? ''));
    $message = htmlspecialchars(trim($input['message']));
    
    // 创建简单的邮件发送类
    class SimpleMailer {
        private $config;
        
        public function __construct() {
            // 使用环境变量配置
            $this->config = [
                'host' => $_ENV['MAIL_HOST'] ?? 'smtp.exmail.qq.com',
                'port' => $_ENV['MAIL_PORT'] ?? 465,
                'username' => $_ENV['MAIL_USERNAME'] ?? '',
                'password' => $_ENV['MAIL_PASSWORD'] ?? '',
                'encryption' => $_ENV['MAIL_ENCRYPTION'] ?? 'ssl',
                'from_address' => $_ENV['MAIL_FROM_ADDRESS'] ?? 'admin@gxggm.com',
                'from_name' => $_ENV['MAIL_FROM_NAME'] ?? 'AlingAi Pro'
            ];
        }
        
        public function sendEmail($to, $subject, $body, $replyTo = null, $replyToName = null) {
            $mail = new PHPMailer(true);
            
            try {
                // 服务器设置
                $mail->isSMTP();
                $mail->Host = $this->config['host'];
                $mail->SMTPAuth = !empty($this->config['username']);
                $mail->Username = $this->config['username'];
                $mail->Password = $this->config['password'];
                $mail->Port = $this->config['port'];
                
                // 加密设置
                if ($this->config['encryption'] === 'ssl') {
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                } elseif ($this->config['encryption'] === 'tls') {
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                }
                
                // 字符集设置
                $mail->CharSet = 'UTF-8';
                
                // 发件人
                $mail->setFrom($this->config['from_address'], $this->config['from_name']);
                
                // 收件人
                $mail->addAddress($to);
                
                // 回复地址
                if ($replyTo) {
                    $mail->addReplyTo($replyTo, $replyToName);
                }
                
                // 内容
                $mail->isHTML(true);
                $mail->Subject = $subject;
                $mail->Body = $body;
                
                $mail->send();
                return true;
                
            } catch (Exception $e) {
                error_log("邮件发送失败: " . $e->getMessage());
                return false;
            }
        }
    }

    // 准备邮件内容
    $subject = "来自 {$name} 的联系消息 - AlingAi Pro";
    $body = "
    <html>
    <head>
        <meta charset='UTF-8'>
        <title>联系表单消息</title>
    </head>
    <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
        <div style='max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 5px;'>
            <h2 style='color: #2c3e50; border-bottom: 2px solid #3498db; padding-bottom: 10px;'>
                新的联系消息
            </h2>
            
            <table style='width: 100%; border-collapse: collapse; margin: 20px 0;'>
                <tr>
                    <td style='padding: 10px; border: 1px solid #ddd; background-color: #f8f9fa; font-weight: bold; width: 120px;'>
                        姓名:
                    </td>
                    <td style='padding: 10px; border: 1px solid #ddd;'>
                        {$name}
                    </td>
                </tr>
                <tr>
                    <td style='padding: 10px; border: 1px solid #ddd; background-color: #f8f9fa; font-weight: bold;'>
                        邮箱:
                    </td>
                    <td style='padding: 10px; border: 1px solid #ddd;'>
                        {$email}
                    </td>
                </tr>";
    
    if (!empty($company)) {
        $body .= "
                <tr>
                    <td style='padding: 10px; border: 1px solid #ddd; background-color: #f8f9fa; font-weight: bold;'>
                        公司:
                    </td>
                    <td style='padding: 10px; border: 1px solid #ddd;'>
                        {$company}
                    </td>
                </tr>";
    }
    
    $body .= "
                <tr>
                    <td style='padding: 10px; border: 1px solid #ddd; background-color: #f8f9fa; font-weight: bold;'>
                        时间:
                    </td>
                    <td style='padding: 10px; border: 1px solid #ddd;'>
                        " . date('Y-m-d H:i:s') . "
                    </td>
                </tr>
            </table>
            
            <div style='margin: 20px 0;'>
                <h3 style='color: #2c3e50; margin-bottom: 10px;'>消息内容:</h3>
                <div style='background-color: #f8f9fa; padding: 15px; border-left: 4px solid #3498db; border-radius: 3px;'>
                    " . nl2br($message) . "
                </div>
            </div>
            
            <div style='margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd; font-size: 12px; color: #7f8c8d;'>
                <p>此邮件来自 AlingAi Pro 官网联系表单</p>
                <p>请及时回复客户咨询</p>
            </div>
        </div>
    </body>
    </html>";

    // 发送邮件
    $mailer = new SimpleMailer();
    $adminEmail = $_ENV['CONTACT_EMAIL'] ?? $_ENV['MAIL_FROM_ADDRESS'] ?? 'admin@gxggm.com';
    
    $emailSent = $mailer->sendEmail(
        $adminEmail,
        $subject,
        $body,
        $email,
        $name
    );

    if ($emailSent) {
        // 记录成功日志
        error_log("联系表单消息发送成功: {$name} ({$email})");
        
        echo json_encode([
            'success' => true,
            'message' => '消息发送成功！我们会尽快回复您。',
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => '邮件发送失败，请稍后重试或直接联系我们。'
        ]);
    }

} catch (Exception $e) {
    error_log("联系表单处理错误: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => '服务器错误，请稍后重试。'
    ]);
}
