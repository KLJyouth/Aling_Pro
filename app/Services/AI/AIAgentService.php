<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Exception;

/**
 * AI智能体服务类
 * 
 * 用于管理智能体接口和调用
 */
class AIAgentService
{
    /**
     * 获取所有智能体
     *
     * @param bool $onlyActive 是否只返回激活的智能体
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllAgents($onlyActive = false)
    {
        $query = DB::table('ai_agents')
            ->whereNull('deleted_at')
            ->orderBy('sort_order', 'asc');

        if ($onlyActive) {
            $query->where('is_active', true);
        }

        return $query->get();
    }

    /**
     * 获取单个智能体
     *
     * @param int|string $id 智能体ID或代码
     * @return object|null
     */
    public function getAgent($id)
    {
        if (is_numeric($id)) {
            return DB::table('ai_agents')
                ->where('id', $id)
                ->whereNull('deleted_at')
                ->first();
        } else {
            return DB::table('ai_agents')
                ->where('code', $id)
                ->whereNull('deleted_at')
                ->first();
        }
    }

    /**
     * 创建智能体
     *
     * @param array $data 智能体数据
     * @return int 新创建的智能体ID
     */
    public function createAgent(array $data)
    {
        // 处理JSON字段
        foreach (['capabilities', 'parameters', 'config'] as $field) {
            if (isset($data[$field]) && is_array($data[$field])) {
                $data[$field] = json_encode($data[$field]);
            }
        }

        $data['created_at'] = now();
        $data['updated_at'] = now();

        return DB::table('ai_agents')->insertGetId($data);
    }

    /**
     * 更新智能体
     *
     * @param int $id 智能体ID
     * @param array $data 更新数据
     * @return bool
     */
    public function updateAgent($id, array $data)
    {
        // 处理JSON字段
        foreach (['capabilities', 'parameters', 'config'] as $field) {
            if (isset($data[$field]) && is_array($data[$field])) {
                $data[$field] = json_encode($data[$field]);
            }
        }

        $data['updated_at'] = now();

        return DB::table('ai_agents')
            ->where('id', $id)
            ->update($data);
    }

    /**
     * 删除智能体（软删除）
     *
     * @param int $id 智能体ID
     * @return bool
     */
    public function deleteAgent($id)
    {
        return DB::table('ai_agents')
            ->where('id', $id)
            ->update([
                'deleted_at' => now(),
                'updated_at' => now()
            ]);
    }

    /**
     * 激活/停用智能体
     *
     * @param int $id 智能体ID
     * @param bool $active 是否激活
     * @return bool
     */
    public function toggleAgent($id, $active = true)
    {
        return DB::table('ai_agents')
            ->where('id', $id)
            ->update([
                'is_active' => $active,
                'updated_at' => now()
            ]);
    }

    /**
     * 测试智能体连接
     *
     * @param string $agentCode 智能体代码
     * @param array $config 配置
     * @return array 测试结果
     */
    public function testAgentConnection($agentCode, array $config = [])
    {
        try {
            $agent = $this->getAgent($agentCode);
            if (!$agent) {
                return [
                    'success' => false,
                    'message' => '找不到指定的智能体'
                ];
            }

            switch ($agent->provider) {
                case 'koudi':
                    return $this->testKoudiAgent($agent, $config);
                case 'huawei':
                    return $this->testHuaweiAgent($agent, $config);
                case 'aliyun':
                    return $this->testTongyiAgent($agent, $config);
                default:
                    return [
                        'success' => false,
                        'message' => '不支持的智能体提供商'
                    ];
            }
        } catch (Exception $e) {
            Log::error('AI智能体测试失败', [
                'agent' => $agentCode,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'success' => false,
                'message' => '测试失败: ' . $e->getMessage()
            ];
        }
    }

    /**
     * 测试扣子智能体
     *
     * @param object $agent 智能体对象
     * @param array $config 配置
     * @return array 测试结果
     */
    private function testKoudiAgent($agent, array $config)
    {
        // 获取API密钥
        $apiKey = $config['api_key'] ?? $this->getAgentApiKey($agent->code);
        if (!$apiKey) {
            return [
                'success' => false,
                'message' => '缺少API密钥'
            ];
        }

        // 测试连接
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
            'Content-Type' => 'application/json'
        ])->post($agent->api_endpoint . '/chat', [
            'messages' => [
                ['role' => 'user', 'content' => '你好，这是一个测试消息']
            ],
            'model' => 'koudi-chat'
        ]);

