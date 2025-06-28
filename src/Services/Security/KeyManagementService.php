<?php

namespace AlingAi\Services\Security;

use AlingAi\Models\Security\CryptoKey;
use AlingAi\Utils\Logger;
use AlingAi\Utils\Database;

/**
 * 密钥管理服务
 * 管理加密密钥的创建、存储、检索和撤销
 *
 * @package AlingAi\Services\Security
 */
class KeyManagementService
{
    /**
     * 数据库连接
     *
     * @var Database
     */
    protected $db;
    
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
        $this->db = new Database();
        $this->logger = new Logger('key_management');
    }
    
    /**
     * 保存密钥对
     * 
     * @param int $userId 用户ID
     * @param string $keyName 密钥名称
     * @param string $publicKey 公钥
     * @param string $privateKey 私钥
     * @param int|null $expiry 过期时间戳
     * @return string 密钥ID
     * @throws \Exception 保存失败时抛出异常
     */
    public function saveKeyPair($userId, $keyName, $publicKey, $privateKey, $expiry = null)
    {
        try {
            // 生成唯一密钥ID
            $keyId = $this->generateKeyId();
            
            // 准备密钥数据
            $keyData = [
                'key_id' => $keyId,
                'user_id' => $userId,
                'key_name' => $keyName,
                'public_key' => $publicKey,
                'private_key' => $this->encryptPrivateKey($privateKey, $userId),
                'algorithm' => 'dilithium',
                'created_at' => time(),
                'expires_at' => $expiry,
                'status' => 'active',
                'key_type' => 'asymmetric'
            ];
            
            // 保存到数据库
            $this->db->insert('crypto_keys', $keyData);
            
            // 记录操作
            $this->logger->info('密钥对已保存', [
                'key_id' => $keyId,
                'user_id' => $userId,
                'key_name' => $keyName
            ]);
            
            return $keyId;
        } catch (\Exception $e) {
            $this->logger->error('保存密钥对失败', [
                'error' => $e->getMessage(),
                'user_id' => $userId
            ]);
            throw new \Exception('保存密钥对失败: ' . $e->getMessage());
        }
    }
    
    /**
     * 保存对称密钥
     * 
     * @param int $userId 用户ID
     * @param string $keyName 密钥名称
     * @param string $key 密钥
     * @param int|null $expiry 过期时间戳
     * @return string 密钥ID
     * @throws \Exception 保存失败时抛出异常
     */
    public function saveSymmetricKey($userId, $keyName, $key, $expiry = null)
    {
        try {
            // 生成唯一密钥ID
            $keyId = $this->generateKeyId();
            
            // 准备密钥数据
            $keyData = [
                'key_id' => $keyId,
                'user_id' => $userId,
                'key_name' => $keyName,
                'key' => $this->encryptSymmetricKey($key, $userId),
                'algorithm' => 'aes-256-gcm',
                'created_at' => time(),
                'expires_at' => $expiry,
                'status' => 'active',
                'key_type' => 'symmetric'
            ];
            
            // 保存到数据库
            $this->db->insert('crypto_keys', $keyData);
            
            // 记录操作
            $this->logger->info('对称密钥已保存', [
                'key_id' => $keyId,
                'user_id' => $userId,
                'key_name' => $keyName
            ]);
            
            return $keyId;
        } catch (\Exception $e) {
            $this->logger->error('保存对称密钥失败', [
                'error' => $e->getMessage(),
                'user_id' => $userId
            ]);
            throw new \Exception('保存对称密钥失败: ' . $e->getMessage());
        }
    }
    
    /**
     * 获取密钥信息
     * 
     * @param string $keyId 密钥ID
     * @return array|null 密钥信息，如果不存在则返回null
     * @throws \Exception 获取失败时抛出异常
     */
    public function getKeyById($keyId)
    {
        try {
            // 从数据库查询密钥
            $key = $this->db->query(
                "SELECT * FROM crypto_keys WHERE key_id = ? AND status = 'active'",
                [$keyId]
            )->fetch();
            
            if (!$key) {
                return null;
            }
            
            // 解密私钥或对称密钥
            if ($key['key_type'] === 'asymmetric' && isset($key['private_key'])) {
                $key['private_key'] = $this->decryptPrivateKey($key['private_key'], $key['user_id']);
            } elseif ($key['key_type'] === 'symmetric' && isset($key['key'])) {
                $key['key'] = $this->decryptSymmetricKey($key['key'], $key['user_id']);
            }
            
            // 检查密钥是否过期
            if ($key['expires_at'] && $key['expires_at'] < time()) {
                // 自动将过期密钥标记为过期
                $this->db->update('crypto_keys', ['status' => 'expired'], ['key_id' => $keyId]);
                $this->logger->info('密钥已过期', ['key_id' => $keyId]);
                return null;
            }
            
            return $key;
        } catch (\Exception $e) {
            $this->logger->error('获取密钥失败', [
                'error' => $e->getMessage(),
                'key_id' => $keyId
            ]);
            throw new \Exception('获取密钥失败: ' . $e->getMessage());
        }
    }
    
    /**
     * 获取用户的密钥列表
     * 
     * @param int $userId 用户ID
     * @return array 密钥列表
     * @throws \Exception 获取失败时抛出异常
     */
    public function getUserKeys($userId)
    {
        try {
            // 从数据库查询用户密钥
            $keys = $this->db->query(
                "SELECT key_id, key_name, algorithm, created_at, expires_at, status, key_type FROM crypto_keys WHERE user_id = ?",
                [$userId]
            )->fetchAll();
            
            // 记录操作
            $this->logger->info('获取用户密钥列表', [
                'user_id' => $userId,
                'count' => count($keys)
            ]);
            
            return $keys;
        } catch (\Exception $e) {
            $this->logger->error('获取用户密钥列表失败', [
                'error' => $e->getMessage(),
                'user_id' => $userId
            ]);
            throw new \Exception('获取用户密钥列表失败: ' . $e->getMessage());
        }
    }
    
    /**
     * 撤销密钥
     * 
     * @param string $keyId 密钥ID
     * @return bool 是否成功撤销
     * @throws \Exception 撤销失败时抛出异常
     */
    public function revokeKey($keyId)
    {
        try {
            // 更新密钥状态为已撤销
            $result = $this->db->update(
                'crypto_keys',
                ['status' => 'revoked', 'revoked_at' => time()],
                ['key_id' => $keyId]
            );
            
            if ($result) {
                // 记录操作
                $this->logger->info('密钥已撤销', ['key_id' => $keyId]);
                return true;
            }
            
            return false;
        } catch (\Exception $e) {
            $this->logger->error('撤销密钥失败', [
                'error' => $e->getMessage(),
                'key_id' => $keyId
            ]);
            throw new \Exception('撤销密钥失败: ' . $e->getMessage());
        }
    }
    
    /**
     * 轮换密钥
     * 
     * @param string $oldKeyId 旧密钥ID
     * @param string $newKeyName 新密钥名称
     * @return string 新密钥ID
     * @throws \Exception 轮换失败时抛出异常
     */
    public function rotateKey($oldKeyId, $newKeyName = null)
    {
        try {
            // 获取旧密钥信息
            $oldKey = $this->getKeyById($oldKeyId);
            if (!$oldKey) {
                throw new \Exception('旧密钥不存在或已过期');
            }
            
            // 确定新密钥名称
            $keyName = $newKeyName ?: $oldKey['key_name'] . ' (rotated)';
            
            // 根据密钥类型创建新密钥
            if ($oldKey['key_type'] === 'asymmetric') {
                // 创建新的密钥对
                $encryptionService = new QuantumEncryptionService();
                $keyPair = $encryptionService->generateKeyPair($oldKey['algorithm']);
                
                $newKeyId = $this->saveKeyPair(
                    $oldKey['user_id'],
                    $keyName,
                    $keyPair['public_key'],
                    $keyPair['private_key'],
                    $oldKey['expires_at']
                );
            } else {
                // 创建新的对称密钥
                $encryptionService = new QuantumEncryptionService();
                $key = $encryptionService->generateRandomKey();
                
                $newKeyId = $this->saveSymmetricKey(
                    $oldKey['user_id'],
                    $keyName,
                    $key,
                    $oldKey['expires_at']
                );
            }
            
            // 撤销旧密钥
            $this->revokeKey($oldKeyId);
            
            // 记录操作
            $this->logger->info('密钥已轮换', [
                'old_key_id' => $oldKeyId,
                'new_key_id' => $newKeyId
            ]);
            
            return $newKeyId;
        } catch (\Exception $e) {
            $this->logger->error('轮换密钥失败', [
                'error' => $e->getMessage(),
                'old_key_id' => $oldKeyId
            ]);
            throw new \Exception('轮换密钥失败: ' . $e->getMessage());
        }
    }
    
    /**
     * 生成唯一密钥ID
     * 
     * @return string 密钥ID
     */
    protected function generateKeyId()
    {
        // 生成UUID v4
        $data = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
        
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
    
    /**
     * 加密私钥
     * 
     * @param string $privateKey 私钥
     * @param int $userId 用户ID
     * @return string 加密后的私钥
     */
    protected function encryptPrivateKey($privateKey, $userId)
    {
        // 在实际应用中，这里应该使用主密钥或用户派生密钥进行加密
        // 这里使用简化的实现
        $masterKey = $this->getMasterKeyForUser($userId);
        $encryptionService = new QuantumEncryptionService();
        
        return $encryptionService->encrypt($privateKey, $masterKey);
    }
    
    /**
     * 解密私钥
     * 
     * @param string $encryptedPrivateKey 加密的私钥
     * @param int $userId 用户ID
     * @return string 解密后的私钥
     */
    protected function decryptPrivateKey($encryptedPrivateKey, $userId)
    {
        // 在实际应用中，这里应该使用主密钥或用户派生密钥进行解密
        // 这里使用简化的实现
        $masterKey = $this->getMasterKeyForUser($userId);
        $encryptionService = new QuantumEncryptionService();
        
        return $encryptionService->decrypt($encryptedPrivateKey, $masterKey);
    }
    
    /**
     * 加密对称密钥
     * 
     * @param string $key 对称密钥
     * @param int $userId 用户ID
     * @return string 加密后的对称密钥
     */
    protected function encryptSymmetricKey($key, $userId)
    {
        // 在实际应用中，这里应该使用主密钥或用户派生密钥进行加密
        // 这里使用简化的实现
        $masterKey = $this->getMasterKeyForUser($userId);
        $encryptionService = new QuantumEncryptionService();
        
        return $encryptionService->encrypt($key, $masterKey);
    }
    
    /**
     * 解密对称密钥
     * 
     * @param string $encryptedKey 加密的对称密钥
     * @param int $userId 用户ID
     * @return string 解密后的对称密钥
     */
    protected function decryptSymmetricKey($encryptedKey, $userId)
    {
        // 在实际应用中，这里应该使用主密钥或用户派生密钥进行解密
        // 这里使用简化的实现
        $masterKey = $this->getMasterKeyForUser($userId);
        $encryptionService = new QuantumEncryptionService();
        
        return $encryptionService->decrypt($encryptedKey, $masterKey);
    }
    
    /**
     * 获取用户的主密钥
     * 
     * @param int $userId 用户ID
     * @return string 主密钥
     */
    protected function getMasterKeyForUser($userId)
    {
        // 在实际应用中，这里应该从安全存储中获取主密钥或派生用户密钥
        // 这里使用简化的实现（不安全，仅用于演示）
        $appSecret = getenv('APP_SECRET') ?: 'default-app-secret-key';
        return hash_hmac('sha256', 'user-master-key-' . $userId, $appSecret);
    }
} 