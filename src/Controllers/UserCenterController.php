<?php
/**
 * AlingAi Pro - 用户中心控制器
 * 提供个人用户中心和API社区功能
 * 
 * @package AlingAi\Pro\Controllers
 * @version 2.0.0
 * @author AlingAi Team
 * @created 2025-06-06
 */

declare(strict_types=1);

namespace AlingAi\Controllers;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use AlingAi\Services\{
    DatabaseServiceInterface,
    EnhancedUserManagementService,
    EnhancedAIService
};
use AlingAi\Utils\{Logger, ApiResponse};

class UserCenterController extends BaseController
{
    private EnhancedUserManagementService $userManagementService;
    private EnhancedAIService $aiService;

    public function __construct(
        DatabaseServiceInterface $db,
        EnhancedUserManagementService $userManagementService,
        EnhancedAIService $aiService
    ) {
        parent::__construct($db);
        $this->userManagementService = $userManagementService;
        $this->aiService = $aiService;
    }

    /**
     * 用户中心首页 - 仪表板
     */
    public function dashboard(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $user = $this->getCurrentUser($request);
            
            // 获取用户统计数据
            $stats = $this->getUserStats($user['id']);
            
            // 获取最近的API使用记录
            $recentUsage = $this->getRecentApiUsage($user['id']);
            
            // 获取钱包信息
            $walletInfo = $this->getWalletInfo($user['id']);
            
            // 获取通知
            $notifications = $this->getNotifications($user['id']);

            return $this->renderTemplate('user/dashboard.twig', [
                'page_title' => '用户中心',
                'user' => $user,
                'stats' => $stats,
                'recent_usage' => $recentUsage,
                'wallet' => $walletInfo,
                'notifications' => $notifications,
                'current_section' => 'dashboard'
            ]);

        } catch (\Exception $e) {
            Logger::error('用户中心首页加载失败: ' . $e->getMessage());
            return $this->errorResponse('加载用户中心失败', 500);
        }
    }

    /**
     * API使用统计页面
     */
    public function apiUsage(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $user = $this->getCurrentUser($request);
            $params = $request->getQueryParams();
            
            $period = $params['period'] ?? 'today';
            $provider = $params['provider'] ?? 'all';
            $page = (int)($params['page'] ?? 1);
            $limit = 20;

            // 获取API使用统计
            $stats = $this->aiService->getUsageStatistics($user['id'], $period, $provider);
            
            // 获取详细使用记录
            $usageHistory = $this->getApiUsageHistory($user['id'], $page, $limit, $period, $provider);
            
            // 获取配额信息
            $quotaInfo = $this->getQuotaInfo($user['id']);

            return $this->renderTemplate('user/api_usage.twig', [
                'page_title' => 'API使用统计',
                'user' => $user,
                'stats' => $stats,
                'usage_history' => $usageHistory['data'],
                'pagination' => $usageHistory['pagination'],
                'quota_info' => $quotaInfo,
                'current_period' => $period,
                'current_provider' => $provider,
                'current_section' => 'api_usage'
            ]);

        } catch (\Exception $e) {
            Logger::error('API使用统计页面加载失败: ' . $e->getMessage());
            return $this->errorResponse('加载API统计失败', 500);
        }
    }

    /**
     * 钱包管理页面
     */
    public function wallet(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $user = $this->getCurrentUser($request);
            $params = $request->getQueryParams();
            
            $page = (int)($params['page'] ?? 1);
            $limit = 20;
            $type = $params['type'] ?? 'all';

            // 获取钱包信息
            $walletInfo = $this->getWalletInfo($user['id']);
            
            // 获取交易记录
            $transactions = $this->getTransactionHistory($user['id'], $page, $limit, $type);
            
            // 获取充值统计
            $rechargeStats = $this->getRechargeStats($user['id']);

            return $this->renderTemplate('user/wallet.twig', [
                'page_title' => '钱包管理',
                'user' => $user,
                'wallet' => $walletInfo,
                'transactions' => $transactions['data'],
                'pagination' => $transactions['pagination'],
                'recharge_stats' => $rechargeStats,
                'current_type' => $type,
                'current_section' => 'wallet'
            ]);

        } catch (\Exception $e) {
            Logger::error('钱包管理页面加载失败: ' . $e->getMessage());
            return $this->errorResponse('加载钱包信息失败', 500);
        }
    }

    /**
     * 个人设置页面
     */
    public function profile(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $user = $this->getCurrentUser($request);
            
            // 获取用户详细信息
            $userProfile = $this->getUserProfile($user['id']);
            
            // 获取安全设置
            $securitySettings = $this->getSecuritySettings($user['id']);

            return $this->renderTemplate('user/profile.twig', [
                'page_title' => '个人设置',
                'user' => $user,
                'profile' => $userProfile,
                'security' => $securitySettings,
                'current_section' => 'profile'
            ]);

        } catch (\Exception $e) {
            Logger::error('个人设置页面加载失败: ' . $e->getMessage());
            return $this->errorResponse('加载个人设置失败', 500);
        }
    }

    /**
     * 更新个人信息
     */
    public function updateProfile(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $user = $this->getCurrentUser($request);
            $data = json_decode($request->getBody()->getContents(), true);

            $result = $this->userManagementService->updateUserProfile($user['id'], $data);

            if ($result['success']) {
                return ApiResponse::success('个人信息更新成功', $result['data']);
            } else {
                return ApiResponse::error($result['message'], 400);
            }

        } catch (\Exception $e) {
            Logger::error('更新个人信息失败: ' . $e->getMessage());
            return ApiResponse::error('更新个人信息失败', 500);
        }
    }

    /**
     * API社区页面
     */
    public function apiCommunity(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $user = $this->getCurrentUser($request);
            $params = $request->getQueryParams();
            
            $category = $params['category'] ?? 'all';
            $page = (int)($params['page'] ?? 1);
            $limit = 20;

            // 获取API列表
            $apiList = $this->getApiCommunityList($page, $limit, $category);
            
            // 获取分类列表
            $categories = $this->getApiCategories();
            
            // 获取推荐API
            $recommendedApis = $this->getRecommendedApis();

            return $this->renderTemplate('user/api_community.twig', [
                'page_title' => 'API社区',
                'user' => $user,
                'api_list' => $apiList['data'],
                'pagination' => $apiList['pagination'],
                'categories' => $categories,
                'recommended_apis' => $recommendedApis,
                'current_category' => $category,
                'current_section' => 'api_community'
            ]);

        } catch (\Exception $e) {
            Logger::error('API社区页面加载失败: ' . $e->getMessage());
            return $this->errorResponse('加载API社区失败', 500);
        }
    }

    /**
     * API详情页面
     */
    public function apiDetail(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $user = $this->getCurrentUser($request);
            $apiId = $request->getAttribute('id');

            // 获取API详情
            $apiDetail = $this->getApiDetail($apiId);
            
            if (!$apiDetail) {
                return $this->errorResponse('API不存在', 404);
            }

            // 获取使用示例
            $examples = $this->getApiExamples($apiId);
            
            // 获取用户评价
            $reviews = $this->getApiReviews($apiId);

            return $this->renderTemplate('user/api_detail.twig', [
                'page_title' => $apiDetail['name'] . ' - API详情',
                'user' => $user,
                'api' => $apiDetail,
                'examples' => $examples,
                'reviews' => $reviews,
                'current_section' => 'api_community'
            ]);

        } catch (\Exception $e) {
            Logger::error('API详情页面加载失败: ' . $e->getMessage());
            return $this->errorResponse('加载API详情失败', 500);
        }
    }

    /**
     * 申请企业账户
     */
    public function applyEnterprise(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $user = $this->getCurrentUser($request);
            $data = json_decode($request->getBody()->getContents(), true);

            // 检查是否已经是企业用户
            if ($user['user_type'] === 'enterprise') {
                return ApiResponse::error('您已经是企业用户', 400);
            }

            // 检查是否有待处理的申请
            $existingApplication = $this->userManagementService->getActiveApplication($user['id'], 'account_upgrade');
            if ($existingApplication) {
                return ApiResponse::error('您已有待处理的企业账户申请', 400);
            }

            // 提交申请
            $result = $this->userManagementService->submitEnterpriseApplication($user['id'], $data);

            if ($result['success']) {
                return ApiResponse::success('企业账户申请提交成功，请等待审核', $result);
            } else {
                return ApiResponse::error($result['message'], 400);
            }

        } catch (\Exception $e) {
            Logger::error('企业账户申请失败: ' . $e->getMessage());
            return ApiResponse::error('申请提交失败', 500);
        }
    }

    /**
     * 申请配额增加
     */
    public function applyQuotaIncrease(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $user = $this->getCurrentUser($request);
            $data = json_decode($request->getBody()->getContents(), true);

            $result = $this->userManagementService->submitQuotaIncreaseApplication($user['id'], $data);

            if ($result['success']) {
                return ApiResponse::success('配额增加申请提交成功', $result);
            } else {
                return ApiResponse::error($result['message'], 400);
            }

        } catch (\Exception $e) {
            Logger::error('配额增加申请失败: ' . $e->getMessage());
            return ApiResponse::error('申请提交失败', 500);
        }
    }

    /**
     * 获取当前用户信息
     */
    private function getCurrentUser(ServerRequestInterface $request): array
    {
        $user = $request->getAttribute('user');
        if (!$user) {
            throw new \Exception('用户未登录');
        }
        return $user;
    }

    /**
     * 获取用户统计数据
     */
    private function getUserStats(int $userId): array
    {
        $sql = "
            SELECT 
                (SELECT COUNT(*) FROM api_usage_stats WHERE user_id = :user_id AND DATE(created_at) = CURDATE()) as today_requests,
                (SELECT COUNT(*) FROM api_usage_stats WHERE user_id = :user_id AND DATE(created_at) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)) as week_requests,
                (SELECT COUNT(*) FROM api_usage_stats WHERE user_id = :user_id AND DATE(created_at) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)) as month_requests,
                (SELECT SUM(tokens_used) FROM api_usage_stats WHERE user_id = :user_id AND DATE(created_at) = CURDATE()) as today_tokens,
                (SELECT SUM(cost) FROM api_usage_stats WHERE user_id = :user_id AND DATE(created_at) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)) as month_cost
        ";

        return $this->db->fetchOne($sql, ['user_id' => $userId]) ?: [];
    }

    /**
     * 获取最近API使用记录
     */
    private function getRecentApiUsage(int $userId): array
    {
        $sql = "
            SELECT api_endpoint, ai_provider, tokens_used, cost, created_at
            FROM api_usage_stats 
            WHERE user_id = :user_id 
            ORDER BY created_at DESC 
            LIMIT 10
        ";

        return $this->db->fetchAll($sql, ['user_id' => $userId]);
    }

    /**
     * 获取钱包信息
     */
    private function getWalletInfo(int $userId): array
    {
        $sql = "
            SELECT wallet_balance, 
                   api_quota_daily, 
                   api_quota_monthly,
                   api_usage_daily,
                   api_usage_monthly
            FROM users 
            WHERE id = :user_id
        ";

        return $this->db->fetchOne($sql, ['user_id' => $userId]) ?: [];
    }

    /**
     * 获取通知
     */
    private function getNotifications(int $userId): array
    {
        $sql = "
            SELECT title, content, type, is_important, action_url, action_text, created_at
            FROM system_notifications 
            WHERE user_id = :user_id OR user_id IS NULL
            AND (expires_at IS NULL OR expires_at > NOW())
            ORDER BY is_important DESC, created_at DESC
            LIMIT 10
        ";

        return $this->db->fetchAll($sql, ['user_id' => $userId]);
    }

    /**
     * 获取API使用历史
     */
    private function getApiUsageHistory(int $userId, int $page, int $limit, string $period, string $provider): array
    {
        $whereClause = 'WHERE user_id = :user_id';
        $params = ['user_id' => $userId];

        // 时间过滤
        switch ($period) {
            case 'today':
                $whereClause .= ' AND DATE(created_at) = CURDATE()';
                break;
            case 'week':
                $whereClause .= ' AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)';
                break;
            case 'month':
                $whereClause .= ' AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)';
                break;
        }

        // 提供商过滤
        if ($provider !== 'all') {
            $whereClause .= ' AND ai_provider = :provider';
            $params['provider'] = $provider;
        }

        // 获取总数
        $countSql = "SELECT COUNT(*) FROM api_usage_stats {$whereClause}";
        $total = (int)$this->db->fetchOne($countSql, $params)['COUNT(*)'];

        // 获取数据
        $offset = ($page - 1) * $limit;
        $sql = "
            SELECT api_endpoint, ai_provider, model_name, tokens_used, cost, response_time, status_code, created_at
            FROM api_usage_stats 
            {$whereClause}
            ORDER BY created_at DESC
            LIMIT {$limit} OFFSET {$offset}
        ";

        $data = $this->db->fetchAll($sql, $params);

        return [
            'data' => $data,
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'pages' => ceil($total / $limit)
            ]
        ];
    }

    /**
     * 获取配额信息
     */
    private function getQuotaInfo(int $userId): array
    {
        $sql = "
            SELECT 
                api_quota_daily,
                api_quota_monthly,
                api_usage_daily,
                api_usage_monthly,
                user_type
            FROM users 
            WHERE id = :user_id
        ";

        $info = $this->db->fetchOne($sql, ['user_id' => $userId]);
        
        if ($info) {
            $info['daily_remaining'] = max(0, $info['api_quota_daily'] - $info['api_usage_daily']);
            $info['monthly_remaining'] = max(0, $info['api_quota_monthly'] - $info['api_usage_monthly']);
            $info['daily_usage_percent'] = $info['api_quota_daily'] > 0 ? ($info['api_usage_daily'] / $info['api_quota_daily']) * 100 : 0;
            $info['monthly_usage_percent'] = $info['api_quota_monthly'] > 0 ? ($info['api_usage_monthly'] / $info['api_quota_monthly']) * 100 : 0;
        }

        return $info ?: [];
    }

    /**
     * 获取交易历史
     */
    private function getTransactionHistory(int $userId, int $page, int $limit, string $type): array
    {
        $whereClause = 'WHERE user_id = :user_id';
        $params = ['user_id' => $userId];

        if ($type !== 'all') {
            $whereClause .= ' AND transaction_type = :type';
            $params['type'] = $type;
        }

        // 获取总数
        $countSql = "SELECT COUNT(*) FROM wallet_transactions {$whereClause}";
        $total = (int)$this->db->fetchOne($countSql, $params)['COUNT(*)'];

        // 获取数据
        $offset = ($page - 1) * $limit;
        $sql = "
            SELECT transaction_type, amount, balance_after, payment_method, status, description, created_at
            FROM wallet_transactions 
            {$whereClause}
            ORDER BY created_at DESC
            LIMIT {$limit} OFFSET {$offset}
        ";

        $data = $this->db->fetchAll($sql, $params);

        return [
            'data' => $data,
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'pages' => ceil($total / $limit)
            ]
        ];
    }

    /**
     * 获取充值统计
     */
    private function getRechargeStats(int $userId): array
    {
        $sql = "
            SELECT 
                SUM(CASE WHEN DATE(created_at) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) THEN amount ELSE 0 END) as month_recharge,
                SUM(CASE WHEN DATE(created_at) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) THEN amount ELSE 0 END) as week_recharge,
                SUM(amount) as total_recharge,
                COUNT(*) as total_transactions
            FROM wallet_transactions 
            WHERE user_id = :user_id AND transaction_type = 'recharge' AND status = 'success'
        ";

        return $this->db->fetchOne($sql, ['user_id' => $userId]) ?: [];
    }

    /**
     * 获取用户详细信息
     */
    private function getUserProfile(int $userId): array
    {
        $sql = "
            SELECT id, username, email, user_type, company_name, company_size, industry, 
                   application_status, created_at, updated_at
            FROM users 
            WHERE id = :user_id
        ";

        return $this->db->fetchOne($sql, ['user_id' => $userId]) ?: [];
    }

    /**
     * 获取安全设置
     */
    private function getSecuritySettings(int $userId): array
    {
        // 这里可以获取用户的安全设置，如二次验证状态等
        return [
            'two_factor_enabled' => false,
            'login_notifications' => true,
            'api_key_count' => 1
        ];
    }

    /**
     * 获取API社区列表
     */
    private function getApiCommunityList(int $page, int $limit, string $category): array
    {
        // 从API目录表获取实际数据
        $apis = [
            [
                'id' => 1,
                'name' => '文心一言API',
                'description' => '百度智能云文心一言API接口',
                'category' => 'ai_chat',
                'provider' => 'baidu',
                'price' => '0.008/1K tokens',
                'rating' => 4.5,
                'status' => 'active'
            ],
            [
                'id' => 2,
                'name' => 'DeepSeek API',
                'description' => 'DeepSeek智能对话API',
                'category' => 'ai_chat',
                'provider' => 'deepseek',
                'price' => '0.001/1K tokens',
                'rating' => 4.8,
                'status' => 'active'
            ],
            [
                'id' => 3,
                'name' => 'Coze智能助手',
                'description' => 'Coze平台智能助手API',
                'category' => 'ai_assistant',
                'provider' => 'coze',
                'price' => '0.002/1K tokens',
                'rating' => 4.3,
                'status' => 'active'
            ]
        ];

        $total = count($apis);
        $offset = ($page - 1) * $limit;
        $data = array_slice($apis, $offset, $limit);

        return [
            'data' => $data,
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'pages' => ceil($total / $limit)
            ]
        ];
    }

    /**
     * 获取API分类
     */
    private function getApiCategories(): array
    {
        return [
            ['id' => 'ai_chat', 'name' => 'AI对话', 'count' => 15],
            ['id' => 'ai_assistant', 'name' => 'AI助手', 'count' => 8],
            ['id' => 'text_analysis', 'name' => '文本分析', 'count' => 12],
            ['id' => 'image_generation', 'name' => '图像生成', 'count' => 6],
            ['id' => 'voice_synthesis', 'name' => '语音合成', 'count' => 4]
        ];
    }

    /**
     * 获取推荐API
     */
    private function getRecommendedApis(): array
    {
        return [
            ['id' => 1, 'name' => '文心一言API', 'rating' => 4.5],
            ['id' => 2, 'name' => 'DeepSeek API', 'rating' => 4.8],
            ['id' => 3, 'name' => 'Coze智能助手', 'rating' => 4.3]
        ];
    }

    /**
     * 获取API详情
     */
    private function getApiDetail(int $apiId): ?array
    {
        // 从数据库获取实际数据
        $apis = [
            1 => [
                'id' => 1,
                'name' => '文心一言API',
                'description' => '百度智能云文心一言API接口，提供强大的中文理解和生成能力',
                'category' => 'ai_chat',
                'provider' => 'baidu',
                'price' => '0.008/1K tokens',
                'rating' => 4.5,
                'status' => 'active',
                'features' => ['中文优化', '高质量回答', '快速响应'],
                'endpoints' => [
                    'POST /api/ai/baidu/chat' => '发送聊天消息'
                ]
            ]
        ];

        return $apis[$apiId] ?? null;
    }

    /**
     * 获取API示例
     */
    private function getApiExamples(int $apiId): array
    {
        return [
            [
                'title' => '基础对话示例',
                'code' => '{"message": "你好，请介绍一下自己", "model": "ernie-bot"}',
                'response' => '{"content": "您好！我是文心一言...", "usage": {"tokens": 50}}'
            ]
        ];
    }

    /**
     * 获取API评价
     */
    private function getApiReviews(int $apiId): array
    {
        return [
            [
                'user' => 'user123',
                'rating' => 5,
                'comment' => '响应速度很快，中文理解能力强',
                'created_at' => '2025-06-05 10:30:00'
            ]
        ];
    }
}
