<?php
/**
 * 路由配置文件
 * 定义所有前端页面和API路由
 */

namespace AlingAi\Config;

class Routes {
    
    /**
     * 前端页面路由
     */
    public static function getWebRoutes() {
        return [
            // 主页和基础页面
            '/' => ['controller' => 'WebController', 'method' => 'index'],
            '/home' => ['controller' => 'WebController', 'method' => 'index'],
            '/chat' => ['controller' => 'WebController', 'method' => 'chat'],
            '/login' => ['controller' => 'WebController', 'method' => 'login'],
            '/register' => ['controller' => 'WebController', 'method' => 'register'],
            '/dashboard' => ['controller' => 'WebController', 'method' => 'dashboard'],
            '/admin' => ['controller' => 'WebController', 'method' => 'admin'],
            '/profile' => ['controller' => 'WebController', 'method' => 'profile'],
            '/contact' => ['controller' => 'WebController', 'method' => 'contact'],
            '/privacy' => ['controller' => 'WebController', 'method' => 'privacy'],
            '/terms' => ['controller' => 'WebController', 'method' => 'terms'],
            
            // 静态页面
            '/about' => ['controller' => 'WebController', 'method' => 'about'],
            '/features' => ['controller' => 'WebController', 'method' => 'features'],
            '/pricing' => ['controller' => 'WebController', 'method' => 'pricing'],
            '/support' => ['controller' => 'WebController', 'method' => 'support'],
            
            // 管理和监控页面
            '/admin/console' => ['controller' => 'AdminController', 'method' => 'console'],
            '/admin/users' => ['controller' => 'AdminController', 'method' => 'users'],
            '/admin/settings' => ['controller' => 'AdminController', 'method' => 'settings'],
            '/monitoring' => ['controller' => 'MonitoringController', 'method' => 'dashboard'],
            '/health' => ['controller' => 'MonitoringController', 'method' => 'health'],
        ];
    }
    
    /**
     * API路由
     */
    public static function getApiRoutes() {
        return [
            // 认证API
            'POST /api/auth/login' => ['controller' => 'AuthController', 'method' => 'login'],
            'POST /api/auth/register' => ['controller' => 'AuthController', 'method' => 'register'],
            'POST /api/auth/logout' => ['controller' => 'AuthController', 'method' => 'logout'],
            'POST /api/auth/refresh' => ['controller' => 'AuthController', 'method' => 'refresh'],
            'GET /api/auth/user' => ['controller' => 'AuthController', 'method' => 'getUser'],
            'POST /api/auth/reset-password' => ['controller' => 'AuthController', 'method' => 'resetPassword'],
            
            // 聊天API
            'POST /api/chat/send' => ['controller' => 'ChatController', 'method' => 'sendMessage'],
            'GET /api/chat/conversations' => ['controller' => 'ChatController', 'method' => 'getConversations'],
            'GET /api/chat/conversation/{id}' => ['controller' => 'ChatController', 'method' => 'getConversation'],
            'DELETE /api/chat/conversation/{id}' => ['controller' => 'ChatController', 'method' => 'deleteConversation'],
            'POST /api/chat/regenerate' => ['controller' => 'ChatController', 'method' => 'regenerateResponse'],
            
            // 用户API
            'GET /api/user/profile' => ['controller' => 'UserController', 'method' => 'getProfile'],
            'PUT /api/user/profile' => ['controller' => 'UserController', 'method' => 'updateProfile'],
            'POST /api/user/avatar' => ['controller' => 'UserController', 'method' => 'uploadAvatar'],
            'GET /api/user/settings' => ['controller' => 'UserController', 'method' => 'getSettings'],
            'PUT /api/user/settings' => ['controller' => 'UserController', 'method' => 'updateSettings'],
            
            // 系统API
            'GET /api/system/status' => ['controller' => 'SystemController', 'method' => 'getStatus'],
            'GET /api/system/config' => ['controller' => 'SystemController', 'method' => 'getConfig'],
            'GET /api/system/models' => ['controller' => 'SystemController', 'method' => 'getModels'],
            'GET /api/system/version' => ['controller' => 'SystemController', 'method' => 'getVersion'],
            
            // 管理API (需要管理员权限)
            'GET /api/admin/users' => ['controller' => 'AdminController', 'method' => 'getUsers'],
            'POST /api/admin/users' => ['controller' => 'AdminController', 'method' => 'createUser'],
            'PUT /api/admin/users/{id}' => ['controller' => 'AdminController', 'method' => 'updateUser'],
            'DELETE /api/admin/users/{id}' => ['controller' => 'AdminController', 'method' => 'deleteUser'],
            'GET /api/admin/statistics' => ['controller' => 'AdminController', 'method' => 'getStatistics'],
            'GET /api/admin/logs' => ['controller' => 'AdminController', 'method' => 'getLogs'],
            
            // 文件上传API
            'POST /api/upload/image' => ['controller' => 'UploadController', 'method' => 'uploadImage'],
            'POST /api/upload/file' => ['controller' => 'UploadController', 'method' => 'uploadFile'],
            'DELETE /api/upload/{id}' => ['controller' => 'UploadController', 'method' => 'deleteFile'],
            
            // 监控API
            'GET /api/monitoring/metrics' => ['controller' => 'MonitoringController', 'method' => 'getMetrics'],
            'GET /api/monitoring/performance' => ['controller' => 'MonitoringController', 'method' => 'getPerformance'],
            'GET /api/monitoring/errors' => ['controller' => 'MonitoringController', 'method' => 'getErrors'],
        ];
    }
    
    /**
     * WebSocket路由
     */
    public static function getWebSocketRoutes() {
        return [
            '/ws/chat' => ['handler' => 'ChatWebSocketHandler'],
            '/ws/notifications' => ['handler' => 'NotificationWebSocketHandler'],
            '/ws/monitoring' => ['handler' => 'MonitoringWebSocketHandler'],
        ];
    }
    
    /**
     * 中间件配置
     */
    public static function getMiddleware() {
        return [
            // 全局中间件
            'global' => [
                'CorsMiddleware',
                'SecurityHeadersMiddleware',
                'RateLimitMiddleware',
            ],
            
            // 认证中间件
            'auth' => [
                'AuthenticationMiddleware',
            ],
            
            // 管理员中间件
            'admin' => [
                'AuthenticationMiddleware',
                'AdminMiddleware',
            ],
            
            // API中间件
            'api' => [
                'ApiAuthMiddleware',
                'ApiRateLimitMiddleware',
                'JsonResponseMiddleware',
            ],
        ];
    }
    
    /**
     * 静态资源路由
     */
    public static function getStaticRoutes() {
        return [
            '/assets' => [
                'path' => 'public/assets',
                'cache' => 3600 * 24 * 30, // 30天缓存
                'compression' => true,
            ],
            '/uploads' => [
                'path' => 'public/uploads',
                'cache' => 3600 * 24 * 7, // 7天缓存
                'compression' => false,
            ],
        ];
    }
}
