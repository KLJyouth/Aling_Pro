<?php

declare(strict_types=1);

namespace AlingAi\Controllers;

use AlingAi\Models\{User, Conversation, Document, UserLog};
use AlingAi\Services\{CacheService, DatabaseServiceInterface, EmailService, EnhancedUserManagementService};
use AlingAi\Utils\Logger;
use Psr\Http\Message\{ResponseInterface, ServerRequestInterface};
use Exception;

/**
 * 增强管理员控制器
 * 专注于企业用户管理、API配额管理、支付统计等高级功能
 */
class EnhancedAdminController extends BaseController
{
    private EnhancedUserManagementService $userManagementService;
    private EmailService $emailService;

    public function __construct(
        DatabaseServiceInterface $db,
        CacheService $cache,
        EmailService $emailService,
        EnhancedUserManagementService $userManagementService
    ) {
        parent::__construct($db, $cache);
        $this->emailService = $emailService;
        $this->userManagementService = $userManagementService;
    }

    /**
     * 获取增强管理员仪表板数据
     */
    public function enhancedDashboard(ServerRequestInterface $request): array
    {
        try {
            if (!$this->isAdmin($request)) {
                return $this->jsonResponse(['error' => '需要管理员权限'], 403);
            }

            $dashboardData = [
                'overview' => $this->getSystemOverview(),
                'monitoring' => $this->getMonitoringData(),
                'backup_status' => $this->getBackupStatus(),
                'security_status' => $this->getSecurityStatus(),
                'performance_metrics' => $this->getPerformanceMetrics(),
                'operations_tasks' => $this->getOperationsTasks(),
                'system_health' => $this->getSystemHealth(),
                'alerts' => $this->getActiveAlerts(),
                'quick_actions' => $this->getQuickActions()
            ];

            return $this->jsonResponse($dashboardData);
        } catch (\Exception $e) {
            $this->logger->error('Enhanced dashboard error: ' . $e->getMessage());
            return $this->jsonResponse(['error' => '获取仪表板数据失败'], 500);
        }
    }

    /**
     * 获取系统概览数据
     */
    private function getSystemOverview(): array
    {
        $db = $this->databaseService->getConnection();
        
        return [
            'users' => [
                'total' => $db->table('users')->count(),
                'active_today' => $db->table('users')
                    ->where('last_login_at', '>=', date('Y-m-d 00:00:00'))
                    ->count(),
                'new_this_week' => $db->table('users')
                    ->where('created_at', '>=', date('Y-m-d', strtotime('-7 days')))
                    ->count()
            ],
            'conversations' => [
                'total' => $db->table('conversations')->count(),
                'today' => $db->table('conversations')
                    ->where('created_at', '>=', date('Y-m-d 00:00:00'))
                    ->count(),
                'active' => $db->table('conversations')
                    ->where('status', 'active')
                    ->count()
            ],
            'messages' => [
                'total' => $db->table('messages')->count(),
                'today' => $db->table('messages')
                    ->where('created_at', '>=', date('Y-m-d 00:00:00'))
                    ->count(),
                'avg_per_conversation' => round(
                    $db->table('messages')->count() / max(1, $db->table('conversations')->count()),
                    2
                )
            ],
            'system' => [
                'uptime' => $this->getSystemUptime(),
                'php_version' => PHP_VERSION,
                'memory_usage' => memory_get_usage(true),
                'disk_space' => disk_free_space('/'),
                'server_load' => sys_getloadavg()[0] ?? 0
            ]
        ];
    }

