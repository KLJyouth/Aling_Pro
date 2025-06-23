<?php

/**
 * AlingAi Pro 6.0 API Routes
 * 零信任量子加密系统 API 路由配置
 */

use AlingAi\Config\Routes;
use AlingAi\Controllers\AuthController;
use AlingAi\Controllers\UserController;
use AlingAi\Controllers\SystemController;
use AlingAi\Controllers\SystemManagementController;
use AlingAi\Controllers\MonitoringController;
use AlingAi\Controllers\WalletController;
use AlingAi\Controllers\EnterpriseAdminController;
use AlingAi\Controllers\PaymentController;
use AlingAi\Controllers\DocumentController;
use AlingAi\Controllers\SimpleApiController;
use AlingAi\Controllers\Security\QuantumCryptoController;
use AlingAi\Controllers\Security\SecurityTestController;

/**
 * API路由定义
 * 基于自定义路由系统的API端点配置
 */
class ApiRoutes {
    
    /**
     * 获取所有API路由
     */
    public static function getRoutes() {
        return array_merge(
            self::getAuthRoutes(),
            self::getUserRoutes(),
            self::getSystemRoutes(),
            self::getEnterpriseRoutes(),
            self::getSecurityRoutes(),
            self::getBlockchainRoutes(),
            self::getAdminRoutes(),
            self::getMonitoringRoutes(),
            self::getPublicRoutes()
        );
    }
    
    /**
     * 认证相关路由
     */
    private static function getAuthRoutes() {
        return [
            'POST /api/auth/login' => ['controller' => 'AuthController', 'method' => 'login'],
            'POST /api/auth/register' => ['controller' => 'AuthController', 'method' => 'register'],
            'POST /api/auth/logout' => ['controller' => 'AuthController', 'method' => 'logout'],
            'POST /api/auth/refresh' => ['controller' => 'AuthController', 'method' => 'refresh'],
            'POST /api/auth/forgot-password' => ['controller' => 'AuthController', 'method' => 'forgotPassword'],
            'POST /api/auth/reset-password' => ['controller' => 'AuthController', 'method' => 'resetPassword'],
            'POST /api/auth/verify-email' => ['controller' => 'AuthController', 'method' => 'verifyEmail'],
            'POST /api/auth/resend-verification' => ['controller' => 'AuthController', 'method' => 'resendVerification'],
            'GET /api/auth/user' => ['controller' => 'AuthController', 'method' => 'getUser', 'middleware' => ['auth']],
        ];
    }
    
    /**
     * 用户相关路由
     */
    private static function getUserRoutes() {
        return [
            'GET /api/user/profile' => ['controller' => 'UserController', 'method' => 'profile', 'middleware' => ['auth']],
            'PUT /api/user/profile' => ['controller' => 'UserController', 'method' => 'updateProfile', 'middleware' => ['auth']],
            'POST /api/user/avatar' => ['controller' => 'UserController', 'method' => 'uploadAvatar', 'middleware' => ['auth']],
            'GET /api/user/settings' => ['controller' => 'UserController', 'method' => 'getSettings', 'middleware' => ['auth']],
            'PUT /api/user/settings' => ['controller' => 'UserController', 'method' => 'updateSettings', 'middleware' => ['auth']],
            'POST /api/user/change-password' => ['controller' => 'UserController', 'method' => 'changePassword', 'middleware' => ['auth']],
            'GET /api/user/activity' => ['controller' => 'UserController', 'method' => 'getActivity', 'middleware' => ['auth']],
            'GET /api/user/notifications' => ['controller' => 'UserController', 'method' => 'getNotifications', 'middleware' => ['auth']],
            'PUT /api/user/notifications/{id}/read' => ['controller' => 'UserController', 'method' => 'markNotificationAsRead', 'middleware' => ['auth']],
        ];
    }
    
