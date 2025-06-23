<?php

namespace AlingAi\Core;

use AlingAi\Controllers\Api\AuthApiController;
use AlingAi\Controllers\Api\EnhancedChatApiController;
use AlingAi\Controllers\Api\UserApiController;
use AlingAi\Controllers\Api\AdminApiController;
use AlingAi\Controllers\Api\SystemApiController;
use AlingAi\Controllers\Api\FileApiController;
use AlingAi\Controllers\Api\MonitorApiController;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;

/**
 * API Routes Registration
 * 
 * Registers all API routes and applies middleware
 */
class ApiRoutes
{
    private App $app;
    
    public function __construct(App $app)
    {
        $this->app = $app;
    }
    
    /**
     * Register all API routes
     */
    public function register(): void
    {
        $this->app->group('/api/v1', function (RouteCollectorProxy $group) {
            $this->registerAuthRoutes($group);
            $this->registerChatRoutes($group);
            $this->registerUserRoutes($group);
            $this->registerAdminRoutes($group);
            $this->registerSystemRoutes($group);
            $this->registerFileRoutes($group);
            $this->registerMonitorRoutes($group);
        });
    }
    
    /**
     * Authentication routes
     */
    private function registerAuthRoutes(RouteCollectorProxy $group): void
    {
        $group->group('/auth', function (RouteCollectorProxy $auth) {
            // Public authentication routes
            $auth->post('/login', [AuthApiController::class, 'login']);
            $auth->post('/register', [AuthApiController::class, 'register']);
            $auth->post('/forgot-password', [AuthApiController::class, 'forgotPassword']);
            $auth->post('/reset-password', [AuthApiController::class, 'resetPassword']);
            $auth->post('/verify-email', [AuthApiController::class, 'verifyEmail']);
            $auth->post('/resend-verification', [AuthApiController::class, 'resendVerification']);
            
            // Protected authentication routes
            $auth->post('/logout', [AuthApiController::class, 'logout']);
            $auth->post('/refresh', [AuthApiController::class, 'refreshToken']);
            $auth->get('/me', [AuthApiController::class, 'getCurrentUser']);
            $auth->post('/change-password', [AuthApiController::class, 'changePassword']);
            $auth->delete('/revoke-token', [AuthApiController::class, 'revokeToken']);
            $auth->get('/sessions', [AuthApiController::class, 'getUserSessions']);
            $auth->delete('/sessions/{id}', [AuthApiController::class, 'revokeSession']);
        });
    }
    
    /**
     * Chat routes
     */    private function registerChatRoutes(RouteCollectorProxy $group): void
    {
        $group->group('/chat', function (RouteCollectorProxy $chat) {
            // Chat sessions
            $chat->get('/sessions', [EnhancedChatApiController::class, 'getUserSessions']);
            $chat->post('/sessions', [EnhancedChatApiController::class, 'createSession']);
            $chat->get('/sessions/{id}', [EnhancedChatApiController::class, 'getSession']);
            $chat->put('/sessions/{id}', [EnhancedChatApiController::class, 'updateSession']);
            $chat->delete('/sessions/{id}', [EnhancedChatApiController::class, 'deleteSession']);
            
            // Chat messages
            $chat->get('/sessions/{id}/messages', [EnhancedChatApiController::class, 'getMessages']);
            $chat->post('/sessions/{id}/messages', [EnhancedChatApiController::class, 'sendMessage']);
            $chat->delete('/messages/{id}', [EnhancedChatApiController::class, 'deleteMessage']);
            
            // Chat utilities
            $chat->get('/models', [EnhancedChatApiController::class, 'getAvailableModels']);
            $chat->post('/export/{id}', [EnhancedChatApiController::class, 'exportConversation']);
            $chat->post('/import', [EnhancedChatApiController::class, 'importConversation']);
        });
    }
    
    /**
     * User routes
     */
    private function registerUserRoutes(RouteCollectorProxy $group): void
    {
        $group->group('/user', function (RouteCollectorProxy $user) {
            // Profile management
            $user->get('/profile', [UserApiController::class, 'getProfile']);
            $user->put('/profile', [UserApiController::class, 'updateProfile']);
            $user->post('/avatar', [UserApiController::class, 'uploadAvatar']);
            $user->delete('/avatar', [UserApiController::class, 'deleteAvatar']);
            
            // Settings
            $user->get('/settings', [UserApiController::class, 'getSettings']);
            $user->put('/settings', [UserApiController::class, 'updateSettings']);
            
            // Security
            $user->post('/change-password', [UserApiController::class, 'changePassword']);
            $user->get('/activity', [UserApiController::class, 'getActivityLogs']);
            $user->get('/statistics', [UserApiController::class, 'getStatistics']);
            
            // Account management
            $user->post('/export-data', [UserApiController::class, 'exportUserData']);
            $user->delete('/account', [UserApiController::class, 'deleteAccount']);
        });
    }
    
