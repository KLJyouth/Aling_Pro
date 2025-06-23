<?php
namespace AlingAi\Monitoring\HealthCheck;

use AlingAi\Monitoring\Storage\MetricsStorageInterface;
use AlingAi\Monitoring\ApiGateway;
use AlingAi\Monitoring\Config\GatewayConfig;
use Psr\Log\LoggerInterface;

/**
 * 健康检查服务 - 定期检查API可用性和响应时间
 */
class HealthCheckService
{
    /**
     * @var MetricsStorageInterface
     */
    private $metricsStorage;
    
    /**
     * @var ApiGateway
     */
    private $apiGateway;
    
    /**
     * @var GatewayConfig
     */
    private $gatewayConfig;
    
    /**
     * @var LoggerInterface
     */
    private $logger;
    
    /**
     * @var array 健康检查配置
     */
    private $checkConfigs = [];

    /**
     * 构造函数
     */
    public function __construct(
        MetricsStorageInterface $metricsStorage,
        ApiGateway $apiGateway,
        GatewayConfig $gatewayConfig,
        LoggerInterface $logger
    ) {
        $this->metricsStorage = $metricsStorage;
        $this->apiGateway = $apiGateway;
        $this->gatewayConfig = $gatewayConfig;
        $this->logger = $logger;
    }

    /**
     * 加载健康检查配置
     */
    public function loadCheckConfigs(array $configs = null): void
    {
        if ($configs !== null) {
            $this->checkConfigs = $configs;
            return;
        }
        
        // 从配置获取健康检查设置
        $globalSettings = $this->gatewayConfig->getGlobalSettings();
        $this->checkConfigs = $globalSettings['health_checks'] ?? [];
        
        if (empty($this->checkConfigs)) {
            // 使用默认配置
            $this->checkConfigs = $this->getDefaultConfigs();
        }
    }

    /**
     * 获取默认健康检查配置
     */
    private function getDefaultConfigs(): array
    {
        $configs = [];
        
        // 为所有配置的外部API创建健康检查
        $providers = $this->gatewayConfig->getAllProviders();
        
        foreach ($providers as $providerName => $providerConfig) {
            $configs[] = [
                'api_name' => $providerName . ':health',
                'type' => 'external',
                'method' => 'GET',
                'endpoint' => 'health', // 假设每个API都有健康检查端点
                'expected_status' => 200,
                'timeout' => 5,
                'interval' => 60, // 每分钟检查一次
                'retries' => 3,
                'enabled' => true,
            ];
        }
        
        // 添加内部API健康检查
        $configs[] = [
            'api_name' => 'internal:system/health',
            'type' => 'internal',
            'method' => 'GET',
            'endpoint' => 'system/health',
            'expected_status' => 200,
            'timeout' => 5,
            'interval' => 60,
            'retries' => 3,
            'enabled' => true,
        ];
        
        return $configs;
    }

    /**
     * 运行所有健康检查
     */
    public function runChecks(): void
    {
        if (empty($this->checkConfigs)) {
            $this->loadCheckConfigs();
        }
        
        foreach ($this->checkConfigs as $config) {
            if (!($config['enabled'] ?? true)) {
                continue;
            }
            
            $this->runSingleCheck($config);
        }
    }

