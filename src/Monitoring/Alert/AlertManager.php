<?php
namespace AlingAi\Monitoring\Alert;

use AlingAi\Monitoring\Alert\Channel\AlertChannelInterface;
use Psr\Log\LoggerInterface;

/**
 * 告警管理器 - 处理API监控告警
 */
class AlertManager
{
    /**
     * 告警严重级别
     */
    const SEVERITY_LOW = 'low';
    const SEVERITY_MEDIUM = 'medium';
    const SEVERITY_HIGH = 'high';
    const SEVERITY_CRITICAL = 'critical';
    
    /**
     * @var array 告警通道
     */
    private $channels = [];
    
    /**
     * @var LoggerInterface
     */
    private $logger;
    
    /**
     * @var array 已触发的告警缓存
     */
    private $alertCache = [];
    
    /**
     * @var int 告警去重时间窗口(秒)
     */
    private $deduplicationWindow = 300; // 5分钟
    
    /**
     * @var array 严重级别设置
     */
    private $severitySettings = [
        self::SEVERITY_LOW => [
            'channels' => ['log'],
            'throttle_interval' => 3600, // 1小时
        ],
        self::SEVERITY_MEDIUM => [
            'channels' => ['log', 'email'],
            'throttle_interval' => 1800, // 30分钟
        ],
        self::SEVERITY_HIGH => [
            'channels' => ['log', 'email', 'sms'],
            'throttle_interval' => 600, // 10分钟
        ],
        self::SEVERITY_CRITICAL => [
            'channels' => ['log', 'email', 'sms', 'chat'],
            'throttle_interval' => 300, // 5分钟
        ],
    ];

    /**
     * 构造函数
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * 添加告警通道
     */
    public function addChannel(string $name, AlertChannelInterface $channel): void
    {
        $this->channels[$name] = $channel;
    }

    /**
     * 设置告警去重时间窗口
     */
    public function setDeduplicationWindow(int $seconds): void
    {
        $this->deduplicationWindow = $seconds;
    }

    /**
     * 设置严重级别设置
     */
    public function setSeveritySettings(array $settings): void
    {
        $this->severitySettings = array_merge($this->severitySettings, $settings);
    }

    /**
     * 触发告警
     *
     * @param string $severity 严重级别
     * @param string $title 告警标题
     * @param string $message 告警消息
     * @param array $metadata 附加数据
     * @return bool 是否成功
     */
    public function triggerAlert(string $severity, string $title, string $message, array $metadata = []): bool
    {
        // 检查是否在有效的严重级别内
        if (!isset($this->severitySettings[$severity])) {
            $this->logger->warning("尝试触发未知严重级别的告警", [
                'severity' => $severity,
                'title' => $title,
            ]);
            
            $severity = self::SEVERITY_MEDIUM; // 使用默认级别
        }
        
        // 构建告警对象
        $alert = [
            'id' => md5($title . $message . json_encode($metadata)),
            'severity' => $severity,
            'title' => $title,
            'message' => $message,
            'metadata' => $metadata,
            'timestamp' => time(),
            'resolved' => false,
        ];
        
        // 检查是否需要去重
        if ($this->shouldDeduplicate($alert)) {
            $this->logger->debug("告警被去重", [
                'alert_id' => $alert['id'],
                'title' => $title,
            ]);
            
            return false;
        }
        
        // 缓存告警
        $this->alertCache[$alert['id']] = $alert;
        
        // 根据严重级别获取通道列表
        $channelNames = $this->severitySettings[$severity]['channels'] ?? ['log'];
        
        // 记录告警
        $this->logger->notice("触发告警", [
            'alert_id' => $alert['id'],
            'severity' => $severity,
            'title' => $title,
            'message' => $message,
            'channels' => $channelNames,
        ]);
        
        // 发送到各个通道
        $success = true;
        foreach ($channelNames as $channelName) {
            if ($channelName === 'log') {
                // 已经记录到日志
                continue;
            }
            
            if (!isset($this->channels[$channelName])) {
                $this->logger->warning("尝试使用未配置的告警通道", [
                    'channel' => $channelName,
                ]);
                
                continue;
            }
            
            try {
                $channelSuccess = $this->channels[$channelName]->send($alert);
                
                if (!$channelSuccess) {
                    $this->logger->error("通过通道发送告警失败", [
                        'channel' => $channelName,
                        'alert_id' => $alert['id'],
                    ]);
                    
                    $success = false;
                }
            } catch (\Exception $e) {
                $this->logger->error("通过通道发送告警时发生异常", [
                    'channel' => $channelName,
                    'alert_id' => $alert['id'],
                    'error' => $e->getMessage(),
                ]);
                
                $success = false;
            }
        }
        
        return $success;
    }