    /**
     * 系统相关路由
     */
    private static function getSystemRoutes() {
        return [
            'GET /api/version' => ['controller' => 'SystemController', 'method' => 'getVersion'],
            'GET /api/health' => ['controller' => 'SystemController', 'method' => 'healthCheck'],
            'GET /api/health/detailed' => ['controller' => 'SystemController', 'method' => 'detailedHealthCheck'],
            'GET /api/status' => ['controller' => 'SystemController', 'method' => 'getStatus'],
            'GET /api/metrics' => ['controller' => 'SystemController', 'method' => 'getMetrics'],
            'GET /api/system/info' => ['controller' => 'SystemController', 'method' => 'getSystemInfo', 'middleware' => ['auth', 'admin']],
            'GET /api/system/config' => ['controller' => 'SystemController', 'method' => 'getConfig', 'middleware' => ['auth', 'admin']],
            'PUT /api/system/config' => ['controller' => 'SystemController', 'method' => 'updateConfig', 'middleware' => ['auth', 'admin']],
        ];
    }
    
    /**
     * 企业服务路由
     */
    private static function getEnterpriseRoutes() {
        return [
            'GET /api/enterprise/dashboard' => ['controller' => 'EnterpriseAdminController', 'method' => 'dashboard', 'middleware' => ['auth']],
            'GET /api/enterprise/organizations' => ['controller' => 'EnterpriseAdminController', 'method' => 'getOrganizations', 'middleware' => ['auth']],
            'POST /api/enterprise/organizations' => ['controller' => 'EnterpriseAdminController', 'method' => 'createOrganization', 'middleware' => ['auth']],
            'GET /api/enterprise/workspaces' => ['controller' => 'EnterpriseAdminController', 'method' => 'getWorkspaces', 'middleware' => ['auth']],
            'POST /api/enterprise/workspaces' => ['controller' => 'EnterpriseAdminController', 'method' => 'createWorkspace', 'middleware' => ['auth']],
            'GET /api/enterprise/projects' => ['controller' => 'EnterpriseAdminController', 'method' => 'getProjects', 'middleware' => ['auth']],
            'POST /api/enterprise/projects' => ['controller' => 'EnterpriseAdminController', 'method' => 'createProject', 'middleware' => ['auth']],
            'GET /api/enterprise/teams' => ['controller' => 'EnterpriseAdminController', 'method' => 'getTeams', 'middleware' => ['auth']],
            'POST /api/enterprise/teams' => ['controller' => 'EnterpriseAdminController', 'method' => 'createTeam', 'middleware' => ['auth']],
        ];
    }
    
    /**
     * 安全和加密路由
     */
    private static function getSecurityRoutes() {
        return [
            'POST /api/security/encrypt' => ['controller' => 'QuantumCryptoController', 'method' => 'encrypt', 'middleware' => ['auth', 'quantum-security']],
            'POST /api/security/decrypt' => ['controller' => 'QuantumCryptoController', 'method' => 'decrypt', 'middleware' => ['auth', 'quantum-security']],
            'POST /api/security/sign' => ['controller' => 'QuantumCryptoController', 'method' => 'sign', 'middleware' => ['auth', 'quantum-security']],
            'POST /api/security/verify' => ['controller' => 'QuantumCryptoController', 'method' => 'verify', 'middleware' => ['auth', 'quantum-security']],
            'GET /api/security/test' => ['controller' => 'SecurityTestController', 'method' => 'runTests', 'middleware' => ['auth', 'admin']],
            'GET /api/security/status' => ['controller' => 'SecurityTestController', 'method' => 'getSecurityStatus', 'middleware' => ['auth']],
        ];
    }
    
    /**
     * 区块链和钱包路由
     */
    private static function getBlockchainRoutes() {
        return [
            'GET /api/wallet/balance' => ['controller' => 'WalletController', 'method' => 'getBalance', 'middleware' => ['auth']],
            'POST /api/wallet/transfer' => ['controller' => 'WalletController', 'method' => 'transfer', 'middleware' => ['auth']],
            'GET /api/wallet/transactions' => ['controller' => 'WalletController', 'method' => 'getTransactions', 'middleware' => ['auth']],
            'POST /api/wallet/create' => ['controller' => 'WalletController', 'method' => 'createWallet', 'middleware' => ['auth']],
            'GET /api/payment/methods' => ['controller' => 'PaymentController', 'method' => 'getPaymentMethods', 'middleware' => ['auth']],
            'POST /api/payment/process' => ['controller' => 'PaymentController', 'method' => 'processPayment', 'middleware' => ['auth']],
        ];
    }
    
