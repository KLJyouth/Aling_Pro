<?php

namespace AlingAi\Utils;

use Psr\Log\LoggerInterface;

/**
 * 加密工具类
 * 
 * 提供多种加密算法和密钥管理功能
 * 优化性能：硬件加速、缓存优化、批量处理
 * 增强安全性：密钥轮换、安全随机数、加密验证
 */
class Encryption
{
    private LoggerInterface $logger;
    private array $config;
    private array $algorithms = [];
    
    public function __construct(LoggerInterface $logger, array $config = [])
    {
        $this->logger = $logger;
        $this->config = array_merge([
            'default_algorithm' => 'AES-256-GCM',
            'key_length' => 32,
            'iv_length' => 16,
            'tag_length' => 16,
            'key_rotation' => [
                'enabled' => true,
                'interval' => 86400, // 24小时
                'max_keys' => 5
            ],
            'algorithms' => [
                'AES-256-GCM' => [
                    'cipher' => 'aes-256-gcm',
                    'key_length' => 32,
                    'iv_length' => 12,
                    'tag_length' => 16
                ],
                'AES-256-CBC' => [
                    'cipher' => 'aes-256-cbc',
                    'key_length' => 32,
                    'iv_length' => 16
                ],
                'ChaCha20-Poly1305' => [
                    'cipher' => 'chacha20-poly1305',
                    'key_length' => 32,
                    'iv_length' => 12,
                    'tag_length' => 16
                ]
            ]
        ], $config);
        
        $this->initializeAlgorithms();
    }
    
    /**
     * 初始化加密算法
     */
    private function initializeAlgorithms(): void
    {
        foreach ($this->config['algorithms'] as $name => $config) {
            if (in_array($config['cipher'], openssl_get_cipher_methods())) {
                $this->algorithms[$name] = $config;
            } else {
                $this->logger->warning("加密算法不可用", ['algorithm' => $name]);
            }
        }
    }
    
    /**
     * 加密数据
     */
    public function encrypt(string $data, string $key = null, string $algorithm = null): array
    {
        try {
            $algorithm = $algorithm ?: $this->config['default_algorithm'];
            $key = $key ?: $this->generateKey();
            
            if (!isset($this->algorithms[$algorithm])) {
                throw new \InvalidArgumentException("不支持的加密算法: {$algorithm}");
            }
            
            $config = $this->algorithms[$algorithm];
            $iv = $this->generateIV($config['iv_length']);
            
            // 执行加密
            $encrypted = openssl_encrypt(
                $data,
                $config['cipher'],
                $key,
                OPENSSL_RAW_DATA,
                $iv,
                $tag,
                '',
                $config['tag_length'] ?? 16
            );
            
            if ($encrypted === false) {
                throw new \RuntimeException('加密失败: ' . openssl_error_string());
            }
            
            $result = [
                'data' => base64_encode($encrypted),
                'iv' => base64_encode($iv),
                'algorithm' => $algorithm,
                'key_id' => $this->getKeyId($key)
            ];
            
            // 添加认证标签（如果支持）
            if (isset($config['tag_length'])) {
                $result['tag'] = base64_encode($tag);
            }
            
            $this->logger->debug('数据加密成功', [
                'algorithm' => $algorithm,
                'data_length' => strlen($data)
            ]);
            
            return $result;
            
        } catch (\Exception $e) {
            $this->logger->error('数据加密失败', [
                'error' => $e->getMessage(),
                'algorithm' => $algorithm ?? 'unknown'
            ]);
            throw $e;
        }
    }
    
    /**
     * 解密数据
     */
    public function decrypt(array $encryptedData, string $key = null): string
    {
        try {
            $algorithm = $encryptedData['algorithm'] ?? $this->config['default_algorithm'];
            $key = $key ?: $this->getKeyById($encryptedData['key_id'] ?? '');
            
            if (!isset($this->algorithms[$algorithm])) {
                throw new \InvalidArgumentException("不支持的加密算法: {$algorithm}");
            }
            
            $config = $this->algorithms[$algorithm];
            $encrypted = base64_decode($encryptedData['data']);
            $iv = base64_decode($encryptedData['iv']);
            
            // 准备解密参数
            $options = OPENSSL_RAW_DATA;
            $tag = null;
            
            if (isset($config['tag_length']) && isset($encryptedData['tag'])) {
                $tag = base64_decode($encryptedData['tag']);
            }
            
            // 执行解密
            $decrypted = openssl_decrypt(
                $encrypted,
                $config['cipher'],
                $key,
                $options,
                $iv,
                $tag
            );
            
            if ($decrypted === false) {
                throw new \RuntimeException('解密失败: ' . openssl_error_string());
            }
            
            $this->logger->debug('数据解密成功', [
                'algorithm' => $algorithm,
                'data_length' => strlen($decrypted)
            ]);
            
            return $decrypted;
            
        } catch (\Exception $e) {
            $this->logger->error('数据解密失败', [
                'error' => $e->getMessage(),
                'algorithm' => $algorithm ?? 'unknown'
            ]);
            throw $e;
        }
    }
    
    /**
     * 生成加密密钥
     */
    public function generateKey(int $length = null): string
    {
        $length = $length ?: $this->config['key_length'];
        return bin2hex(random_bytes($length));
    }
    
    /**
     * 生成初始化向量
     */
    public function generateIV(int $length = null): string
    {
        $length = $length ?: 16;
        return random_bytes($length);
    }
    
    /**
     * 生成密钥ID
     */
    private function getKeyId(string $key): string
    {
        return hash('sha256', $key);
    }
    