    /**
     * 获取监控数据
     */
    private function getMonitoringData(): array
    {
        $db = $this->databaseService->getConnection();
        $now = date('Y-m-d H:i:s');
        $oneHourAgo = date('Y-m-d H:i:s', strtotime('-1 hour'));

        return [
            'real_time_metrics' => $this->monitoringService->getRealTimeMetrics(),
            'performance_trends' => $db->table('system_monitoring')
                ->select('metric_name', 'metric_value', 'collected_at')
                ->where('metric_type', 'performance')
                ->where('collected_at', '>=', $oneHourAgo)
                ->orderBy('collected_at', 'desc')
                ->limit(100)
                ->get(),
            'error_rates' => $this->getErrorRates(),
            'response_times' => $this->getResponseTimes(),
            'active_connections' => $this->getActiveConnections()
        ];
    }

    /**
     * 获取备份状态
     */
    private function getBackupStatus(): array
    {
        $db = $this->databaseService->getConnection();
        
        $latestBackups = $db->table('backup_records')
            ->select('backup_type', 'status', 'start_time', 'end_time', 'backup_size')
            ->orderBy('start_time', 'desc')
            ->limit(10)
            ->get();

        $backupStats = $db->table('backup_records')
            ->selectRaw('
                backup_type,
                COUNT(*) as total_backups,
                SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as successful_backups,
                SUM(backup_size) as total_size,
                MAX(start_time) as last_backup
            ')
            ->groupBy('backup_type')
            ->get();

        return [
            'latest_backups' => $latestBackups,
            'backup_statistics' => $backupStats,
            'backup_schedule' => $this->backupService->getScheduleStatus(),
            'storage_usage' => $this->backupService->getStorageUsage(),
            'retention_policy' => $this->backupService->getRetentionPolicy()
        ];
    }

    /**
     * 获取安全状态
     */
    private function getSecurityStatus(): array
    {
        $db = $this->databaseService->getConnection();
        
        $latestScans = $db->table('security_scans')
            ->select('scan_type', 'status', 'critical_issues', 'start_time', 'end_time')
            ->orderBy('start_time', 'desc')
            ->limit(10)
            ->get();

        return [
            'latest_scans' => $latestScans,
            'vulnerability_summary' => $this->securityService->getVulnerabilitySummary(),
            'access_violations' => $this->securityService->getAccessViolations(),
            'security_alerts' => $this->securityService->getActiveAlerts(),
            'compliance_status' => $this->securityService->getComplianceStatus()
        ];
    }

    /**
     * 运维任务管理
     */
    public function operationsTasks(ServerRequestInterface $request): array
    {
        try {
            if (!$this->isAdmin($request)) {
                return $this->jsonResponse(['error' => '需要管理员权限'], 403);
            }

            $method = $request->getMethod();
            
            switch ($method) {
                case 'GET':
                    return $this->getOperationsTasks();
                case 'POST':
                    return $this->createOperationsTask($request);
                case 'PUT':
                    return $this->updateOperationsTask($request);
                case 'DELETE':
                    return $this->deleteOperationsTask($request);
                default:
                    return $this->jsonResponse(['error' => '不支持的方法'], 405);
            }
        } catch (\Exception $e) {
            $this->logger->error('Operations tasks error: ' . $e->getMessage());
            return $this->jsonResponse(['error' => '操作失败'], 500);
        }
    }

    /**
     * 执行备份操作
     */
    public function executeBackup(ServerRequestInterface $request): array
    {
        try {
            if (!$this->isAdmin($request)) {
                return $this->jsonResponse(['error' => '需要管理员权限'], 403);
            }

            $body = json_decode($request->getBody()->getContents(), true);
            $backupType = $body['type'] ?? 'full';
            $backupScope = $body['scope'] ?? 'database';

            $result = $this->backupService->executeBackup($backupType, $backupScope);

            return $this->jsonResponse([
                'success' => true,
                'message' => '备份任务已启动',
                'backup_id' => $result['backup_id'],
                'estimated_time' => $result['estimated_time']
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Backup execution error: ' . $e->getMessage());
            return $this->jsonResponse(['error' => '备份执行失败: ' . $e->getMessage()], 500);
        }
    }

    /**
     * 执行安全扫描
     */
    public function executeSecurityScan(ServerRequestInterface $request): array
    {
        try {
            if (!$this->isAdmin($request)) {
                return $this->jsonResponse(['error' => '需要管理员权限'], 403);
            }

            $body = json_decode($request->getBody()->getContents(), true);
            $scanType = $body['scan_type'] ?? 'vulnerability';
            $target = $body['target'] ?? 'full_system';

            $result = $this->securityService->executeScan($scanType, $target);

            return $this->jsonResponse([
                'success' => true,
                'message' => '安全扫描已启动',
                'scan_id' => $result['scan_id'],
                'estimated_time' => $result['estimated_time']
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Security scan error: ' . $e->getMessage());
            return $this->jsonResponse(['error' => '安全扫描失败: ' . $e->getMessage()], 500);
        }
    }

    /**
     * 执行性能测试
     */
    public function executePerformanceTest(ServerRequestInterface $request): array
    {
        try {
            if (!$this->isAdmin($request)) {
                return $this->jsonResponse(['error' => '需要管理员权限'], 403);
            }

            $body = json_decode($request->getBody()->getContents(), true);
            
            $testConfig = [
                'test_type' => $body['test_type'] ?? 'load',
                'test_name' => $body['test_name'] ?? '负载测试',
                'target_url' => $body['target_url'] ?? '/api/chat',
                'concurrent_users' => $body['concurrent_users'] ?? 10,
                'duration_seconds' => $body['duration_seconds'] ?? 60,
                'parameters' => $body['parameters'] ?? []
            ];

            $testId = $this->executePerformanceTestTask($testConfig);

            return $this->jsonResponse([
                'success' => true,
                'message' => '性能测试已启动',
                'test_id' => $testId,
                'status_url' => "/admin/api/performance-test/{$testId}/status"
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Performance test error: ' . $e->getMessage());
            return $this->jsonResponse(['error' => '性能测试失败: ' . $e->getMessage()], 500);
        }
    }

    /**
     * 获取系统健康状态
     */
    public function getSystemHealth(): array
    {
        return [
            'database' => $this->checkDatabaseHealth(),
            'cache' => $this->checkCacheHealth(),
            'storage' => $this->checkStorageHealth(),
            'memory' => $this->checkMemoryHealth(),
            'network' => $this->checkNetworkHealth(),
            'services' => $this->checkServicesHealth()
        ];
    }

    /**
     * 导出系统报告
     */
    public function exportSystemReport(ServerRequestInterface $request): array
    {
        try {
            if (!$this->isAdmin($request)) {
                return $this->jsonResponse(['error' => '需要管理员权限'], 403);
            }

            $reportType = $request->getQueryParams()['type'] ?? 'comprehensive';
            $format = $request->getQueryParams()['format'] ?? 'json';

            $reportData = $this->generateSystemReport($reportType);
            
            $filename = "system_report_{$reportType}_" . date('Y_m_d_H_i_s') . ".{$format}";
            $filePath = __DIR__ . "/../../storage/reports/{$filename}";

            // 确保目录存在
            if (!file_exists(dirname($filePath))) {
                mkdir(dirname($filePath), 0755, true);
            }

            // 导出报告
            switch ($format) {
                case 'json':
                    file_put_contents($filePath, json_encode($reportData, JSON_PRETTY_PRINT));
                    break;
                case 'csv':
                    $this->exportToCsv($reportData, $filePath);
                    break;
                case 'pdf':
                    $this->exportToPdf($reportData, $filePath);
                    break;
            }

            return $this->jsonResponse([
                'success' => true,
                'message' => '报告导出成功',
                'filename' => $filename,
                'download_url' => "/admin/download/report/{$filename}"
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Report export error: ' . $e->getMessage());
            return $this->jsonResponse(['error' => '报告导出失败'], 500);
        }
    }

    // 企业用户管理

    /**
     * 获取企业用户列表
     */
    public function getEnterpriseUsers(ServerRequestInterface $request): array
    {
        try {
            if (!$this->isAdmin($request)) {
                return $this->jsonResponse(['error' => '需要管理员权限'], 403);
            }

            $users = $this->userManagementService->getAllEnterpriseUsers();

            return $this->jsonResponse($users);
        } catch (\Exception $e) {
            $this->logger->error('Get enterprise users error: ' . $e->getMessage());
            return $this->jsonResponse(['error' => '获取企业用户失败'], 500);
        }
    }

    /**
     * 申请成为企业用户
     */
    public function applyEnterpriseUser(ServerRequestInterface $request): array
    {
        try {
            $body = json_decode($request->getBody()->getContents(), true);
            $userId = $body['user_id'] ?? null;
            $companyName = $body['company_name'] ?? null;
            $contactEmail = $body['contact_email'] ?? null;

            if (!$userId || !$companyName || !$contactEmail) {
                return $this->jsonResponse(['error' => '缺少必要参数'], 400);
            }

            $result = $this->userManagementService->applyForEnterpriseUser($userId, $companyName, $contactEmail);

            return $this->jsonResponse([
                'success' => true,
                'message' => '申请已提交，请等待审核',
                'application_id' => $result['application_id']
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Apply enterprise user error: ' . $e->getMessage());
            return $this->jsonResponse(['error' => '申请成为企业用户失败'], 500);
        }
    }

    /**
     * 审核企业用户申请
     */
    public function reviewEnterpriseUserApplication(ServerRequestInterface $request): array
    {
        try {
            if (!$this->isAdmin($request)) {
                return $this->jsonResponse(['error' => '需要管理员权限'], 403);
            }

            $body = json_decode($request->getBody()->getContents(), true);
            $applicationId = $body['application_id'] ?? null;
            $status = $body['status'] ?? null;
            $remarks = $body['remarks'] ?? null;

            if (!$applicationId || !in_array($status, ['approved', 'rejected'])) {
                return $this->jsonResponse(['error' => '缺少必要参数'], 400);
            }

            $this->userManagementService->reviewApplication($applicationId, $status, $remarks);

            return $this->jsonResponse(['success' => true, 'message' => '审核完成']);
        } catch (\Exception $e) {
            $this->logger->error('Review enterprise user application error: ' . $e->getMessage());
            return $this->jsonResponse(['error' => '审核企业用户申请失败'], 500);
        }
    }

    /**
     * 更新企业用户配额
     */
    public function updateEnterpriseUserQuota(ServerRequestInterface $request): array
    {
        try {
            if (!$this->isAdmin($request)) {
                return $this->jsonResponse(['error' => '需要管理员权限'], 403);
            }

            $body = json_decode($request->getBody()->getContents(), true);
            $userId = $body['user_id'] ?? null;
            $newQuota = $body['quota'] ?? null;

            if (!$userId || !$newQuota) {
                return $this->jsonResponse(['error' => '缺少必要参数'], 400);
            }

            $this->userManagementService->updateUserQuota($userId, $newQuota);

            return $this->jsonResponse(['success' => true, 'message' => '配额更新成功']);
        } catch (\Exception $e) {
            $this->logger->error('Update enterprise user quota error: ' . $e->getMessage());
            return $this->jsonResponse(['error' => '更新企业用户配额失败'], 500);
        }
    }

    /**
     * 获取企业用户支付统计
     */
    public function getEnterpriseUserPaymentStats(ServerRequestInterface $request): array
    {
        try {
            if (!$this->isAdmin($request)) {
                return $this->jsonResponse(['error' => '需要管理员权限'], 403);
            }

            $stats = $this->userManagementService->getPaymentStatistics();

            return $this->jsonResponse($stats);
        } catch (\Exception $e) {
            $this->logger->error('Get enterprise user payment stats error: ' . $e->getMessage());
            return $this->jsonResponse(['error' => '获取企业用户支付统计失败'], 500);
        }
    }

    // 辅助方法

    private function getSystemUptime(): string
    {
        if (function_exists('sys_getloadavg')) {
            $uptime = shell_exec('uptime');
            return $uptime ? trim($uptime) : 'Unknown';
        }
        return 'N/A';
    }

    private function getErrorRates(): array
    {
        $db = $this->databaseService->getConnection();
        
        return $db->table('system_logs')
            ->selectRaw('level, COUNT(*) as count')
            ->where('created_at', '>=', date('Y-m-d H:i:s', strtotime('-24 hours')))
            ->groupBy('level')
            ->get()
            ->keyBy('level')
            ->toArray();
    }

    private function getResponseTimes(): array
    {
        $db = $this->databaseService->getConnection();
        
        return $db->table('system_monitoring')
            ->select('metric_value', 'collected_at')
            ->where('metric_name', 'response_time')
            ->where('collected_at', '>=', date('Y-m-d H:i:s', strtotime('-1 hour')))
            ->orderBy('collected_at', 'desc')
            ->limit(60)
            ->get()
            ->toArray();
    }

    private function getActiveConnections(): int
    {
        // 根据数据库类型实现
        try {
            $db = $this->databaseService->getConnection();
            $result = $db->select("SHOW STATUS LIKE 'Threads_connected'");
            return $result[0]->Value ?? 0;
        } catch (\Exception $e) {
            return 0;
        }
    }

    private function checkDatabaseHealth(): array
    {
        try {
            $db = $this->databaseService->getConnection();
            $start = microtime(true);
            $db->select('SELECT 1');
            $responseTime = (microtime(true) - $start) * 1000;
            
            return [
                'status' => 'healthy',
                'response_time' => round($responseTime, 2),
                'connection_count' => $this->getActiveConnections()
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'unhealthy',
                'error' => $e->getMessage()
            ];
        }
    }

    private function checkCacheHealth(): array
    {
        try {
            $start = microtime(true);
            $this->cacheService->set('health_check', 'test', 10);
            $result = $this->cacheService->get('health_check');
            $responseTime = (microtime(true) - $start) * 1000;
            
            return [
                'status' => $result === 'test' ? 'healthy' : 'degraded',
                'response_time' => round($responseTime, 2)
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'unhealthy',
                'error' => $e->getMessage()
            ];
        }
    }

    private function checkStorageHealth(): array
    {
        $path = __DIR__ . '/../../storage';
        $totalBytes = disk_total_space($path);
        $freeBytes = disk_free_space($path);
        $usedBytes = $totalBytes - $freeBytes;
        $usagePercent = ($usedBytes / $totalBytes) * 100;

        return [
            'status' => $usagePercent > 90 ? 'warning' : 'healthy',
            'total_space' => $totalBytes,
            'free_space' => $freeBytes,
            'used_space' => $usedBytes,
            'usage_percent' => round($usagePercent, 2)
        ];
    }

    private function checkMemoryHealth(): array
    {
        $memoryUsage = memory_get_usage(true);
        $memoryLimit = ini_get('memory_limit');
        $memoryLimitBytes = $this->convertToBytes($memoryLimit);
        $usagePercent = ($memoryUsage / $memoryLimitBytes) * 100;

        return [
            'status' => $usagePercent > 80 ? 'warning' : 'healthy',
            'current_usage' => $memoryUsage,
            'memory_limit' => $memoryLimitBytes,
            'usage_percent' => round($usagePercent, 2)
        ];
    }

    private function convertToBytes(string $value): int
    {
        $unit = strtoupper(substr($value, -1));
        $number = (int) substr($value, 0, -1);
        
        switch ($unit) {
            case 'G': return $number * 1024 * 1024 * 1024;
            case 'M': return $number * 1024 * 1024;
            case 'K': return $number * 1024;
            default: return (int) $value;
        }
    }
}
