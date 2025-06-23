<?php

declare(strict_types=1);

namespace AlingAi\Services;

use AlingAi\Services\Interfaces\AIServiceInterface;
use Psr\Log\LoggerInterface;

/**
 * DeepSeek AI服务实现
 * 专注于API调用，不处理数据库和会话管理
 */
class DeepSeekAIService implements AIServiceInterface
{
    private array $config;
    private LoggerInterface $logger;
    private string $apiKey;
    private string $apiUrl;

    public function __construct(array $config = [], ?LoggerInterface $logger = null)
    {
        $this->config = array_merge($this->getDefaultConfig(), $config);
        $this->logger = $logger ?? $this->createDefaultLogger();
        $this->apiKey = $this->config['api_key'];
        $this->apiUrl = $this->config['api_url'];
    }

    /**
     * 获取AI完成响应
     */
    public function getCompletion(array $messages, array $options = []): array
    {
        try {
            $this->logger->info('开始AI调用', [
                'service' => $this->getServiceName(),
                'message_count' => count($messages)
            ]);

            $requestData = [
                'model' => $options['model'] ?? $this->config['model'],
                'messages' => $messages,
                'max_tokens' => $options['max_tokens'] ?? $this->config['max_tokens'],
                'temperature' => $options['temperature'] ?? $this->config['temperature'],
                'stream' => false
            ];

            $response = $this->makeApiRequest($requestData);

            if (!$response['success']) {
                throw new \Exception($response['error'] ?? 'AI调用失败');
            }

            $this->logger->info('AI调用成功', [
                'service' => $this->getServiceName(),
                'usage' => $response['data']['usage'] ?? []
            ]);

            return [
                'success' => true,
                'data' => [
                    'content' => $response['data']['choices'][0]['message']['content'] ?? '',
                    'model' => $response['data']['model'] ?? '',
                    'finish_reason' => $response['data']['choices'][0]['finish_reason'] ?? ''
                ],
                'usage' => $response['data']['usage'] ?? []
            ];

        } catch (\Exception $e) {
            $this->logger->error('AI调用失败', [
                'service' => $this->getServiceName(),
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'data' => null,
                'usage' => []
            ];
        }
    }

    /**
     * 健康检查
     */
    public function healthCheck(): array
    {
        try {
            $testMessages = [['role' => 'user', 'content' => 'Hello']];
            $result = $this->getCompletion($testMessages, ['max_tokens' => 10]);

            return [
                'success' => $result['success'],
                'status' => $result['success'] ? 'healthy' : 'unhealthy',
                'details' => [
                    'service' => $this->getServiceName(),
                    'api_url' => $this->apiUrl,
                    'model' => $this->config['model'],
                    'error' => $result['error'] ?? null
                ]
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'status' => 'unhealthy',
                'details' => [
                    'service' => $this->getServiceName(),
                    'error' => $e->getMessage()
                ]
            ];
        }
    }

    /**
     * 获取服务配置
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * 设置服务配置
     */
    public function setConfig(array $config): void
    {
        $this->config = array_merge($this->config, $config);
        $this->apiKey = $this->config['api_key'];
        $this->apiUrl = $this->config['api_url'];
    }

    /**
     * 获取服务名称
     */
    public function getServiceName(): string
    {
        return 'DeepSeek AI';
    }

    /**
     * 检查服务是否可用
     */
    public function isAvailable(): bool
    {
        $health = $this->healthCheck();
        return $health['success'];
    }

    /**
     * 发送API请求
     */
    private function makeApiRequest(array $data): array
    {
        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->apiKey
        ];

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $this->apiUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => true
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new \Exception("cURL错误: {$error}");
        }

        if ($httpCode !== 200) {
            throw new \Exception("API请求失败，HTTP状态码: {$httpCode}");
        }

        $responseData = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception("JSON解析失败: " . json_last_error_msg());
        }

        return [
            'success' => true,
            'data' => $responseData
        ];
    }

    /**
     * 获取默认配置
     */
    private function getDefaultConfig(): array
    {
        return [
            'api_key' => getenv('DEEPSEEK_API_KEY') ?: '',
            'api_url' => getenv('DEEPSEEK_API_URL') ?: 'https://api.deepseek.com/v1/chat/completions',
            'model' => getenv('DEEPSEEK_MODEL') ?: 'deepseek-chat',
            'max_tokens' => 2048,
            'temperature' => 0.7
        ];
    }

    /**
     * 创建默认日志记录器
     */
    private function createDefaultLogger(): LoggerInterface
    {
        return new class implements LoggerInterface {
            public function emergency($message, array $context = []): void {}
            public function alert($message, array $context = []): void {}
            public function critical($message, array $context = []): void {}
            public function error($message, array $context = []): void {}
            public function warning($message, array $context = []): void {}
            public function notice($message, array $context = []): void {}
            public function info($message, array $context = []): void {}
            public function debug($message, array $context = []): void {}
            public function log($level, $message, array $context = []): void {}
        };
    }
}
