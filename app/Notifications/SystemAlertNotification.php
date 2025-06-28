<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SystemAlertNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * 告警列表
     *
     * @var array
     */
    protected $alerts;

    /**
     * 创建一个新的通知实例
     *
     * @param array $alerts 告警列表
     * @return void
     */
    public function __construct(array $alerts)
    {
        $this->alerts = $alerts;
    }

    /**
     * 获取通知发送的通道
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    /**
     * 获取邮件通知的表示形式
     *
     * @param mixed $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $mailMessage = (new MailMessage)
            ->subject('系统告警通知')
            ->greeting('您好！')
            ->line('系统检测到以下告警，请尽快处理：');

        foreach ($this->alerts as $alert) {
            $mailMessage->line('-----------------------------------');
            $mailMessage->line('【' . $this->getLevelText($alert['level']) . '】' . $alert['message']);
            $mailMessage->line('来源：' . $alert['source']);
            $mailMessage->line('类型：' . $this->getTypeText($alert['type']));
            $mailMessage->line('时间：' . ($alert['created_at'] ?? now()->toDateTimeString()));
            
            if (isset($alert['details']) && is_array($alert['details'])) {
                $details = '';
                foreach ($alert['details'] as $key => $value) {
                    if (is_scalar($value)) {
                        $details .= $key . ': ' . $value . "\n";
                    }
                }
                if (!empty($details)) {
                    $mailMessage->line('详情：' . $details);
                }
            }
        }

        $mailMessage->action('查看告警详情', url('/admin/monitoring/alerts'))
            ->line('感谢您使用我们的系统！');

        return $mailMessage;
    }

    /**
     * 获取数据库通知的表示形式
     *
     * @param mixed $notifiable
     * @return array
     */
    public function toDatabase($notifiable)
    {
        $alertCount = count($this->alerts);
        $criticalCount = count(array_filter($this->alerts, function ($alert) {
            return $alert['level'] === 'critical';
        }));
        
        return [
            'title' => '系统告警通知',
            'message' => "系统检测到 {$alertCount} 个告警，其中 {$criticalCount} 个严重告警，请尽快处理。",
            'alerts' => $this->alerts,
            'url' => '/admin/monitoring/alerts'
        ];
    }

    /**
     * 获取告警级别文本
     *
     * @param string $level 告警级别
     * @return string
     */
    private function getLevelText(string $level): string
    {
        switch ($level) {
            case 'critical':
                return '严重';
            case 'warning':
                return '警告';
            case 'info':
                return '信息';
            default:
                return $level;
        }
    }

    /**
     * 获取告警类型文本
     *
     * @param string $type 告警类型
     * @return string
     */
    private function getTypeText(string $type): string
    {
        switch ($type) {
            case 'performance':
                return '性能';
            case 'health':
                return '健康状态';
            case 'application':
                return '应用';
            case 'security':
                return '安全';
            default:
                return $type;
        }
    }
} 