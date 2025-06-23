<?php
namespace AlingAi\Monitoring\Config;

use Psr\Log\LoggerInterface;
use Exception;

/**
 * 网关配置 - 管理API网关配置
 */
class GatewayConfig
{
    /**
     * @var string 配置文件路径
     */
    private $configFile;
    
    /**
     * @var array 配置数据
     */
    private $config;
    
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * 构造函数
     */
    public function __construct(string $configFile, LoggerInterface $logger)
    {
        $this->configFile = $configFile;
        $this->logger = $logger;
        $this->loadConfig();
    }

    /**
     * 加载配置
     */
    private function loadConfig(): void
    {
        if (file_exists($this->configFile)) {
            $content = file_get_contents($this->configFile);
            $this->config = json_decode($content, true) ?? [];
        } else {
            $this->config = [
                'providers' => [],
                'thresholds' => [
                    'default' => [
                        'duration' => 5.0, // 默认响应时间阈值(秒)
                        'error_rate' => 0.1, // 默认错误率阈值(0-1)
                    ],
                ],
            ];
            
            // 创建默认配置文件
            $this->saveConfig();
        }
    }

    /**
     * 保存配置
     */
    private function saveConfig(): void
    {
        try {
            $dir = dirname($this->configFile);
            
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            
            file_put_contents($this->configFile, json_encode($this->config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        } catch (Exception $e) {
            $this->logger->error("保存API网关配置失败", [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * 获取API提供者配置
     */
    public function getProviderConfig(string $providerName): array
    {
        return $this->config['providers'][$providerName] ?? [];
    }

    /**
     * 设置API提供者配置
     */
    public function setProviderConfig(string $providerName, array $config): void
    {
        $this->config['providers'][$providerName] = $config;
        $this->saveConfig();
    }

    /**
     * 删除API提供者配置
     */
    public function deleteProviderConfig(string $providerName): void
    {
        if (isset($this->config['providers'][$providerName])) {
            unset($this->config['providers'][$providerName]);
            $this->saveConfig();
        }
    }

    /**
     * 获取所有API提供者
     */
    public function getAllProviders(): array
    {
        return $this->config['providers'] ?? [];
    }

    /**
     * 获取阈值配置
     */
    public function getThresholds(): array
    {
        return $this->config['thresholds'] ?? [];
    }

    /**
     * 设置阈值配置
     */
    public function setThresholds(array $thresholds): void
    {
        $this->config['thresholds'] = $thresholds;
        $this->saveConfig();
    }

    /**
     * 获取特定API的阈值配置
     */
    public function getApiThresholds(string $apiName): array
    {
        return $this->config['thresholds'][$apiName] ?? $this->config['thresholds']['default'] ?? [];
    }

    /**
     * 设置特定API的阈值配置
     */
    public function setApiThresholds(string $apiName, array $thresholds): void
    {
        $this->config['thresholds'][$apiName] = $thresholds;
        $this->saveConfig();
    }

    /**
     * 获取全局设置
     */
    public function getGlobalSettings(): array
    {
        return $this->config['global_settings'] ?? [];
    }

    /**
     * 设置全局设置
     */
    public function setGlobalSettings(array $settings): void
    {
        $this->config['global_settings'] = $settings;
        $this->saveConfig();
    }
} 