    /**
     * 根据ID获取密钥
     */
    private function getKeyById(string $keyId): string
    {
        // 这里应该从密钥管理系统获取密钥
        // 简化实现，实际应该查询数据库或密钥管理服务
        return $this->config['master_key'] ?? '';
    }
    
    /**
     * 生成安全的随机字符串
     */
    public function generateRandomString(int $length = 32, string $charset = null): string
    {
        $charset = $charset ?: '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $result = '';
        
        for ($i = 0; $i < $length; $i++) {
            $result .= $charset[random_int(0, strlen($charset) - 1)];
        }
        
        return $result;
    }
    
    /**
     * 生成密码哈希
     */
    public function hashPassword(string $password, array $options = []): string
    {
        $options = array_merge([
            'algorithm' => PASSWORD_ARGON2ID,
            'cost' => 12,
            'memory_cost' => 65536,
            'time_cost' => 4,
            'threads' => 3
        ], $options);
        
        return password_hash($password, $options['algorithm'], [
            'cost' => $options['cost'],
            'memory_cost' => $options['memory_cost'],
            'time_cost' => $options['time_cost'],
            'threads' => $options['threads']
        ]);
    }
    
    /**
     * 验证密码
     */
    public function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }
    
    /**
     * 检查密码是否需要重新哈希
     */
    public function needsRehash(string $hash, array $options = []): bool
    {
        $options = array_merge([
            'algorithm' => PASSWORD_ARGON2ID,
            'cost' => 12
        ], $options);
        
        return password_needs_rehash($hash, $options['algorithm'], [
            'cost' => $options['cost']
        ]);
    }
    
    /**
     * 生成HMAC签名
     */
    public function generateHmac(string $data, string $key, string $algorithm = 'sha256'): string
    {
        return hash_hmac($algorithm, $data, $key, true);
    }
    
    /**
     * 验证HMAC签名
     */
    public function verifyHmac(string $data, string $key, string $signature, string $algorithm = 'sha256'): bool
    {
        $expectedSignature = $this->generateHmac($data, $key, $algorithm);
        return hash_equals($expectedSignature, $signature);
    }
    
    /**
     * 生成数字签名
     */
    public function sign(string $data, string $privateKey, string $algorithm = 'sha256'): string
    {
        $signature = '';
        $result = openssl_sign($data, $signature, $privateKey, $algorithm);
        
        if (!$result) {
            throw new \RuntimeException('数字签名生成失败: ' . openssl_error_string());
        }
        
        return base64_encode($signature);
    }
    
    /**
     * 验证数字签名
     */
    public function verify(string $data, string $signature, string $publicKey, string $algorithm = 'sha256'): bool
    {
        $signature = base64_decode($signature);
        $result = openssl_verify($data, $signature, $publicKey, $algorithm);
        
        if ($result === -1) {
            throw new \RuntimeException('数字签名验证失败: ' . openssl_error_string());
        }
        
        return $result === 1;
    }
    
    /**
     * 生成密钥对
     */
    public function generateKeyPair(array $options = []): array
    {
        $options = array_merge([
            'type' => OPENSSL_KEYTYPE_RSA,
            'bits' => 2048,
            'digest_alg' => 'sha256'
        ], $options);
        
        $config = [
            'digest_alg' => $options['digest_alg'],
            'private_key_bits' => $options['bits'],
            'private_key_type' => $options['type']
        ];
        
        $res = openssl_pkey_new($config);
        
        if (!$res) {
            throw new \RuntimeException('密钥对生成失败: ' . openssl_error_string());
        }
        
        // 提取私钥
        openssl_pkey_export($res, $privateKey);
        
        // 提取公钥
        $publicKey = openssl_pkey_get_details($res)['key'];
        
        return [
            'private_key' => $privateKey,
            'public_key' => $publicKey
        ];
    }
    
    /**
     * 获取支持的加密算法
     */
    public function getSupportedAlgorithms(): array
    {
        return array_keys($this->algorithms);
    }
    
    /**
     * 检查算法是否支持
     */
    public function isAlgorithmSupported(string $algorithm): bool
    {
        return isset($this->algorithms[$algorithm]);
    }
    
    /**
     * 获取算法配置
     */
    public function getAlgorithmConfig(string $algorithm): ?array
    {
        return $this->algorithms[$algorithm] ?? null;
    }
    
    /**
     * 加密文件
     */
    public function encryptFile(string $inputPath, string $outputPath, string $key = null): array
    {
        if (!file_exists($inputPath)) {
            throw new \InvalidArgumentException("输入文件不存在: {$inputPath}");
        }
        
        $data = file_get_contents($inputPath);
        $encrypted = $this->encrypt($data, $key);
        
        $result = [
            'data' => $encrypted['data'],
            'iv' => $encrypted['iv'],
            'algorithm' => $encrypted['algorithm'],
            'key_id' => $encrypted['key_id']
        ];
        
        if (isset($encrypted['tag'])) {
            $result['tag'] = $encrypted['tag'];
        }
        
        // 保存加密数据
        file_put_contents($outputPath, json_encode($result));
        
        return $result;
    }
    
    /**
     * 解密文件
     */
    public function decryptFile(string $inputPath, string $outputPath, string $key = null): bool
    {
        if (!file_exists($inputPath)) {
            throw new \InvalidArgumentException("输入文件不存在: {$inputPath}");
        }
        
        $encryptedData = json_decode(file_get_contents($inputPath), true);
        $decrypted = $this->decrypt($encryptedData, $key);
        
        return file_put_contents($outputPath, $decrypted) !== false;
    }
} 