    /**
     * 管理员路由
     */
    private static function getAdminRoutes() {
        return [
            'GET /api/admin/users' => ['controller' => 'UserController', 'method' => 'getUsers', 'middleware' => ['auth', 'admin']],
            'POST /api/admin/users' => ['controller' => 'UserController', 'method' => 'createUser', 'middleware' => ['auth', 'admin']],
            'GET /api/admin/users/{id}' => ['controller' => 'UserController', 'method' => 'getUser', 'middleware' => ['auth', 'admin']],
            'PUT /api/admin/users/{id}' => ['controller' => 'UserController', 'method' => 'updateUser', 'middleware' => ['auth', 'admin']],
            'DELETE /api/admin/users/{id}' => ['controller' => 'UserController', 'method' => 'deleteUser', 'middleware' => ['auth', 'admin']],
            'POST /api/admin/users/{id}/suspend' => ['controller' => 'UserController', 'method' => 'suspendUser', 'middleware' => ['auth', 'admin']],
            'POST /api/admin/users/{id}/activate' => ['controller' => 'UserController', 'method' => 'activateUser', 'middleware' => ['auth', 'admin']],
            'GET /api/admin/users/{id}/activity' => ['controller' => 'UserController', 'method' => 'getUserActivity', 'middleware' => ['auth', 'admin']],
            'GET /api/admin/system/logs' => ['controller' => 'SystemController', 'method' => 'getLogs', 'middleware' => ['auth', 'admin']],
            'POST /api/admin/system/maintenance' => ['controller' => 'SystemController', 'method' => 'toggleMaintenance', 'middleware' => ['auth', 'admin']],
            'POST /api/admin/system/cache/clear' => ['controller' => 'SystemController', 'method' => 'clearCache', 'middleware' => ['auth', 'admin']],
            'GET /api/admin/audit/logs' => ['controller' => 'SystemController', 'method' => 'getAuditLogs', 'middleware' => ['auth', 'admin']],
            'GET /api/admin/audit/logs/{id}' => ['controller' => 'SystemController', 'method' => 'getAuditLog', 'middleware' => ['auth', 'admin']],
            'GET /api/admin/audit/statistics' => ['controller' => 'SystemController', 'method' => 'getAuditStatistics', 'middleware' => ['auth', 'admin']],
        ];
    }
    
    /**
     * 监控路由
     */
    private static function getMonitoringRoutes() {
        return [
            'GET /api/monitoring/metrics' => ['controller' => 'MonitoringController', 'method' => 'getMetrics', 'middleware' => ['auth', 'admin']],
            'GET /api/monitoring/performance' => ['controller' => 'MonitoringController', 'method' => 'getPerformanceMetrics', 'middleware' => ['auth', 'admin']],
            'GET /api/monitoring/alerts' => ['controller' => 'MonitoringController', 'method' => 'getAlerts', 'middleware' => ['auth', 'admin']],
            'POST /api/monitoring/alerts/{id}/acknowledge' => ['controller' => 'MonitoringController', 'method' => 'acknowledgeAlert', 'middleware' => ['auth', 'admin']],
        ];
    }
    
    /**
     * 公开路由
     */
    private static function getPublicRoutes() {
        return [
            'GET /api/docs/api' => ['controller' => 'DocumentController', 'method' => 'getApiDocs'],
            'GET /api/docs/openapi' => ['controller' => 'DocumentController', 'method' => 'getOpenApiSpec'],
            'GET /api/stats/public' => ['controller' => 'SystemController', 'method' => 'getPublicStats'],
            'POST /api/webhooks/github' => ['controller' => 'SystemController', 'method' => 'githubWebhook'],
            'POST /api/webhooks/blockchain/{network}' => ['controller' => 'WalletController', 'method' => 'blockchainWebhook'],
            'POST /api/webhooks/ai/callback' => ['controller' => 'SimpleApiController', 'method' => 'aiCallback'],
        ];
    }
}

// 返回所有API路由
return ApiRoutes::getRoutes();