        if ($response->successful()) {
            return [
                'success' => true,
                'message' => '连接成功',
                'data' => $response->json()
            ];
        }

        return [
            'success' => false,
            'message' => '连接失败: ' . $response->body(),
            'status' => $response->status()
        ];
    }

    /**
     * 测试华为智能体
     *
     * @param object $agent 智能体对象
     * @param array $config 配置
     * @return array 测试结果
     */
    private function testHuaweiAgent($agent, array $config)
    {
        // 华为智能体测试实现
        // 此处需要根据华为智能体的API文档实现
        return [
            'success' => true,
            'message' => '连接成功（模拟）'
        ];
    }

    /**
     * 测试通义智能体
     *
     * @param object $agent 智能体对象
     * @param array $config 配置
     * @return array 测试结果
     */
    private function testTongyiAgent($agent, array $config)
    {
        // 通义智能体测试实现
        // 此处需要根据通义智能体的API文档实现
        return [
            'success' => true,
            'message' => '连接成功（模拟）'
        ];
    }

    /**
     * 获取智能体API密钥
     *
     * @param string $agentCode 智能体代码
     * @return string|null
     */
    public function getAgentApiKey($agentCode)
    {
        $agent = $this->getAgent($agentCode);
        if (!$agent) {
            return null;
        }

        // 从缓存获取
        $cacheKey = "ai_agent_api_key:{$agentCode}";
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        // 根据提供商获取API密钥
        $apiKey = $this->getProviderApiKey($agent->provider);
        
        // 缓存API密钥
        if ($apiKey) {
            Cache::put($cacheKey, $apiKey, 3600); // 缓存1小时
        }
        
        return $apiKey;
    }

    /**
     * 获取提供商API密钥
     *
     * @param string $provider 提供商代码
     * @return string|null
     */
    private function getProviderApiKey($provider)
    {
        // 获取提供商ID
        $providerId = DB::table('ai_model_providers')
            ->where('code', $provider)
            ->value('id');

        if (!$providerId) {
            return null;
        }

        // 获取API密钥
        $apiKey = DB::table('ai_api_keys')
            ->where('provider_id', $providerId)
            ->where('is_active', true)
            ->whereNull('deleted_at')
            ->orderBy('id', 'desc')
            ->value('api_key');

        return $apiKey;
    }

    /**
     * 与智能体对话
     *
     * @param string $agentCode 智能体代码
     * @param string $message 消息内容
     * @param string|null $sessionId 会话ID
     * @param array $options 选项
     * @return array 响应结果
     */
    public function chatWithAgent($agentCode, $message, $sessionId = null, array $options = [])
    {
        try {
            $agent = $this->getAgent($agentCode);
            if (!$agent) {
                return [
                    'success' => false,
                    'message' => '找不到指定的智能体'
                ];
            }

            // 生成会话ID
            if (!$sessionId) {
                $sessionId = Str::uuid()->toString();
            }

            // 获取历史消息
            $history = $this->getSessionHistory($sessionId);

            // 添加当前消息
            $history[] = [
                'role' => 'user',
                'content' => $message
            ];

            // 调用对应的智能体
            switch ($agent->provider) {
                case 'koudi':
                    $result = $this->callKoudiAgent($agent, $history, $options);
                    break;
                case 'huawei':
                    $result = $this->callHuaweiAgent($agent, $history, $options);
                    break;
                case 'aliyun':
                    $result = $this->callTongyiAgent($agent, $history, $options);
                    break;
                default:
                    return [
                        'success' => false,
                        'message' => '不支持的智能体提供商'
                    ];
            }

            if ($result['success']) {
                // 添加响应到历史记录
                $history[] = [
                    'role' => 'assistant',
                    'content' => $result['data']['content']
                ];

                // 保存会话历史
                $this->saveSessionHistory($sessionId, $history);

                // 记录使用日志
                $this->logAgentUsage($agent, $message, $result['data'], $sessionId);
            }

            return $result;
        } catch (Exception $e) {
            Log::error('AI智能体调用失败', [
                'agent' => $agentCode,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'success' => false,
                'message' => '调用失败: ' . $e->getMessage()
            ];
        }
    }

    /**
     * 调用扣子智能体
     *
     * @param object $agent 智能体对象
     * @param array $history 历史消息
     * @param array $options 选项
     * @return array 响应结果
     */
    private function callKoudiAgent($agent, array $history, array $options)
    {
        // 获取API密钥
        $apiKey = $this->getAgentApiKey($agent->code);
        if (!$apiKey) {
            return [
                'success' => false,
                'message' => '缺少API密钥'
            ];
        }

        // 准备请求参数
        $params = [
            'messages' => $history,
            'model' => $options['model'] ?? 'koudi-chat',
            'temperature' => $options['temperature'] ?? 0.7,
            'max_tokens' => $options['max_tokens'] ?? 1000
        ];

        // 发送请求
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
            'Content-Type' => 'application/json'
        ])->post($agent->api_endpoint . '/chat', $params);

        if ($response->successful()) {
            $data = $response->json();
            return [
                'success' => true,
                'data' => [
                    'content' => $data['choices'][0]['message']['content'] ?? '',
                    'raw_response' => $data
                ]
            ];
        }

        return [
            'success' => false,
            'message' => '调用失败: ' . $response->body(),
            'status' => $response->status()
        ];
    }

    /**
     * 调用华为智能体
     *
     * @param object $agent 智能体对象
     * @param array $history 历史消息
     * @param array $options 选项
     * @return array 响应结果
     */
    private function callHuaweiAgent($agent, array $history, array $options)
    {
        // 华为智能体调用实现
        // 此处需要根据华为智能体的API文档实现
        return [
            'success' => true,
            'data' => [
                'content' => '这是华为智能体的模拟响应',
                'raw_response' => ['message' => '模拟响应']
            ]
        ];
    }

    /**
     * 调用通义智能体
     *
     * @param object $agent 智能体对象
     * @param array $history 历史消息
     * @param array $options 选项
     * @return array 响应结果
     */
    private function callTongyiAgent($agent, array $history, array $options)
    {
        // 通义智能体调用实现
        // 此处需要根据通义智能体的API文档实现
        return [
            'success' => true,
            'data' => [
                'content' => '这是通义智能体的模拟响应',
                'raw_response' => ['message' => '模拟响应']
            ]
        ];
    }

    /**
     * 获取会话历史
     *
     * @param string $sessionId 会话ID
     * @return array 历史消息
     */
    private function getSessionHistory($sessionId)
    {
        $cacheKey = "ai_agent_session:{$sessionId}";
        return Cache::get($cacheKey, []);
    }

    /**
     * 保存会话历史
     *
     * @param string $sessionId 会话ID
     * @param array $history 历史消息
     * @return void
     */
    private function saveSessionHistory($sessionId, array $history)
    {
        $cacheKey = "ai_agent_session:{$sessionId}";
        Cache::put($cacheKey, $history, 86400); // 缓存24小时
    }

    /**
     * 记录智能体使用日志
     *
     * @param object $agent 智能体对象
     * @param string $message 用户消息
     * @param array $response 响应数据
     * @param string $sessionId 会话ID
     * @return void
     */
    private function logAgentUsage($agent, $message, array $response, $sessionId)
    {
        // 获取AI模型服务
        $aiModelService = app(AIModelService::class);

        // 获取提供商ID
        $providerId = DB::table('ai_model_providers')
            ->where('code', $agent->provider)
            ->value('id');

        if (!$providerId) {
            return;
        }

        // 记录使用日志
        $aiModelService->logApiUsage([
            'provider_id' => $providerId,
            'request_type' => 'chat',
            'request_data' => [
                'messages' => [['role' => 'user', 'content' => $message]]
            ],
            'response_data' => $response,
            'session_id' => $sessionId,
            'is_success' => true,
            'ip_address' => request()->ip()
        ]);
    }

    /**
     * 执行智能体任务
     *
     * @param string $agentCode 智能体代码
     * @param string $taskType 任务类型
     * @param array $taskData 任务数据
     * @return array 任务结果
     */
    public function executeAgentTask($agentCode, $taskType, array $taskData)
    {
        try {
            $agent = $this->getAgent($agentCode);
            if (!$agent) {
                return [
                    'success' => false,
                    'message' => '找不到指定的智能体'
                ];
            }

            // 检查智能体能力
            $capabilities = json_decode($agent->capabilities, true);
            if (!in_array($taskType, $capabilities)) {
                return [
                    'success' => false,
                    'message' => '该智能体不支持此任务类型'
                ];
            }

            // 调用对应的智能体执行任务
            switch ($agent->provider) {
                case 'koudi':
                    return $this->executeKoudiTask($agent, $taskType, $taskData);
                case 'huawei':
                    return $this->executeHuaweiTask($agent, $taskType, $taskData);
                case 'aliyun':
                    return $this->executeTongyiTask($agent, $taskType, $taskData);
                default:
                    return [
                        'success' => false,
                        'message' => '不支持的智能体提供商'
                    ];
            }
        } catch (Exception $e) {
            Log::error('AI智能体任务执行失败', [
                'agent' => $agentCode,
                'task_type' => $taskType,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'success' => false,
                'message' => '任务执行失败: ' . $e->getMessage()
            ];
        }
    }

    /**
     * 执行扣子智能体任务
     *
     * @param object $agent 智能体对象
     * @param string $taskType 任务类型
     * @param array $taskData 任务数据
     * @return array 任务结果
     */
    private function executeKoudiTask($agent, $taskType, array $taskData)
    {
        // 获取API密钥
        $apiKey = $this->getAgentApiKey($agent->code);
        if (!$apiKey) {
            return [
                'success' => false,
                'message' => '缺少API密钥'
            ];
        }

        // 准备请求参数
        $params = array_merge($taskData, [
            'task_type' => $taskType
        ]);

        // 发送请求
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
            'Content-Type' => 'application/json'
        ])->post($agent->api_endpoint . '/tasks', $params);

        if ($response->successful()) {
            return [
                'success' => true,
                'data' => $response->json()
            ];
        }

        return [
            'success' => false,
            'message' => '任务执行失败: ' . $response->body(),
            'status' => $response->status()
        ];
    }

    /**
     * 执行华为智能体任务
     *
     * @param object $agent 智能体对象
     * @param string $taskType 任务类型
     * @param array $taskData 任务数据
     * @return array 任务结果
     */
    private function executeHuaweiTask($agent, $taskType, array $taskData)
    {
        // 华为智能体任务执行实现
        // 此处需要根据华为智能体的API文档实现
        return [
            'success' => true,
            'data' => [
                'task_id' => Str::uuid()->toString(),
                'status' => 'completed',
                'result' => '这是华为智能体的模拟任务结果'
            ]
        ];
    }

    /**
     * 执行通义智能体任务
     *
     * @param object $agent 智能体对象
     * @param string $taskType 任务类型
     * @param array $taskData 任务数据
     * @return array 任务结果
     */
    private function executeTongyiTask($agent, $taskType, array $taskData)
    {
        // 通义智能体任务执行实现
        // 此处需要根据通义智能体的API文档实现
        return [
            'success' => true,
            'data' => [
                'task_id' => Str::uuid()->toString(),
                'status' => 'completed',
                'result' => '这是通义智能体的模拟任务结果'
            ]
        ];
    }
} 