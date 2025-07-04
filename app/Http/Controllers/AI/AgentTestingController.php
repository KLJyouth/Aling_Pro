<?php

namespace App\Http\Controllers\AI;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AI\Agent;
use App\Models\AI\ApiKey;
use App\Models\AI\ModelProvider;
use App\Models\AI\AIModel;
use App\Services\AI\ApiKeyRotationService;
use App\Services\AI\AuditService;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class AgentTestingController extends Controller
{
    protected $apiKeyService;
    protected $auditService;
    
    /**
     * 构造函数
     *
     * @param ApiKeyRotationService $apiKeyService
     * @param AuditService $auditService
     */
    public function __construct(ApiKeyRotationService $apiKeyService, AuditService $auditService)
    {
        $this->apiKeyService = $apiKeyService;
        $this->auditService = $auditService;
    }
    
    /**
     * 显示智能体测试页面
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $agents = Agent::with(["provider", "model"])
            ->where("is_active", true)
            ->get();
            
        return view("admin.ai.testing.index", [
            "agents" => $agents,
        ]);
    }
    
    /**
     * 测试智能体
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function test(Request $request)
    {
        $validated = $request->validate([
            "agent_id" => "required|exists:ai_agents,id",
            "prompt" => "required|string",
            "parameters" => "nullable|array",
        ]);
        
        $agent = Agent::with(["provider", "model"])->findOrFail($validated["agent_id"]);
        
        // 获取可用的API密钥
        $apiKey = $this->apiKeyService->getAvailableApiKey($agent->provider_id);
        
        if (!$apiKey) {
            return response()->json([
                "success" => false,
                "message" => "未找到可用的API密钥",
            ], 400);
        }
        
        // 准备请求参数
        $parameters = $validated["parameters"] ?? [];
        $defaultParameters = json_decode($agent->parameters, true) ?: [];
        $mergedParameters = array_merge($defaultParameters, $parameters);
        
        // 添加提示信息
        $mergedParameters["prompt"] = $validated["prompt"];
        
        // 如果是OpenAI格式，则使用messages格式
        if ($agent->provider->identifier === "openai") {
            $mergedParameters = [
                "model" => $agent->model->identifier,
                "messages" => [
                    [
                        "role" => "user",
                        "content" => $validated["prompt"]
                    ]
                ],
                "temperature" => $mergedParameters["temperature"] ?? 0.7,
                "max_tokens" => $mergedParameters["max_tokens"] ?? 1000,
            ];
        }
        
        // 准备请求头
        $headers = [
            "Content-Type" => "application/json",
        ];
        
        // 添加认证头
        if ($agent->provider->auth_header) {
            $headers[$agent->provider->auth_header] = $agent->provider->auth_scheme . " " . $apiKey->decrypted_key;
        }
        
        // 请求端点
        $endpoint = $agent->endpoint;
        if (!Str::startsWith($endpoint, "http")) {
            $endpoint = $agent->provider->base_url . $endpoint;
        }
        
        $client = new Client();
        $startTime = microtime(true);
        $requestId = Str::uuid()->toString();
        
        try {
            $response = $client->post($endpoint, [
                "headers" => $headers,
                "json" => $mergedParameters,
                "timeout" => 60,
            ]);
            
            $responseTime = round((microtime(true) - $startTime) * 1000);
            $responseBody = $response->getBody()->getContents();
            $responseData = json_decode($responseBody, true);
            
            // 计算标记数和成本
            $inputTokens = 0;
            $outputTokens = 0;
            $cost = 0;
            
            // 根据不同的提供商计算标记数和成本
            if ($agent->provider->identifier === "openai") {
                if (isset($responseData["usage"])) {
                    $inputTokens = $responseData["usage"]["prompt_tokens"] ?? 0;
                    $outputTokens = $responseData["usage"]["completion_tokens"] ?? 0;
                    $cost = ($inputTokens * $agent->model->token_cost_input / 1000) + 
                            ($outputTokens * $agent->model->token_cost_output / 1000);
                }
            }
            
            // 记录API调用
            $this->apiKeyService->logApiCall($apiKey, [
                "model_id" => $agent->model_id,
                "agent_id" => $agent->id,
                "user_id" => auth()->id(),
                "request_id" => $requestId,
                "ip_address" => $request->ip(),
                "endpoint" => $endpoint,
                "request_data" => json_encode($mergedParameters),
                "response_data" => $responseBody,
                "response_time" => $responseTime,
                "input_tokens" => $inputTokens,
                "output_tokens" => $outputTokens,
                "status" => "success",
                "cost" => $cost,
            ]);
            
            // 提取响应内容
            $content = "";
            if ($agent->provider->identifier === "openai") {
                $content = $responseData["choices"][0]["message"]["content"] ?? "";
            } elseif (isset($responseData["response"])) {
                $content = $responseData["response"];
            } elseif (isset($responseData["output"])) {
                $content = $responseData["output"];
            } elseif (isset($responseData["text"])) {
                $content = $responseData["text"];
            } elseif (isset($responseData["content"])) {
                $content = $responseData["content"];
            } else {
                $content = json_encode($responseData);
            }
            
            return response()->json([
                "success" => true,
                "message" => "智能体测试成功",
                "data" => [
                    "content" => $content,
                    "raw_response" => $responseData,
                    "response_time" => $responseTime,
                    "input_tokens" => $inputTokens,
                    "output_tokens" => $outputTokens,
                    "total_tokens" => $inputTokens + $outputTokens,
                    "cost" => $cost,
                ],
            ]);
            
        } catch (RequestException $e) {
            $responseTime = round((microtime(true) - $startTime) * 1000);
            $errorMessage = $e->getMessage();
            $responseBody = $e->hasResponse() ? $e->getResponse()->getBody()->getContents() : "";
            
            // 记录API调用
            $this->apiKeyService->logApiCall($apiKey, [
                "model_id" => $agent->model_id,
                "agent_id" => $agent->id,
                "user_id" => auth()->id(),
                "request_id" => $requestId,
                "ip_address" => $request->ip(),
                "endpoint" => $endpoint,
                "request_data" => json_encode($mergedParameters),
                "response_data" => $responseBody,
                "response_time" => $responseTime,
                "status" => "error",
                "error_message" => $errorMessage,
            ]);
            
            return response()->json([
                "success" => false,
                "message" => "智能体测试失败",
                "error" => $errorMessage,
                "response" => $responseBody ? json_decode($responseBody, true) : null,
            ], 500);
        }
    }
    
    /**
     * 显示智能体比较页面
     *
     * @return \Illuminate\Http\Response
     */
    public function compare()
    {
        $agents = Agent::with(["provider", "model"])
            ->where("is_active", true)
            ->get();
            
        return view("admin.ai.testing.compare", [
            "agents" => $agents,
        ]);
    }
    
    /**
     * 比较多个智能体
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function compareAgents(Request $request)
    {
        $validated = $request->validate([
            "agent_ids" => "required|array",
            "agent_ids.*" => "exists:ai_agents,id",
            "prompt" => "required|string",
        ]);
        
        $results = [];
        
        foreach ($validated["agent_ids"] as $agentId) {
            $testRequest = new Request([
                "agent_id" => $agentId,
                "prompt" => $validated["prompt"],
            ]);
            
            $response = $this->test($testRequest);
            $content = json_decode($response->getContent(), true);
            
            $agent = Agent::with(["provider", "model"])->find($agentId);
            
            $results[] = [
                "agent_id" => $agentId,
                "agent_name" => $agent->name,
                "provider_name" => $agent->provider->name,
                "model_name" => $agent->model->name,
                "success" => $content["success"],
                "content" => $content["success"] ? $content["data"]["content"] : ($content["error"] ?? "测试失败"),
                "response_time" => $content["success"] ? $content["data"]["response_time"] : 0,
                "total_tokens" => $content["success"] ? $content["data"]["total_tokens"] : 0,
                "cost" => $content["success"] ? $content["data"]["cost"] : 0,
            ];
        }
        
        return response()->json([
            "success" => true,
            "results" => $results,
        ]);
    }
    
    /**
     * 显示智能体调试页面
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function debug($id)
    {
        $agent = Agent::with(["provider", "model"])->findOrFail($id);
        
        return view("admin.ai.testing.debug", [
            "agent" => $agent,
            "parameters" => json_decode($agent->parameters, true) ?: [],
        ]);
    }
}
