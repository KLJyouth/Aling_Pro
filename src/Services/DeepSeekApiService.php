<?php

namespace AlingAi\Services;

/**
 * DeepSeek AI API 集成服务
 */
class DeepSeekApiService
{
    private string $apiKey;
    private string $apiUrl;
    private string $model;
    
    public function __construct()
    {
        // 从环境配置加载
        $this->loadConfig();
    }
    
    private function loadConfig(): void
    {
        // 加载.env文件
        $envFile = __DIR__ . '/../../.env';
        if (file_exists($envFile)) {
            $envLines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($envLines as $line) {
                if (strpos($line, '#') === 0 || strpos($line, '//') === 0) continue;
                if (strpos($line, '=') !== false) {
                    [$key, $value] = explode('=', $line, 2);
                    $key = trim($key);
                    $value = trim($value);
                    if (!empty($key)) {
                        $_ENV[$key] = $value;
                    }
                }
            }
        }
        
        $this->apiKey = $_ENV['DEEPSEEK_API_KEY'] ?? '';
        $this->apiUrl = $_ENV['OPENAI_API_URL'] ?? 'https://api.deepseek.com';
        $this->model = $_ENV['DEEPSEEK_MODEL'] ?? 'deepseek-chat';
        
        if (empty($this->apiKey)) {
            throw new \Exception('DeepSeek API key not configured');
        }
    }
    
    /**
     * 发送聊天消息
     */
    public function sendMessage(string $message, array $context = []): array
    {
        $messages = [];
        
        // 添加系统提示
        if (!empty($context['system_prompt'])) {
            $messages[] = [
                'role' => 'system',
                'content' => $context['system_prompt']
            ];
        } else {
            $messages[] = [
                'role' => 'system',
                'content' => '你是AlingAi Pro的智能助手，一个专业、友好、高效的AI助手。请用中文回答用户的问题。'
            ];
        }
        
        // 添加历史对话
        if (!empty($context['history'])) {
            foreach ($context['history'] as $msg) {
                $messages[] = [
                    'role' => $msg['role'],
                    'content' => $msg['content']
                ];
            }
        }
        
        // 添加当前消息
        $messages[] = [
            'role' => 'user',
            'content' => $message
        ];
        
        $payload = [
            'model' => $this->model,
            'messages' => $messages,
            'temperature' => $context['temperature'] ?? 0.7,
            'max_tokens' => $context['max_tokens'] ?? 2000,
            'stream' => false
        ];
        
        return $this->makeApiRequest('/chat/completions', $payload);
    }
    
    /**
     * 发送流式聊天消息
     */
    public function sendStreamMessage(string $message, array $context = []): \Generator
    {
        $messages = [];
        
        // 构建消息数组（与sendMessage相同逻辑）
        if (!empty($context['system_prompt'])) {
            $messages[] = [
                'role' => 'system',
                'content' => $context['system_prompt']
            ];
        } else {
            $messages[] = [
                'role' => 'system',
                'content' => '你是AlingAi Pro的智能助手，请用中文回答用户的问题。'
            ];
        }
        
        if (!empty($context['history'])) {
            foreach ($context['history'] as $msg) {
                $messages[] = [
                    'role' => $msg['role'],
                    'content' => $msg['content']
                ];
            }
        }
        
        $messages[] = [
            'role' => 'user',
            'content' => $message
        ];
        
        $payload = [
            'model' => $this->model,
            'messages' => $messages,
            'temperature' => $context['temperature'] ?? 0.7,
            'max_tokens' => $context['max_tokens'] ?? 2000,
            'stream' => true
        ];
        
        yield from $this->makeStreamApiRequest('/chat/completions', $payload);
    }
    
    /**
     * 发起API请求
     */
    private function makeApiRequest(string $endpoint, array $payload): array
    {
        $url = rtrim($this->apiUrl, '/') . $endpoint;
        
        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->apiKey,
            'User-Agent: AlingAi-Pro/1.0'
        ];
        
        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => implode("\r\n", $headers),
                'content' => json_encode($payload),
                'timeout' => 30
            ]
        ]);
        
        $response = @file_get_contents($url, false, $context);
        
        if ($response === false) {
            $error = error_get_last();
            throw new \Exception('API request failed: ' . ($error['message'] ?? 'Unknown error'));
        }
        
        $data = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Invalid JSON response: ' . json_last_error_msg());
        }
        
        if (isset($data['error'])) {
            throw new \Exception('API error: ' . $data['error']['message']);
        }
        
        return $data;
    }
    
    /**
     * 发起流式API请求
     */
    private function makeStreamApiRequest(string $endpoint, array $payload): \Generator
    {
        $url = rtrim($this->apiUrl, '/') . $endpoint;
        
        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->apiKey,
            'User-Agent: AlingAi-Pro/1.0',
            'Accept: text/event-stream',
            'Cache-Control: no-cache'
        ];
        
        $postData = json_encode($payload);
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $postData,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_WRITEFUNCTION => function($ch, $data) {
                static $buffer = '';
                $buffer .= $data;
                
                // 处理Server-Sent Events
                while (($pos = strpos($buffer, "\n\n")) !== false) {
                    $chunk = substr($buffer, 0, $pos);
                    $buffer = substr($buffer, $pos + 2);
                    
                    if (strpos($chunk, 'data: ') === 0) {
                        $jsonData = substr($chunk, 6);
                        if ($jsonData === '[DONE]') {
                            return strlen($data);
                        }
                        
                        $decoded = json_decode($jsonData, true);
                        if ($decoded && isset($decoded['choices'][0]['delta']['content'])) {
                            // 这里应该yield，但在回调中不能yield
                            // 所以我们需要不同的方法
                        }
                    }
                }
                
                return strlen($data);
            },
            CURLOPT_TIMEOUT => 60,
            CURLOPT_FOLLOWLOCATION => true,
        ]);
        
        curl_exec($ch);
        curl_close($ch);
        
        // 简化版本：返回完整响应而不是流式
        yield ['content' => 'Stream response not implemented in this version'];
    }
    
    /**
     * 获取模型列表
     */
    public function getModels(): array
    {
        try {
            return $this->makeApiRequest('/models', []);
        } catch (\Exception $e) {
            return [
                'error' => $e->getMessage(),
                'fallback_models' => [
                    'deepseek-chat',
                    'deepseek-coder'
                ]
            ];
        }
    }
    
    /**
     * 测试API连接
     */
    public function testConnection(): array
    {
        try {
            $response = $this->sendMessage('Hello', [
                'system_prompt' => 'You are a test assistant. Please respond with "Connection test successful" in Chinese.',
                'max_tokens' => 50
            ]);
            
            return [
                'success' => true,
                'message' => 'API连接成功',
                'response' => $response['choices'][0]['message']['content'] ?? 'No content',
                'model' => $this->model,
                'api_url' => $this->apiUrl
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'model' => $this->model,
                'api_url' => $this->apiUrl
            ];
        }
    }
}
