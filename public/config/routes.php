<?php
/**
 * 增强应用程序路由配置
 * 
 * @package AlingAi\Pro
 */

declare(strict_types=1];

use Slim\App;
use Slim\Routing\RouteCollectorProxy;
use AlingAi\Controllers\{
    HomeController,
    AuthController,
    AdminController,
    ApiController,
    SimpleApiController,
    ChatController,
    DocumentController,
    UserController,
    SystemController,
    WebController,
    EnhancedAdminController,
    EnterpriseAdminController,
    CacheManagementController,
    SystemManagementController,
    MonitoringController,
    WalletController,
    PaymentController,
    UnifiedAdminController
};
use AlingAi\Controllers\Api\AuthApiController;
use AlingAi\Controllers\Api\UserApiController;
use AlingAi\Controllers\Api\ChatApiController;
use AlingAi\Controllers\Api\FileApiController;
use AlingAi\Controllers\Api\HistoryApiController;
use AlingAi\Controllers\Api\MonitorApiController;
use AlingAi\Controllers\Api\SystemApiController;
use AlingAi\Controllers\Api\AdminApiController;
use AlingAi\Controllers\Collaboration\BusinessCollaborationController;
use AlingAi\Controllers\DataExchange\DataExchangeController;
use AlingAi\Controllers\Blockchain\BlockchainController;
use AlingAi\Controllers\Api\UserSettingsApiController;
use AlingAi\Middleware\{AuthenticationMiddleware, PermissionMiddleware};
use AlingAi\Controllers\Frontend\{
    EnhancedFrontendController,
    Enhanced3DThreatVisualizationController,
    RealTimeSecurityController,
    ThreatVisualizationController
};
use AlingAi\Controllers\Api\{
    IntelligentAgentController,
    SecurityMonitoringApiController,
    SettingsController
};
use AlingAi\Controllers\Admin\ConfigurationController;
use AlingAi\Security\Middleware\ApiEncryptionMiddleware;
use AlingAi\Security\Middleware\AuthenticationMiddleware as SecurityAuthMiddleware;

