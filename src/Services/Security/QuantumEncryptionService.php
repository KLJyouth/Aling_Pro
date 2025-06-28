<?php

namespace AlingAi\Services\Security;

use AlingAi\Services\Security\Encryption\AESEncryption;
use AlingAi\Services\Security\Encryption\DilithiumSignature;
use AlingAi\Utils\Logger;

/**
 * 量子加密服务
 * 提供量子安全的加密、解密、签名和验证功能
 *
 * @package AlingAi\Services\Security
 */
class QuantumEncryptionService
{
    /**
     * AES加密实现
     *
     * @var AESEncryption
     */
    protected $aesEncryption;
    
    /**
     * Dilithium签名实现
     *
     * @var DilithiumSignature
     */
    protected $dilithiumSignature;
    
    /**
     * 日志记录器
     *
     * @var Logger
     */
    protected $logger;
    
    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->aesEncryption = new AESEncryption();
        $this->dilithiumSignature = new DilithiumSignature();
        $this->logger = new Logger('quantum_encryption');
    }
    
    /**
     * 加密数据
     * 
     * @param mixed $data 要加密的数据
     * @param string $key 加密密钥
     * @param mixed|null $additionalData 附加数据（用于认证）
     * @return string 加密后的数据
     * @throws \Exception 加密失败时抛出异常
     */
    public function encrypt($data, $key, $additionalData = null)
    {
        try {
            // 序列化数据（如果不是字符串）
            if (!is_string($data)) {
                $data = serialize($data);
            }
            
            // 使用AES-GCM进行加密
            $encryptedData = $this->aesEncryption->encrypt($data, $key, $additionalData);
            
            // 记录加密操作
            $this->logger->info('数据加密成功', [
                'key_id' => substr(md5($key), 0, 8),
                'data_size' => strlen($data)
            ]);
            
            return $encryptedData;
        } catch (\Exception $e) {
            $this->logger->error('数据加密失败', [
                'error' => $e->getMessage()
            ]);
            throw new \Exception('加密失败: ' . $e->getMessage());
        }
    }
    
    /**
     * 解密数据
     * 
     * @param string $encryptedData 加密的数据
     * @param string $key 解密密钥
     * @param mixed|null $additionalData 附加数据（用于认证）
     * @return mixed 解密后的数据
     * @throws \Exception 解密失败时抛出异常
     */
    public function decrypt($encryptedData, $key, $additionalData = null)
    {
        try {
            // 使用AES-GCM进行解密
            $decryptedData = $this->aesEncryption->decrypt($encryptedData, $key, $additionalData);
            
            // 尝试反序列化（如果是序列化的数据）
            if ($this->isSerialized($decryptedData)) {
                $decryptedData = unserialize($decryptedData);
            }
            
            // 记录解密操作
            $this->logger->info('数据解密成功', [
                'key_id' => substr(md5($key), 0, 8)
            ]);
            
            return $decryptedData;
        } catch (\Exception $e) {
            $this->logger->error('数据解密失败', [
                'error' => $e->getMessage()
            ]);
            throw new \Exception('解密失败: ' . $e->getMessage());
        }
    }
    
    /**
     * 签名数据
     * 
     * @param mixed $data 要签名的数据
     * @param string $privateKey 私钥
     * @return string 签名
     * @throws \Exception 签名失败时抛出异常
     */
    public function sign($data, $privateKey)
    {
        try {
            // 序列化数据（如果不是字符串）
            if (!is_string($data)) {
                $data = serialize($data);
            }
            
            // 使用Dilithium进行签名
            $signature = $this->dilithiumSignature->sign($data, $privateKey);
            
            // 记录签名操作
            $this->logger->info('数据签名成功', [
                'data_size' => strlen($data)
            ]);
            
            return $signature;
        } catch (\Exception $e) {
            $this->logger->error('数据签名失败', [
                'error' => $e->getMessage()
            ]);
            throw new \Exception('签名失败: ' . $e->getMessage());
        }
    }
    
    /**
     * 验证签名
     * 
     * @param mixed $data 原始数据
     * @param string $signature 签名
     * @param string $publicKey 公钥
     * @return bool 签名是否有效
     * @throws \Exception 验证失败时抛出异常
     */
    public function verify($data, $signature, $publicKey)
    {
        try {
            // 序列化数据（如果不是字符串）
            if (!is_string($data)) {
                $data = serialize($data);
            }
            
            // 使用Dilithium验证签名
            $isValid = $this->dilithiumSignature->verify($data, $signature, $publicKey);
            
            // 记录验证操作
            $this->logger->info('签名验证' . ($isValid ? '成功' : '失败'), [
                'data_size' => strlen($data)
            ]);
            
            return $isValid;
        } catch (\Exception $e) {
            $this->logger->error('签名验证失败', [
                'error' => $e->getMessage()
            ]);
            throw new \Exception('验证失败: ' . $e->getMessage());
        }
    }
    
    /**
     * 生成密钥对
     * 
     * @param string $algorithm 算法（默认为'dilithium'）
     * @return array 包含公钥和私钥的数组
     * @throws \Exception 生成失败时抛出异常
     */
    public function generateKeyPair($algorithm = 'dilithium')
    {
        try {
            // 根据算法生成密钥对
            switch ($algorithm) {
                case 'dilithium':
                    $keyPair = $this->dilithiumSignature->generateKeyPair();
                    break;
                case 'kyber':
                    // 未来可以添加Kyber算法支持
                    throw new \Exception('暂不支持Kyber算法');
                default:
                    throw new \Exception('不支持的算法: ' . $algorithm);
            }
            
            // 记录密钥生成操作
            $this->logger->info('密钥对生成成功', [
                'algorithm' => $algorithm
            ]);
            
            return $keyPair;
        } catch (\Exception $e) {
            $this->logger->error('密钥对生成失败', [
                'error' => $e->getMessage(),
                'algorithm' => $algorithm
            ]);
            throw new \Exception('生成密钥对失败: ' . $e->getMessage());
        }
    }
    
    /**
     * 生成随机密钥
     * 
     * @param int $length 密钥长度（字节）
     * @return string 随机密钥
     */
    public function generateRandomKey($length = 32)
    {
        try {
            // 使用安全的随机数生成器
            $key = random_bytes($length);
            
            return bin2hex($key);
        } catch (\Exception $e) {
            $this->logger->error('随机密钥生成失败', [
                'error' => $e->getMessage()
            ]);
            throw new \Exception('生成随机密钥失败: ' . $e->getMessage());
        }
    }
    
    /**
     * 检查字符串是否是序列化的数据
     * 
     * @param string $data 要检查的数据
     * @return bool 是否为序列化数据
     */
    protected function isSerialized($data)
    {
        // 快速检查是否可能是序列化的数据
        if (!is_string($data) || strlen($data) < 4) {
            return false;
        }
        
        // 检查序列化格式
        $firstChar = $data[0];
        $lastChar = $data[strlen($data) - 1];
        
        // 序列化数据通常以特定字符开头，以";"结尾
        $validFirstChars = ['a', 'O', 's', 'i', 'd', 'b', 'N'];
        
        return in_array($firstChar, $validFirstChars) && $lastChar === ';' && @unserialize($data) !== false;
    }
} 