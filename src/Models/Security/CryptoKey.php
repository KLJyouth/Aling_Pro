<?php

namespace AlingAi\Models\Security;

use AlingAi\Models\BaseModel;
use AlingAi\Utils\Database;

/**
 * 加密密钥模型
 * 管理系统中的加密密钥
 *
 * @package AlingAi\Models\Security
 */
class CryptoKey extends BaseModel
{
    /**
     * 表名
     *
     * @var string
     */
    protected $table = 'crypto_keys';
    
    /**
     * 主键
     *
     * @var string
     */
    protected $primaryKey = 'key_id';
    
    /**
     * 可填充字段
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'key_name',
        'key_type',
        'public_key',
        'private_key',
        'algorithm',
        'status',
        'created_at',
        'expires_at',
        'last_used_at',
        'revoked_at'
    ];
    
    /**
     * 隐藏字段
     *
     * @var array
     */
    protected $hidden = [
        'private_key'
    ];
    
    /**
     * 创建新密钥
     *
     * @param array $data 密钥数据
     * @return string|bool 密钥ID或失败
     */
    public function createKey($data)
    {
        try {
            // 生成唯一密钥ID
            $keyId = $this->generateKeyId();
            
            // 准备密钥数据
            $keyData = [
                'key_id' => $keyId,
                'user_id' => $data['user_id'],
                'key_name' => $data['key_name'],
                'key_type' => $data['key_type'] ?? 'dilithium',
                'public_key' => $data['public_key'],
                'private_key' => $data['private_key'] ?? null,
                'algorithm' => $data['algorithm'] ?? 'dilithium5',
                'status' => $data['status'] ?? 'active',
                'created_at' => time(),
                'expires_at' => $data['expires_at'] ?? null,
                'last_used_at' => null,
                'revoked_at' => null
            ];
            
            // 保存到数据库
            $this->db->insert($this->table, $keyData);
            
            return $keyId;
        } catch (\Exception $e) {
            $this->logger->error('创建密钥失败', ['error' => $e->getMessage()]);
            return false;
        }
    }
    
    /**
     * 获取用户的密钥
     *
     * @param int $userId 用户ID
     * @param string|null $status 密钥状态
     * @return array 密钥列表
     */
    public function getUserKeys($userId, $status = 'active')
    {
        try {
            $query = "SELECT * FROM {$this->table} WHERE user_id = ?";
            $params = [$userId];
            
            if ($status) {
                $query .= " AND status = ?";
                $params[] = $status;
            }
            
            $query .= " ORDER BY created_at DESC";
            
            return $this->db->query($query, $params)->fetchAll();
        } catch (\Exception $e) {
            $this->logger->error('获取用户密钥失败', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }
    
    /**
     * 获取密钥详情
     *
     * @param string $keyId 密钥ID
     * @param int|null $userId 用户ID（可选，用于验证所有权）
     * @return array|null 密钥详情
     */
    public function getKey($keyId, $userId = null)
    {
        try {
            $query = "SELECT * FROM {$this->table} WHERE key_id = ?";
            $params = [$keyId];
            
            if ($userId) {
                $query .= " AND user_id = ?";
                $params[] = $userId;
            }
            
            $key = $this->db->query($query, $params)->fetch();
            
            return $key ?: null;
        } catch (\Exception $e) {
            $this->logger->error('获取密钥详情失败', [
                'key_id' => $keyId,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }
    
    /**
     * 更新密钥状态
     *
     * @param string $keyId 密钥ID
     * @param string $status 新状态
     * @param int|null $userId 用户ID（可选，用于验证所有权）
     * @return bool 是否成功
     */
    public function updateKeyStatus($keyId, $status, $userId = null)
    {
        try {
            $updateData = ['status' => $status];
            
            // 如果是撤销，记录撤销时间
            if ($status === 'revoked') {
                $updateData['revoked_at'] = time();
            }
            
            $whereConditions = ['key_id' => $keyId];
            
            // 如果提供了用户ID，添加到条件中
            if ($userId) {
                $whereConditions['user_id'] = $userId;
            }
            
            $this->db->update($this->table, $updateData, $whereConditions);
            
            return true;
        } catch (\Exception $e) {
            $this->logger->error('更新密钥状态失败', [
                'key_id' => $keyId,
                'status' => $status,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * 更新密钥最后使用时间
     *
     * @param string $keyId 密钥ID
     * @return bool 是否成功
     */
    public function updateLastUsed($keyId)
    {
        try {
            $this->db->update($this->table, [
                'last_used_at' => time()
            ], ['key_id' => $keyId]);
            
            return true;
        } catch (\Exception $e) {
            $this->logger->error('更新密钥使用时间失败', [
                'key_id' => $keyId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * 删除密钥
     *
     * @param string $keyId 密钥ID
     * @param int|null $userId 用户ID（可选，用于验证所有权）
     * @return bool 是否成功
     */
    public function deleteKey($keyId, $userId = null)
    {
        try {
            $whereConditions = ['key_id' => $keyId];
            
            // 如果提供了用户ID，添加到条件中
            if ($userId) {
                $whereConditions['user_id'] = $userId;
            }
            
            $this->db->delete($this->table, $whereConditions);
            
            return true;
        } catch (\Exception $e) {
            $this->logger->error('删除密钥失败', [
                'key_id' => $keyId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * 生成唯一密钥ID
     *
     * @return string 密钥ID
     */
    protected function generateKeyId()
    {
        return 'key_' . uniqid() . '_' . time();
    }
} 