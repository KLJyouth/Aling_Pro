<?php
/**
 * 简单API控制器 - 不依赖复杂服务
 */

declare(strict_types=1);

namespace AlingAi\Controllers;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class SimpleApiController extends BaseController
{
    /**
     * 用户信息端点
     */
    public function userInfo(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            // 模拟用户信息
            $userInfo = [
                'id' => 1,
                'username' => 'demo_user',
                'email' => 'demo@example.com',
                'created_at' => '2024-01-01 00:00:00',
                'last_login' => date('Y-m-d H:i:s'),
                'profile' => [
                    'display_name' => 'Demo User',
                    'avatar' => null,
                    'bio' => '这是一个演示用户',
                    'location' => '中国',
                    'website' => ''
                ],
                'settings' => [
                    'language' => 'zh-cn',
                    'timezone' => 'Asia/Shanghai',
                    'theme' => 'light',
                    'notifications' => true
                ],
                'statistics' => [
                    'total_chats' => 42,
                    'total_messages' => 256,
                    'last_activity' => date('Y-m-d H:i:s')
                ]
            ];

            return $this->successResponse($response, $userInfo);

        } catch (\Exception $e) {
            return $this->errorResponse($response, '获取用户信息失败: ' . $e->getMessage(), 500);
        }
    }

    /**
     * 系统设置端点
     */
    public function getSettings(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $settings = [
                'system' => [
                    'name' => 'AlingAi Pro',
                    'version' => '2.0.0',
                    'environment' => $_ENV['APP_ENV'] ?? 'production',
                    'debug_mode' => $_ENV['APP_DEBUG'] ?? false,
                    'maintenance_mode' => false
                ],
                'features' => [
                    'chat_enabled' => true,
                    'ai_enabled' => true,
                    'file_upload' => true,
                    'multi_language' => true,
                    'real_time' => true,
                    'advanced_ui' => true
                ],
                'limits' => [
                    'max_message_length' => 8000,
                    'max_file_size' => '50MB',
                    'daily_requests' => 10000,
                    'concurrent_connections' => 100
                ],
                'ai_settings' => [
                    'default_model' => 'gpt-3.5-turbo',
                    'available_models' => ['gpt-3.5-turbo', 'claude-3', 'gemini-pro'],
                    'max_tokens' => 4000,
                    'temperature' => 0.7
                ],
                'ui_settings' => [
                    'theme_options' => ['light', 'dark', 'auto'],
                    'language_options' => ['zh-cn', 'en-us', 'ja', 'ko'],
                    'animation_enabled' => true,
                    'sound_enabled' => true
                ]
            ];

            return $this->successResponse($response, $settings);

        } catch (\Exception $e) {
            return $this->errorResponse($response, '获取系统设置失败: ' . $e->getMessage(), 500);
        }
    }

    /**
     * 聊天发送端点
     */
    public function sendChatMessage(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $data = $request->getParsedBody();
            
            if (!isset($data['message']) || empty(trim($data['message']))) {
                return $this->errorResponse($response, '消息内容不能为空', 400);
            }

            $message = trim($data['message']);
            $conversationId = $data['conversation_id'] ?? null;

            // 验证消息长度
            if (strlen($message) > 8000) {
                return $this->errorResponse($response, '消息长度超过限制', 400);
            }

            // 模拟AI响应
            $aiResponse = $this->generateMockAIResponse($message);

            $result = [
                'status' => 'success',
                'message_id' => uniqid('msg_'),
                'user_message' => $message,
                'ai_response' => $aiResponse,
                'timestamp' => date('c'),
                'conversation_id' => $conversationId ?? uniqid('conv_'),
                'response_time_ms' => rand(800, 2000)
            ];

            return $this->successResponse($response, $result);

        } catch (\Exception $e) {
            return $this->errorResponse($response, '发送消息失败: ' . $e->getMessage(), 500);
        }
    }

    /**
     * AI模型端点
     */
    public function getAIModels(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $models = [
                [
                    'id' => 'gpt-3.5-turbo',
                    'name' => 'GPT-3.5 Turbo',
                    'provider' => 'OpenAI',
                    'type' => 'chat',
                    'status' => 'active',
                    'max_tokens' => 4096,
                    'cost_per_1k_tokens' => 0.002,
                    'features' => ['conversational', 'creative', 'analytical']
                ],
                [
                    'id' => 'claude-3',
                    'name' => 'Claude 3',
                    'provider' => 'Anthropic',
                    'type' => 'chat',
                    'status' => 'active',
                    'max_tokens' => 8192,
                    'cost_per_1k_tokens' => 0.003,
                    'features' => ['reasoning', 'code', 'analysis']
                ],
                [
                    'id' => 'gemini-pro',
                    'name' => 'Gemini Pro',
                    'provider' => 'Google',
                    'type' => 'multimodal',
                    'status' => 'active',
                    'max_tokens' => 4096,
                    'cost_per_1k_tokens' => 0.001,
                    'features' => ['multimodal', 'vision', 'code']
                ]
            ];

            $result = [
                'models' => $models,
                'default_model' => 'gpt-3.5-turbo',
                'total_count' => count($models),
                'timestamp' => date('c')
            ];

            return $this->successResponse($response, $result);

        } catch (\Exception $e) {
            return $this->errorResponse($response, '获取AI模型失败: ' . $e->getMessage(), 500);
        }
    }

    /**
     * 文件上传端点
     */
    public function uploadFile(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            // 模拟文件上传
            $uploadResult = [
                'status' => 'success',
                'file_id' => uniqid('file_'),
                'filename' => 'uploaded_file.txt',
                'size' => rand(1024, 1048576),
                'type' => 'text/plain',
                'url' => '/uploads/' . uniqid() . '.txt',
                'upload_time' => date('c'),
                'user_id' => 1
            ];

            return $this->successResponse($response, $uploadResult);

        } catch (\Exception $e) {
            return $this->errorResponse($response, '文件上传失败: ' . $e->getMessage(), 500);
        }
    }

    /**
     * 生成模拟AI响应
     */
    private function generateMockAIResponse(string $message): string
    {
        $responses = [
            '您好！我是AlingAi，很高兴为您服务。我理解您的问题，让我为您详细解答。',
            '这是一个很有趣的问题！基于我的理解，我可以为您提供以下建议...',
            '感谢您的提问。根据您的描述，我认为可以从以下几个方面来考虑...',
            '我明白您的意思。这个问题确实需要仔细分析，让我为您逐步解释...',
            '很好的问题！根据我的知识库，我可以为您提供一些有用的信息...'
        ];
        
        return $responses[array_rand($responses)] . "\n\n针对您提到的「" . mb_substr($message, 0, 50) . "」，我建议您可以进一步探讨相关的技术细节和实现方案。";
    }
}
