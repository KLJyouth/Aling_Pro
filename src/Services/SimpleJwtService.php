<?php

namespace AlingAi\Services;

// 确保正确引入Firebase JWT
require_once __DIR__ . '/../../vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

/**
 * 简化的JWT服务
 */
class SimpleJwtService
{
    private string $secret;
    private string $algorithm = 'HS256';
    private int $expireTime = 3600; // 1小时
    
    public function __construct(string $secret = null)
    {
        $this->secret = $secret ?: 'default-jwt-secret-change-in-production';
    }
    
    /**
     * 生成JWT令牌
     */
    public function generateToken(array $data): string
    {
        $payload = array_merge($data, [
            'iat' => time(),
            'exp' => time() + $this->expireTime,
            'iss' => 'alingai-pro'
        ]);
        
        return JWT::encode($payload, $this->secret, $this->algorithm);
    }
    
    /**
     * 生成刷新令牌
     */
    public function generateRefreshToken(int $userId): string
    {
        $payload = [
            'user_id' => $userId,
            'type' => 'refresh',
            'iat' => time(),
            'exp' => time() + (7 * 24 * 3600), // 7天
            'iss' => 'alingai-pro'
        ];
        
        return JWT::encode($payload, $this->secret, $this->algorithm);
    }
    
    /**
     * 验证令牌
     */
    public function verifyToken(string $token): ?array
    {
        try {
            $decoded = JWT::decode($token, new Key($this->secret, $this->algorithm));
            return (array) $decoded;
        } catch (\Exception $e) {
            return null;
        }
    }
    
    /**
     * 从Authorization头提取令牌
     */
    public function extractTokenFromHeader(): ?string
    {
        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? null;
        
        if ($authHeader && preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            return $matches[1];
        }
        
        return null;
    }
}
