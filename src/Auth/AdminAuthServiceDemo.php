<?php

namespace AlingAi\Auth;

/**
 * AdminAuthServiceDemo
 *
 * @package AlingAi\Auth
 */
class AdminAuthServiceDemo
{
    // 演示数据
    private array $validTokens = [
        "demo_token" => [
            "id" => 1,
            "username" => "admin",
            "email" => "admin@example.com",
            "role" => "admin"
        ],
        "user_token" => [
            "id" => 2,
            "username" => "user",
            "email" => "user@example.com",
            "role" => "user"
        ]
    ];
    
    private array $validApiKeys = [
        "demo_api_key" => [
            "id" => 1,
            "username" => "admin",
            "email" => "admin@example.com",
            "role" => "admin"
        ],
        "user_api_key" => [
            "id" => 2,
            "username" => "user",
            "email" => "user@example.com",
            "role" => "user"
        ]
    ];
    
    /**
     * 构造函数
     */
    public function __construct()
    {
        // 初始化代码
    }
    
    /**
     * 验证Token是否有效
     * 
     * @param string $token Token字符串
     * @return bool 是否有效
     */
    public function validateToken(string $token): bool
    {
        return isset($this->validTokens[$token]);
    }
    
    /**
     * 通过Token获取用户信息
     * 
     * @param string $token Token字符串
     * @return array|null 用户信息或null
     */
    public function getUserFromToken(string $token): ?array
    {
        return $this->validTokens[$token] ?? null;
    }
    
    /**
     * 验证API密钥是否有效
     * 
     * @param string $apiKey API密钥
     * @return bool 是否有效
     */
    public function validateApiKey(string $apiKey): bool
    {
        return isset($this->validApiKeys[$apiKey]);
    }
    
    /**
     * 通过API密钥获取用户信息
     * 
     * @param string $apiKey API密钥
     * @return array|null 用户信息或null
     */
    public function getUserFromApiKey(string $apiKey): ?array
    {
        return $this->validApiKeys[$apiKey] ?? null;
    }
    
    /**
     * 检查用户是否有指定权限
     * 
     * @param int $userId 用户ID
     * @param string $permission 权限标识符
     * @return bool 是否有权限
     */
    public function hasPermission(int $userId, string $permission): bool
    {
        // 演示环境下，假设admin用户(ID=1)拥有所有权限
        return $userId === 1;
    }
}
