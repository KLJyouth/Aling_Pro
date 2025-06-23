<?php

namespace AlingAi\Security;

use Psr\Log\LoggerInterface;
use AlingAi\Core\Container;
use AlingAi\Security\Exceptions\EncryptionException;

/**
 * 加密服务类
 * 
 * 提供系统级加密功能，整合多种加密算法，支持API传输加密
 * 
 * @package AlingAi\Security
 * @version 6.0.0
 */
class EncryptionService
{
    private LoggerInterface $logger;
    private Container $container;
    private array $config;
    private ?QuantumCryptographyService $quantumService = null;
    private array $encryptionKeys = [];
    private string $defaultAlgorithm = "AES-256-GCM";
    private string $systemKey;
    private string $systemIv;
    
    /**
     * 构造函数
     */
    public function __construct(LoggerInterface $logger, Container $container)
    {
        $this->logger = $logger;
        $this->container = $container;
        $this->config = $this->loadConfig();
        
        // 初始化系统密钥
        $this->initializeSystemKeys();
        
        // 如果启用量子加密，则初始化量子加密服务
        if ($this->config["enable_quantum_encryption"]) {
            $this->initializeQuantumService();
        }
    }
    
    /**
     * 加载加密配置
     */
    private function loadConfig(): array
    {
        return [
            "enable_quantum_encryption" => (bool)($_ENV["ENABLE_QUANTUM_ENCRYPTION"] ?? false),
            "default_algorithm" => $_ENV["DEFAULT_ENCRYPTION_ALGORITHM"] ?? "AES-256-GCM",
            "key_rotation_interval" => (int)($_ENV["KEY_ROTATION_INTERVAL"] ?? 86400), // 默认24小时
            "api_encryption" => (bool)($_ENV["API_ENCRYPTION_ENABLED"] ?? true),
            "api_encryption_algorithm" => $_ENV["API_ENCRYPTION_ALGORITHM"] ?? "AES-256-CBC",
            "data_encryption" => (bool)($_ENV["DATA_ENCRYPTION_ENABLED"] ?? true),
            "hash_algorithm" => $_ENV["HASH_ALGORITHM"] ?? "sha256",
        ];
    }
    
    /**
     * 初始化系统密钥
     */
    private function initializeSystemKeys(): void
    {
        // 从环境变量获取系统密钥，如果不存在则生成
        $envKey = $_ENV["SYSTEM_ENCRYPTION_KEY"] ?? null;
        
        if (empty($envKey)) {
            // 生成新密钥
            $this->systemKey = $this->generateEncryptionKey();
            $this->systemIv = $this->generateIv();
            
            // 记录警告
            $this->logger->warning("系统加密密钥未配置，已生成临时密钥。请在环境配置中设置SYSTEM_ENCRYPTION_KEY");
        } else {
            $this->systemKey = hex2bin($envKey);
            $this->systemIv = hex2bin($_ENV["SYSTEM_ENCRYPTION_IV"] ?? $this->generateIv(true));
        }
    }
    
    /**
     * 初始化量子加密服务
     */
    private function initializeQuantumService(): void
    {
        try {
            $this->quantumService = $this->container->get(QuantumCryptographyService::class);
            $this->logger->info("量子加密服务已初始化");
        } catch (\Exception $e) {
            $this->logger->error("初始化量子加密服务失败", ["error" => $e->getMessage()]);
        }
    }
    
    /**
     * 生成加密密钥
     */
    public function generateEncryptionKey(bool $asHex = false): string
    {
        $key = random_bytes(32); // 256位密钥
        return $asHex ? bin2hex($key) : $key;
    }
    
    /**
     * 生成初始化向量
     */
    public function generateIv(bool $asHex = false): string
    {
        $iv = random_bytes(16); // 128位IV
        return $asHex ? bin2hex($iv) : $iv;
    }
    
    /**
     * 加密数据
     * 
     * @param string $data 要加密的数据
     * @param string $purpose 加密目的，可以是"api"或"data"
     * @param array $options 加密选项
     * @return array 包含加密数据和元数据的数组
     */
    public function encrypt(string $data, string $purpose = "data", array $options = []): array
    {
        // 确定使用的算法
        $algorithm = $options["algorithm"] ?? ($purpose === "api" 
            ? $this->config["api_encryption_algorithm"] 
            : $this->config["default_algorithm"]);
        
        // 如果启用了量子加密并且目的是API，优先使用量子加密
        if ($this->config["enable_quantum_encryption"] && $purpose === "api" && $this->quantumService) {
            try {
                // 生成量子密钥对
                $keyPair = $this->quantumService->generateQuantumKeyPair();
                
                // 使用量子加密
                $encryptedData = $this->quantumService->encrypt($data, $keyPair["key_id"]);
                
                return [
                    "data" => base64_encode($encryptedData["encrypted"]),
                    "iv" => base64_encode($encryptedData["iv"]),
                    "key_id" => $keyPair["key_id"],
                    "algorithm" => "quantum_" . $keyPair["algorithm"],
                    "timestamp" => time()
                ];
            } catch (\Exception $e) {
                $this->logger->warning("量子加密失败，回退到传统加密", ["error" => $e->getMessage()]);
                // 失败时回退到传统加密
            }
        }
        
        // 传统加密
        $key = $options["key"] ?? $this->systemKey;
        $iv = $options["iv"] ?? $this->generateIv();
        
        // 根据算法选择加密方法
        switch ($algorithm) {
            case "AES-256-GCM":
                $tag = "";
                $encrypted = openssl_encrypt(
                    $data,
                    $algorithm,
                    $key,
                    OPENSSL_RAW_DATA,
                    $iv,
                    $tag,
                    $options["aad"] ?? "",
                    $options["tag_length"] ?? 16
                );
                
                if ($encrypted === false) {
                    throw new EncryptionException("加密失败: " . openssl_error_string());
                }
                
                return [
                    "data" => base64_encode($encrypted),
                    "iv" => base64_encode($iv),
                    "tag" => base64_encode($tag),
                    "algorithm" => $algorithm,
                    "timestamp" => time()
                ];
                
            case "AES-256-CBC":
            default:
                $encrypted = openssl_encrypt(
                    $data,
                    $algorithm,
                    $key,
                    OPENSSL_RAW_DATA,
                    $iv
                );
                
                if ($encrypted === false) {
                    throw new EncryptionException("加密失败: " . openssl_error_string());
                }
                
                return [
                    "data" => base64_encode($encrypted),
                    "iv" => base64_encode($iv),
                    "algorithm" => $algorithm,
                    "timestamp" => time()
                ];
        }
    }
    