    /**
     * Admin routes
     */
    private function registerAdminRoutes(RouteCollectorProxy $group): void
    {
        $group->group('/admin', function (RouteCollectorProxy $admin) {
            // Dashboard
            $admin->get('/dashboard', [AdminApiController::class, 'getDashboardData']);
            
            // User management
            $admin->get('/users', [AdminApiController::class, 'getUsers']);
            $admin->get('/users/{id}', [AdminApiController::class, 'getUser']);
            $admin->put('/users/{id}', [AdminApiController::class, 'updateUser']);
            $admin->delete('/users/{id}', [AdminApiController::class, 'deleteUser']);
            $admin->post('/users/{id}/toggle-status', [AdminApiController::class, 'toggleUserStatus']);
            
            // System management
            $admin->get('/statistics', [AdminApiController::class, 'getSystemStatistics']);
            $admin->get('/logs', [AdminApiController::class, 'getSystemLogs']);
            $admin->post('/maintenance', [AdminApiController::class, 'toggleMaintenanceMode']);
            $admin->post('/cache/clear', [AdminApiController::class, 'clearCache']);
            $admin->post('/backup', [AdminApiController::class, 'createBackup']);
        });
    }
    
    /**
     * System routes
     */
    private function registerSystemRoutes(RouteCollectorProxy $group): void
    {
        $group->group('/system', function (RouteCollectorProxy $system) {
            // Health and status
            $system->get('/health', [SystemApiController::class, 'getSystemHealth']);
            $system->get('/config', [SystemApiController::class, 'getSystemConfig']);
            $system->put('/config', [SystemApiController::class, 'updateSystemConfig']);
            
            // Performance and monitoring
            $system->get('/metrics', [SystemApiController::class, 'getPerformanceMetrics']);
            $system->post('/diagnostics', [SystemApiController::class, 'runDiagnostics']);
            $system->post('/cache/clear', [SystemApiController::class, 'clearSystemCache']);
            
            // Logs
            $system->get('/logs', [SystemApiController::class, 'getSystemLogs']);
        });
    }
    
    /**
     * File routes
     */
    private function registerFileRoutes(RouteCollectorProxy $group): void
    {
        $group->group('/files', function (RouteCollectorProxy $files) {
            // File management
            $files->get('', [FileApiController::class, 'getUserFiles']);
            $files->post('/upload', [FileApiController::class, 'uploadFiles']);
            $files->get('/{id}', [FileApiController::class, 'getFileDetails']);
            $files->put('/{id}', [FileApiController::class, 'updateFile']);
            $files->delete('/{id}', [FileApiController::class, 'deleteFile']);
            
            // File operations
            $files->get('/{id}/download', [FileApiController::class, 'downloadFile']);
            $files->post('/{id}/share', [FileApiController::class, 'shareFile']);
            
            // Storage
            $files->get('/storage/usage', [FileApiController::class, 'getStorageUsage']);
        });
    }
    
    /**
     * Monitoring routes
     */
    private function registerMonitorRoutes(RouteCollectorProxy $group): void
    {
        $group->group('/monitor', function (RouteCollectorProxy $monitor) {
            // Real-time monitoring
            $monitor->get('/metrics', [MonitorApiController::class, 'getRealtimeMetrics']);
            $monitor->get('/health', [MonitorApiController::class, 'getHealthCheck']);
            
            // Analytics
            $monitor->get('/analytics/performance', [MonitorApiController::class, 'getPerformanceAnalytics']);
            $monitor->get('/analytics/users', [MonitorApiController::class, 'getUserActivityAnalytics']);
            
            // Error monitoring
            $monitor->get('/errors', [MonitorApiController::class, 'getErrorMonitoring']);
            $monitor->get('/security', [MonitorApiController::class, 'getSecurityMonitoring']);
            
            // Custom dashboards
            $monitor->post('/dashboards', [MonitorApiController::class, 'createDashboard']);
            $monitor->get('/dashboards/{id}', [MonitorApiController::class, 'getDashboard']);
            
            // Alerts
            $monitor->post('/alerts', [MonitorApiController::class, 'createAlert']);
            
            // Data export
            $monitor->get('/export', [MonitorApiController::class, 'exportData']);
        });
    }
}