return function (App $app) {
    // 注册全局中间�?    $app->add(new ApiEncryptionMiddleware($app->getContainer()->get('encryption'], $app->getContainer()->get('logger'))];
    
    // 安装路由
    $app->group('/install', function (RouteCollectorProxy $group) {
        $group->get('/', 'AlingAi\Install\InstallController:showInstallPage'];
        $group->get('/system-check', 'AlingAi\Install\InstallController:handleSystemCheck'];
        $group->post('/perform', 'AlingAi\Install\InstallController:handleInstall'];
        $group->get('/status', 'AlingAi\Install\InstallController:checkInstallStatus'];
    }];
    
    // Web页面路由 - 使用新的WebController
    $app->get('/', WebController::class . ':index')->setName('home'];
    $app->get('/chat', WebController::class . ':chat')->setName('chat'];
    $app->get('/login', WebController::class . ':login')->setName('login'];
    $app->get('/register', WebController::class . ':register')->setName('register'];
    $app->get('/profile', WebController::class . ':profile')->setName('profile'];
    $app->get('/contact', WebController::class . ':contact')->setName('contact'];
    $app->get('/privacy', WebController::class . ':privacy')->setName('privacy'];
    $app->get('/terms', WebController::class . ':terms')->setName('terms'];
    
      // 增强页面路由
    $app->get('/profile-enhanced', WebController::class . ':profileEnhanced')->setName('profile.enhanced'];
    $app->get('/admin-enhanced', WebController::class . ':adminEnhanced')->setName('admin.enhanced'];
    $app->get('/system-management', WebController::class . ':systemManagement')->setName('system.management'];
    
    // 静态页�?    $app->get('/about', WebController::class . ':about')->setName('about'];
    $app->get('/features', WebController::class . ':features')->setName('features'];
    
    // 认证路由�?    $app->group('/auth', function (RouteCollectorProxy $group) {
        $group->get('/login', AuthController::class . ':showLogin')->setName('auth.login'];
        $group->post('/login', AuthController::class . ':login'];
        $group->get('/register', AuthController::class . ':showRegister')->setName('auth.register'];
        $group->post('/register', AuthController::class . ':register'];
        $group->get('/logout', AuthController::class . ':logout')->setName('auth.logout'];
        $group->get('/forgot-password', AuthController::class . ':showForgotPassword')->setName('auth.forgot'];
        $group->post('/forgot-password', AuthController::class . ':forgotPassword'];
        $group->get('/reset-password/{token}', AuthController::class . ':showResetPassword')->setName('auth.reset'];
        $group->post('/reset-password', AuthController::class . ':resetPassword'];
        $group->get('/verify/{token}', AuthController::class . ':verifyEmail')->setName('auth.verify'];
    }];
    
    // API路由�?    $app->group('/api', function (RouteCollectorProxy $group) use ($app) {
        // 公开API
        $group->group('/public', function (RouteCollectorProxy $group) {
            $group->get('/health', SystemController::class . ':health'];
            $group->get('/status', SystemController::class . ':status'];
            $group->get('/version', SystemController::class . ':version'];
            
            // Re-implementing routes from the simple router file
            $group->get('/system/status', SystemController::class . ':status')->setName('api.public.system.status'];
            $group->get('/performance/metrics', SystemController::class . ':performance')->setName('api.public.performance.metrics'];

            // 支付回调（不需要认证）
            $group->post('/payment/callback/wechat', PaymentController::class . ':wechatCallback'];
            $group->post('/payment/callback/alipay', PaymentController::class . ':alipayCallback'];
        }];
        
        // 测试API端点 - 这些通常用于开发或调试，确保它们不会与公共API冲突�?        $group->get('/database/test', SystemController::class . ':databaseTest'];
        $group->get('/ai/test', SystemController::class . ':aiTest'];
        $group->get('/system/health', SystemController::class . ':health'];
        
        // 认证API
        $group->group('/auth', function (RouteCollectorProxy $group) {
            $group->post('/login', AuthApiController::class . ':login'];
            $group->post('/register', AuthApiController::class . ':register'];
            $group->post('/refresh', AuthApiController::class . ':refreshToken'];
            $group->post('/forgot-password', AuthApiController::class . ':forgotPassword'];
            $group->post('/reset-password', AuthApiController::class . ':resetPassword'];
            $group->post('/verify-email', AuthApiController::class . ':verifyEmail'];
            $group->post('/resend-verification', AuthApiController::class . ':resendVerification'];
        }];
        
        // 通用API端点（无需认证�? 使用简化控制器
        $group->get('/user/info', SimpleApiController::class . ':userInfo'];
        $group->get('/settings', SimpleApiController::class . ':getSettings'];
        $group->post('/chat/send', SimpleApiController::class . ':sendChatMessage'];
        $group->get('/ai/models', SimpleApiController::class . ':getAIModels'];
        $group->post('/upload', SimpleApiController::class . ':uploadFile'];

        // 需要认证的API
        $group->group('/v1', function (RouteCollectorProxy $group) {
            // 新增：系统设置API
            $group->get('/settings', SettingsController::class . ':handleRequest')->setName('api.v1.settings'];

            // 认证相关（需要token�?            $group->group('/auth', function (RouteCollectorProxy $group) {
                $group->post('/logout', AuthApiController::class . ':logout'];
                $group->get('/me', AuthApiController::class . ':getCurrentUser'];
                $group->post('/change-password', AuthApiController::class . ':changePassword'];
                $group->post('/two-factor/enable', AuthApiController::class . ':enableTwoFactor'];
                $group->post('/two-factor/verify', AuthApiController::class . ':verifyTwoFactor'];
                $group->post('/two-factor/disable', AuthApiController::class . ':disableTwoFactor'];
            }];
            
            // 仪表板相�?            $group->group('/dashboard', function (RouteCollectorProxy $group) {
                $group->get('/refresh', UserController::class . ':refreshDashboard'];
            }];
            
            // 用户相关
            $group->group('/user', function (RouteCollectorProxy $group) {
                $group->get('/profile', UserApiController::class . ':getProfile'];
                $group->put('/profile', UserApiController::class . ':updateProfile'];
                $group->post('/avatar', UserApiController::class . ':uploadAvatar'];
                $group->get('/settings', UserApiController::class . ':getSettings'];
                $group->put('/settings', UserApiController::class . ':updateSettings'];
                $group->post('/change-password', UserApiController::class . ':changePassword'];
                $group->get('/activity', UserApiController::class . ':getActivityLogs'];
                $group->delete('/account', UserApiController::class . ':deleteAccount'];
                $group->get('/export', UserApiController::class . ':exportData'];
            }];
            
              // 用户设置增强API
            $group->group('/user-settings', function (RouteCollectorProxy $group) {
                $group->get('', UserSettingsApiController::class . ':getAllSettings'];
                $group->post('', UserSettingsApiController::class . ':updateSettings'];
                $group->post('/batch', UserSettingsApiController::class . ':updateBatchSettings'];
                $group->post('/migrate', UserSettingsApiController::class . ':migrateFromLocalStorage'];
                $group->get('/export', UserSettingsApiController::class . ':exportSettings'];
                $group->post('/import', UserSettingsApiController::class . ':importSettings'];
                $group->delete('/reset', UserSettingsApiController::class . ':resetSettings'];
                $group->get('/category/{category}', UserSettingsApiController::class . ':getSettingsByCategory'];
                $group->put('/category/{category}', UserSettingsApiController::class . ':updateSettingsByCategory'];
            }];

            // 新增：聊天API
            $group->group('/chat', function (RouteCollectorProxy $group) {
                $group->post('/send', ChatApiController::class . ':sendMessage'];
                $group->post('/chat', ChatApiController::class . ':sendMessage'];
                $group->get('/conversations', ChatApiController::class . ':getConversations'];
                $group->get('/conversations/{id}', ChatApiController::class . ':getConversation'];
                $group->delete('/conversations/{id}', ChatApiController::class . ':deleteConversation'];
                $group->post('/regenerate', ChatApiController::class . ':regenerateResponse'];
                $group->get('/models', ChatApiController::class . ':getModels'];
            }];

            // 新增：文件管理API
            $group->group('/files', function (RouteCollectorProxy $group) {
                $group->post('/upload', FileApiController::class . ':uploadFile'];
                $group->get('', FileApiController::class . ':getUserFiles'];
                $group->get('/{id}', FileApiController::class . ':downloadFile'];
                $group->delete('/{id}', FileApiController::class . ':deleteFile'];
                $group->post('/{id}/share', FileApiController::class . ':shareFile'];
            }];

            // 新增：历史记录API
            $group->group('/history', function (RouteCollectorProxy $group) {
                $group->get('/sessions', HistoryApiController::class . ':getSessions'];
                $group->get('', HistoryApiController::class . ':getMessages'];
                $group->post('', HistoryApiController::class . ':saveHistory'];
                $group->get('/{id}', HistoryApiController::class . ':getHistoryById'];
                $group->delete('/{id}', HistoryApiController::class . ':deleteHistory'];
                $group->delete('', HistoryApiController::class . ':clearHistory'];
                $group->get('/search', HistoryApiController::class . ':searchHistory'];
                $group->get('/export', HistoryApiController::class . ':exportHistory'];
            }];

            // 新增：API监控中心路由
            $group->group('/monitor', function (RouteCollectorProxy $group) {
                $group->get('/dashboard', MonitorApiController::class . ':getDashboardData'];
                $group->get('/api-calls', MonitorApiController::class . ':getApiCalls'];
                $group->get('/api-calls/{id}', MonitorApiController::class . ':getApiCall'];
                $group->get('/api-stats', MonitorApiController::class . ':getApiStats'];
                $group->get('/security-events', MonitorApiController::class . ':getSecurityEvents'];
                $group->get('/performance', MonitorApiController::class . ':getPerformanceData'];
                $group->get('/real-time', MonitorApiController::class . ':getRealTimeData'];
            })->add(PermissionMiddleware::class . ':isAdmin'];

            // 钱包管理API
            $group->group('/wallet', function (RouteCollectorProxy $group) {
                $group->get('/info', WalletController::class . ':getWalletInfo'];
                $group->get('/transactions', WalletController::class . ':getTransactionHistory'];
                $group->post('/recharge', WalletController::class . ':createRechargeOrder'];
                $group->post('/transfer', WalletController::class . ':transfer'];
                $group->post('/deduct', WalletController::class . ':deductApiCost'];
            }];
            
            // 支付相关API
            $group->group('/payment', function (RouteCollectorProxy $group) {
                $group->post('/wechat/order', PaymentController::class . ':createWeChatOrder'];
                $group->post('/alipay/order', PaymentController::class . ':createAlipayOrder'];
                $group->get('/status', PaymentController::class . ':queryPaymentStatus'];
                $group->post('/cancel', PaymentController::class . ':cancelPayment'];
            }];

            // 业务协同API - AlingAI Pro 5.0
            $group->group('/collaboration', function (RouteCollectorProxy $group) {
                // 跨组织协�?                $group->post('/projects', BusinessCollaborationController::class . ':createCrossOrganizationProject'];
                $group->get('/projects', BusinessCollaborationController::class . ':getCollaborationProjects'];
                $group->get('/projects/{id}', BusinessCollaborationController::class . ':getCollaborationProject'];
                $group->put('/projects/{id}', BusinessCollaborationController::class . ':updateCollaborationProject'];
                
                // 协作消息
                $group->post('/messages', BusinessCollaborationController::class . ':sendCollaborationMessage'];
                $group->get('/messages/{projectId}', BusinessCollaborationController::class . ':getCollaborationMessages'];
                
                // 文档共享
                $group->post('/documents', BusinessCollaborationController::class . ':shareDocument'];
                $group->get('/documents/{projectId}', BusinessCollaborationController::class . ':getSharedDocuments'];
                $group->put('/documents/{id}/access', BusinessCollaborationController::class . ':updateDocumentAccess'];
            }];

            // 数据交换API - AlingAI Pro 5.0
            $group->group('/data-exchange', function (RouteCollectorProxy $group) {
                $group->post('/export', DataExchangeController::class . ':exportData'];
                $group->post('/import', DataExchangeController::class . ':importData'];
                $group->get('/formats', DataExchangeController::class . ':getSupportedFormats'];
                $group->post('/validate', DataExchangeController::class . ':validateData'];
                $group->get('/templates', DataExchangeController::class . ':getTemplates'];
                $group->get('/history', DataExchangeController::class . ':getExchangeHistory'];
            }];

            // 区块链API - AlingAI Pro 5.0
            $group->group('/blockchain', function (RouteCollectorProxy $group) {
                $group->post('/transactions', BlockchainController::class . ':createTransaction'];
                $group->get('/transactions', BlockchainController::class . ':getTransactions'];
                $group->get('/transactions/{id}', BlockchainController::class . ':getTransaction'];
                $group->get('/balance', BlockchainController::class . ':getBalance'];
                $group->post('/verify', BlockchainController::class . ':verifyData'];
            }];
            
            // 智能代理API - AlingAI Pro 5.0
            $group->group('/intelligent-agent', function (RouteCollectorProxy $group) {
                $group->post('/create', IntelligentAgentController::class . ':createAgent'];
                $group->get('/list', IntelligentAgentController::class . ':listAgents'];
                $group->get('/{id}', IntelligentAgentController::class . ':getAgent'];
                $group->put('/{id}', IntelligentAgentController::class . ':updateAgent'];
                $group->delete('/{id}', IntelligentAgentController::class . ':deleteAgent'];
                $group->post('/{id}/execute', IntelligentAgentController::class . ':executeAgent'];
                $group->get('/{id}/history', IntelligentAgentController::class . ':getAgentHistory'];
                $group->post('/{id}/schedule', IntelligentAgentController::class . ':scheduleAgent'];
            }];
            
            // 安全监控API - AlingAI Pro 5.0
            $group->group('/security-monitoring', function (RouteCollectorProxy $group) {
                $group->get('/dashboard', SecurityMonitoringApiController::class . ':getDashboard'];
                $group->get('/threats', SecurityMonitoringApiController::class . ':getThreats'];
                $group->get('/logs', SecurityMonitoringApiController::class . ':getLogs'];
                $group->post('/scan', SecurityMonitoringApiController::class . ':performSecurityScan'];
                $group->get('/vulnerabilities', SecurityMonitoringApiController::class . ':getVulnerabilities'];
                $group->post('/mitigate/{id}', SecurityMonitoringApiController::class . ':mitigateThreat'];
                $group->get('/reports', SecurityMonitoringApiController::class . ':getSecurityReports'];
                $group->post('/reports/generate', SecurityMonitoringApiController::class . ':generateSecurityReport'];
            }];

            // 新增：系统管理API (部分功能需要管理员权限)
            $group->group('/system', function (RouteCollectorProxy $group) {
                $group->get('/health', SystemApiController::class . ':healthCheck'];
                $group->get('/status', SystemApiController::class . ':getStatus'];
                $group->get('/performance', SystemApiController::class . ':getPerformanceMetrics'];
                
                // 以下需要管理员权限
                $group->get('/config', SystemApiController::class . ':getConfig')->add(PermissionMiddleware::class . ':isAdmin'];
                $group->put('/config', SystemApiController::class . ':updateConfig')->add(PermissionMiddleware::class . ':isAdmin'];
                $group->get('/diagnostics', SystemApiController::class . ':runDiagnostics')->add(PermissionMiddleware::class . ':isAdmin'];
                $group->post('/cache/clear', SystemApiController::class . ':clearCache')->add(PermissionMiddleware::class . ':isAdmin'];
            }];

            // 新增：管理员API
            $group->group('/admin', function (RouteCollectorProxy $group) {
                $group->get('/dashboard', AdminApiController::class . ':getDashboard'];
                $group->get('/users', AdminApiController::class . ':getUsers'];
                $group->get('/users/{id}', AdminApiController::class . ':getUser'];
                $group->put('/users/{id}', AdminApiController::class . ':updateUser'];
                $group->delete('/users/{id}', AdminApiController::class . ':deleteUser'];
                $group->get('/stats', AdminApiController::class . ':getSystemStats'];
                $group->get('/logs', AdminApiController::class . ':getSystemLogs'];
                $group->post('/maintenance', AdminApiController::class . ':toggleMaintenance'];
                $group->post('/backup', AdminApiController::class . ':createBackup'];
            })->add(PermissionMiddleware::class . ':isAdmin'];
        })->add(function($request, $handler) use ($app) {
            $container = $app->getContainer(];
            $authService = $container->get(\AlingAi\Services\AuthService::class];
            $logger = $container->get(\AlingAi\Core\Logging\SimpleLogger::class];
            
            $middleware = new AuthenticationMiddleware($authService, $logger, 'user'];
            return $middleware->process($request, $handler];
        }];
    }];
    
    // 管理员路由组
    $app->group('/admin', function (RouteCollectorProxy $group) {
        $group->get('', AdminController::class . ':index')->setName('admin.index'];
        $group->get('/dashboard', AdminController::class . ':dashboard')->setName('admin.dashboard'];
        $group->get('/users', AdminController::class . ':users')->setName('admin.users'];
        $group->get('/users/{id}', AdminController::class . ':viewUser')->setName('admin.users.view'];
        $group->post('/users/{id}', AdminController::class . ':updateUser'];
        $group->delete('/users/{id}', AdminController::class . ':deleteUser'];
        $group->get('/settings', AdminController::class . ':settings')->setName('admin.settings'];
        $group->post('/settings', AdminController::class . ':updateSettings'];
        $group->get('/logs', AdminController::class . ':logs')->setName('admin.logs'];
        $group->get('/system', AdminController::class . ':system')->setName('admin.system'];
        
        // 增强管理功能 - AlingAI Pro 5.0
        $group->get('/enhanced', EnhancedAdminController::class . ':index')->setName('admin.enhanced'];
        $group->get('/enhanced/dashboard', EnhancedAdminController::class . ':dashboard')->setName('admin.enhanced.dashboard'];
        $group->get('/enhanced/analytics', EnhancedAdminController::class . ':analytics')->setName('admin.enhanced.analytics'];
        $group->get('/enhanced/security', EnhancedAdminController::class . ':security')->setName('admin.enhanced.security'];
        
        // 企业管理功能 - AlingAI Pro 5.0
        $group->get('/enterprise', EnterpriseAdminController::class . ':index')->setName('admin.enterprise'];
        $group->get('/enterprise/organizations', EnterpriseAdminController::class . ':organizations')->setName('admin.enterprise.organizations'];
        $group->get('/enterprise/billing', EnterpriseAdminController::class . ':billing')->setName('admin.enterprise.billing'];
        $group->get('/enterprise/compliance', EnterpriseAdminController::class . ':compliance')->setName('admin.enterprise.compliance'];
        
        // 缓存管理 - AlingAI Pro 5.0
        $group->get('/cache', CacheManagementController::class . ':index')->setName('admin.cache'];
        $group->post('/cache/clear', CacheManagementController::class . ':clearCache'];
        $group->get('/cache/stats', CacheManagementController::class . ':cacheStats'];
        $group->get('/cache/keys', CacheManagementController::class . ':cacheKeys'];
        $group->delete('/cache/keys/{key}', CacheManagementController::class . ':deleteKey'];
        
        // 系统管理 - AlingAI Pro 5.0
        $group->get('/system-management', SystemManagementController::class . ':index')->setName('admin.system-management'];
        $group->get('/system-management/status', SystemManagementController::class . ':systemStatus'];
        $group->post('/system-management/optimize', SystemManagementController::class . ':optimizeSystem'];
        $group->get('/system-management/php-info', SystemManagementController::class . ':phpInfo'];
        $group->get('/system-management/database', SystemManagementController::class . ':databaseInfo'];
        
        // 监控 - AlingAI Pro 5.0
        $group->get('/monitoring', MonitoringController::class . ':index')->setName('admin.monitoring'];
        $group->get('/monitoring/realtime', MonitoringController::class . ':realtime')->setName('admin.monitoring.realtime'];
        $group->get('/monitoring/logs', MonitoringController::class . ':logs')->setName('admin.monitoring.logs'];
        $group->get('/monitoring/alerts', MonitoringController::class . ':alerts')->setName('admin.monitoring.alerts'];
        $group->get('/monitoring/performance', MonitoringController::class . ':performance')->setName('admin.monitoring.performance'];
        
        // 统一管理控制�?- AlingAI Pro 5.0
        $group->get('/unified', UnifiedAdminController::class . ':index')->setName('admin.unified'];
        $group->get('/unified/dashboard', UnifiedAdminController::class . ':dashboard')->setName('admin.unified.dashboard'];
        
        // 配置管理 - AlingAI Pro 5.0
        $group->get('/configuration', ConfigurationController::class . ':index')->setName('admin.configuration'];
        $group->get('/configuration/{section}', ConfigurationController::class . ':viewSection')->setName('admin.configuration.section'];
        $group->post('/configuration/{section}', ConfigurationController::class . ':updateSection'];
        $group->get('/configuration/export', ConfigurationController::class . ':exportConfiguration')->setName('admin.configuration.export'];
        $group->post('/configuration/import', ConfigurationController::class . ':importConfiguration')->setName('admin.configuration.import'];
    }];
    
    // 增强前端路由 - AlingAI Pro 5.0
    $app->group('/enhanced-frontend', function (RouteCollectorProxy $group) {
        $group->get('', EnhancedFrontendController::class . ':index')->setName('enhanced-frontend.index'];
        $group->get('/dashboard', EnhancedFrontendController::class . ':dashboard')->setName('enhanced-frontend.dashboard'];
        
        // 3D威胁可视�?        $group->get('/threat-visualization', ThreatVisualizationController::class . ':index')->setName('enhanced-frontend.threat-visualization'];
        $group->get('/threat-visualization/data', ThreatVisualizationController::class . ':getData'];
        $group->get('/threat-visualization/3d', Enhanced3DThreatVisualizationController::class . ':index')->setName('enhanced-frontend.threat-visualization.3d'];
        $group->get('/threat-visualization/3d/data', Enhanced3DThreatVisualizationController::class . ':getData'];
        
        // 实时安全监控
        $group->get('/real-time-security', RealTimeSecurityController::class . ':index')->setName('enhanced-frontend.real-time-security'];
        $group->get('/real-time-security/data', RealTimeSecurityController::class . ':getData'];
        $group->get('/real-time-security/alerts', RealTimeSecurityController::class . ':getAlerts'];
    }];
    
    // 文档路由
    $app->group('/docs', function (RouteCollectorProxy $group) {
        $group->get('', DocumentController::class . ':index')->setName('docs.index'];
        $group->get('/api', DocumentController::class . ':api')->setName('docs.api'];
        $group->get('/guides', DocumentController::class . ':guides')->setName('docs.guides'];
        $group->get('/faq', DocumentController::class . ':faq')->setName('docs.faq'];
    }];
    
    // 聊天路由
    $app->group('/chat', function (RouteCollectorProxy $group) {
        $group->get('', ChatController::class . ':index')->setName('chat.index'];
        $group->post('/send', ChatController::class . ':send')->setName('chat.send'];
        $group->get('/history', ChatController::class . ':history')->setName('chat.history'];
        $group->delete('/history/{id}', ChatController::class . ':deleteHistory')->setName('chat.history.delete'];
    }];
};