    /**
     * 运行单个健康检查
     */
    private function runSingleCheck(array $config): void
    {
        $apiName = $config['api_name'];
        $type = $config['type'];
        $method = $config['method'] ?? 'GET';
        $endpoint = $config['endpoint'] ?? '';
        $expectedStatus = $config['expected_status'] ?? 200;
        $timeout = $config['timeout'] ?? 5;
        $retries = $config['retries'] ?? 3;
        
        $this->logger->info("运行健康检查", [
            'api_name' => $apiName,
            'type' => $type,
            'endpoint' => $endpoint,
        ]);
        
        try {
            $success = false;
            $reason = null;
            
            for ($attempt = 0; $attempt < $retries; $attempt++) {
                try {
                    if ($type === 'internal') {
                        $result = $this->apiGateway->handleInternalRequest($method, $endpoint);
                        $success = true;
                    } elseif ($type === 'external') {
                        // 从API名称中提取提供者名称
                        $parts = explode(':', $apiName, 2);
                        $providerName = $parts[0];
                        
                        $result = $this->apiGateway->handleExternalRequest(
                            $providerName,
                            $endpoint,
                            $method
                        );
                        
                        $success = true;
                    } else {
                        throw new \InvalidArgumentException("不支持的API类型: $type");
                    }
                    
                    break; // 成功，退出重试循环
                } catch (\Exception $e) {
                    $reason = $e->getMessage();
                    $this->logger->warning("健康检查尝试失败", [
                        'api_name' => $apiName,
                        'attempt' => $attempt + 1,
                        'error' => $reason,
                    ]);
                    
                    // 最后一次尝试失败
                    if ($attempt === $retries - 1) {
                        $success = false;
                    } else {
                        // 等待一段时间后重试
                        usleep(500000); // 500毫秒
                    }
                }
            }
            
            // 记录健康检查结果
            $this->metricsStorage->storeAvailabilityMetric([
                'timestamp' => time(),
                'api_name' => $apiName,
                'type' => $type,
                'available' => $success,
                'reason' => $reason,
            ]);
            
            $this->logger->info("健康检查完成", [
                'api_name' => $apiName,
                'success' => $success,
                'reason' => $reason,
            ]);
        } catch (\Exception $e) {
            $this->logger->error("运行健康检查时发生错误", [
                'api_name' => $apiName,
                'error' => $e->getMessage(),
            ]);
            
            // 记录检查失败
            $this->metricsStorage->storeAvailabilityMetric([
                'timestamp' => time(),
                'api_name' => $apiName,
                'type' => $type,
                'available' => false,
                'reason' => "健康检查执行错误: " . $e->getMessage(),
            ]);
        }
    }

    /**
     * 添加或更新健康检查配置
     */
    public function addOrUpdateCheck(array $config): void
    {
        if (!isset($config['api_name'])) {
            throw new \InvalidArgumentException("健康检查配置必须包含api_name");
        }
        
        $apiName = $config['api_name'];
        
        // 检查是否已存在
        $existingIndex = -1;
        foreach ($this->checkConfigs as $index => $existingConfig) {
            if ($existingConfig['api_name'] === $apiName) {
                $existingIndex = $index;
                break;
            }
        }
        
        if ($existingIndex >= 0) {
            // 更新现有配置
            $this->checkConfigs[$existingIndex] = $config;
        } else {
            // 添加新配置
            $this->checkConfigs[] = $config;
        }
        
        // 保存到全局设置
        $globalSettings = $this->gatewayConfig->getGlobalSettings();
        $globalSettings['health_checks'] = $this->checkConfigs;
        $this->gatewayConfig->setGlobalSettings($globalSettings);
    }

    /**
     * 删除健康检查配置
     */
    public function deleteCheck(string $apiName): void
    {
        foreach ($this->checkConfigs as $index => $config) {
            if ($config['api_name'] === $apiName) {
                unset($this->checkConfigs[$index]);
                break;
            }
        }
        
        // 重新索引数组
        $this->checkConfigs = array_values($this->checkConfigs);
        
        // 保存到全局设置
        $globalSettings = $this->gatewayConfig->getGlobalSettings();
        $globalSettings['health_checks'] = $this->checkConfigs;
        $this->gatewayConfig->setGlobalSettings($globalSettings);
    }

    /**
     * 获取所有健康检查配置
     */
    public function getAllChecks(): array
    {
        if (empty($this->checkConfigs)) {
            $this->loadCheckConfigs();
        }
        
        return $this->checkConfigs;
    }

    /**
     * 获取特定API的健康检查配置
     */
    public function getCheck(string $apiName): ?array
    {
        foreach ($this->checkConfigs as $config) {
            if ($config['api_name'] === $apiName) {
                return $config;
            }
        }
        
        return null;
    }
} 