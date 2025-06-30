<?php
/**
 * AlingAi Pro - OAuth配置文件
 * 
 * 配置第三方社交登录的客户端ID和密钥
 */

return [
    // 是否启用社交登录
    'enabled' => true,
    
    // 社交登录提供商配置
    'providers' => [
        // Google OAuth配置
        'google' => [
            'enabled' => true,
            'client_id' => 'YOUR_GOOGLE_CLIENT_ID', // 替换为实际的Google客户端ID
            'client_secret' => 'YOUR_GOOGLE_CLIENT_SECRET', // 替换为实际的Google客户端密钥
            'redirect_uri' => 'http://localhost:8000/login/google/callback', // 根据实际部署环境修改
            'scopes' => ['email', 'profile'],
            'auth_url' => 'https://accounts.google.com/o/oauth2/auth',
            'token_url' => 'https://accounts.google.com/o/oauth2/token',
            'userinfo_url' => 'https://www.googleapis.com/oauth2/v3/userinfo',
        ],
        
        // GitHub OAuth配置
        'github' => [
            'enabled' => true,
            'client_id' => 'YOUR_GITHUB_CLIENT_ID', // 替换为实际的GitHub客户端ID
            'client_secret' => 'YOUR_GITHUB_CLIENT_SECRET', // 替换为实际的GitHub客户端密钥
            'redirect_uri' => 'http://localhost:8000/login/github/callback', // 根据实际部署环境修改
            'scopes' => ['user:email'],
            'auth_url' => 'https://github.com/login/oauth/authorize',
            'token_url' => 'https://github.com/login/oauth/access_token',
            'userinfo_url' => 'https://api.github.com/user',
        ],
    ],
    
    // 登录成功后的重定向URL
    'success_redirect' => '/dashboard',
    
    // 登录失败后的重定向URL
    'error_redirect' => '/login',
    
    // 会话中存储OAuth状态的键名
    'state_key' => 'oauth_state',
    
    // 状态令牌的过期时间（秒）
    'state_ttl' => 3600,
    
    // 是否自动创建用户（如果用户不存在）
    'auto_create_user' => true,
    
    // 新用户的默认角色
    'default_role' => 'user',
    
    // 是否需要邮箱验证
    'require_email_verification' => false,
    
    // 日志配置
    'logging' => [
        'enabled' => true,
        'level' => 'info', // debug, info, warning, error
    ],
]; 