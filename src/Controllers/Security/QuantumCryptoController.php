<?php

namespace AlingAi\Controllers\Security;

use AlingAi\Models\User;
use AlingAi\Services\Security\QuantumEncryptionService;
use AlingAi\Services\Security\KeyManagementService;
use AlingAi\Utils\ResponseHelper;
use AlingAi\Utils\RequestValidator;

/**
 * 量子加密控制器
 * 处理与量子加密相关的API请求
 *
 * @package AlingAi\Controllers\Security
 */
class QuantumCryptoController
{
    /**
     * 量子加密服务
     *
     * @var QuantumEncryptionService
     */
    protected $encryptionService;
    
    /**
     * 密钥管理服务
     *
     * @var KeyManagementService
     */
    protected $keyManagementService;
    
    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->encryptionService = new QuantumEncryptionService();
        $this->keyManagementService = new KeyManagementService();
    }
    
    /**
     * 加密数据
     * 
     * @param array $request 请求数据
     * @return array 响应数据
     */
    public function encrypt($request)
    {
        // 验证请求参数
        $validator = new RequestValidator($request);
        $validator->required(['data', 'key_id']);
        
        if ($validator->fails()) {
            return ResponseHelper::error('请求参数无效', $validator->errors(), 400);
        }
        
        try {
            // 获取密钥
            $keyData = $this->keyManagementService->getKeyById($request['key_id']);
            if (!$keyData) {
                return ResponseHelper::error('密钥不存在', null, 404);
            }
            
            // 加密数据
            $encryptedData = $this->encryptionService->encrypt(
                $request['data'],
                $keyData['key'],
                isset($request['additional_data']) ? $request['additional_data'] : null
            );
            
            // 记录加密操作
            $this->logOperation('encrypt', $request['key_id'], $request['user_id'] ?? null);
            
            return ResponseHelper::success([
                'encrypted_data' => $encryptedData,
                'timestamp' => time(),
                'algorithm' => 'quantum-resistant-aes-256-gcm'
            ]);
        } catch (\Exception $e) {
            return ResponseHelper::error('加密失败: ' . $e->getMessage(), null, 500);
        }
    }
    
    /**
     * 解密数据
     * 
     * @param array $request 请求数据
     * @return array 响应数据
     */
    public function decrypt($request)
    {
        // 验证请求参数
        $validator = new RequestValidator($request);
        $validator->required(['encrypted_data', 'key_id']);
        
        if ($validator->fails()) {
            return ResponseHelper::error('请求参数无效', $validator->errors(), 400);
        }
        
        try {
            // 获取密钥
            $keyData = $this->keyManagementService->getKeyById($request['key_id']);
            if (!$keyData) {
                return ResponseHelper::error('密钥不存在', null, 404);
            }
            
            // 解密数据
            $decryptedData = $this->encryptionService->decrypt(
                $request['encrypted_data'],
                $keyData['key'],
                isset($request['additional_data']) ? $request['additional_data'] : null
            );
            
            // 记录解密操作
            $this->logOperation('decrypt', $request['key_id'], $request['user_id'] ?? null);
            
            return ResponseHelper::success([
                'decrypted_data' => $decryptedData,
                'timestamp' => time()
            ]);
        } catch (\Exception $e) {
            return ResponseHelper::error('解密失败: ' . $e->getMessage(), null, 500);
        }
    }
    
    /**
     * 签名数据
     * 
     * @param array $request 请求数据
     * @return array 响应数据
     */
    public function sign($request)
    {
        // 验证请求参数
        $validator = new RequestValidator($request);
        $validator->required(['data', 'key_id']);
        
        if ($validator->fails()) {
            return ResponseHelper::error('请求参数无效', $validator->errors(), 400);
        }
        
        try {
            // 获取密钥
            $keyData = $this->keyManagementService->getKeyById($request['key_id']);
            if (!$keyData) {
                return ResponseHelper::error('密钥不存在', null, 404);
            }
            
            // 签名数据
            $signature = $this->encryptionService->sign(
                $request['data'],
                $keyData['private_key']
            );
            
            // 记录签名操作
            $this->logOperation('sign', $request['key_id'], $request['user_id'] ?? null);
            
            return ResponseHelper::success([
                'signature' => $signature,
                'timestamp' => time(),
                'algorithm' => 'quantum-resistant-dilithium'
            ]);
        } catch (\Exception $e) {
            return ResponseHelper::error('签名失败: ' . $e->getMessage(), null, 500);
        }
    }
    
    /**
     * 验证签名
     * 
     * @param array $request 请求数据
     * @return array 响应数据
     */
    public function verify($request)
    {
        // 验证请求参数
        $validator = new RequestValidator($request);
        $validator->required(['data', 'signature', 'key_id']);
        
        if ($validator->fails()) {
            return ResponseHelper::error('请求参数无效', $validator->errors(), 400);
        }
        
        try {
            // 获取密钥
            $keyData = $this->keyManagementService->getKeyById($request['key_id']);
            if (!$keyData) {
                return ResponseHelper::error('密钥不存在', null, 404);
            }
            
            // 验证签名
            $isValid = $this->encryptionService->verify(
                $request['data'],
                $request['signature'],
                $keyData['public_key']
            );
            
            // 记录验证操作
            $this->logOperation('verify', $request['key_id'], $request['user_id'] ?? null);
            
            return ResponseHelper::success([
                'is_valid' => $isValid,
                'timestamp' => time()
            ]);
        } catch (\Exception $e) {
            return ResponseHelper::error('验证失败: ' . $e->getMessage(), null, 500);
        }
    }
    
    /**
     * 生成新的密钥对
     * 
     * @param array $request 请求数据
     * @return array 响应数据
     */
    public function generateKeyPair($request)
    {
        // 验证请求参数
        $validator = new RequestValidator($request);
        $validator->required(['user_id', 'key_name']);
        
        if ($validator->fails()) {
            return ResponseHelper::error('请求参数无效', $validator->errors(), 400);
        }
        
        try {
            // 生成密钥对
            $keyPair = $this->encryptionService->generateKeyPair(
                isset($request['algorithm']) ? $request['algorithm'] : 'dilithium'
            );
            
            // 保存密钥对
            $keyId = $this->keyManagementService->saveKeyPair(
                $request['user_id'],
                $request['key_name'],
                $keyPair['public_key'],
                $keyPair['private_key'],
                isset($request['expiry']) ? $request['expiry'] : null
            );
            
            return ResponseHelper::success([
                'key_id' => $keyId,
                'key_name' => $request['key_name'],
                'algorithm' => isset($request['algorithm']) ? $request['algorithm'] : 'dilithium',
                'created_at' => time()
            ]);
        } catch (\Exception $e) {
            return ResponseHelper::error('生成密钥对失败: ' . $e->getMessage(), null, 500);
        }
    }
    
    /**
     * 获取用户密钥列表
     * 
     * @param array $request 请求数据
     * @return array 响应数据
     */
    public function getUserKeys($request)
    {
        // 验证请求参数
        $validator = new RequestValidator($request);
        $validator->required(['user_id']);
        
        if ($validator->fails()) {
            return ResponseHelper::error('请求参数无效', $validator->errors(), 400);
        }
        
        try {
            // 获取用户密钥列表
            $keys = $this->keyManagementService->getUserKeys($request['user_id']);
            
            return ResponseHelper::success([
                'keys' => $keys,
                'count' => count($keys)
            ]);
        } catch (\Exception $e) {
            return ResponseHelper::error('获取密钥列表失败: ' . $e->getMessage(), null, 500);
        }
    }
    
    /**
     * 撤销密钥
     * 
     * @param array $request 请求数据
     * @return array 响应数据
     */
    public function revokeKey($request)
    {
        // 验证请求参数
        $validator = new RequestValidator($request);
        $validator->required(['key_id', 'user_id']);
        
        if ($validator->fails()) {
            return ResponseHelper::error('请求参数无效', $validator->errors(), 400);
        }
        
        try {
            // 验证密钥所有权
            $keyData = $this->keyManagementService->getKeyById($request['key_id']);
            if (!$keyData) {
                return ResponseHelper::error('密钥不存在', null, 404);
            }
            
            if ($keyData['user_id'] != $request['user_id']) {
                return ResponseHelper::error('无权操作此密钥', null, 403);
            }
            
            // 撤销密钥
            $this->keyManagementService->revokeKey($request['key_id']);
            
            return ResponseHelper::success([
                'message' => '密钥已成功撤销',
                'revoked_at' => time()
            ]);
        } catch (\Exception $e) {
            return ResponseHelper::error('撤销密钥失败: ' . $e->getMessage(), null, 500);
        }
    }
    
    /**
     * 记录加密操作
     * 
     * @param string $operation 操作类型
     * @param string $keyId 密钥ID
     * @param int|null $userId 用户ID
     * @return void
     */
    protected function logOperation($operation, $keyId, $userId = null)
    {
        try {
            // 记录操作日志
            $logData = [
                'operation' => $operation,
                'key_id' => $keyId,
                'user_id' => $userId,
                'timestamp' => time(),
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
            ];
            
            // 保存日志
            // 这里可以使用日志服务保存操作记录
        } catch (\Exception $e) {
            // 记录日志失败不应影响主要功能
            error_log('记录加密操作日志失败: ' . $e->getMessage());
        }
    }
}
