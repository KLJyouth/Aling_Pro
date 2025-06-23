<?php
namespace AlingAi\Monitoring;

use AlingAi\Monitoring\Metrics\MetricsCollector;
use AlingAi\Monitoring\Config\GatewayConfig;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use Psr\Log\LoggerInterface;
use Exception;

/**
 * API网关 - 处理所有API请求的代理和监控
 */
class ApiGateway
{
    /**
     * @var MetricsCollector
     */
    private $metricsCollector;
    
    /**
     * @var GatewayConfig
     */
    private $config;
    
    /**
     * @var LoggerInterface
     */
    private $logger;
    
    /**
     * @var array
     */
    private $clients = [];

    /**
     * 构造函数
     */
    public function __construct(MetricsCollector $metricsCollector, GatewayConfig $config, LoggerInterface $logger)
    {
        $this->metricsCollector = $metricsCollector;
        $this->config = $config;
        $this->logger = $logger;
    }

    /**
     * 处理内部API请求
     */
    public function handleInternalRequest($method, $path, $params = [], $headers = [])
    {
        $startTime = microtime(true);
        $apiName = "internal:$path";
        
        try {
            // 实际处理内部API调用的逻辑
            $result = $this->callInternalApi($method, $path, $params, $headers);
            
            // 记录成功指标
            $this->metricsCollector->recordApiCall(
                $apiName,
                'internal',
                microtime(true) - $startTime,
                true,
                null
            );
            
            return $result;
        } catch (Exception $e) {
            // 记录失败指标
            $this->metricsCollector->recordApiCall(
                $apiName,
                'internal',
                microtime(true) - $startTime,
                false,
                $e->getMessage()
            );
            
            $this->logger->error("内部API调用失败: $path", [
                'exception' => $e->getMessage(),
                'method' => $method,
                'params' => $params
            ]);
            
            throw $e;
        }
    }

    /**
     * 处理外部第三方API请求
     */
    public function handleExternalRequest($apiProvider, $endpoint, $method = 'GET', $params = [], $headers = [])
    {
        $startTime = microtime(true);
        $apiName = "$apiProvider:$endpoint";
        
        try {
            // 获取或创建API客户端
            $client = $this->getApiClient($apiProvider);
            
            // 发送请求
            $options = [
                'headers' => $headers,
            ];
            
            if ($method === 'GET') {
                $options['query'] = $params;
            } else {
                $options['json'] = $params;
            }
            
            $response = $client->request($method, $endpoint, $options);
            
            // 记录成功指标
            $this->metricsCollector->recordApiCall(
                $apiName,
                'external',
                microtime(true) - $startTime,
                true,
                null,
                $response->getStatusCode()
            );
            
            return json_decode($response->getBody(), true);
        } catch (Exception $e) {
            // 记录失败指标
            $this->metricsCollector->recordApiCall(
                $apiName,
                'external',
                microtime(true) - $startTime,
                false,
                $e->getMessage()
            );
            
            $this->logger->error("外部API调用失败: $apiProvider - $endpoint", [
                'exception' => $e->getMessage(),
                'method' => $method,
                'params' => $params
            ]);
            
            throw $e;
        }
    }

    /**
     * 处理第三方接入的API请求
     */
    public function handleIncomingRequest($requestData)
    {
        $startTime = microtime(true);
        $endpoint = $requestData['endpoint'] ?? 'unknown';
        $apiName = "incoming:$endpoint";
        
        try {
            // 处理第三方接入的API请求
            $result = $this->processIncomingRequest($requestData);
            
            // 记录成功指标
            $this->metricsCollector->recordApiCall(
                $apiName,
                'incoming',
                microtime(true) - $startTime,
                true,
                null
            );
            
            return $result;
        } catch (Exception $e) {
            // 记录失败指标
            $this->metricsCollector->recordApiCall(
                $apiName,
                'incoming',
                microtime(true) - $startTime,
                false,
                $e->getMessage()
            );
            
            $this->logger->error("第三方接入API处理失败: $endpoint", [
                'exception' => $e->getMessage(),
                'requestData' => $requestData
            ]);
            
            throw $e;
        }
    }

