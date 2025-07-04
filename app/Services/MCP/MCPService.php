<?php

namespace App\Services\MCP;

use App\Models\User;
use App\Models\MCP\MCPInterface;
use App\Models\MCP\MCPLog;
use App\Exceptions\MCPException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/**
 * MCP服务类
 * 
 * 管理控制平台(Management Control Platform)接口服务
 * 提供系统级别的管理和控制功能
 */
class MCPService
{
    /**
     * API基础URL
     *
     * @var string
     */
    protected $baseUrl;

    /**
     * API密钥
     *
     * @var string
     */
    protected $apiKey;

    /**
     * API密钥
     *
     * @var string
     */
    protected $apiSecret;

    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->baseUrl = config("mcp.base_url", "https://mcp.alingai.pro/api/v1");
        $this->apiKey = config("mcp.api_key");
        $this->apiSecret = config("mcp.api_secret");
    }

    /**
     * 获取系统状态
     *
     * @return array
     */
    public function getSystemStatus()
    {
        return $this->sendRequest("GET", "system/status");
    }

    /**
     * 获取系统资源使用情况
     *
     * @return array
     */
    public function getResourceUsage()
    {
        return $this->sendRequest("GET", "system/resources");
    }

    /**
     * 获取用户统计数据
     *
     * @return array
     */
    public function getUserStats()
    {
        return $this->sendRequest("GET", "users/stats");
    }

    /**
     * 获取API使用统计数据
     *
     * @return array
     */
    public function getApiStats()
    {
        return $this->sendRequest("GET", "api/stats");
    }

    /**
     * 执行系统维护任务
     *
     * @param string $task 任务名称
     * @param array $params 任务参数
     * @return array
     */
    public function runMaintenanceTask($task, array $params = [])
    {
        return $this->sendRequest("POST", "system/maintenance/" . $task, $params);
    }

    /**
     * 获取系统配置
     *
     * @param string $configGroup 配置组名称
     * @return array
     */
    public function getSystemConfig($configGroup = null)
    {
        $endpoint = "system/config";
        if ($configGroup) {
            $endpoint .= "/" . $configGroup;
        }
        return $this->sendRequest("GET", $endpoint);
    }

    /**
     * 更新系统配置
     *
     * @param string $configGroup 配置组名称
     * @param array $configData 配置数据
     * @return array
     */
    public function updateSystemConfig($configGroup, array $configData)
    {
        return $this->sendRequest("PUT", "system/config/" . $configGroup, $configData);
    }

    /**
     * 发送请求到MCP API
     *
     * @param string $method 请求方法
     * @param string $endpoint 端点
     * @param array $data 请求数据
     * @return array
     * @throws MCPException
     */
    protected function sendRequest($method, $endpoint, array $data = [])
    {
        $url = $this->baseUrl . "/" . ltrim($endpoint, "/");
        $timestamp = time();
        $nonce = Str::random(16);
        
        // 生成签名
        $signature = $this->generateSignature($method, $endpoint, $timestamp, $nonce, $data);
        
        $headers = [
            "X-MCP-Key" => $this->apiKey,
            "X-MCP-Timestamp" => $timestamp,
            "X-MCP-Nonce" => $nonce,
            "X-MCP-Signature" => $signature,
            "Accept" => "application/json",
        ];

        try {
            $response = Http::withHeaders($headers);
            
            if ($method === "GET") {
                $response = $response->get($url, $data);
            } elseif ($method === "POST") {
                $response = $response->post($url, $data);
            } elseif ($method === "PUT") {
                $response = $response->put($url, $data);
            } elseif ($method === "DELETE") {
                $response = $response->delete($url, $data);
            }

            // 记录API调用日志
            $this->logApiCall($method, $endpoint, $data, $response->status(), $response->json());

            if ($response->successful()) {
                return $response->json();
            }

            throw new MCPException(
                $response->json()["message"] ?? "未知错误",
                $response->status()
            );
        } catch (\Exception $e) {
            if ($e instanceof MCPException) {
                throw $e;
            }
            
            Log::error("MCP API调用失败", [
                "message" => $e->getMessage(),
                "endpoint" => $endpoint,
                "method" => $method,
            ]);
            
            throw new MCPException("MCP API调用失败: " . $e->getMessage());
        }
    }

    /**
     * 生成API签名
     *
     * @param string $method 请求方法
     * @param string $endpoint 端点
     * @param int $timestamp 时间戳
     * @param string $nonce 随机字符串
     * @param array $data 请求数据
     * @return string
     */
    protected function generateSignature($method, $endpoint, $timestamp, $nonce, array $data)
    {
        // 按字母顺序排序参数
        if (!empty($data)) {
            ksort($data);
        }
        
        // 构建签名字符串
        $signString = strtoupper($method) . "\n";
        $signString .= $endpoint . "\n";
        $signString .= $timestamp . "\n";
        $signString .= $nonce . "\n";
        $signString .= json_encode($data) . "\n";
        $signString .= $this->apiSecret;
        
        // 使用HMAC-SHA256算法生成签名
        return hash_hmac("sha256", $signString, $this->apiSecret);
    }

    /**
     * 记录API调用日志
     *
     * @param string $method 请求方法
     * @param string $endpoint 端点
     * @param array $requestData 请求数据
     * @param int $statusCode 状态码
     * @param array $responseData 响应数据
     * @return void
     */
    protected function logApiCall($method, $endpoint, $requestData, $statusCode, $responseData)
    {
        try {
            MCPLog::create([
                "method" => $method,
                "endpoint" => $endpoint,
                "request_data" => json_encode($requestData),
                "status_code" => $statusCode,
                "response_data" => json_encode($responseData),
                "created_at" => now(),
            ]);
        } catch (\Exception $e) {
            Log::error("无法记录MCP API调用日志", [
                "message" => $e->getMessage(),
            ]);
        }
    }
}
