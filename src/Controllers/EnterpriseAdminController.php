<?php

declare(strict_types=1);

namespace AlingAi\Controllers;

use AlingAi\Services\{DatabaseServiceInterface, CacheService, EmailService, EnhancedUserManagementService};
use AlingAi\Utils\Logger;
use Psr\Http\Message\{ResponseInterface, ServerRequestInterface};
use Exception;

/**
 * 企业管理员控制器
 * 专注于企业用户管理、API配额管理、支付统计等高级功能
 */
class EnterpriseAdminController extends BaseController
{
    private EnhancedUserManagementService $userManagementService;
    private EmailService $emailService;
    protected Logger $logger;

    public function __construct(
        DatabaseServiceInterface $db,
        CacheService $cache,
        EmailService $emailService,
        EnhancedUserManagementService $userManagementService
    ) {
        parent::__construct($db, $cache);
        $this->emailService = $emailService;
        $this->userManagementService = $userManagementService;
        $this->logger = new Logger();
    }

    /**
     * 获取企业用户管理仪表板
     */
    public function getEnterpriseAdminDashboard(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            if (!$this->isAdmin($request)) {
                return $this->errorResponse($response, '需要管理员权限', 403);
            }

            $dashboardData = [
                'overview' => $this->getEnterpriseOverview(),
                'pending_applications' => $this->getPendingApplications(),
                'api_usage_stats' => $this->getApiUsageStats(),
                'payment_overview' => $this->getPaymentOverview(),
                'ai_providers_status' => $this->getAiProvidersStatus(),
                'recent_activities' => $this->getRecentActivities()
            ];

            return $this->successResponse($response, $dashboardData);

        } catch (Exception $e) {
            $this->logger->error('Enterprise admin dashboard error: ' . $e->getMessage());
            return $this->errorResponse($response, '获取仪表板数据失败', 500);
        }
    }

    /**
     * 获取企业用户列表
     */
    public function getEnterpriseUsers(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            if (!$this->isAdmin($request)) {
                return $this->errorResponse($response, '需要管理员权限', 403);
            }

            $params = $request->getQueryParams();
            $page = (int)($params['page'] ?? 1);
            $limit = min((int)($params['limit'] ?? 20), 100);
            $status = $params['status'] ?? null;
            $applicationStatus = $params['application_status'] ?? null;

            $users = $this->userManagementService->getEnterpriseUsers([
                'page' => $page,
                'limit' => $limit,
                'status' => $status,
                'application_status' => $applicationStatus
            ]);

            return $this->successResponse($response, $users);

        } catch (Exception $e) {
            $this->logger->error('Get enterprise users error: ' . $e->getMessage());
            return $this->errorResponse($response, '获取企业用户失败', 500);
        }
    }

    /**
     * 申请成为企业用户
     */
    public function applyEnterpriseUser(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $data = $this->getJsonData($request);
            $userId = $data['user_id'] ?? null;
            $companyName = $data['company_name'] ?? null;
            $contactEmail = $data['contact_email'] ?? null;
            $reason = $data['reason'] ?? null;

            if (!$userId || !$companyName || !$contactEmail) {
                return $this->errorResponse($response, '缺少必要参数', 400);
            }            $result = $this->userManagementService->submitEnterpriseApplication([
                'user_id' => (int)$userId,
                'company_name' => $companyName,
                'contact_email' => $contactEmail,
                'reason' => $reason
            ]);

            if ($result) {
                return $this->successResponse($response, [
                    'application_id' => $result['application_id'],
                    'message' => '企业用户申请已提交，请等待审核'
                ]);
            }

            return $this->errorResponse($response, '申请提交失败', 500);

        } catch (Exception $e) {
            $this->logger->error('Apply enterprise user error: ' . $e->getMessage());
            return $this->errorResponse($response, '申请成为企业用户失败', 500);
        }
    }

    /**
     * 审核企业用户申请
     */
    public function reviewEnterpriseUserApplication(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            if (!$this->isAdmin($request)) {
                return $this->errorResponse($response, '需要管理员权限', 403);
            }

            $data = $this->getJsonData($request);
            $applicationId = $data['application_id'] ?? null;
            $status = $data['status'] ?? null;
            $reviewNotes = $data['review_notes'] ?? null;
            $reviewerId = $data['reviewer_id'] ?? null;

            if (!$applicationId || !in_array($status, ['approved', 'rejected'])) {
                return $this->errorResponse($response, '缺少必要参数', 400);
            }            $result = $this->userManagementService->reviewEnterpriseApplication(
                (int)$applicationId,
                $status,
                [
                    'admin_id' => (int)$reviewerId,
                    'notes' => $reviewNotes
                ]
            );

            if ($result) {
                return $this->successResponse($response, ['message' => '审核完成']);
            }

            return $this->errorResponse($response, '审核失败', 500);

        } catch (Exception $e) {
            $this->logger->error('Review enterprise user application error: ' . $e->getMessage());
            return $this->errorResponse($response, '审核企业用户申请失败', 500);
        }
    }

    /**
     * 更新企业用户配额
     */
    public function updateEnterpriseUserQuota(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            if (!$this->isAdmin($request)) {
                return $this->errorResponse($response, '需要管理员权限', 403);
            }

            $data = $this->getJsonData($request);
            $userId = $data['user_id'] ?? null;
            $quotaType = $data['quota_type'] ?? 'monthly'; // daily, monthly
            $newQuota = $data['new_quota'] ?? null;
            $reason = $data['reason'] ?? null;

            if (!$userId || !$newQuota) {
                return $this->errorResponse($response, '缺少必要参数', 400);
            }            $result = $this->userManagementService->updateUserQuota(
                (int)$userId,
                [
                    'daily_quota' => $quotaType === 'daily' ? (int)$newQuota : null,
                    'monthly_quota' => $quotaType === 'monthly' ? (int)$newQuota : null,
                    'reason' => $reason
                ]
            );

            if ($result) {
                return $this->successResponse($response, ['message' => '配额更新成功']);
            }

            return $this->errorResponse($response, '配额更新失败', 500);

        } catch (Exception $e) {
            $this->logger->error('Update enterprise user quota error: ' . $e->getMessage());
            return $this->errorResponse($response, '更新企业用户配额失败', 500);
        }
    }

    /**
     * 获取企业用户支付统计
     */
    public function getEnterpriseUserPaymentStats(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            if (!$this->isAdmin($request)) {
                return $this->errorResponse($response, '需要管理员权限', 403);
            }

            $params = $request->getQueryParams();
            $period = $params['period'] ?? '30d'; // 7d, 30d, 90d
            $userId = $params['user_id'] ?? null;

            $stats = $this->getPaymentStatistics($period, $userId);

            return $this->successResponse($response, $stats);

        } catch (Exception $e) {
            $this->logger->error('Get enterprise user payment stats error: ' . $e->getMessage());
            return $this->errorResponse($response, '获取企业用户支付统计失败', 500);
        }
    }

    /**
     * 管理AI提供商配置
     */
    public function manageAiProviders(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            if (!$this->isAdmin($request)) {
                return $this->errorResponse($response, '需要管理员权限', 403);
            }

            $method = $request->getMethod();
            
            switch ($method) {
                case 'GET':
                    return $this->getAiProviders($response);
                case 'POST':
                    return $this->createAiProvider($request, $response);
                case 'PUT':
                    return $this->updateAiProvider($request, $response);
                case 'DELETE':
                    return $this->deleteAiProvider($request, $response);
                default:
                    return $this->errorResponse($response, '不支持的方法', 405);
            }

        } catch (Exception $e) {
            $this->logger->error('Manage AI providers error: ' . $e->getMessage());
            return $this->errorResponse($response, 'AI提供商管理失败', 500);
        }
    }

    /**
     * 获取系统监控数据
     */
    public function getSystemMonitoring(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            if (!$this->isAdmin($request)) {
                return $this->errorResponse($response, '需要管理员权限', 403);
            }

            $monitoringData = [
                'system_health' => $this->getSystemHealth(),
                'api_performance' => $this->getApiPerformance(),
                'error_rates' => $this->getErrorRates(),
                'active_users' => $this->getActiveUsers(),
                'resource_usage' => $this->getResourceUsage()
            ];

            return $this->successResponse($response, $monitoringData);

        } catch (Exception $e) {
            $this->logger->error('System monitoring error: ' . $e->getMessage());
            return $this->errorResponse($response, '获取系统监控数据失败', 500);
        }
    }

    // ============== 私有辅助方法 ==============

    /**
     * 检查是否为管理员
     */
    private function isAdmin(ServerRequestInterface $request): bool
    {
        // 从请求中获取用户身份信息
        $authHeader = $request->getHeaderLine('Authorization');
        if (!$authHeader) {
            return false;
        }

        // 这里应该验证JWT令牌并检查用户角色
        // 暂时简化实现
        return str_contains($authHeader, 'admin');
    }

    /**
     * 获取企业概览数据
     */
    private function getEnterpriseOverview(): array
    {
        $db = $this->db->getConnection();
        
        return [
            'total_enterprise_users' => $db->table('users')->where('user_type', 'enterprise')->count(),
            'pending_applications' => $db->table('user_applications')->where('status', 'pending')->count(),
            'total_api_calls_today' => $db->table('api_usage_stats')
                ->whereDate('created_at', date('Y-m-d'))
                ->sum('tokens_used'),
            'revenue_this_month' => $db->table('wallet_transactions')
                ->where('transaction_type', 'recharge')
                ->where('status', 'success')
                ->whereMonth('created_at', date('m'))
                ->sum('amount')
        ];
    }

    /**
     * 获取待处理申请
     */
    private function getPendingApplications(): array
    {
        $db = $this->db->getConnection();
        
        return $db->table('user_applications as ua')
            ->join('users as u', 'ua.user_id', '=', 'u.id')
            ->select(['ua.*', 'u.username', 'u.email'])
            ->where('ua.status', 'pending')
            ->orderBy('ua.created_at', 'desc')
            ->limit(10)
            ->get()
            ->toArray();
    }

    /**
     * 获取API使用统计
     */
    private function getApiUsageStats(): array
    {
        $db = $this->db->getConnection();
        
        return [
            'today' => $db->table('api_usage_stats')
                ->whereDate('created_at', date('Y-m-d'))
                ->selectRaw('COUNT(*) as requests, SUM(tokens_used) as tokens, AVG(response_time) as avg_response_time')
                ->first(),
            'top_providers' => $db->table('api_usage_stats')
                ->selectRaw('ai_provider, COUNT(*) as requests, SUM(tokens_used) as tokens')
                ->whereDate('created_at', '>=', date('Y-m-d', strtotime('-7 days')))
                ->groupBy('ai_provider')
                ->orderByDesc('requests')
                ->limit(5)
                ->get()
        ];
    }

    /**
     * 获取支付概览
     */
    private function getPaymentOverview(): array
    {
        $db = $this->db->getConnection();
        
        return [
            'total_revenue' => $db->table('wallet_transactions')
                ->where('transaction_type', 'recharge')
                ->where('status', 'success')
                ->sum('amount'),
            'monthly_revenue' => $db->table('wallet_transactions')
                ->where('transaction_type', 'recharge')
                ->where('status', 'success')
                ->whereMonth('created_at', date('m'))
                ->sum('amount'),
            'total_transactions' => $db->table('wallet_transactions')->count(),
            'recent_transactions' => $db->table('wallet_transactions as wt')
                ->join('users as u', 'wt.user_id', '=', 'u.id')
                ->select(['wt.*', 'u.username'])
                ->orderBy('wt.created_at', 'desc')
                ->limit(10)
                ->get()
        ];
    }

    /**
     * 获取AI提供商状态
     */
    private function getAiProvidersStatus(): array
    {
        $db = $this->db->getConnection();
        
        return $db->table('ai_provider_configs')
            ->select(['provider_name', 'display_name', 'status', 'priority'])
            ->orderBy('priority', 'desc')
            ->get()
            ->toArray();
    }

    /**
     * 获取最近活动
     */
    private function getRecentActivities(): array
    {
        $db = $this->db->getConnection();
        
        return $db->table('user_logs')
            ->select(['action', 'description', 'ip_address', 'created_at'])
            ->whereIn('action', ['login', 'enterprise_application', 'quota_update', 'payment'])
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get()
            ->toArray();
    }

    /**
     * 获取支付统计数据
     */
    private function getPaymentStatistics(string $period, ?string $userId = null): array
    {
        $db = $this->db->getConnection();
        
        $periodMap = [
            '7d' => 7,
            '30d' => 30,
            '90d' => 90
        ];
        
        $days = $periodMap[$period] ?? 30;
        
        $query = $db->table('wallet_transactions')
            ->where('created_at', '>=', date('Y-m-d', strtotime("-{$days} days")));
            
        if ($userId) {
            $query->where('user_id', $userId);
        }
        
        return [
            'total_amount' => $query->sum('amount'),
            'transaction_count' => $query->count(),
            'avg_amount' => $query->avg('amount'),
            'by_method' => $query->selectRaw('payment_method, COUNT(*) as count, SUM(amount) as total')
                ->groupBy('payment_method')
                ->get(),
            'daily_trend' => $query->selectRaw('DATE(created_at) as date, COUNT(*) as count, SUM(amount) as total')
                ->groupBy('date')
                ->orderBy('date')
                ->get()
        ];
    }

    /**
     * 获取AI提供商列表
     */
    private function getAiProviders(ResponseInterface $response): ResponseInterface
    {
        $db = $this->db->getConnection();
        $providers = $db->table('ai_provider_configs')->get();
        
        return $this->successResponse($response, $providers);
    }

    /**
     * 创建AI提供商
     */
    private function createAiProvider(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $data = $this->getJsonData($request);
        
        // 验证必要字段
        if (!isset($data['provider_name']) || !isset($data['display_name']) || !isset($data['api_base_url'])) {
            return $this->errorResponse($response, '缺少必要参数', 400);
        }
        
        $db = $this->db->getConnection();
        $id = $db->table('ai_provider_configs')->insertGetId($data);
        
        return $this->successResponse($response, ['id' => $id, 'message' => 'AI提供商创建成功']);
    }

    /**
     * 更新AI提供商
     */
    private function updateAiProvider(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $data = $this->getJsonData($request);
        $providerId = $data['id'] ?? null;
        
        if (!$providerId) {
            return $this->errorResponse($response, '缺少提供商ID', 400);
        }
        
        unset($data['id']);
        
        $db = $this->db->getConnection();
        $updated = $db->table('ai_provider_configs')
            ->where('id', $providerId)
            ->update($data);
            
        if ($updated) {
            return $this->successResponse($response, ['message' => 'AI提供商更新成功']);
        }
        
        return $this->errorResponse($response, 'AI提供商更新失败', 500);
    }

    /**
     * 删除AI提供商
     */
    private function deleteAiProvider(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $data = $this->getJsonData($request);
        $providerId = $data['id'] ?? null;
        
        if (!$providerId) {
            return $this->errorResponse($response, '缺少提供商ID', 400);
        }
        
        $db = $this->db->getConnection();
        $deleted = $db->table('ai_provider_configs')
            ->where('id', $providerId)
            ->delete();
            
        if ($deleted) {
            return $this->successResponse($response, ['message' => 'AI提供商删除成功']);
        }
        
        return $this->errorResponse($response, 'AI提供商删除失败', 500);
    }

    /**
     * 获取系统健康状态
     */
    private function getSystemHealth(): array
    {
        return [
            'database' => $this->checkDatabaseHealth(),
            'cache' => $this->checkCacheHealth(),
            'storage' => $this->checkStorageHealth(),
            'memory' => $this->checkMemoryHealth()
        ];
    }

    /**
     * 获取API性能指标
     */
    private function getApiPerformance(): array
    {
        $db = $this->db->getConnection();
        
        return [
            'avg_response_time' => $db->table('api_usage_stats')
                ->whereDate('created_at', date('Y-m-d'))
                ->avg('response_time'),
            'requests_per_minute' => $db->table('api_usage_stats')
                ->where('created_at', '>=', date('Y-m-d H:i:00', strtotime('-1 minute')))
                ->count(),
            'error_rate' => $db->table('api_usage_stats')
                ->whereDate('created_at', date('Y-m-d'))
                ->where('status_code', '>=', 400)
                ->count() / max(1, $db->table('api_usage_stats')->whereDate('created_at', date('Y-m-d'))->count()) * 100
        ];
    }

    /**
     * 获取错误率统计
     */
    private function getErrorRates(): array
    {
        $db = $this->db->getConnection();
        
        return $db->table('api_usage_stats')
            ->selectRaw('
                DATE(created_at) as date,
                COUNT(*) as total_requests,
                COUNT(CASE WHEN status_code >= 400 THEN 1 END) as error_requests,
                (COUNT(CASE WHEN status_code >= 400 THEN 1 END) * 100.0 / COUNT(*)) as error_rate
            ')
            ->where('created_at', '>=', date('Y-m-d', strtotime('-7 days')))
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    /**
     * 获取活跃用户统计
     */
    private function getActiveUsers(): array
    {
        $db = $this->db->getConnection();
        
        return [
            'online' => $db->table('users')
                ->where('last_login_at', '>=', date('Y-m-d H:i:s', strtotime('-15 minutes')))
                ->count(),
            'today' => $db->table('users')
                ->whereDate('last_login_at', date('Y-m-d'))
                ->count(),
            'this_week' => $db->table('users')
                ->where('last_login_at', '>=', date('Y-m-d', strtotime('-7 days')))
                ->count()
        ];
    }

    /**
     * 获取资源使用情况
     */
    private function getResourceUsage(): array
    {
        return [
            'memory_usage' => memory_get_usage(true),
            'memory_peak' => memory_get_peak_usage(true),
            'disk_free' => disk_free_space('/'),
            'disk_total' => disk_total_space('/'),
            'load_average' => sys_getloadavg()[0] ?? 0
        ];
    }

    // 健康检查辅助方法
    private function checkDatabaseHealth(): array
    {
        try {
            $start = microtime(true);
            $this->db->getConnection()->table('users')->limit(1)->get();
            $responseTime = (microtime(true) - $start) * 1000;
            
            return [
                'status' => 'healthy',
                'response_time' => round($responseTime, 2)
            ];
        } catch (Exception $e) {
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
            $this->cache->set('health_check', 'test', 10);
            $result = $this->cache->get('health_check');
            $responseTime = (microtime(true) - $start) * 1000;
            
            return [
                'status' => $result === 'test' ? 'healthy' : 'degraded',
                'response_time' => round($responseTime, 2)
            ];
        } catch (Exception $e) {
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
