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
    EnhancedAdminController
};
use AlingAi\Controllers\Api\UserSettingsApiController;
use AlingAi\Middleware\AuthenticationMiddleware;

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
    $app->group('/api', function (RouteCollectorProxy $group) {';
        // 公开API
        $group->group('/public', function (RouteCollectorProxy $group) {';
            $group->get('/health', SystemController::class . ':health');';
            $group->get('/status', SystemController::class . ':status');';
            $group->get('/version', SystemController::class . ':version');';
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
            });

            // 历史记录相关
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
            $group->get('/logs/{type}', EnhancedAdminController::class . ':getLogsByType');';
            $group->delete('/logs/{type}', EnhancedAdminController::class . ':clearLogs');';
            
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
