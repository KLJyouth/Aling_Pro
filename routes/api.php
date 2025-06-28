<?php

/**
 * AlingAi Pro 6.0 API Routes
 * 零信任量子加密系�?API 路由配置
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
use App\Http\Controllers\Security\QuantumCryptoController;
use App\Http\Controllers\Security\SecurityTestController;
use App\Http\Controllers\Security\VulnerabilityScanController;
use App\Http\Controllers\Security\IntrusionDetectionController;
use App\Http\Controllers\Security\CryptoKeyController;
use App\Http\Controllers\Security\SecurityIssueController;
use App\Http\Controllers\Security\SecurityTestResultController;
use App\Http\Controllers\Security\SecurityThreatController;
use App\Http\Controllers\Monitoring\SystemMonitorController;
use App\Http\Controllers\Monitoring\AlertController;
use App\Http\Controllers\Security\QuantumSecurityController;
use App\Http\Controllers\Security\QuantumSecurityDashboardController;
use App\Http\Controllers\Security\QuarantineController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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
            self::getTicketRoutes(),
            self::getSettingRoutes(),
            self::getNewsRoutes(),
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
     * 安全和加密路�?
     */
    private static function getSecurityRoutes() {
        return [
            // 量子加密相关路由
            'POST /api/security/encrypt' => ['controller' => QuantumCryptoController::class, 'method' => 'encrypt', 'middleware' => ['auth', 'quantum-security']],
            'POST /api/security/decrypt' => ['controller' => QuantumCryptoController::class, 'method' => 'decrypt', 'middleware' => ['auth', 'quantum-security']],
            'POST /api/security/sign' => ['controller' => QuantumCryptoController::class, 'method' => 'sign', 'middleware' => ['auth', 'quantum-security']],
            'POST /api/security/verify' => ['controller' => QuantumCryptoController::class, 'method' => 'verify', 'middleware' => ['auth', 'quantum-security']],
            
            // 密钥管理路由
            'GET /api/security/keys' => ['controller' => CryptoKeyController::class, 'method' => 'index', 'middleware' => ['auth', 'admin']],
            'POST /api/security/keys' => ['controller' => CryptoKeyController::class, 'method' => 'store', 'middleware' => ['auth', 'admin']],
            'GET /api/security/keys/{id}' => ['controller' => CryptoKeyController::class, 'method' => 'show', 'middleware' => ['auth', 'admin']],
            'PUT /api/security/keys/{id}' => ['controller' => CryptoKeyController::class, 'method' => 'update', 'middleware' => ['auth', 'admin']],
            'DELETE /api/security/keys/{id}' => ['controller' => CryptoKeyController::class, 'method' => 'destroy', 'middleware' => ['auth', 'admin']],
            
            // 安全测试相关路由
            'POST /api/security/tests/run' => ['controller' => SecurityTestController::class, 'method' => 'runTest', 'middleware' => ['auth', 'admin']],
            'GET /api/security/tests/{testId}/status' => ['controller' => SecurityTestController::class, 'method' => 'getTestStatus', 'middleware' => ['auth', 'admin']],
            'GET /api/security/tests/history' => ['controller' => SecurityTestController::class, 'method' => 'getTestHistory', 'middleware' => ['auth', 'admin']],
            'GET /api/security/tests/{testId}/report' => ['controller' => SecurityTestController::class, 'method' => 'getTestReport', 'middleware' => ['auth', 'admin']],
            'POST /api/security/tests/{testId}/cancel' => ['controller' => SecurityTestController::class, 'method' => 'cancelTest', 'middleware' => ['auth', 'admin']],
            
            // 漏洞扫描相关路由
            'POST /api/security/vulnerabilities/scan' => ['controller' => VulnerabilityScanController::class, 'method' => 'startScan', 'middleware' => ['auth', 'admin']],
            'GET /api/security/vulnerabilities/scan/{scanId}/status' => ['controller' => VulnerabilityScanController::class, 'method' => 'getScanStatus', 'middleware' => ['auth', 'admin']],
            'GET /api/security/vulnerabilities/scan/{scanId}/result' => ['controller' => VulnerabilityScanController::class, 'method' => 'getScanResult', 'middleware' => ['auth', 'admin']],
            'GET /api/security/vulnerabilities/history' => ['controller' => VulnerabilityScanController::class, 'method' => 'getScanHistory', 'middleware' => ['auth', 'admin']],
            'POST /api/security/vulnerabilities/scan/{scanId}/cancel' => ['controller' => VulnerabilityScanController::class, 'method' => 'cancelScan', 'middleware' => ['auth', 'admin']],
            
            // 入侵检测相关路�?
            'GET /api/security/intrusion/attempts' => ['controller' => IntrusionDetectionController::class, 'method' => 'getIntrusionAttempts', 'middleware' => ['auth', 'admin']],
            'GET /api/security/intrusion/{attemptId}/detail' => ['controller' => IntrusionDetectionController::class, 'method' => 'getIntrusionDetail', 'middleware' => ['auth', 'admin']],
            'POST /api/security/intrusion/{attemptId}/resolve' => ['controller' => IntrusionDetectionController::class, 'method' => 'resolveIntrusion', 'middleware' => ['auth', 'admin']],
            
            // 安全问题管理路由
            'GET /api/security/issues' => ['controller' => SecurityIssueController::class, 'method' => 'index', 'middleware' => ['auth', 'admin']],
            'POST /api/security/issues' => ['controller' => SecurityIssueController::class, 'method' => 'store', 'middleware' => ['auth', 'admin']],
            'GET /api/security/issues/{id}' => ['controller' => SecurityIssueController::class, 'method' => 'show', 'middleware' => ['auth', 'admin']],
            'PUT /api/security/issues/{id}' => ['controller' => SecurityIssueController::class, 'method' => 'update', 'middleware' => ['auth', 'admin']],
            'POST /api/security/issues/{id}/resolve' => ['controller' => SecurityIssueController::class, 'method' => 'resolve', 'middleware' => ['auth', 'admin']],
            
            // 安全测试结果路由
            'GET /api/security/test-results' => ['controller' => SecurityTestResultController::class, 'method' => 'index', 'middleware' => ['auth', 'admin']],
            'GET /api/security/test-results/{id}' => ['controller' => SecurityTestResultController::class, 'method' => 'show', 'middleware' => ['auth', 'admin']],
            'GET /api/security/test-results/{id}/report' => ['controller' => SecurityTestResultController::class, 'method' => 'getReport', 'middleware' => ['auth', 'admin']],
            
            // 量子AI安全系统路由
            'POST /api/security/quantum/detect-threats' => ['controller' => QuantumSecurityController::class, 'method' => 'detectThreats', 'middleware' => ['auth', 'admin']],
            'GET /api/security/quantum/status' => ['controller' => QuantumSecurityController::class, 'method' => 'getSecurityStatus', 'middleware' => ['auth', 'admin']],
            'GET /api/security/quantum/defense-level' => ['controller' => QuantumSecurityController::class, 'method' => 'getDefenseLevel', 'middleware' => ['auth', 'admin']],
            'POST /api/security/quantum/defense-level' => ['controller' => QuantumSecurityController::class, 'method' => 'setDefenseLevel', 'middleware' => ['auth', 'admin']],
            'POST /api/security/quantum/respond-to-threat' => ['controller' => QuantumSecurityController::class, 'method' => 'respondToThreat', 'middleware' => ['auth', 'admin']],
            
            // 安全威胁管理路由
            'GET /api/security/threats' => ['controller' => SecurityThreatController::class, 'method' => 'index', 'middleware' => ['auth', 'admin']],
            'POST /api/security/threats' => ['controller' => SecurityThreatController::class, 'method' => 'store', 'middleware' => ['auth', 'admin']],
            'GET /api/security/threats/{id}' => ['controller' => SecurityThreatController::class, 'method' => 'show', 'middleware' => ['auth', 'admin']],
            'PUT /api/security/threats/{id}' => ['controller' => SecurityThreatController::class, 'method' => 'update', 'middleware' => ['auth', 'admin']],
            'POST /api/security/threats/{id}/resolve' => ['controller' => SecurityThreatController::class, 'method' => 'resolve', 'middleware' => ['auth', 'admin']],
            'POST /api/security/threats/{id}/false-positive' => ['controller' => SecurityThreatController::class, 'method' => 'markAsFalsePositive', 'middleware' => ['auth', 'admin']],
            'POST /api/security/threats/{id}/respond' => ['controller' => SecurityThreatController::class, 'method' => 'respondToThreat', 'middleware' => ['auth', 'admin']],
            'GET /api/security/threats/statistics' => ['controller' => SecurityThreatController::class, 'method' => 'getStatistics', 'middleware' => ['auth', 'admin']],
            
            // 量子安全仪表盘路�?
            'GET /api/security/dashboard/overview' => ['controller' => QuantumSecurityDashboardController::class, 'method' => 'getDashboardOverview', 'middleware' => ['auth', 'admin']],
            'GET /api/security/dashboard/threat-trends' => ['controller' => QuantumSecurityDashboardController::class, 'method' => 'getThreatTrends', 'middleware' => ['auth', 'admin']],
            'GET /api/security/dashboard/threat-distribution' => ['controller' => QuantumSecurityDashboardController::class, 'method' => 'getThreatDistribution', 'middleware' => ['auth', 'admin']],
            'GET /api/security/dashboard/defense-effectiveness' => ['controller' => QuantumSecurityDashboardController::class, 'method' => 'getDefenseEffectiveness', 'middleware' => ['auth', 'admin']],
            
            // 隔离区API路由
            'GET /api/security/quarantine/items' => ['controller' => QuarantineController::class, 'method' => 'getQuarantineItems', 'middleware' => ['auth', 'admin']],
            'GET /api/security/quarantine/items/{id}' => ['controller' => QuarantineController::class, 'method' => 'getQuarantineItem', 'middleware' => ['auth', 'admin']],
            'POST /api/security/quarantine/items/{id}/update-status' => ['controller' => QuarantineController::class, 'method' => 'apiUpdateStatus', 'middleware' => ['auth', 'admin']],
            'GET /api/security/quarantine/ip-bans' => ['controller' => QuarantineController::class, 'method' => 'getIpBans', 'middleware' => ['auth', 'admin']],
            'POST /api/security/quarantine/ban-ip' => ['controller' => QuarantineController::class, 'method' => 'apiBanIp', 'middleware' => ['auth', 'admin']],
            'POST /api/security/quarantine/revoke-ip-ban/{id}' => ['controller' => QuarantineController::class, 'method' => 'apiRevokeIpBan', 'middleware' => ['auth', 'admin']],
            'GET /api/security/quarantine/statistics' => ['controller' => QuarantineController::class, 'method' => 'getQuarantineStatistics', 'middleware' => ['auth', 'admin']],
        ];
    }
    
    /**
     * 工单系统路由
     */
    private static function getTicketRoutes() {
        return [
            // 工单管理路由
            'GET /api/tickets' => ['controller' => 'App\Http\Controllers\Api\TicketApiController', 'method' => 'index', 'middleware' => ['auth']],
            'POST /api/tickets' => ['controller' => 'App\Http\Controllers\Api\TicketApiController', 'method' => 'store', 'middleware' => ['auth']],
            'GET /api/tickets/{id}' => ['controller' => 'App\Http\Controllers\Api\TicketApiController', 'method' => 'show', 'middleware' => ['auth']],
            'PUT /api/tickets/{id}' => ['controller' => 'App\Http\Controllers\Api\TicketApiController', 'method' => 'update', 'middleware' => ['auth']],
            'DELETE /api/tickets/{id}' => ['controller' => 'App\Http\Controllers\Api\TicketApiController', 'method' => 'destroy', 'middleware' => ['auth', 'admin']],
            
            // 工单回复路由
            'POST /api/tickets/{id}/reply' => ['controller' => 'App\Http\Controllers\Api\TicketApiController', 'method' => 'reply', 'middleware' => ['auth']],
            'GET /api/tickets/{id}/replies' => ['controller' => 'App\Http\Controllers\Api\TicketApiController', 'method' => 'getReplies', 'middleware' => ['auth']],
            
            // 工单操作路由
            'POST /api/tickets/{id}/assign' => ['controller' => 'App\Http\Controllers\Api\TicketApiController', 'method' => 'assign', 'middleware' => ['auth', 'role:admin,support']],
            'POST /api/tickets/{id}/close' => ['controller' => 'App\Http\Controllers\Api\TicketApiController', 'method' => 'close', 'middleware' => ['auth']],
            'POST /api/tickets/{id}/reopen' => ['controller' => 'App\Http\Controllers\Api\TicketApiController', 'method' => 'reopen', 'middleware' => ['auth']],
            
            // 工单附件路由
            'POST /api/tickets/{id}/attachments' => ['controller' => 'App\Http\Controllers\Api\TicketApiController', 'method' => 'uploadAttachment', 'middleware' => ['auth']],
            'DELETE /api/tickets/{id}/attachments/{attachmentId}' => ['controller' => 'App\Http\Controllers\Api\TicketApiController', 'method' => 'deleteAttachment', 'middleware' => ['auth']],
            'GET /api/tickets/{id}/attachments' => ['controller' => 'App\Http\Controllers\Api\TicketApiController', 'method' => 'getAttachments', 'middleware' => ['auth']],
            
            // 工单统计路由
            'GET /api/tickets/statistics' => ['controller' => 'App\Http\Controllers\Api\TicketApiController', 'method' => 'getStatistics', 'middleware' => ['auth']],
            
            // 工单部门路由
            'GET /api/ticket-departments' => ['controller' => 'App\Http\Controllers\Api\TicketApiController', 'method' => 'getDepartments', 'middleware' => ['auth']],
            'POST /api/ticket-departments' => ['controller' => 'App\Http\Controllers\Api\TicketApiController', 'method' => 'createDepartment', 'middleware' => ['auth', 'admin']],
            'GET /api/ticket-departments/{id}' => ['controller' => 'App\Http\Controllers\Api\TicketApiController', 'method' => 'showDepartment', 'middleware' => ['auth']],
            'PUT /api/ticket-departments/{id}' => ['controller' => 'App\Http\Controllers\Api\TicketApiController', 'method' => 'updateDepartment', 'middleware' => ['auth', 'admin']],
            'DELETE /api/ticket-departments/{id}' => ['controller' => 'App\Http\Controllers\Api\TicketApiController', 'method' => 'deleteDepartment', 'middleware' => ['auth', 'admin']],
            
            // 工单分类路由
            'GET /api/ticket-categories' => ['controller' => 'App\Http\Controllers\Api\TicketApiController', 'method' => 'getCategories', 'middleware' => ['auth']],
            'POST /api/ticket-categories' => ['controller' => 'App\Http\Controllers\Api\TicketApiController', 'method' => 'createCategory', 'middleware' => ['auth', 'admin']],
            'GET /api/ticket-categories/{id}' => ['controller' => 'App\Http\Controllers\Api\TicketApiController', 'method' => 'showCategory', 'middleware' => ['auth']],
            'PUT /api/ticket-categories/{id}' => ['controller' => 'App\Http\Controllers\Api\TicketApiController', 'method' => 'updateCategory', 'middleware' => ['auth', 'admin']],
            'DELETE /api/ticket-categories/{id}' => ['controller' => 'App\Http\Controllers\Api\TicketApiController', 'method' => 'deleteCategory', 'middleware' => ['auth', 'admin']],
            'GET /api/ticket-categories/by-department/{departmentId}' => ['controller' => 'App\Http\Controllers\Api\TicketApiController', 'method' => 'getCategoriesByDepartment', 'middleware' => ['auth']],
        ];
    }
    
    /**
     * 网站设置路由
     */
    private static function getSettingRoutes() {
        return [
            // 设置管理路由
            'GET /api/settings' => ['controller' => 'App\Http\Controllers\Api\SettingApiController', 'method' => 'index', 'middleware' => ['auth', 'admin']],
            'GET /api/settings/{key}' => ['controller' => 'App\Http\Controllers\Api\SettingApiController', 'method' => 'show', 'middleware' => ['auth', 'admin']],
            'PUT /api/settings/{key}' => ['controller' => 'App\Http\Controllers\Api\SettingApiController', 'method' => 'update', 'middleware' => ['auth', 'admin']],
            'POST /api/settings' => ['controller' => 'App\Http\Controllers\Api\SettingApiController', 'method' => 'store', 'middleware' => ['auth', 'admin']],
            'DELETE /api/settings/{key}' => ['controller' => 'App\Http\Controllers\Api\SettingApiController', 'method' => 'destroy', 'middleware' => ['auth', 'admin']],
            
            // 设置分组路由
            'GET /api/settings/group/{group}' => ['controller' => 'App\Http\Controllers\Api\SettingApiController', 'method' => 'getGroup', 'middleware' => ['auth', 'admin']],
            'PUT /api/settings/group/{group}' => ['controller' => 'App\Http\Controllers\Api\SettingApiController', 'method' => 'updateGroup', 'middleware' => ['auth', 'admin']],
            
            // 设置操作路由
            'POST /api/settings/clear-cache' => ['controller' => 'App\Http\Controllers\Api\SettingApiController', 'method' => 'clearCache', 'middleware' => ['auth', 'admin']],
            'POST /api/settings/init-system' => ['controller' => 'App\Http\Controllers\Api\SettingApiController', 'method' => 'initSystemSettings', 'middleware' => ['auth', 'admin']],
            
            // 公共设置路由（无需登录�?
            'GET /api/public-settings' => ['controller' => 'App\Http\Controllers\Api\SettingApiController', 'method' => 'getPublicSettings'],
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
     * 管理员路�?
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
            
            'GET /api/monitoring/system/performance' => ['controller' => SystemMonitorController::class, 'method' => 'getPerformanceMetrics', 'middleware' => ['auth', 'admin']],
            'GET /api/monitoring/system/application' => ['controller' => SystemMonitorController::class, 'method' => 'getApplicationMetrics', 'middleware' => ['auth', 'admin']],
            'GET /api/monitoring/system/health' => ['controller' => SystemMonitorController::class, 'method' => 'getHealthStatus', 'middleware' => ['auth', 'admin']],
            
            'GET /api/monitoring/system/alerts' => ['controller' => AlertController::class, 'method' => 'getActiveAlerts', 'middleware' => ['auth', 'admin']],
            'GET /api/monitoring/system/alerts/{id}' => ['controller' => AlertController::class, 'method' => 'getAlertDetail', 'middleware' => ['auth', 'admin']],
            'POST /api/monitoring/system/alerts/{id}/acknowledge' => ['controller' => AlertController::class, 'method' => 'acknowledgeAlert', 'middleware' => ['auth', 'admin']],
            'POST /api/monitoring/system/alerts/{id}/resolve' => ['controller' => AlertController::class, 'method' => 'resolveAlert', 'middleware' => ['auth', 'admin']],
            'POST /api/monitoring/system/check' => ['controller' => AlertController::class, 'method' => 'triggerSystemCheck', 'middleware' => ['auth', 'admin']],
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
    /**
     * ���Ź���·��
     */
    private static function getNewsRoutes() {
        return [
            // ���Ź���·��
            'GET /api/news' => ['controller' => 'App\Http\Controllers\Api\NewsApiController', 'method' => 'index'],
            'GET /api/news/{slug}' => ['controller' => 'App\Http\Controllers\Api\NewsApiController', 'method' => 'show'],
            'GET /api/news/categories' => ['controller' => 'App\Http\Controllers\Api\NewsApiController', 'method' => 'getCategories'],
            'GET /api/news/tags' => ['controller' => 'App\Http\Controllers\Api\NewsApiController', 'method' => 'getTags'],
            'GET /api/news/category/{slug}' => ['controller' => 'App\Http\Controllers\Api\NewsApiController', 'method' => 'getByCategory'],
            'GET /api/news/tag/{slug}' => ['controller' => 'App\Http\Controllers\Api\NewsApiController', 'method' => 'getByTag'],
            'GET /api/news/{slug}/comments' => ['controller' => 'App\Http\Controllers\Api\NewsApiController', 'method' => 'getComments'],
            'POST /api/news/{slug}/comment' => ['controller' => 'App\Http\Controllers\Api\NewsApiController', 'method' => 'addComment', 'middleware' => ['auth:api']],
            'GET /api/news/featured' => ['controller' => 'App\Http\Controllers\Api\NewsApiController', 'method' => 'getFeatured'],
            
            // ��̨���Ź���·��
            'POST /api/admin/news' => ['controller' => 'App\Http\Controllers\Api\NewsApiController', 'method' => 'store', 'middleware' => ['auth:api', 'role:admin,editor']],
            'PUT /api/admin/news/{id}' => ['controller' => 'App\Http\Controllers\Api\NewsApiController', 'method' => 'update', 'middleware' => ['auth:api', 'role:admin,editor']],
            'DELETE /api/admin/news/{id}' => ['controller' => 'App\Http\Controllers\Api\NewsApiController', 'method' => 'destroy', 'middleware' => ['auth:api', 'role:admin,editor']],
            'POST /api/admin/news/{id}/toggle-featured' => ['controller' => 'App\Http\Controllers\Api\NewsApiController', 'method' => 'toggleFeatured', 'middleware' => ['auth:api', 'role:admin,editor']],
            'POST /api/admin/news/{id}/publish' => ['controller' => 'App\Http\Controllers\Api\NewsApiController', 'method' => 'publish', 'middleware' => ['auth:api', 'role:admin,editor']],
            'POST /api/admin/news/{id}/draft' => ['controller' => 'App\Http\Controllers\Api\NewsApiController', 'method' => 'draft', 'middleware' => ['auth:api', 'role:admin,editor']],
            'POST /api/admin/news/{id}/archive' => ['controller' => 'App\Http\Controllers\Api\NewsApiController', 'method' => 'archive', 'middleware' => ['auth:api', 'role:admin,editor']],
            
            // �������·��
            'GET /api/admin/news/categories' => ['controller' => 'App\Http\Controllers\Api\NewsApiController', 'method' => 'adminGetCategories', 'middleware' => ['auth:api', 'role:admin,editor']],
            'POST /api/admin/news/categories' => ['controller' => 'App\Http\Controllers\Api\NewsApiController', 'method' => 'storeCategory', 'middleware' => ['auth:api', 'role:admin,editor']],
            'GET /api/admin/news/categories/{id}' => ['controller' => 'App\Http\Controllers\Api\NewsApiController', 'method' => 'showCategory', 'middleware' => ['auth:api', 'role:admin,editor']],
            'PUT /api/admin/news/categories/{id}' => ['controller' => 'App\Http\Controllers\Api\NewsApiController', 'method' => 'updateCategory', 'middleware' => ['auth:api', 'role:admin,editor']],
            'DELETE /api/admin/news/categories/{id}' => ['controller' => 'App\Http\Controllers\Api\NewsApiController', 'method' => 'destroyCategory', 'middleware' => ['auth:api', 'role:admin,editor']],
            'POST /api/admin/news/categories/{id}/toggle-status' => ['controller' => 'App\Http\Controllers\Api\NewsApiController', 'method' => 'toggleCategoryStatus', 'middleware' => ['auth:api', 'role:admin,editor']],
            
            // ��ǩ����·��
            'GET /api/admin/news/tags' => ['controller' => 'App\Http\Controllers\Api\NewsApiController', 'method' => 'adminGetTags', 'middleware' => ['auth:api', 'role:admin,editor']],
            'POST /api/admin/news/tags' => ['controller' => 'App\Http\Controllers\Api\NewsApiController', 'method' => 'storeTag', 'middleware' => ['auth:api', 'role:admin,editor']],
            'GET /api/admin/news/tags/{id}' => ['controller' => 'App\Http\Controllers\Api\NewsApiController', 'method' => 'showTag', 'middleware' => ['auth:api', 'role:admin,editor']],
            'PUT /api/admin/news/tags/{id}' => ['controller' => 'App\Http\Controllers\Api\NewsApiController', 'method' => 'updateTag', 'middleware' => ['auth:api', 'role:admin,editor']],
            'DELETE /api/admin/news/tags/{id}' => ['controller' => 'App\Http\Controllers\Api\NewsApiController', 'method' => 'destroyTag', 'middleware' => ['auth:api', 'role:admin,editor']],
            'POST /api/admin/news/tags/{id}/toggle-status' => ['controller' => 'App\Http\Controllers\Api\NewsApiController', 'method' => 'toggleTagStatus', 'middleware' => ['auth:api', 'role:admin,editor']],
            
            // ���۹���·��
            'GET /api/admin/news/comments' => ['controller' => 'App\Http\Controllers\Api\NewsApiController', 'method' => 'adminGetComments', 'middleware' => ['auth:api', 'role:admin,editor,moderator']],
            'GET /api/admin/news/comments/{id}' => ['controller' => 'App\Http\Controllers\Api\NewsApiController', 'method' => 'showComment', 'middleware' => ['auth:api', 'role:admin,editor,moderator']],
            'POST /api/admin/news/comments/{id}/approve' => ['controller' => 'App\Http\Controllers\Api\NewsApiController', 'method' => 'approveComment', 'middleware' => ['auth:api', 'role:admin,editor,moderator']],
            'POST /api/admin/news/comments/{id}/reject' => ['controller' => 'App\Http\Controllers\Api\NewsApiController', 'method' => 'rejectComment', 'middleware' => ['auth:api', 'role:admin,editor,moderator']],
            'POST /api/admin/news/comments/{id}/reply' => ['controller' => 'App\Http\Controllers\Api\NewsApiController', 'method' => 'replyToComment', 'middleware' => ['auth:api', 'role:admin,editor,moderator']],
            'DELETE /api/admin/news/comments/{id}' => ['controller' => 'App\Http\Controllers\Api\NewsApiController', 'method' => 'destroyComment', 'middleware' => ['auth:api', 'role:admin,editor,moderator']],
            'POST /api/admin/news/comments/batch-action' => ['controller' => 'App\Http\Controllers\Api\NewsApiController', 'method' => 'batchActionComments', 'middleware' => ['auth:api', 'role:admin,editor,moderator']],
        ];
    }