    /**
     * 获取API客户端
     */
    private function getApiClient($apiProvider)
    {
        if (!isset($this->clients[$apiProvider])) {
            // 获取API提供者的配置
            $providerConfig = $this->config->getProviderConfig($apiProvider);
            
            // 创建handler stack
            $stack = HandlerStack::create();
            
            // 添加重试中间件
            $stack->push(Middleware::retry(function ($retries, $request, $response, $exception) {
                // 最多重试3次
                if ($retries >= 3) {
                    return false;
                }
                
                // 服务器错误时重试
                if ($response && $response->getStatusCode() >= 500) {
                    return true;
                }
                
                // 连接错误时重试
                if ($exception instanceof \GuzzleHttp\Exception\ConnectException) {
                    return true;
                }
                
                return false;
            }, function ($retries) {
                // 指数退避策略
                return 1000 * pow(2, $retries);
            }));
            
            // 创建客户端
            $this->clients[$apiProvider] = new Client([
                'base_uri' => $providerConfig['base_url'],
                'handler' => $stack,
                'timeout' => $providerConfig['timeout'] ?? 30,
                'headers' => $providerConfig['default_headers'] ?? [],
            ]);
        }
        
        return $this->clients[$apiProvider];
    }

    /**
     * 调用内部API
     */
    private function callInternalApi($method, $path, $params, $headers)
    {
        // 这里实现内部API的实际调用逻辑
        // 根据项目的具体架构实现
        // 示例实现：
        $controllerPath = $this->resolveControllerPath($path);
        
        if (!file_exists($controllerPath)) {
            throw new Exception("内部API不存在: $path");
        }
        
        // 调用控制器处理请求
        require_once $controllerPath;
        $controllerClass = $this->getControllerClass($path);
        $controller = new $controllerClass();
        
        return $controller->handleRequest($method, $params, $headers);
    }

    /**
     * 处理第三方接入的API请求
     */
    private function processIncomingRequest($requestData)
    {
        // 实现处理第三方接入API请求的逻辑
        // 根据项目的具体需求实现
        // 示例实现：
        $endpoint = $requestData['endpoint'] ?? '';
        $params = $requestData['params'] ?? [];
        
        // 根据endpoint路由到对应的处理器
        $handlerClass = $this->getIncomingRequestHandler($endpoint);
        if (!$handlerClass) {
            throw new Exception("未找到处理器: $endpoint");
        }
        
        $handler = new $handlerClass();
        return $handler->process($params);
    }

    /**
     * 解析控制器路径
     */
    private function resolveControllerPath($path)
    {
        // 实现将API路径映射到控制器文件的逻辑
        // 示例实现：
        $basePath = __DIR__ . '/../../src/Controllers/';
        $pathParts = explode('/', trim($path, '/'));
        
        if (count($pathParts) < 2) {
            throw new Exception("无效的API路径: $path");
        }
        
        $controllerName = ucfirst(end($pathParts)) . 'Controller';
        $namespace = implode('\\', array_slice($pathParts, 0, -1));
        
        return $basePath . str_replace('\\', '/', $namespace) . '/' . $controllerName . '.php';
    }

    /**
     * 获取控制器类名
     */
    private function getControllerClass($path)
    {
        // 实现将API路径映射到控制器类名的逻辑
        // 示例实现：
        $pathParts = explode('/', trim($path, '/'));
        
        if (count($pathParts) < 2) {
            throw new Exception("无效的API路径: $path");
        }
        
        $controllerName = ucfirst(end($pathParts)) . 'Controller';
        $namespace = 'AlingAi\\Controllers\\' . implode('\\', array_slice($pathParts, 0, -1));
        
        return $namespace . '\\' . $controllerName;
    }

    /**
     * 获取入站请求处理器
     */
    private function getIncomingRequestHandler($endpoint)
    {
        // 实现将入站请求endpoint映射到处理器的逻辑
        // 示例实现：
        $handlers = [
            'data/sync' => 'AlingAi\\IncomingHandlers\\DataSyncHandler',
            'user/verify' => 'AlingAi\\IncomingHandlers\\UserVerifyHandler',
            // 添加更多映射...
        ];
        
        return $handlers[$endpoint] ?? null;
    }
} 