    /**
     * 解决告警
     */
    public function resolveAlert(string $alertId): bool
    {
        if (!isset($this->alertCache[$alertId])) {
            $this->logger->warning("尝试解决不存在的告警", [
                'alert_id' => $alertId,
            ]);
            
            return false;
        }
        
        $alert = $this->alertCache[$alertId];
        $alert['resolved'] = true;
        $alert['resolved_at'] = time();
        
        $this->alertCache[$alertId] = $alert;
        
        $this->logger->info("告警已解决", [
            'alert_id' => $alertId,
            'title' => $alert['title'],
        ]);
        
        // 通知告警解决
        $channelNames = $this->severitySettings[$alert['severity']]['channels'] ?? ['log'];
        
        foreach ($channelNames as $channelName) {
            if ($channelName === 'log' || !isset($this->channels[$channelName])) {
                continue;
            }
            
            try {
                if (method_exists($this->channels[$channelName], 'sendResolution')) {
                    $this->channels[$channelName]->sendResolution($alert);
                }
            } catch (\Exception $e) {
                $this->logger->error("通过通道发送告警解决通知时发生异常", [
                    'channel' => $channelName,
                    'alert_id' => $alertId,
                    'error' => $e->getMessage(),
                ]);
            }
        }
        
        return true;
    }

    /**
     * 检查是否应该去重
     */
    private function shouldDeduplicate(array $alert): bool
    {
        $alertId = $alert['id'];
        
        // 检查是否已存在相同ID的告警
        if (!isset($this->alertCache[$alertId])) {
            return false;
        }
        
        $existingAlert = $this->alertCache[$alertId];
        $severity = $alert['severity'];
        
        // 如果告警已解决，不去重
        if ($existingAlert['resolved']) {
            return false;
        }
        
        // 获取节流间隔
        $throttleInterval = $this->severitySettings[$severity]['throttle_interval'] ?? 3600;
        
        // 检查是否在节流时间窗口内
        $timeSinceLastAlert = time() - $existingAlert['timestamp'];
        
        return $timeSinceLastAlert < $throttleInterval;
    }

    /**
     * 清理过期的告警缓存
     */
    public function cleanupAlertCache(): void
    {
        $now = time();
        $expiredIds = [];
        
        foreach ($this->alertCache as $alertId => $alert) {
            // 已解决的告警保留一段时间后删除
            if ($alert['resolved'] && isset($alert['resolved_at'])) {
                if ($now - $alert['resolved_at'] > 86400) { // 保留24小时
                    $expiredIds[] = $alertId;
                }
            } else {
                // 未解决的告警，检查是否超过最大缓存时间
                if ($now - $alert['timestamp'] > 604800) { // 保留7天
                    $expiredIds[] = $alertId;
                }
            }
        }
        
        foreach ($expiredIds as $alertId) {
            unset($this->alertCache[$alertId]);
        }
        
        if (count($expiredIds) > 0) {
            $this->logger->debug("清理过期告警缓存", [
                'cleaned_count' => count($expiredIds),
            ]);
        }
    }
} 