<?php
/**
 * 增强应用程序路由配置
 * 
 * @package AlingAi\Pro
 */

declare(strict_types=1);

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
    SecurityMonitoringApiController
};
use AlingAi\Controllers\Admin\ConfigurationController;

return function (App $app) {
    // Web页面路由 - 使用新的WebController
//     $app->get('/', WebController::class . ':index')->setName('home'); // 不可达代码';
    $app->get('/chat', WebController::class . ':chat')->setName('chat');';
    $app->get('/login', WebController::class . ':login')->setName('login');';
    $app->get('/register', WebController::class . ':register')->setName('register');';
    $app->get('/profile', WebController::class . ':profile')->setName('profile');';
    $app->get('/contact', WebController::class . ':contact')->setName('contact');';
    $app->get('/privacy', WebController::class . ':privacy')->setName('privacy');';
    $app->get('/terms', WebController::class . ':terms')->setName('terms');';
      // 增强页面路由
    $app->get('/profile-enhanced', WebController::class . ':profileEnhanced')->setName('profile.enhanced');';
    $app->get('/admin-enhanced', WebController::class . ':adminEnhanced')->setName('admin.enhanced');';
    $app->get('/system-management', WebController::class . ':systemManagement')->setName('system.management');';
    $app->get('/system-management', WebController::class . ':systemManagement')->setName('system.management');';
    
    // 静态页面
    $app->get('/about', WebController::class . ':about')->setName('about');';
    $app->get('/features', WebController::class . ':features')->setName('features');';
    
    // 认证路由组
    $app->group('/auth', function (RouteCollectorProxy $group) {';
        $group->get('/login', AuthController::class . ':showLogin')->setName('auth.login');';
        $group->post('/login', AuthController::class . ':login');';
        $group->get('/register', AuthController::class . ':showRegister')->setName('auth.register');';
        $group->post('/register', AuthController::class . ':register');';
        $group->get('/logout', AuthController::class . ':logout')->setName('auth.logout');';
        $group->get('/forgot-password', AuthController::class . ':showForgotPassword')->setName('auth.forgot');';
        $group->post('/forgot-password', AuthController::class . ':forgotPassword');';
        $group->get('/reset-password/{token}', AuthController::class . ':showResetPassword')->setName('auth.reset');';
        $group->post('/reset-password', AuthController::class . ':resetPassword');';
        $group->get('/verify/{token}', AuthController::class . ':verifyEmail')->setName('auth.verify');';
    });
    
    // API路由组
    $app->group('/api', function (RouteCollectorProxy $group) {        // 公开API';
        $group->group('/public', function (RouteCollectorProxy $group) {';
            $group->get('/health', SystemController::class . ':health');';
            $group->get('/status', SystemController::class . ':status');';
            $group->get('/version', SystemController::class . ':version');';
            
            // 支付回调（不需要认证）
            $group->post('/payment/callback/wechat', PaymentController::class . ':wechatCallback');';
            $group->post('/payment/callback/alipay', PaymentController::class . ':alipayCallback');';
        });
        
        // 测试API端点
        $group->get('/database/test', SystemController::class . ':databaseTest');';
        $group->get('/ai/test', SystemController::class . ':aiTest');';
        $group->get('/system/status', SystemController::class . ':status');';
        $group->get('/system/health', SystemController::class . ':health');';
        
        // 认证API
        $group->group('/auth', function (RouteCollectorProxy $group) {';
            $group->post('/login', AuthController::class . ':apiLogin');';
            $group->post('/register', AuthController::class . ':apiRegister');';
            $group->post('/refresh', AuthController::class . ':refreshToken');';
            $group->post('/forgot-password', AuthController::class . ':apiForgotPassword');';
            $group->post('/reset-password', AuthController::class . ':apiResetPassword');';
        });
        
        // 通用API端点（无需认证）- 使用简化控制器
        $group->get('/user/info', SimpleApiController::class . ':userInfo');';
        $group->get('/settings', SimpleApiController::class . ':getSettings');';
        $group->post('/chat/send', SimpleApiController::class . ':sendChatMessage');';
        $group->get('/ai/models', SimpleApiController::class . ':getAIModels');';
        $group->post('/upload', SimpleApiController::class . ':uploadFile');';

        // 需要认证的API
        $group->group('/v1', function (RouteCollectorProxy $group) {';
            // 仪表板相关
            $group->group('/dashboard', function (RouteCollectorProxy $group) {';
                $group->get('/refresh', UserController::class . ':refreshDashboard');';
            });
            
            // 用户相关
            $group->group('/user', function (RouteCollectorProxy $group) {';
                $group->get('/profile', UserController::class . ':profile');';
                $group->put('/profile', UserController::class . ':updateProfile');';
                $group->post('/avatar', UserController::class . ':uploadAvatar');';
                $group->get('/settings', UserController::class . ':settings');';
                $group->put('/settings', UserController::class . ':updateSettings');';
                $group->delete('/account', UserController::class . ':deleteAccount');';
            });
              // 用户设置增强API
            $group->group('/user-settings', function (RouteCollectorProxy $group) {';
                $group->get('', UserSettingsApiController::class . ':getAllSettings');';
                $group->post('', UserSettingsApiController::class . ':updateSettings');';
                $group->post('/batch', UserSettingsApiController::class . ':updateBatchSettings');';
                $group->post('/migrate', UserSettingsApiController::class . ':migrateFromLocalStorage');';
                $group->get('/export', UserSettingsApiController::class . ':exportSettings');';
                $group->post('/import', UserSettingsApiController::class . ':importSettings');';
                $group->delete('/reset', UserSettingsApiController::class . ':resetSettings');';
                $group->get('/category/{category}', UserSettingsApiController::class . ':getSettingsByCategory');';
                $group->put('/category/{category}', UserSettingsApiController::class . ':updateSettingsByCategory');';
            });

            // 钱包管理API
            $group->group('/wallet', function (RouteCollectorProxy $group) {';
                $group->get('/info', WalletController::class . ':getWalletInfo');';
                $group->get('/transactions', WalletController::class . ':getTransactionHistory');';
                $group->post('/recharge', WalletController::class . ':createRechargeOrder');';
                $group->post('/transfer', WalletController::class . ':transfer');';
                $group->post('/deduct', WalletController::class . ':deductApiCost');';
            });            // 支付相关API
            $group->group('/payment', function (RouteCollectorProxy $group) {';
                $group->post('/wechat/order', PaymentController::class . ':createWeChatOrder');';
                $group->post('/alipay/order', PaymentController::class . ':createAlipayOrder');';
                $group->get('/status', PaymentController::class . ':queryPaymentStatus');';
                $group->post('/cancel', PaymentController::class . ':cancelPayment');';
            });

            // 业务协同API - AlingAI Pro 5.0
            $group->group('/collaboration', function (RouteCollectorProxy $group) {';
                // 跨组织协作
                $group->post('/projects', BusinessCollaborationController::class . ':createCrossOrganizationProject');';
                $group->get('/projects', BusinessCollaborationController::class . ':getCollaborationProjects');';
                $group->get('/projects/{id}', BusinessCollaborationController::class . ':getCollaborationProject');';
                $group->put('/projects/{id}', BusinessCollaborationController::class . ':updateCollaborationProject');';
                $group->delete('/projects/{id}', BusinessCollaborationController::class . ':deleteCollaborationProject');';
                
                // 工作流管理
                $group->get('/workflows/templates', BusinessCollaborationController::class . ':getWorkflowTemplates');';
                $group->post('/workflows/optimize', BusinessCollaborationController::class . ':optimizeWorkflow');';
                $group->get('/workflows/{id}/status', BusinessCollaborationController::class . ':getWorkflowStatus');';
                
                // 创新项目管理
                $group->post('/innovation/manage', BusinessCollaborationController::class . ':manageInnovationProject');';
                $group->get('/innovation/proposals', BusinessCollaborationController::class . ':getInnovationProposals');';
                $group->get('/innovation/statistics', BusinessCollaborationController::class . ':getInnovationStatistics');';
                
                // 协同决策支持
                $group->post('/decisions/support', BusinessCollaborationController::class . ':supportCollaborativeDecision');';
                $group->get('/decisions/{id}', BusinessCollaborationController::class . ':getDecisionRecord');';
                
                // 协作统计和报告
                $group->get('/statistics', BusinessCollaborationController::class . ':getCollaborationStatistics');';
                $group->get('/reports/performance', BusinessCollaborationController::class . ':getPerformanceReport');';
                $group->get('/reports/roi', BusinessCollaborationController::class . ':getROIReport');';
            });

            // 数据交换API - AlingAI Pro 5.0
            $group->group('/data-exchange', function (RouteCollectorProxy $group) {';
                // 数据目录管理
                $group->get('/catalog', DataExchangeController::class . ':browseCatalog');';
                $group->post('/catalog', DataExchangeController::class . ':createCatalogEntry');';
                $group->get('/catalog/{id}', DataExchangeController::class . ':getCatalogEntry');';
                $group->put('/catalog/{id}', DataExchangeController::class . ':updateCatalogEntry');';
                
                // 数据交换请求
                $group->post('/requests', DataExchangeController::class . ':createExchangeRequest');';
                $group->get('/requests', DataExchangeController::class . ':getExchangeRequests');';
                $group->get('/requests/{id}', DataExchangeController::class . ':getExchangeRequest');';
                $group->put('/requests/{id}/approve', DataExchangeController::class . ':approveExchangeRequest');';
                $group->put('/requests/{id}/reject', DataExchangeController::class . ':rejectExchangeRequest');';
                
                // 数据质量和合规
                $group->post('/quality/assess', DataExchangeController::class . ':assessDataQuality');';
                $group->post('/compliance/check', DataExchangeController::class . ':checkCompliance');';
                $group->get('/compliance/reports', DataExchangeController::class . ':getComplianceReports');';
                
                // 数据导出和传输
                $group->post('/export', DataExchangeController::class . ':exportData');';
                $group->get('/export/{id}/status', DataExchangeController::class . ':getExportStatus');';
                $group->get('/export/{id}/download', DataExchangeController::class . ':downloadExportedData');';
                
                // 统计和监控
                $group->get('/statistics', DataExchangeController::class . ':getDataExchangeStatistics');';
                $group->get('/monitoring/dashboard', DataExchangeController::class . ':getMonitoringDashboard');';
            });

            // 区块链集成API - AlingAI Pro 5.0
            $group->group('/blockchain', function (RouteCollectorProxy $group) {';
                // 数据认证
                $group->post('/certify', BlockchainController::class . ':certifyData');';
                $group->get('/certificates/{id}', BlockchainController::class . ':getCertificate');';
                $group->post('/certificates/verify', BlockchainController::class . ':verifyCertificate');';
                
                // 智能合约
                $group->post('/contracts/deploy', BlockchainController::class . ':deploySmartContract');';
                $group->get('/contracts', BlockchainController::class . ':getSmartContracts');';
                $group->post('/contracts/{id}/execute', BlockchainController::class . ':executeSmartContract');';
                
                // 交易监控
                $group->get('/transactions', BlockchainController::class . ':getTransactions');';
                $group->get('/transactions/{id}', BlockchainController::class . ':getTransaction');';
                $group->get('/transactions/{id}/status', BlockchainController::class . ':getTransactionStatus');';
                
                // 网络状态
                $group->get('/networks', BlockchainController::class . ':getSupportedNetworks');';
                $group->get('/networks/{network}/status', BlockchainController::class . ':getNetworkStatus');';
                
                // 统计和分析
                $group->get('/statistics', BlockchainController::class . ':getBlockchainStatistics');';
                $group->get('/analytics/performance', BlockchainController::class . ':getPerformanceAnalytics');';
            });
            
            // 聊天相关
            $group->group('/chat', function (RouteCollectorProxy $group) {';
                // 聊天历史记录
                $group->get('/history', ChatController::class . ':getChatHistory');';
                $group->get('/conversations', ChatController::class . ':getConversations');';
                $group->post('/conversations', ChatController::class . ':createConversation');';
                $group->get('/conversations/{id}', ChatController::class . ':getConversation');';
                $group->put('/conversations/{id}', ChatController::class . ':updateConversation');';
                $group->delete('/conversations/{id}', ChatController::class . ':deleteConversation');';
                $group->post('/conversations/{id}/messages', ChatController::class . ':sendMessage');';
                $group->get('/conversations/{id}/messages', ChatController::class . ':getMessages');';
                
                // 增强AI聊天
                $group->post('/enhanced', ApiController::class . ':enhancedChat');';
            });            // 历史记录相关 - 暂时注释掉，等待HistoryController实现
            /*
            $group->group('/history', function (RouteCollectorProxy $group) {';
                // 用户操作历史
                $group->get('/user-actions', HistoryController::class . ':getUserActionHistory');';
                // 系统事件历史
                $group->get('/system-events', HistoryController::class . ':getSystemEventHistory');';
                // 聊天记录归档
                $group->get('/chat-archive', HistoryController::class . ':getChatArchive');';
                // 历史记录导出
                $group->post('/export', HistoryController::class . ':exportHistory');';
                // 详细历史记录端点
                $group->get('/detailed/{id}', HistoryController::class . ':getDetailedHistory');';
            });
            */

            // AI服务相关
            $group->group('/ai', function (RouteCollectorProxy $group) {';
                $group->get('/status', ApiController::class . ':aiStatus');';
                $group->get('/usage', ApiController::class . ':aiUsageStats');';
            });

            // 系统监控相关
            $group->group('/system', function (RouteCollectorProxy $group) {';
                $group->get('/metrics', ApiController::class . ':systemMetrics');';
                $group->get('/health', ApiController::class . ':systemHealth');';
                $group->get('/alerts', ApiController::class . ':systemAlerts');';
                $group->get('/database', ApiController::class . ':databaseStatus');';
                $group->post('/cleanup', ApiController::class . ':cleanupSystem');';
            });
            
            // 监控API路由
            $group->group('/monitoring', function (RouteCollectorProxy $group) {';
                $group->get('/status', MonitoringController::class . ':getSystemStatus');';
                $group->get('/historical', MonitoringController::class . ':getHistoricalData');';
            });

            // 邮件服务相关
            $group->group('/email', function (RouteCollectorProxy $group) {';
                $group->post('/test', ApiController::class . ':sendTestEmail');';
                $group->get('/stats', ApiController::class . ':emailStats');';
            });

            // 配置管理
            $group->group('/config', function (RouteCollectorProxy $group) {';
                $group->get('', ApiController::class . ':getConfig');';
                $group->put('', ApiController::class . ':updateConfig');';
            });
            
            // 文档相关
            $group->group('/documents', function (RouteCollectorProxy $group) {';
                $group->get('', DocumentController::class . ':index');';
                $group->post('', DocumentController::class . ':create');';
                $group->get('/categories', DocumentController::class . ':categories');';
                $group->get('/{id}', DocumentController::class . ':show');';
                $group->put('/{id}', DocumentController::class . ':update');';
                $group->delete('/{id}', DocumentController::class . ':delete');';
                $group->post('/{id}/share', DocumentController::class . ':share');';
            });
            
        })->add(AuthenticationMiddleware::class);
        
        // 管理员API
        $group->group('/admin', function (RouteCollectorProxy $group) {';
            $group->get('/dashboard', AdminController::class . ':dashboard');';
            $group->get('/users', AdminController::class . ':users');';
            $group->get('/users/{id}', AdminController::class . ':getUser');';
            $group->put('/users/{id}', AdminController::class . ':updateUser');';
            $group->delete('/users/{id}', AdminController::class . ':deleteUser');';
            $group->get('/system/stats', AdminController::class . ':systemStats');';
            $group->get('/system/logs', AdminController::class . ':systemLogs');';
            $group->post('/system/maintenance', AdminController::class . ':maintenanceMode');';
            $group->post('/system/cache/clear', AdminController::class . ':clearCache');';
            $group->get('/analytics', AdminController::class . ':analytics');';
        })->add(AuthenticationMiddleware::class . ':admin');';
        
        // 增强管理员API
        $group->group('/enhanced-admin', function (RouteCollectorProxy $group) {';
            // 增强仪表板
            $group->get('/dashboard', EnhancedAdminController::class . ':getEnhancedDashboard');';
            $group->get('/overview', EnhancedAdminController::class . ':getSystemOverview');';
            
            // 监控API
            $group->get('/monitoring/metrics', EnhancedAdminController::class . ':getMonitoringMetrics');';
            $group->get('/monitoring/history', EnhancedAdminController::class . ':getMonitoringHistory');';
            $group->post('/monitoring/start', EnhancedAdminController::class . ':startMonitoring');';
            $group->post('/monitoring/stop', EnhancedAdminController::class . ':stopMonitoring');';
            
            // 备份API
            $group->get('/backup/status', EnhancedAdminController::class . ':getBackupStatus');';
            $group->post('/backup/create', EnhancedAdminController::class . ':createBackup');';
            $group->get('/backup/list', EnhancedAdminController::class . ':listBackups');';
            $group->post('/backup/restore/{id}', EnhancedAdminController::class . ':restoreBackup');';
            $group->delete('/backup/{id}', EnhancedAdminController::class . ':deleteBackup');';
            
            // 安全API
            $group->get('/security/status', EnhancedAdminController::class . ':getSecurityStatus');';
            $group->post('/security/scan', EnhancedAdminController::class . ':runSecurityScan');';
            $group->get('/security/scans', EnhancedAdminController::class . ':getSecurityScans');';
            $group->get('/security/scan/{id}', EnhancedAdminController::class . ':getSecurityScanDetails');';
            
            // 运维任务API
            $group->get('/tasks', EnhancedAdminController::class . ':getTasks');';
            $group->post('/tasks', EnhancedAdminController::class . ':createTask');';
            $group->put('/tasks/{id}', EnhancedAdminController::class . ':updateTask');';
            $group->delete('/tasks/{id}', EnhancedAdminController::class . ':deleteTask');';
            $group->post('/tasks/{id}/execute', EnhancedAdminController::class . ':executeTask');';
            
            // 系统健康检查
            $group->get('/health', EnhancedAdminController::class . ':getSystemHealth');';
            $group->post('/health/check', EnhancedAdminController::class . ':runHealthCheck');';
              // 日志管理
            $group->get('/logs', EnhancedAdminController::class . ':getLogs');';
            $group->get('/logs/{type}', EnhancedAdminController::class . ':getLogsByType');            $group->delete('/logs/{type}', EnhancedAdminController::class . ':clearLogs');';
            
        })->add(AuthenticationMiddleware::class . ':admin');';
        
        // 企业管理员API（需要管理员权限）
        $group->group('/enterprise-admin', function (RouteCollectorProxy $group) {';
            // 企业仪表板
            $group->get('/dashboard', EnterpriseAdminController::class . ':getEnterpriseAdminDashboard');';
            
            // 企业用户管理
            $group->get('/users', EnterpriseAdminController::class . ':getEnterpriseUsers');';
            $group->get('/users/{id}', EnterpriseAdminController::class . ':getEnterpriseUser');';
            $group->put('/users/{id}/quota', EnterpriseAdminController::class . ':updateEnterpriseUserQuota');';
            
            // 企业用户申请管理
            $group->post('/apply', EnterpriseAdminController::class . ':applyEnterpriseUser');';
            $group->get('/applications', EnterpriseAdminController::class . ':getEnterpriseApplications');';
            $group->put('/applications/{id}/review', EnterpriseAdminController::class . ':reviewEnterpriseUserApplication');';
            
            // 支付统计
            $group->get('/payment-stats', EnterpriseAdminController::class . ':getEnterpriseUserPaymentStats');';
            $group->get('/payment-analytics', EnterpriseAdminController::class . ':getPaymentAnalytics');';
            
            // AI提供商管理
            $group->get('/ai-providers', EnterpriseAdminController::class . ':getAiProviders');';
            $group->post('/ai-providers', EnterpriseAdminController::class . ':createAiProvider');';
            $group->put('/ai-providers/{id}', EnterpriseAdminController::class . ':updateAiProvider');';
            $group->delete('/ai-providers/{id}', EnterpriseAdminController::class . ':deleteAiProvider');';
            $group->post('/ai-providers/{id}/test', EnterpriseAdminController::class . ':testAiProvider');';
            
            // 系统监控
            $group->get('/monitoring', EnterpriseAdminController::class . ':getSystemMonitoring');';
            $group->get('/monitoring/health', EnterpriseAdminController::class . ':getSystemHealth');';
            $group->get('/monitoring/performance', EnterpriseAdminController::class . ':getPerformanceMetrics');';
            
        })->add(AuthenticationMiddleware::class . ':admin');';
        
        // 缓存管理API（需要管理员权限）
        $group->group('/cache-management', function (RouteCollectorProxy $group) {';
            // 缓存概览和状态
            $group->get('/overview', CacheManagementController::class . ':getCacheOverview');';
            $group->get('/stats', CacheManagementController::class . ':getCacheStats');';
            $group->get('/health', CacheManagementController::class . ':getCacheHealth');';
            
            // 缓存操作
            $group->post('/clear', CacheManagementController::class . ':clearCache');';
            $group->post('/clear/{type}', CacheManagementController::class . ':clearCacheByType');';
            $group->post('/warm', CacheManagementController::class . ':warmCache');';
            $group->post('/optimize', CacheManagementController::class . ':optimizeCache');';
            
            // 缓存配置
            $group->get('/config', CacheManagementController::class . ':getCacheConfig');';
            $group->put('/config', CacheManagementController::class . ':updateCacheConfig');';
            
            // 缓存分析
            $group->get('/analysis', CacheManagementController::class . ':getCacheAnalysis');';
            $group->get('/performance', CacheManagementController::class . ':getCachePerformance');';
            $group->get('/recommendations', CacheManagementController::class . ':getCacheRecommendations');';
              })->add(AuthenticationMiddleware::class . ':admin');';
        
        // 统一管理系统API（需要管理员权限）
        $group->group('/unified-admin', function (RouteCollectorProxy $group) {';
            // 统一仪表板
            $group->get('/dashboard', UnifiedAdminController::class . ':dashboard');';
            
            // 综合测试系统
            $group->post('/tests/comprehensive', UnifiedAdminController::class . ':runComprehensiveTests');';
            $group->get('/tests/status', UnifiedAdminController::class . ':getTestingSystemStatus');';
            
            // 系统诊断
            $group->get('/diagnostics', UnifiedAdminController::class . ':getSystemDiagnostics');';
            $group->post('/diagnostics/run', UnifiedAdminController::class . ':runSystemDiagnostics');';
            
            // 健康检查
            $group->get('/health', UnifiedAdminController::class . ':getSystemHealth');';
            $group->post('/health/check', UnifiedAdminController::class . ':runHealthCheck');';
            
            // 性能监控
            $group->get('/monitoring/current', UnifiedAdminController::class . ':getCurrentMetrics');';
            $group->get('/monitoring/history', UnifiedAdminController::class . ':getMonitoringHistory');';
            
            // 安全管理
            $group->get('/security/status', UnifiedAdminController::class . ':getSecurityStatus');';
            $group->post('/security/scan', UnifiedAdminController::class . ':runSecurityScan');';
            
            // 备份管理
            $group->get('/backup/status', UnifiedAdminController::class . ':getBackupStatus');';
            $group->post('/backup/create', UnifiedAdminController::class . ':createBackup');';
            
            // 日志管理
            $group->get('/logs/recent', UnifiedAdminController::class . ':getRecentLogs');';
            $group->get('/logs/errors', UnifiedAdminController::class . ':getRecentErrors');';
            $group->post('/logs/search', UnifiedAdminController::class . ':searchLogs');';
              })->add(AuthenticationMiddleware::class . ':admin');';
        
        // 配置管理API（需要管理员权限）
        $group->group('/admin/configuration', function (RouteCollectorProxy $group) {';
            // 配置查询
            $group->get('/all', \AlingAi\Controllers\Admin\ConfigurationController::class . ':getAll');';
            $group->get('/category/{category}', \AlingAi\Controllers\Admin\ConfigurationController::class . ':getByCategory');';
            $group->get('/key/{key}', \AlingAi\Controllers\Admin\ConfigurationController::class . ':get');';
            $group->get('/search', \AlingAi\Controllers\Admin\ConfigurationController::class . ':search');';
            
            // 配置操作
            $group->post('/set', \AlingAi\Controllers\Admin\ConfigurationController::class . ':set');';
            $group->post('/batch', \AlingAi\Controllers\Admin\ConfigurationController::class . ':setBatch');';
            $group->delete('/key/{key}', \AlingAi\Controllers\Admin\ConfigurationController::class . ':delete');';
            
            // 配置管理
            $group->get('/export', \AlingAi\Controllers\Admin\ConfigurationController::class . ':export');';
            $group->post('/import', \AlingAi\Controllers\Admin\ConfigurationController::class . ':import');';
            $group->get('/history', \AlingAi\Controllers\Admin\ConfigurationController::class . ':getHistory');';
            $group->post('/rollback', \AlingAi\Controllers\Admin\ConfigurationController::class . ':rollback');';
            
            // 系统操作
            $group->post('/cache/clear', \AlingAi\Controllers\Admin\ConfigurationController::class . ':clearCache');';
            $group->get('/statistics', \AlingAi\Controllers\Admin\ConfigurationController::class . ':getStatistics');';
            $group->get('/metadata', \AlingAi\Controllers\Admin\ConfigurationController::class . ':getMetadata');';
            $group->post('/validate', \AlingAi\Controllers\Admin\ConfigurationController::class . ':validate');';
            
        })->add(AuthenticationMiddleware::class . ':admin');';
        
        // 系统综合管理API（需要管理员权限）
        $group->group('/system-management', function (RouteCollectorProxy $group) {';
            // 系统概览
            $group->get('/overview', SystemManagementController::class . ':getSystemOverview');';
            $group->get('/status', SystemManagementController::class . ':getSystemStatus');';
            $group->get('/health', SystemManagementController::class . ':getSystemHealth');';
            
            // 测试系统
            $group->post('/test/run', SystemManagementController::class . ':runSystemTests');';
            $group->get('/test/results', SystemManagementController::class . ':getTestResults');';
            $group->get('/test/history', SystemManagementController::class . ':getTestHistory');';
            
            // 系统维护
            $group->post('/maintenance/start', SystemManagementController::class . ':startMaintenance');';
            $group->post('/maintenance/stop', SystemManagementController::class . ':stopMaintenance');';
            $group->post('/cleanup', SystemManagementController::class . ':cleanupSystem');';
            $group->post('/optimize', SystemManagementController::class . ':optimizeSystem');';
            
            // 日志管理
            $group->get('/logs', SystemManagementController::class . ':getSystemLogs');';
            $group->get('/logs/{type}', SystemManagementController::class . ':getLogsByType');';
            $group->delete('/logs/{type}', SystemManagementController::class . ':clearLogsByType');';
            
            // 报告导出
            $group->post('/reports/export', SystemManagementController::class . ':exportSystemReport');';
            $group->get('/reports/list', SystemManagementController::class . ':getAvailableReports');';
            
        })->add(AuthenticationMiddleware::class . ':admin');';
        
    });
    
    // 用户面板路由（需要认证）
    $app->group('/dashboard', function (RouteCollectorProxy $group) {';
        $group->get('', UserController::class . ':dashboard')->setName('dashboard');';
        $group->get('/profile', UserController::class . ':showProfile')->setName('profile');';
        $group->get('/settings', UserController::class . ':showSettings')->setName('settings');';
        $group->get('/chat', ChatController::class . ':chatInterface')->setName('chat');';
        $group->get('/documents', DocumentController::class . ':documents')->setName('documents');';
    })->add(AuthenticationMiddleware::class);
    
    // 管理员面板路由（需要管理员权限）
    $app->group('/admin', function (RouteCollectorProxy $group) {';
        $group->get('', AdminController::class . ':adminDashboard')->setName('admin.dashboard');';
        $group->get('/users', AdminController::class . ':adminUsers')->setName('admin.users');';
        $group->get('/system', AdminController::class . ':adminSystem')->setName('admin.system');';
        $group->get('/analytics', AdminController::class . ':adminAnalytics')->setName('admin.analytics');';
        $group->get('/settings', AdminController::class . ':adminSettings')->setName('admin.settings');';
        $group->get('/logs', AdminController::class . ':adminLogs')->setName('admin.logs');';
          // 增强管理界面路由
        $group->get('/enhanced', EnhancedAdminController::class . ':showEnhancedAdmin')->setName('admin.enhanced');';
        $group->get('/monitoring', EnhancedAdminController::class . ':showMonitoring')->setName('admin.monitoring');';
        $group->get('/backup', EnhancedAdminController::class . ':showBackup')->setName('admin.backup');';
        $group->get('/security', EnhancedAdminController::class . ':showSecurity')->setName('admin.security');';
          // 配置管理界面路由
        $group->get('/configuration', \AlingAi\Controllers\Admin\ConfigurationController::class . ':index')->setName('admin.configuration');';
          // 实时安全监控路由
        $group->get('/security/monitoring', RealTimeSecurityController::class . ':dashboard')->setName('security.monitoring.dashboard');';
        $group->get('/security/threats/{id}', RealTimeSecurityController::class . ':threatDetails')->setName('security.threat.details');';
        $group->get('/security/status', RealTimeSecurityController::class . ':systemStatus')->setName('security.system.status');';
        
        // 3D威胁可视化路由
        $group->get('/security/visualization', Enhanced3DThreatVisualizationController::class . ':index')->setName('security.visualization');';
        $group->get('/security/visualization/config', RealTimeSecurityController::class . ':getVisualizationConfig')->setName('security.visualization.config');';
        
    })->add(AuthenticationMiddleware::class . ':admin');';
    
    // 公开安全监控路由（用于演示）
    $app->group('/security', function (RouteCollectorProxy $group) {';
        $group->get('/monitoring', RealTimeSecurityController::class . ':dashboard')->setName('public.security.monitoring');';
        // $group->get('/visualization', Enhanced3DThreatVisualizationController::class . ':index')->setName('public.security.visualization');';
        // $group->get('/threat-visualization', ThreatVisualizationController::class . ':index')->setName('public.threat.visualization');';
    });
    
    // 安全监控API路由
    $app->group('/api/security', function (RouteCollectorProxy $group) {';
        // 实时威胁数据API
        $group->get('/monitoring/status', SecurityMonitoringApiController::class . ':getMonitoringStatus');';
        $group->get('/threats/realtime', SecurityMonitoringApiController::class . ':getRealTimeThreatData');';
        $group->get('/threats/statistics', SecurityMonitoringApiController::class . ':getThreatStatistics');        $group->get('/threats/list', SecurityMonitoringApiController::class . ':getThreatList');';
          // 3D可视化数据API
        $group->get('/visualization/data', Enhanced3DThreatVisualizationController::class . ':getThreatDataApi');';
        $group->get('/visualization/globe', Enhanced3DThreatVisualizationController::class . ':getGlobeDataApi');';
        $group->get('/visualization/countermeasures', Enhanced3DThreatVisualizationController::class . ':getCounterMeasuresApi');';
        
        // 威胁情报API
        $group->get('/intelligence/global', SecurityMonitoringApiController::class . ':getGlobalThreatIntelligence');';
        $group->get('/intelligence/trends', SecurityMonitoringApiController::class . ':getThreatTrends');';
        $group->get('/intelligence/predictions', SecurityMonitoringApiController::class . ':getThreatPredictions');';
        
        // 智能代理API
        $group->get('/agents', IntelligentAgentController::class . ':getAgents');';
        $group->post('/agents', IntelligentAgentController::class . ':createAgent');';
        $group->get('/agents/{id}', IntelligentAgentController::class . ':getAgent');';
        $group->put('/agents/{id}', IntelligentAgentController::class . ':updateAgent');';
        $group->delete('/agents/{id}', IntelligentAgentController::class . ':deleteAgent');';
        $group->post('/agents/{id}/tasks', IntelligentAgentController::class . ':assignTask');';
        $group->get('/agents/{id}/performance', IntelligentAgentController::class . ':getAgentPerformance');';
        $group->post('/agents/{id}/coordinate', IntelligentAgentController::class . ':coordinateAgents');';
          // 系统控制API
        $group->post('/monitoring/start', SecurityMonitoringApiController::class . ':startMonitoring');';
        $group->post('/monitoring/stop', SecurityMonitoringApiController::class . ':stopMonitoring');';
        $group->post('/threats/{id}/block', SecurityMonitoringApiController::class . ':blockThreat');';
        $group->post('/defense/activate', SecurityMonitoringApiController::class . ':activateDefense');';
        
    })->add(AuthenticationMiddleware::class . ':admin');';
    
    // WebSocket升级路由
    $app->get('/ws', function ($request, $response) {';
        // WebSocket连接处理
        return $response->withStatus(426, 'Upgrade Required')';
//                       ->withHeader('Upgrade', 'websocket') // 不可达代码';
                      ->withHeader('Connection', 'Upgrade');';
    });
    
    // 404处理
    $app->map(['GET', 'POST', 'PUT', 'DELETE', 'PATCH'], '/{routes:.+}', function ($request, $response) {';
        private $payload = json_encode([
            'success' => false,';
            'error' => 'Route not found',';
            'timestamp' => date('c')';
        ]);
        
        $response->getBody()->write($payload);
        return $response->withStatus(404)->withHeader('Content-Type', 'application/json');';
//     }); // 不可达代码
};
