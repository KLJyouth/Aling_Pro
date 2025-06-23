<?php

declare(strict_types=1);

namespace AlingAi\Services;

use AlingAi\Services\DatabaseServiceInterface;
use AlingAi\Services\CacheService;
use AlingAi\Services\EmailService;
use AlingAi\Utils\Logger;
use Exception;

/**
 * 增强用户管理服务
 * 支持企业用户、API配额管理、申请审核等高级功能
 */
class EnhancedUserManagementService
{
    private DatabaseServiceInterface $db;
    private CacheService $cache;
    private EmailService $emailService;
    private Logger $logger;
    
    // 用户类型常量
    public const USER_TYPE_PERSONAL = 'personal';
    public const USER_TYPE_ENTERPRISE = 'enterprise';
    
    // 申请状态常量
    public const APPLICATION_STATUS_PENDING = 'pending';
    public const APPLICATION_STATUS_APPROVED = 'approved';
    public const APPLICATION_STATUS_REJECTED = 'rejected';
    public const APPLICATION_STATUS_UNDER_REVIEW = 'under_review';
      // 申请类型常量
    public const APPLICATION_TYPE_ACCOUNT_UPGRADE = 'account_upgrade';
    public const APPLICATION_TYPE_QUOTA_INCREASE = 'quota_increase';
    public const APPLICATION_TYPE_ENTERPRISE_ACCESS = 'enterprise_access';
    public const APPLICATION_TYPE_QUOTA_UPGRADE = 'quota_upgrade';
    public const APPLICATION_TYPE_SPECIAL_PERMISSION = 'special_permission';
    
    public function __construct(
        DatabaseServiceInterface $db,
        CacheService $cache,
        EmailService $emailService,
        Logger $logger
    ) {
        $this->db = $db;
        $this->cache = $cache;
        $this->emailService = $emailService;
        $this->logger = $logger;
    }
    
    /**
     * 创建企业用户申请
     */
    public function createEnterpriseApplication(array $data): array
    {
        try {
            // 验证必需字段
            $required = ['user_id', 'company_name', 'company_size', 'industry', 'reason'];
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    throw new Exception("缺少必需字段: {$field}");
                }
            }
            
            // 检查用户是否存在
            $user = $this->getUserById($data['user_id']);
            if (!$user) {
                throw new Exception('用户不存在');
            }
            
            // 检查是否已有待处理的申请
            $existingApplication = $this->db->select(
                'user_applications',
                ['id'],
                [
                    'user_id' => $data['user_id'],
                    'application_type' => self::APPLICATION_TYPE_ENTERPRISE_ACCESS,
                    'status' => [self::APPLICATION_STATUS_PENDING, self::APPLICATION_STATUS_UNDER_REVIEW]
                ]
            );
            
            if ($existingApplication) {
                throw new Exception('您已有待处理的企业用户申请');
            }
            
            // 创建申请记录
            $applicationData = [
                'user_id' => $data['user_id'],
                'application_type' => self::APPLICATION_TYPE_ENTERPRISE_ACCESS,
                'requested_data' => json_encode([
                    'user_type' => self::USER_TYPE_ENTERPRISE,
                    'company_name' => $data['company_name'],
                    'company_size' => $data['company_size'],
                    'industry' => $data['industry'],
                    'api_quota_daily' => $data['api_quota_daily'] ?? 1000,
                    'api_quota_monthly' => $data['api_quota_monthly'] ?? 30000
                ]),
                'reason' => $data['reason'],
                'attachments' => isset($data['attachments']) ? json_encode($data['attachments']) : null,
                'status' => self::APPLICATION_STATUS_PENDING
            ];
              $applicationId = $this->db->insert('user_applications', $applicationData);
            
            if (!$applicationId) {
                throw new Exception('Failed to create application record');
            }
            
            // 确保 applicationId 是整数
            $applicationId = (int)$applicationId;
            
            // 发送申请确认邮件
            $this->sendApplicationConfirmationEmail($user, $applicationId);
            
            // 通知管理员
            $this->notifyAdminNewApplication($applicationId, $user);
            
            // 记录日志
            $this->logger->info('Enterprise application created', [
                'user_id' => $data['user_id'],
                'application_id' => $applicationId,
                'company_name' => $data['company_name']
            ]);
            
