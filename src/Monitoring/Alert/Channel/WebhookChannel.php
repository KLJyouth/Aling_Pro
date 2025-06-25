<?php
namespace AlingAi\Monitoring\Alert\Channel;

use Psr\Log\LoggerInterface;

/**
 * Webhook告警通道 - 通过HTTP请求发送告警
 */
class WebhookChannel implements AlertChannelInterface
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
        $endpoints = $this->getEndpoints($alert);
        
        if (empty($endpoints)) {
            $this->logger->warning("没有配置Webhook端点", [
                'alert_id' => $alert['id'],
            ]);
            
            return false;
        }
        
        $payload = $this->formatPayload($alert);
        
        return $this->sendWebhook($endpoints, $payload, $alert);
    }

    /**
     * {@inheritdoc}
     */
    public function sendResolution(array $alert): bool
    {
        $endpoints = $this->getEndpoints($alert);
        
        if (empty($endpoints)) {
            return false;
        }
        
        $payload = $this->formatResolutionPayload($alert);
        
        return $this->sendWebhook($endpoints, $payload, $alert);
    }

    /**
     * 获取Webhook端点列表
     */
    private function getEndpoints(array $alert): array
    {
        $endpoints = $this->config['endpoints'] ?? [];
        
        // 根据告警严重级别获取特定端点
        $severity = $alert['severity'];
        if (isset($this->config['severity_endpoints'][$severity])) {
            $endpoints = array_merge($endpoints, $this->config['severity_endpoints'][$severity]);
        }
        
        return array_unique($endpoints);
    }

    /**
     * 格式化Webhook负载
     */
    private function formatPayload(array $alert): array
    {
        return [
            'id' => $alert['id'],
            'title' => $alert['title'],
            'message' => $alert['message'],
            'severity' => $alert['severity'],
            'timestamp' => $alert['timestamp'],
            'metadata' => $alert['metadata'],
            'status' => 'firing',
        ];
    }

    /**
     * 格式化告警解决Webhook负载
     */
    private function formatResolutionPayload(array $alert): array
    {
        return [
            'id' => $alert['id'],
            'title' => $alert['title'],
            'message' => $alert['message'],
            'severity' => $alert['severity'],
            'timestamp' => $alert['timestamp'],
            'resolved_at' => $alert['resolved_at'] ?? time(),
            'metadata' => $alert['metadata'],
            'status' => 'resolved',
        ];
    }

    /**
     * 发送Webhook请求
     */
    private function sendWebhook(array $endpoints, array $payload, array $alert): bool
    {
        $success = true;
        $headers = [
            'Content-Type: application/json',
            'User-Agent: AlingAi-Monitoring/1.0',
        ];
        
        // 添加自定义请求头
        if (isset($this->config['headers']) && is_array($this->config['headers'])) {
            foreach ($this->config['headers'] as $name => $value) {
                $headers[] = "{$name}: {$value}";
            }
        }
        
        foreach ($endpoints as $endpoint) {
            try {
                $this->logger->info("发送Webhook告警", [
                    'alert_id' => $alert['id'],
                    'endpoint' => $endpoint,
                ]);
                
                $ch = curl_init($endpoint);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_TIMEOUT, 10);
                
                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
                
                if ($httpCode < 200 || $httpCode >= 300) {
                    $this->logger->error("Webhook请求失败", [
                        'alert_id' => $alert['id'],
                        'endpoint' => $endpoint,
                        'http_code' => $httpCode,
                        'response' => $response,
                    ]);
                    
                    $success = false;
                }
            } catch (\Exception $e) {
                $this->logger->error("Webhook请求异常", [
                    'alert_id' => $alert['id'],
                    'endpoint' => $endpoint,
                    'error' => $e->getMessage(),
                ]);
                
                $success = false;
            }
        }
        
        return $success;
    }
} 