    /**
     * 解密数据
     * 
     * @param array $encryptedPackage 加密数据包
     * @param array $options 解密选项
     * @return string 解密后的数据
     */
    public function decrypt(array $encryptedPackage, array $options = []): string
    {
        // 验证必要参数
        if (!isset($encryptedPackage["data"]) || !isset($encryptedPackage["algorithm"])) {
            throw new EncryptionException("无效的加密数据包");
        }
        
        // 解码数据
        $encryptedData = base64_decode($encryptedPackage["data"]);
        $iv = base64_decode($encryptedPackage["iv"] ?? "");
        
        // 检查是否为量子加密
        if (strpos($encryptedPackage["algorithm"], "quantum_") === 0 && $this->quantumService) {
            if (!isset($encryptedPackage["key_id"])) {
                throw new EncryptionException("缺少量子加密密钥ID");
            }
            
            try {
                return $this->quantumService->decrypt($encryptedData, $iv, $encryptedPackage["key_id"]);
            } catch (\Exception $e) {
                throw new EncryptionException("量子解密失败: " . $e->getMessage());
            }
        }
        
        // 传统解密
        $key = $options["key"] ?? $this->systemKey;
        $algorithm = $encryptedPackage["algorithm"];
        
        // 根据算法选择解密方法
        switch ($algorithm) {
            case "AES-256-GCM":
                if (!isset($encryptedPackage["tag"])) {
                    throw new EncryptionException("GCM模式解密缺少认证标签");
                }
                
                $tag = base64_decode($encryptedPackage["tag"]);
                $decrypted = openssl_decrypt(
                    $encryptedData,
                    $algorithm,
                    $key,
                    OPENSSL_RAW_DATA,
                    $iv,
                    $tag,
                    $options["aad"] ?? ""
                );
                break;
                
            case "AES-256-CBC":
            default:
                $decrypted = openssl_decrypt(
                    $encryptedData,
                    $algorithm,
                    $key,
                    OPENSSL_RAW_DATA,
                    $iv
                );
                break;
        }
        
        if ($decrypted === false) {
            throw new EncryptionException("解密失败: " . openssl_error_string());
        }
        
        return $decrypted;
    }
    
    /**
     * 生成安全哈希
     * 
     * @param string $data 要哈希的数据
     * @param string $salt 盐值
     * @return string 哈希结果
     */
    public function hash(string $data, string $salt = ""): string
    {
        $algorithm = $this->config["hash_algorithm"];
        return hash_hmac($algorithm, $data, $salt ?: $this->systemKey);
    }
    
    /**
     * 验证哈希
     * 
     * @param string $data 原始数据
     * @param string $hash 哈希值
     * @param string $salt 盐值
     * @return bool 验证结果
     */
    public function verifyHash(string $data, string $hash, string $salt = ""): bool
    {
        return hash_equals($hash, $this->hash($data, $salt));
    }
    
    /**
     * 为API请求加密数据
     * 
     * @param array $data 要加密的数据
     * @return array 加密后的数据包
     */
    public function encryptApiData(array $data): array
    {
        if (!$this->config["api_encryption"]) {
            return $data;
        }
        
        $jsonData = json_encode($data);
        $encrypted = $this->encrypt($jsonData, "api");
        
        return [
            "encrypted" => true,
            "data" => $encrypted["data"],
            "iv" => $encrypted["iv"],
            "algorithm" => $encrypted["algorithm"],
            "timestamp" => $encrypted["timestamp"],
            "key_id" => $encrypted["key_id"] ?? null,
            "tag" => $encrypted["tag"] ?? null
        ];
    }
    
    /**
     * 解密API数据
     * 
     * @param array $encryptedPackage 加密的数据包
     * @return array 解密后的数据
     */
    public function decryptApiData(array $encryptedPackage): array
    {
        if (!isset($encryptedPackage["encrypted"]) || $encryptedPackage["encrypted"] !== true) {
            return $encryptedPackage;
        }
        
        $decrypted = $this->decrypt($encryptedPackage);
        return json_decode($decrypted, true);
    }
    
    /**
     * 获取加密服务状态
     * 
     * @return array 状态信息
     */
    public function getStatus(): array
    {
        $status = [
            "default_algorithm" => $this->config["default_algorithm"],
            "api_encryption_enabled" => $this->config["api_encryption"],
            "data_encryption_enabled" => $this->config["data_encryption"],
            "quantum_encryption_enabled" => $this->config["enable_quantum_encryption"] && $this->quantumService !== null,
            "system_key_configured" => isset($_ENV["SYSTEM_ENCRYPTION_KEY"]),
        ];
        
        if ($this->quantumService) {
            $status["quantum_service"] = $this->quantumService->getStatus();
        }
        
        return $status;
    }
}

</rewritten_file>

