<?php

namespace AlingAi\Services;

use AlingAi\Config\EnhancedConfig;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

/**
 * 增强AI服务
 * 支持DeepSeek和百度文心一言API
 */
class EnhancedAIService
{
    private static $instance = null;
    private $config;
    private $httpClient;
    private $dbService;

    private function __construct()
    {
        $this->config = EnhancedConfig::getInstance();
        $this->httpClient = new Client(['timeout' => 30]);
        $this->dbService = EnhancedDatabaseService::getInstance();
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * 发送聊天消息（主要接口）
     */
    public function chat(string $message, array $context = [], string $provider = 'auto'): array
    {
        try {
            // 自动选择提供商
            if ($provider === 'auto') {
                $provider = $this->selectBestProvider();
            }

            // 记录对话开始
            $conversationId = $this->logConversationStart($message, $provider);

            // 根据提供商发送请求
            switch ($provider) {
                case 'deepseek':
                    $response = $this->chatWithDeepSeek($message, $context);
                    break;
                case 'baidu':
                    $response = $this->chatWithBaidu($message, $context);
                    break;
                default:
                    throw new \Exception("不支持的AI提供商: {$provider}");
            }

            // 记录对话结果
            $this->logConversationEnd($conversationId, $response);

            return [
                'success' => true,
                'provider' => $provider,
                'response' => $response['content'],
                'usage' => $response['usage'] ?? [],
                'conversation_id' => $conversationId,
                'timestamp' => date('Y-m-d H:i:s')
            ];

        } catch (\Exception $e) {
            $this->logError('AI聊天失败', $e->getMessage(), [
                'message' => $message,
                'provider' => $provider
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'provider' => $provider,
                'timestamp' => date('Y-m-d H:i:s')
            ];
        }
    }

    /**
     * DeepSeek聊天
     */
    private function chatWithDeepSeek(string $message, array $context = []): array
    {
        $config = $this->config->get('ai.deepseek');
        
        if (empty($config['api_key'])) {
            throw new \Exception('DeepSeek API密钥未配置');
        }

        // 构建消息历史
        $messages = [];
        
        // 添加系统提示
        $messages[] = [
            'role' => 'system',
            'content' => $this->getSystemPrompt()
        ];

        // 添加上下文
        foreach ($context as $msg) {
            $messages[] = [
                'role' => $msg['role'] ?? 'user',
                'content' => $msg['content']
            ];
        }

        // 添加当前消息
        $messages[] = [
            'role' => 'user',
            'content' => $message
        ];

        $payload = [
            'model' => $config['model'],
            'messages' => $messages,
            'max_tokens' => $config['max_tokens'],
            'temperature' => $config['temperature'],
            'stream' => false
        ];

        try {
            $response = $this->httpClient->post($config['api_url'] . '/chat/completions', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $config['api_key'],
                    'Content-Type' => 'application/json',
                ],
                'json' => $payload
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            if (isset($data['error'])) {
                throw new \Exception('DeepSeek API错误: ' . $data['error']['message']);
            }

            return [
                'content' => $data['choices'][0]['message']['content'] ?? '抱歉，我无法生成回复。',
                'usage' => $data['usage'] ?? [],
                'model' => $data['model'] ?? $config['model']
            ];

        } catch (RequestException $e) {
            throw new \Exception('DeepSeek API请求失败: ' . $e->getMessage());
        }
    }

    /**
     * 百度文心一言聊天
     */
    private function chatWithBaidu(string $message, array $context = []): array
    {
        $config = $this->config->get('ai.baidu');
        
        if (empty($config['api_key']) || empty($config['secret_key'])) {
            throw new \Exception('百度API密钥未配置');
        }

        // 获取访问令牌
        $accessToken = $this->getBaiduAccessToken();

        // 构建消息
        $messages = [];
        
        // 添加上下文
        foreach ($context as $msg) {
            $messages[] = [
                'role' => $msg['role'] ?? 'user',
                'content' => $msg['content']
            ];
        }

        // 添加当前消息
        $messages[] = [
            'role' => 'user',
            'content' => $message
        ];

        $payload = [
            'messages' => $messages,
            'temperature' => 0.7,
            'top_p' => 0.8,
            'penalty_score' => 1.0,
            'stream' => false,
            'system' => $this->getSystemPrompt()
        ];

        try {
            $url = 'https://aip.baidubce.com/rpc/2.0/ai_custom/v1/wenxinworkshop/chat/completions?access_token=' . $accessToken;
            
            $response = $this->httpClient->post($url, [
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'json' => $payload
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            if (isset($data['error_code'])) {
                throw new \Exception('百度API错误: ' . $data['error_msg']);
            }

            return [
                'content' => $data['result'] ?? '抱歉，我无法生成回复。',
                'usage' => [
                    'prompt_tokens' => $data['usage']['prompt_tokens'] ?? 0,
                    'completion_tokens' => $data['usage']['completion_tokens'] ?? 0,
                    'total_tokens' => $data['usage']['total_tokens'] ?? 0
                ],
                'model' => 'ernie-bot'
            ];

        } catch (RequestException $e) {
            throw new \Exception('百度API请求失败: ' . $e->getMessage());
        }
    }

    /**
     * 获取百度访问令牌
     */
    private function getBaiduAccessToken(): string
    {
        $cacheKey = 'baidu_access_token';
        $token = $this->dbService->cacheGet($cacheKey);

        if ($token) {
            return $token;
        }

        $config = $this->config->get('ai.baidu');
        
        try {
            $response = $this->httpClient->post('https://aip.baidubce.com/oauth/2.0/token', [
                'form_params' => [
                    'grant_type' => 'client_credentials',
                    'client_id' => $config['api_key'],
                    'client_secret' => $config['secret_key']
                ]
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            if (isset($data['error'])) {
                throw new \Exception('获取百度访问令牌失败: ' . $data['error_description']);
            }

            $token = $data['access_token'];
            $expiresIn = $data['expires_in'] - 300; // 提前5分钟过期

            // 缓存访问令牌
            $this->dbService->cacheSet($cacheKey, $token, $expiresIn);

            return $token;

        } catch (RequestException $e) {
            throw new \Exception('获取百度访问令牌失败: ' . $e->getMessage());
        }
    }

    /**
     * 选择最佳提供商
     */
    private function selectBestProvider(): string
    {
        // 检查提供商状态
        $providers = ['deepseek', 'baidu'];
        $availableProviders = [];

        foreach ($providers as $provider) {
            if ($this->isProviderAvailable($provider)) {
                $availableProviders[] = $provider;
            }
        }

        if (empty($availableProviders)) {
            throw new \Exception('没有可用的AI提供商');
        }

        // 根据负载均衡选择
        return $this->selectProviderByLoad($availableProviders);
    }

    /**
     * 检查提供商是否可用
     */
    private function isProviderAvailable(string $provider): bool
    {
        switch ($provider) {
            case 'deepseek':
                return !empty($this->config->get('ai.deepseek.api_key'));
            case 'baidu':
                return !empty($this->config->get('ai.baidu.api_key')) && 
                       !empty($this->config->get('ai.baidu.secret_key'));
            default:
                return false;
        }
    }

    /**
     * 根据负载选择提供商
     */
    private function selectProviderByLoad(array $providers): string
    {
        // 简单的轮询策略
        $cacheKey = 'ai_provider_index';
        $index = $this->dbService->cacheGet($cacheKey) ?? 0;
        $index = ($index + 1) % count($providers);
        $this->dbService->cacheSet($cacheKey, $index, 300);
        
        return $providers[$index];
    }

    /**
     * 获取系统提示
     */
    private function getSystemPrompt(): string
    {
        return "你是AlingAi Pro的智能助手，一个专业、友好、有帮助的AI助手。你的特点是：
1. 专业知识丰富，能够在各个领域提供准确的信息和建议
2. 友好耐心，善于倾听用户需求并提供个性化的帮助
3. 逻辑清晰，回答问题时条理分明，易于理解
4. 安全可靠，严格遵守用户隐私和数据安全原则
5. 持续学习，不断提升服务质量

请用简洁明了的中文回答用户问题，提供有价值的信息和建议。";
    }

    /**
     * 记录对话开始
     */
    private function logConversationStart(string $message, string $provider): string
    {
        $conversationId = uniqid('conv_');
        
        try {
            $this->dbService->insert('ai_conversations', [
                'conversation_id' => $conversationId,
                'user_message' => $message,
                'provider' => $provider,
                'status' => 'processing',
                'created_at' => date('Y-m-d H:i:s'),
                'user_ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
            ]);
        } catch (\Exception $e) {
            // 记录失败不影响主流程
            $this->logError('记录对话开始失败', $e->getMessage());
        }

        return $conversationId;
    }

    /**
     * 记录对话结束
     */
    private function logConversationEnd(string $conversationId, array $response): void
    {
        try {
            $this->dbService->update('ai_conversations', [
                'ai_response' => $response['content'],
                'usage_tokens' => json_encode($response['usage'] ?? []),
                'model' => $response['model'] ?? '',
                'status' => 'completed',
                'completed_at' => date('Y-m-d H:i:s')
            ], [
                'conversation_id' => $conversationId
            ]);
        } catch (\Exception $e) {
            $this->logError('记录对话结束失败', $e->getMessage());
        }
    }

    /**
     * 获取对话历史
     */
    public function getConversationHistory(int $limit = 50): array
    {
        try {
            return $this->dbService->fetchAll(
                "SELECT * FROM ai_conversations ORDER BY created_at DESC LIMIT ?",
                [$limit]
            );
        } catch (\Exception $e) {
            $this->logError('获取对话历史失败', $e->getMessage());
            return [];
        }
    }

    /**
     * 获取使用统计
     */
    public function getUsageStats(string $period = 'today'): array
    {
        try {
            $whereClause = '';
            $params = [];

            switch ($period) {
                case 'today':
                    $whereClause = 'WHERE DATE(created_at) = CURDATE()';
                    break;
                case 'week':
                    $whereClause = 'WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)';
                    break;
                case 'month':
                    $whereClause = 'WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)';
                    break;
            }

            $stats = $this->dbService->fetchOne("
                SELECT 
                    COUNT(*) as total_conversations,
                    COUNT(CASE WHEN provider = 'deepseek' THEN 1 END) as deepseek_count,
                    COUNT(CASE WHEN provider = 'baidu' THEN 1 END) as baidu_count,
                    COUNT(CASE WHEN status = 'completed' THEN 1 END) as successful_count,
                    COUNT(CASE WHEN status = 'failed' THEN 1 END) as failed_count,
                    AVG(TIMESTAMPDIFF(SECOND, created_at, completed_at)) as avg_response_time
                FROM ai_conversations 
                {$whereClause}
            ", $params);

            return $stats ?: [
                'total_conversations' => 0,
                'deepseek_count' => 0,
                'baidu_count' => 0,
                'successful_count' => 0,
                'failed_count' => 0,
                'avg_response_time' => 0
            ];

        } catch (\Exception $e) {
            $this->logError('获取使用统计失败', $e->getMessage());
            return [];
        }
    }

    /**
     * 健康检查
     */
    public function healthCheck(): array
    {
        $status = [
            'deepseek' => false,
            'baidu' => false,
            'timestamp' => date('Y-m-d H:i:s')
        ];

        // DeepSeek健康检查
        try {
            if ($this->isProviderAvailable('deepseek')) {
                $response = $this->chatWithDeepSeek('Hi', []);
                $status['deepseek'] = !empty($response['content']);
            }
        } catch (\Exception $e) {
            $this->logError('DeepSeek健康检查失败', $e->getMessage());
        }

        // 百度健康检查
        try {
            if ($this->isProviderAvailable('baidu')) {
                $response = $this->chatWithBaidu('Hi', []);
                $status['baidu'] = !empty($response['content']);
            }
        } catch (\Exception $e) {
            $this->logError('百度健康检查失败', $e->getMessage());
        }

        return $status;
    }

    /**
     * 记录错误日志
     */
    private function logError(string $message, string $error, array $context = []): void
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
     * 获取健康状态（API兼容方法）
     */
    public function getHealthStatus(): array
    {
        return $this->healthCheck();
    }

    /**
     * 获取使用统计（API兼容方法）
     */
    public function getUsageStatistics(int $userId, string $period = 'today', string $provider = 'all'): array
    {
        try {
            $whereClause = '';
            $params = [];

            // 添加用户过滤
            $whereClause .= 'WHERE user_id = :user_id';
            $params['user_id'] = $userId;

            // 添加时间过滤
            switch ($period) {
                case 'today':
                    $whereClause .= ' AND DATE(created_at) = CURDATE()';
                    break;
                case 'week':
                    $whereClause .= ' AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)';
                    break;
                case 'month':
                    $whereClause .= ' AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)';
                    break;
            }

            // 添加提供商过滤
            if ($provider !== 'all') {
                $whereClause .= ' AND provider = :provider';
                $params['provider'] = $provider;
            }

            $stats = $this->dbService->fetchOne("
                SELECT 
                    COUNT(*) as total_conversations,
                    COUNT(CASE WHEN provider = 'deepseek' THEN 1 END) as deepseek_count,
                    COUNT(CASE WHEN provider = 'baidu' THEN 1 END) as baidu_count,
                    COUNT(CASE WHEN status = 'completed' THEN 1 END) as successful_count,
                    COUNT(CASE WHEN status = 'failed' THEN 1 END) as failed_count,
                    AVG(TIMESTAMPDIFF(SECOND, created_at, completed_at)) as avg_response_time,
                    SUM(JSON_EXTRACT(usage_tokens, '$.total_tokens')) as total_tokens_used
                FROM ai_conversations 
                {$whereClause}
            ", $params);

            return $stats ?: [
                'total_conversations' => 0,
                'deepseek_count' => 0,
                'baidu_count' => 0,
                'successful_count' => 0,
                'failed_count' => 0,
                'avg_response_time' => 0,
                'total_tokens_used' => 0
            ];

        } catch (\Exception $e) {
            $this->logError('获取用户使用统计失败', $e->getMessage());
            return [];
        }
    }
}
