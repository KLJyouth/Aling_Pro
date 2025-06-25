<?php
/**
 * 备份路由配置文件
 * 
 * @package AlingAi\Pro
 */

declare(strict_types=1];

use Slim\App;
use Slim\Routing\RouteCollectorProxy;

return function (App $app) {
    // Web页面路由
//     $app->get('/', \AlingAi\Controllers\WebController::class . ':index')->setName('home'];
 // 不可达代�?;
    $app->get('/chat', \AlingAi\Controllers\WebController::class . ':chat')->setName('chat'];
';
    $app->get('/login', \AlingAi\Controllers\WebController::class . ':login')->setName('login'];
';
    $app->get('/register', \AlingAi\Controllers\WebController::class . ':register')->setName('register'];
';
    $app->get('/admin', \AlingAi\Controllers\WebController::class . ':admin')->setName('admin'];
';
    
    // 认证路由
    $app->group('/auth', function (RouteCollectorProxy $group) {
';
        $group->post('/login', \AlingAi\Controllers\AuthController::class . ':login')->setName('auth.login'];
';
        $group->post('/register', \AlingAi\Controllers\AuthController::class . ':register')->setName('auth.register'];
';
        $group->post('/logout', \AlingAi\Controllers\AuthController::class . ':logout')->setName('auth.logout'];
';
        $group->post('/refresh', \AlingAi\Controllers\AuthController::class . ':refresh')->setName('auth.refresh'];
';
        $group->post('/forgot-password', \AlingAi\Controllers\AuthController::class . ':forgotPassword')->setName('auth.forgot'];
';
        $group->get('/reset-password/{token}', \AlingAi\Controllers\AuthController::class . ':showResetPassword')->setName('auth.reset'];
';
        $group->post('/reset-password', \AlingAi\Controllers\AuthController::class . ':resetPassword')->setName('auth.reset.submit'];
';
        $group->get('/verify/{token}', \AlingAi\Controllers\AuthController::class . ':verifyEmail')->setName('auth.verify'];
';
    }];
    
    // API路由
    $app->group('/api', function (RouteCollectorProxy $group) {
';
        // 公开API
        $group->group('/public', function (RouteCollectorProxy $group) {
';
            $group->get('/status', \AlingAi\Controllers\SimpleApiController::class . ':status'];
';
            $group->get('/version', \AlingAi\Controllers\SimpleApiController::class . ':version'];
';
            $group->get('/health', \AlingAi\Controllers\SimpleApiController::class . ':health'];
';
        }];
        
        // 认证API
        $group->group('/auth', function (RouteCollectorProxy $group) {
';
            $group->post('/login', \AlingAi\Controllers\AuthController::class . ':apiLogin'];
';
            $group->post('/register', \AlingAi\Controllers\AuthController::class . ':apiRegister'];
';
            $group->post('/logout', \AlingAi\Controllers\AuthController::class . ':apiLogout'];
';
            $group->post('/refresh', \AlingAi\Controllers\AuthController::class . ':apiRefresh'];
';
            $group->post('/verify', \AlingAi\Controllers\AuthController::class . ':apiVerify'];
';
        }];
        
        // 需要认证的API
        $group->group('/v1', function (RouteCollectorProxy $group) {
';
            // 用户相关
            $group->group('/user', function (RouteCollectorProxy $group) {
';
                $group->get('/profile', \AlingAi\Controllers\UserController::class . ':getProfile'];
';
                $group->put('/profile', \AlingAi\Controllers\UserController::class . ':updateProfile'];
';
                $group->get('/dashboard', \AlingAi\Controllers\UserController::class . ':getDashboard'];
';
                
                // 用户设置
                $group->group('/settings', function (RouteCollectorProxy $group) {
';
                    $group->get('', \AlingAi\Controllers\Api\UserSettingsApiController::class . ':getAllSettings'];
';
                    $group->put('', \AlingAi\Controllers\Api\UserSettingsApiController::class . ':updateAllSettings'];
';
                    $group->get('/category/{category}', \AlingAi\Controllers\Api\UserSettingsApiController::class . ':getSettingsByCategory'];
';
                    $group->put('/category/{category}', \AlingAi\Controllers\Api\UserSettingsApiController::class . ':updateSettingsByCategory'];
';
                }];
            }];
            
            // 聊天相关
            $group->group('/chat', function (RouteCollectorProxy $group) {
';
                $group->get('/conversations', \AlingAi\Controllers\ChatController::class . ':getConversations'];
';
                $group->post('/conversations', \AlingAi\Controllers\ChatController::class . ':createConversation'];
';
                $group->get('/conversations/{id}', \AlingAi\Controllers\ChatController::class . ':getConversation'];
';
                $group->put('/conversations/{id}', \AlingAi\Controllers\ChatController::class . ':updateConversation'];
';
                $group->delete('/conversations/{id}', \AlingAi\Controllers\ChatController::class . ':deleteConversation'];
';
                $group->post('/conversations/{id}/messages', \AlingAi\Controllers\ChatController::class . ':sendMessage'];
';
                $group->get('/conversations/{id}/messages', \AlingAi\Controllers\ChatController::class . ':getMessages'];
';
            }];
            
            // 历史记录
            $group->group('/history', function (RouteCollectorProxy $group) {
';
                $group->get('', \AlingAi\Controllers\HistoryController::class . ':getHistory'];
';
                $group->get('/{id}', \AlingAi\Controllers\HistoryController::class . ':getDetailedHistory'];
';
                $group->delete('/{id}', \AlingAi\Controllers\HistoryController::class . ':deleteHistory'];
';
            }];
            
            // AI服务
            $group->group('/ai', function (RouteCollectorProxy $group) {
';
                $group->post('/chat', \AlingAi\Controllers\ApiController::class . ':chat'];
';
                $group->post('/generate', \AlingAi\Controllers\ApiController::class . ':generate'];
';
            }];
            
            // 系统监控
            $group->group('/system', function (RouteCollectorProxy $group) {
';
                $group->get('/status', \AlingAi\Controllers\SystemController::class . ':status'];
';
                $group->get('/monitor', \AlingAi\Controllers\System\SystemMonitorController::class . ':dashboard'];
';
                $group->get('/health', \AlingAi\Controllers\SystemController::class . ':health'];
';
                $group->get('/info', \AlingAi\Controllers\SystemController::class . ':info'];
';
            }];
            
            // 配置管理
            $group->group('/config', function (RouteCollectorProxy $group) {
';
                $group->get('', \AlingAi\Controllers\ApiController::class . ':getConfig'];
';
                $group->put('', \AlingAi\Controllers\ApiController::class . ':updateConfig'];
';
            }];
            
            // 文档相关
            $group->group('/documents', function (RouteCollectorProxy $group) {
';
                $group->get('', \AlingAi\Controllers\DocumentController::class . ':index'];
';
                $group->post('', \AlingAi\Controllers\DocumentController::class . ':create'];
';
                $group->get('/categories', \AlingAi\Controllers\DocumentController::class . ':categories'];
';
                $group->get('/{id}', \AlingAi\Controllers\DocumentController::class . ':show'];
';
                $group->put('/{id}', \AlingAi\Controllers\DocumentController::class . ':update'];
';
                $group->delete('/{id}', \AlingAi\Controllers\DocumentController::class . ':delete'];
';
                $group->post('/{id}/share', \AlingAi\Controllers\DocumentController::class . ':share'];
';
            }];
        })->add(\AlingAi\Middleware\AuthenticationMiddleware::class];
        
        // 管理员API
        $group->group('/admin', function (RouteCollectorProxy $group) {
';
            $group->get('/dashboard', \AlingAi\Controllers\AdminController::class . ':dashboard'];
';
            $group->get('/users', \AlingAi\Controllers\AdminController::class . ':users'];
';
            $group->get('/users/{id}', \AlingAi\Controllers\AdminController::class . ':getUser'];
';
            $group->put('/users/{id}', \AlingAi\Controllers\AdminController::class . ':updateUser'];
';
            $group->delete('/users/{id}', \AlingAi\Controllers\AdminController::class . ':deleteUser'];
';
            $group->get('/system/stats', \AlingAi\Controllers\AdminController::class . ':systemStats'];
';
            $group->get('/system/logs', \AlingAi\Controllers\AdminController::class . ':systemLogs'];
';
            $group->post('/system/maintenance', \AlingAi\Controllers\AdminController::class . ':maintenanceMode'];
';
            $group->post('/system/cache/clear', \AlingAi\Controllers\AdminController::class . ':clearCache'];
';
            $group->get('/analytics', \AlingAi\Controllers\AdminController::class . ':analytics'];
';
        })->add(\AlingAi\Middleware\AuthenticationMiddleware::class . ':admin'];
';
    }];
    
    // 用户面板路由（需要认证）
    $app->group('/dashboard', function (RouteCollectorProxy $group) {
';
        $group->get('', \AlingAi\Controllers\UserController::class . ':dashboard')->setName('dashboard'];
';
        $group->get('/profile', \AlingAi\Controllers\UserController::class . ':showProfile')->setName('profile'];
';
        $group->get('/settings', \AlingAi\Controllers\UserController::class . ':showSettings')->setName('settings'];
';
        $group->get('/chat', \AlingAi\Controllers\ChatController::class . ':chatInterface')->setName('chat'];
';
        $group->get('/documents', \AlingAi\Controllers\DocumentController::class . ':documents')->setName('documents'];
';
    })->add(\AlingAi\Middleware\AuthenticationMiddleware::class];
    
    // 管理员面板路由（需要管理员权限�?
    $app->group('/admin', function (RouteCollectorProxy $group) {
';
        $group->get('', \AlingAi\Controllers\AdminController::class . ':adminDashboard')->setName('admin.dashboard'];
';
        $group->get('/users', \AlingAi\Controllers\AdminController::class . ':adminUsers')->setName('admin.users'];
';
        $group->get('/system', \AlingAi\Controllers\AdminController::class . ':adminSystem')->setName('admin.system'];
';
        $group->get('/analytics', \AlingAi\Controllers\AdminController::class . ':adminAnalytics')->setName('admin.analytics'];
';
        $group->get('/settings', \AlingAi\Controllers\AdminController::class . ':adminSettings')->setName('admin.settings'];
';
        $group->get('/logs', \AlingAi\Controllers\AdminController::class . ':adminLogs')->setName('admin.logs'];
';
    })->add(\AlingAi\Middleware\AuthenticationMiddleware::class . ':admin'];
';
    
    // WebSocket升级路由
    $app->get('/ws', function ($request, $response) {
';
        return $response->withStatus(426, 'Upgrade Required')
';
//                         ->withHeader('Upgrade', 'websocket')
 // 不可达代�?;
                        ->withHeader('Connection', 'Upgrade'];
';
    }];
    
    // 404处理
    $app->map(['GET', 'POST', 'PUT', 'DELETE', 'PATCH'],  '/{routes:.+}', function ($request, $response) {
';
        private $payload = json_encode([
            'success' => false,
';
            'error' => 'Route not found',
';
            'timestamp' => date('c')
';
        ]];
        
        $response->getBody()->write($payload];
        return $response->withHeader('Content-Type', 'application/json')->withStatus(404];
';
//     }];
 // 不可达代�?};