            return [
                'success' => true,
                'application_id' => $applicationId,
                'message' => '企业用户申请已提交，我们将在3个工作日内处理'
            ];
            
        } catch (Exception $e) {
            $this->logger->error('Failed to create enterprise application', [
                'error' => $e->getMessage(),
                'data' => $data
            ]);
            throw $e;
        }
    }
    
    /**
     * 审核用户申请
     */
    public function reviewApplication(int $applicationId, string $action, array $reviewData): array
    {
        try {
            // 验证操作
            if (!in_array($action, ['approve', 'reject', 'under_review'])) {
                throw new Exception('无效的审核操作');
            }
            
            // 获取申请信息
            $application = $this->db->select('user_applications', ['*'], ['id' => $applicationId]);
            if (!$application) {
                throw new Exception('申请不存在');
            }
            
            // 获取用户信息
            $user = $this->getUserById($application['user_id']);
            if (!$user) {
                throw new Exception('用户不存在');
            }
              $status = '';
            switch($action) {
                case 'approve':
                    $status = self::APPLICATION_STATUS_APPROVED;
                    break;
                case 'reject':
                    $status = self::APPLICATION_STATUS_REJECTED;
                    break;
                case 'under_review':
                    $status = self::APPLICATION_STATUS_UNDER_REVIEW;
                    break;
                default:
                    throw new Exception('无效的操作');
            }
            
            // 更新申请状态
            $updateData = [
                'status' => $status,
                'reviewer_id' => $reviewData['reviewer_id'],
                'review_notes' => $reviewData['notes'] ?? null,
                'reviewed_at' => date('Y-m-d H:i:s')
            ];
            
            $this->db->update('user_applications', $updateData, ['id' => $applicationId]);
            
            // 如果批准，更新用户信息
            if ($action === 'approve') {
                $requestedData = json_decode($application['requested_data'], true);
                $this->applyApplicationChanges($user['id'], $requestedData);
            }
            
            // 发送审核结果邮件
            $this->sendReviewResultEmail($user, $application, $action, $reviewData['notes'] ?? '');
            
            // 创建系统通知
            $this->createSystemNotification([
                'user_id' => $user['id'],
                'title' => $this->getApplicationTitle($application['application_type']),
                'content' => $this->getReviewResultMessage($action, $reviewData['notes'] ?? ''),
                'type' => $action === 'approve' ? 'success' : ($action === 'reject' ? 'error' : 'info')
            ]);
            
            $this->logger->info('Application reviewed', [
                'application_id' => $applicationId,
                'action' => $action,
                'reviewer_id' => $reviewData['reviewer_id']
            ]);
            
            return [
                'success' => true,
                'message' => '申请审核完成'
            ];
            
        } catch (Exception $e) {
            $this->logger->error('Failed to review application', [
                'error' => $e->getMessage(),
                'application_id' => $applicationId,
                'action' => $action
            ]);
            throw $e;
        }
    }
    
    /**
     * 获取用户API使用统计
     */
    public function getUserApiStats(int $userId, string $period = 'daily'): array
    {
        try {
            $user = $this->getUserById($userId);
            if (!$user) {
                throw new Exception('用户不存在');
            }
            
            // 缓存键
            $cacheKey = "api_stats:{$userId}:{$period}";
            $cached = $this->cache->get($cacheKey);
            if ($cached) {
                return json_decode($cached, true);
            }
            
            // 根据周期计算时间范围
            $timeRange = $this->getTimeRange($period);
            
            // 获取使用统计
            $stats = $this->db->query(
                "SELECT 
                    COUNT(*) as total_requests,
                    SUM(tokens_used) as total_tokens,
                    SUM(cost) as total_cost,
                    AVG(response_time) as avg_response_time,
                    ai_provider,
                    model_name,
                    DATE(created_at) as usage_date
                FROM api_usage_stats 
                WHERE user_id = ? AND created_at >= ? AND created_at <= ?
                GROUP BY ai_provider, model_name, DATE(created_at)
                ORDER BY created_at DESC",
                [$userId, $timeRange['start'], $timeRange['end']]
            );
            
            // 获取配额信息
            $quotaInfo = [
                'daily_quota' => (int)$user['api_quota_daily'],
                'monthly_quota' => (int)$user['api_quota_monthly'],
                'daily_usage' => (int)$user['api_usage_daily'],
                'monthly_usage' => (int)$user['api_usage_monthly'],
                'daily_remaining' => max(0, (int)$user['api_quota_daily'] - (int)$user['api_usage_daily']),
                'monthly_remaining' => max(0, (int)$user['api_quota_monthly'] - (int)$user['api_usage_monthly'])
            ];
            
            $result = [
                'stats' => $stats,
                'quota' => $quotaInfo,
                'period' => $period,
                'time_range' => $timeRange
            ];
            
            // 缓存结果5分钟
            $this->cache->set($cacheKey, json_encode($result), 300);
            
            return $result;
            
        } catch (Exception $e) {
            $this->logger->error('Failed to get user API stats', [
                'error' => $e->getMessage(),
                'user_id' => $userId,
                'period' => $period
            ]);
            throw $e;
        }
    }
    
    /**
     * 检查API配额限制
     */
    public function checkApiQuota(int $userId, int $requestedTokens = 1): array
    {
        try {
            $user = $this->getUserById($userId);
            if (!$user) {
                throw new Exception('用户不存在');
            }
            
            $dailyUsage = (int)$user['api_usage_daily'];
            $monthlyUsage = (int)$user['api_usage_monthly'];
            $dailyQuota = (int)$user['api_quota_daily'];
            $monthlyQuota = (int)$user['api_quota_monthly'];
            
            // 检查每日配额
            $dailyRemaining = max(0, $dailyQuota - $dailyUsage);
            $monthlyRemaining = max(0, $monthlyQuota - $monthlyUsage);
            
            $canProceed = $dailyRemaining >= $requestedTokens && $monthlyRemaining >= $requestedTokens;
            
            return [
                'can_proceed' => $canProceed,
                'daily_usage' => $dailyUsage,
                'daily_quota' => $dailyQuota,
                'daily_remaining' => $dailyRemaining,
                'monthly_usage' => $monthlyUsage,
                'monthly_quota' => $monthlyQuota,
                'monthly_remaining' => $monthlyRemaining,
                'requested_tokens' => $requestedTokens
            ];
            
        } catch (Exception $e) {
            $this->logger->error('Failed to check API quota', [
                'error' => $e->getMessage(),
                'user_id' => $userId,
                'requested_tokens' => $requestedTokens
            ]);
            throw $e;
        }
    }
    
    /**
     * 更新API使用量
     */
    public function updateApiUsage(int $userId, int $tokensUsed, array $usageData): bool
    {
        try {
            // 更新用户的使用量计数器
            $this->db->query(
                "UPDATE users SET 
                    api_usage_daily = api_usage_daily + ?,
                    api_usage_monthly = api_usage_monthly + ?
                WHERE id = ?",
                [$tokensUsed, $tokensUsed, $userId]
            );
            
            // 记录详细使用统计
            $statData = array_merge([
                'user_id' => $userId,
                'tokens_used' => $tokensUsed
            ], $usageData);
            
            $this->db->insert('api_usage_stats', $statData);
            
            // 清除缓存
            $this->cache->delete("api_stats:{$userId}:daily");
            $this->cache->delete("api_stats:{$userId}:monthly");
            
            return true;
            
        } catch (Exception $e) {
            $this->logger->error('Failed to update API usage', [
                'error' => $e->getMessage(),
                'user_id' => $userId,
                'tokens_used' => $tokensUsed
            ]);
            return false;
        }
    }
    
    /**
     * 重置API配额
     */
    public function resetApiQuota(int $userId, string $resetType, array $resetData = []): bool
    {
        try {
            $user = $this->getUserById($userId);
            if (!$user) {
                throw new Exception('用户不存在');
            }
            
            $updateData = [];
            $previousUsage = 0;
            $resetQuota = 0;
            
            switch ($resetType) {
                case 'daily':
                    $previousUsage = (int)$user['api_usage_daily'];
                    $updateData['api_usage_daily'] = 0;
                    $resetQuota = (int)$user['api_quota_daily'];
                    break;
                    
                case 'monthly':
                    $previousUsage = (int)$user['api_usage_monthly'];
                    $updateData['api_usage_monthly'] = 0;
                    $resetQuota = (int)$user['api_quota_monthly'];
                    break;
                    
                case 'manual':
                    if (isset($resetData['daily'])) {
                        $previousUsage += (int)$user['api_usage_daily'];
                        $updateData['api_usage_daily'] = 0;
                        $resetQuota += (int)$user['api_quota_daily'];
                    }
                    if (isset($resetData['monthly'])) {
                        $previousUsage += (int)$user['api_usage_monthly'];
                        $updateData['api_usage_monthly'] = 0;
                        $resetQuota += (int)$user['api_quota_monthly'];
                    }
                    break;
                    
                default:
                    throw new Exception('无效的重置类型');
            }
            
            // 更新用户数据
            if (!empty($updateData)) {
                $this->db->update('users', $updateData, ['id' => $userId]);
            }
            
            // 记录重置日志
            $this->db->insert('api_quota_resets', [
                'user_id' => $userId,
                'reset_type' => $resetType,
                'previous_usage' => $previousUsage,
                'reset_quota' => $resetQuota,
                'reset_reason' => $resetData['reason'] ?? null,
                'reset_by' => $resetData['reset_by'] ?? null
            ]);
            
            // 清除缓存
            $this->cache->delete("api_stats:{$userId}:daily");
            $this->cache->delete("api_stats:{$userId}:monthly");
            
            $this->logger->info('API quota reset', [
                'user_id' => $userId,
                'reset_type' => $resetType,
                'previous_usage' => $previousUsage
            ]);
            
            return true;
            
        } catch (Exception $e) {
            $this->logger->error('Failed to reset API quota', [
                'error' => $e->getMessage(),
                'user_id' => $userId,
                'reset_type' => $resetType
            ]);
            return false;
        }
    }
    
    /**
     * 生成防机器人验证码
     */
    public function generateAntiBotVerification(int $userId): array
    {
        try {
            // 生成6位数字验证码
            $code = str_pad((string)random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
            $expires = date('Y-m-d H:i:s', time() + 300); // 5分钟有效
            
            // 更新用户验证码
            $this->db->update('users', [
                'verification_code' => $code,
                'verification_expires' => $expires
            ], ['id' => $userId]);
            
            return [
                'success' => true,
                'code' => $code,
                'expires' => $expires,
                'message' => '验证码已生成，5分钟内有效'
            ];
            
        } catch (Exception $e) {
            $this->logger->error('Failed to generate anti-bot verification', [
                'error' => $e->getMessage(),
                'user_id' => $userId
            ]);
            throw $e;
        }
    }
    
    /**
     * 验证防机器人验证码
     */
    public function verifyAntiBotCode(int $userId, string $code): bool
    {
        try {
            $user = $this->getUserById($userId);
            if (!$user) {
                return false;
            }
            
            // 检查验证码
            if ($user['verification_code'] !== $code) {
                return false;
            }
            
            // 检查是否过期
            if (strtotime($user['verification_expires']) < time()) {
                return false;
            }
            
            // 标记为已验证
            $this->db->update('users', [
                'anti_bot_verified' => 1,
                'verification_code' => null,
                'verification_expires' => null
            ], ['id' => $userId]);
            
            $this->logger->info('Anti-bot verification successful', [
                'user_id' => $userId
            ]);
            
            return true;
            
        } catch (Exception $e) {
            $this->logger->error('Failed to verify anti-bot code', [
                'error' => $e->getMessage(),
                'user_id' => $userId,
                'code' => $code
            ]);
            return false;
        }
    }
    
    // 私有辅助方法
    
    private function getUserById(int $userId): ?array
    {
        return $this->db->select('users', ['*'], ['id' => $userId]);
    }
    
    private function getTimeRange(string $period): array
    {
        $now = new \DateTime();
        
        switch ($period) {
            case 'daily':
                return [
                    'start' => $now->format('Y-m-d 00:00:00'),
                    'end' => $now->format('Y-m-d 23:59:59')
                ];
                
            case 'weekly':
                $start = clone $now;
                $start->modify('monday this week');
                $end = clone $start;
                $end->modify('+6 days');
                return [
                    'start' => $start->format('Y-m-d 00:00:00'),
                    'end' => $end->format('Y-m-d 23:59:59')
                ];
                
            case 'monthly':
                return [
                    'start' => $now->format('Y-m-01 00:00:00'),
                    'end' => $now->format('Y-m-t 23:59:59')
                ];
                
            default:
                return [
                    'start' => $now->format('Y-m-d 00:00:00'),
                    'end' => $now->format('Y-m-d 23:59:59')
                ];
        }
    }
    
    private function applyApplicationChanges(int $userId, array $requestedData): bool
    {
        return $this->db->update('users', $requestedData, ['id' => $userId]);
    }
      private function sendApplicationConfirmationEmail(array $user, int $applicationId): void
    {
        // 发送申请确认邮件的实现
        $subject = '企业用户申请确认';
        $body = "亲爱的 {$user['username']}，\n\n您的企业用户申请已提交成功。\n申请编号：{$applicationId}\n\n我们将在3个工作日内审核您的申请，请耐心等待。";
        
        $this->emailService->send(
            $user['email'],
            $subject,
            $body
        );
    }
    
    private function notifyAdminNewApplication(int $applicationId, array $user): void
    {
        // 通知管理员有新申请
        $this->createSystemNotification([
            'title' => '新的企业用户申请',
            'content' => "用户 {$user['username']} 提交了企业用户申请，请及时处理。",
            'type' => 'info',
            'category' => 'admin',
            'action_url' => "/admin/applications/{$applicationId}",
            'action_text' => '查看申请'
        ]);
    }      private function sendReviewResultEmail(array $user, array $application, string $action, string $notes): void
    {
        $status = '';
        switch($action) {
            case 'approve':
                $status = '已批准';
                break;
            case 'reject':
                $status = '已拒绝';
                break;
            default:
                $status = '正在审核中';
        }
        
        $subject = "申请审核结果 - {$status}";
        $body = "亲爱的 {$user['username']}，\n\n您的申请审核结果：{$status}\n\n";
        if ($notes) {
            $body .= "备注：{$notes}\n\n";
        }
        $body .= "如有疑问，请联系客服。";
        
        $this->emailService->send(
            $user['email'],
            $subject,
            $body
        );
    }private function createSystemNotification(array $data): int
    {
        $result = $this->db->insert('system_notifications', $data);
        return $result ? 1 : 0;
    }
      private function getApplicationTitle(string $applicationType): string
    {
        switch($applicationType) {
            case self::APPLICATION_TYPE_ENTERPRISE_ACCESS:
                return '企业用户申请';
            case self::APPLICATION_TYPE_QUOTA_UPGRADE:
                return '配额升级申请';
            case self::APPLICATION_TYPE_SPECIAL_PERMISSION:
                return '特殊权限申请';
            default:
                return '用户申请';
        }    }
      private function getReviewResultMessage(string $action, string $notes): string
    {
        $message = '';
        switch($action) {
            case 'approve':
                $message = '您的申请已经通过审核。';
                break;
            case 'reject':
                $message = '很抱歉，您的申请未能通过审核。';
                break;
            case 'under_review':
                $message = '您的申请正在审核中，请耐心等待。';
                break;
            default:
                $message = '申请状态已更新。';
        }
        
        if ($notes) {
            $message .= "\n\n备注：{$notes}";
        }
          return $message;
    }
    
    /**
     * 获取企业用户列表
     */
    public function getEnterpriseUsers(array $filters = []): array
    {
        try {
            $where = ["user_type = 'enterprise'"];
            $params = [];
            
            if (!empty($filters['status'])) {
                $where[] = "application_status = ?";
                $params[] = $filters['status'];
            }
            
            if (!empty($filters['company'])) {
                $where[] = "company_name LIKE ?";
                $params[] = '%' . $filters['company'] . '%';
            }
            
            $limit = $filters['limit'] ?? 20;
            $offset = $filters['offset'] ?? 0;
            
            $whereClause = implode(' AND ', $where);
            
            $sql = "SELECT 
                        id, username, email, company_name, company_size, industry,
                        application_status, approved_at, api_quota_daily, api_quota_monthly,
                        api_usage_daily, api_usage_monthly, wallet_balance, created_at
                    FROM users 
                    WHERE {$whereClause}
                    ORDER BY created_at DESC
                    LIMIT {$limit} OFFSET {$offset}";
                    
            $users = $this->db->query($sql, $params);
            
            // 获取总数
            $countSql = "SELECT COUNT(*) as total FROM users WHERE {$whereClause}";
            $total = $this->db->query($countSql, $params)[0]['total'] ?? 0;
            
            return [
                'users' => $users,
                'total' => (int)$total,
                'limit' => $limit,
                'offset' => $offset
            ];
            
        } catch (Exception $e) {
            $this->logger->error('获取企业用户列表失败', [
                'error' => $e->getMessage(),
                'filters' => $filters
            ]);
            throw $e;
        }
    }
      /**
     * 提交企业申请
     */
    public function submitEnterpriseApplication(array $data): array
    {
        try {
            // 补充缺失的字段，确保 createEnterpriseApplication 需要的所有字段都存在
            $applicationData = [
                'user_id' => $data['user_id'],
                'company_name' => $data['company_name'],
                'company_size' => $data['company_size'] ?? 'medium', // 提供默认值
                'industry' => $data['industry'] ?? 'technology', // 提供默认值
                'reason' => $data['reason'] ?? $data['contact_email'] ?? '企业用户申请',
                'api_quota_daily' => $data['api_quota_daily'] ?? 1000,
                'api_quota_monthly' => $data['api_quota_monthly'] ?? 30000
            ];
            
            return $this->createEnterpriseApplication($applicationData);
            
        } catch (Exception $e) {
            $this->logger->error('提交企业申请失败', [
                'error' => $e->getMessage(),
                'data' => $data
            ]);
            throw $e;
        }
    }
    
    /**
     * 审核企业用户申请
     */
    public function reviewEnterpriseApplication(int $applicationId, string $action, array $reviewData): array
    {
        return $this->reviewApplication($applicationId, $action, $reviewData);
    }
      /**
     * 更新用户配额
     */
    public function updateUserQuota(int $userId, array $quotaData): array
    {
        try {
            $this->db->beginTransaction();
            
            // 获取当前用户信息
            $currentUser = $this->getUserById($userId);
            if (!$currentUser) {
                throw new Exception('用户不存在');
            }
            
            $updateData = [];
            if (isset($quotaData['daily_quota']) && $quotaData['daily_quota'] !== null) {
                $updateData['api_quota_daily'] = (int)$quotaData['daily_quota'];
            }
            if (isset($quotaData['monthly_quota']) && $quotaData['monthly_quota'] !== null) {
                $updateData['api_quota_monthly'] = (int)$quotaData['monthly_quota'];
            }
            
            if (empty($updateData)) {
                throw new Exception('没有需要更新的配额数据');
            }
            
            $sql = "UPDATE users SET " . 
                   implode(', ', array_map(fn($key) => "$key = ?", array_keys($updateData))) . 
                   " WHERE id = ?";
            
            $params = array_values($updateData);
            $params[] = $userId;
            
            $this->db->execute($sql, $params);
            
            // 记录配额变更日志
            $logSql = "INSERT INTO api_quota_resets (user_id, reset_type, old_quota_daily, new_quota_daily, old_quota_monthly, new_quota_monthly, admin_id, notes)
                       VALUES (?, 'manual_adjustment', ?, ?, ?, ?, ?, ?)";
                       
            $this->db->execute($logSql, [
                $userId,
                (int)$currentUser['api_quota_daily'],
                $updateData['api_quota_daily'] ?? (int)$currentUser['api_quota_daily'],
                (int)$currentUser['api_quota_monthly'],
                $updateData['api_quota_monthly'] ?? (int)$currentUser['api_quota_monthly'],
                $quotaData['admin_id'] ?? 0,
                $quotaData['reason'] ?? '手动调整配额'
            ]);
            
            $this->db->commit();
            
            // 清除缓存
            $this->cache->delete("user_quota_{$userId}");
            
            $this->logger->info('用户配额更新成功', [
                'user_id' => $userId,
                'quota_data' => $quotaData,
                'update_data' => $updateData
            ]);
            
            return [
                'success' => true,
                'message' => '配额更新成功',
                'quota' => $updateData
            ];
            
        } catch (Exception $e) {
            $this->db->rollback();
            $this->logger->error('更新用户配额失败', [
                'error' => $e->getMessage(),
                'user_id' => $userId,
                'quota_data' => $quotaData
            ]);
            throw $e;
        }
    }
}
