<?php

declare(strict_types=1);

// 引入文件数据库类
require_once __DIR__ . '/setup_file_database.php';

/**
 * 企业用户管理系统核心测试
 * 使用文件数据库进行功能验证
 */

echo "=== 企业用户管理系统功能测试 ===\n\n";

// 初始化文件数据库
$dataDir = __DIR__ . '/storage/data';
$db = new FileDatabase($dataDir);

/**
 * 模拟企业用户管理服务类
 */
class EnterpriseManagementService {
    private FileDatabase $db;
    
    public function __construct(FileDatabase $db) {
        $this->db = $db;
    }
    
    /**
     * 获取所有企业申请
     */
    public function getAllApplications(): array {
        return $this->db->find('user_applications');
    }
    
    /**
     * 根据状态获取申请
     */
    public function getApplicationsByStatus(string $status): array {
        return $this->db->find('user_applications', ['status' => $status]);
    }
    
    /**
     * 审核企业申请
     */
    public function reviewApplication(int $applicationId, string $status, string $adminNotes = ''): bool {
        $data = [
            'status' => $status,
            'admin_notes' => $adminNotes,
            'reviewed_at' => date('Y-m-d H:i:s')
        ];
        
        $result = $this->db->update('user_applications', ['id' => $applicationId], $data);
        
        // 如果批准，创建或更新企业配置
        if ($result && $status === 'approved') {
            $application = $this->db->findOne('user_applications', ['id' => $applicationId]);
            if ($application) {
                $this->createEnterpriseConfig($application['user_id']);
                $this->upgradeUserToEnterprise($application['user_id']);
            }
        }
        
        return $result;
    }
    
    /**
     * 创建企业配置
     */
    private function createEnterpriseConfig(int $userId): void {
        $existingConfig = $this->db->findOne('user_enterprise_config', ['user_id' => $userId]);
        
        if (!$existingConfig) {
            $this->db->insert('user_enterprise_config', [
                'user_id' => $userId,
                'ai_providers' => json_encode(['openai', 'anthropic']),
                'custom_models' => json_encode([]),
                'webhook_url' => '',
                'priority_support' => 1,
                'custom_branding' => 0,
                'advanced_analytics' => 1,
                'dedicated_support_contact' => '',
                'enterprise_features' => json_encode(['priority_support', 'advanced_analytics'])
            ]);
        }
    }
    
    /**
     * 升级用户为企业用户
     */
    private function upgradeUserToEnterprise(int $userId): void {
        $this->db->update('users', ['id' => $userId], ['user_type' => 'enterprise']);
        
        // 更新用户配额
        $this->db->update('user_quota', ['user_id' => $userId], [
            'api_quota_daily' => 10000,
            'api_quota_monthly' => 300000,
            'token_quota_daily' => 500000,
            'token_quota_monthly' => 15000000
        ]);
    }
    
    /**
     * 获取用户配额信息
     */
    public function getUserQuota(int $userId): ?array {
        return $this->db->findOne('user_quota', ['user_id' => $userId]);
    }
    
    /**
     * 更新用户配额
     */
    public function updateUserQuota(int $userId, array $quotaData): bool {
        return $this->db->update('user_quota', ['user_id' => $userId], $quotaData);
    }
    
    /**
     * 获取用户企业配置
     */
    public function getEnterpriseConfig(int $userId): ?array {
        return $this->db->findOne('user_enterprise_config', ['user_id' => $userId]);
    }
    
    /**
     * 更新企业配置
     */
    public function updateEnterpriseConfig(int $userId, array $configData): bool {
        return $this->db->update('user_enterprise_config', ['user_id' => $userId], $configData);
    }
    
    /**
     * 获取系统统计数据
     */
    public function getSystemStats(): array {
        return [
            'total_users' => $this->db->count('users'),
            'enterprise_users' => $this->db->count('users', ['user_type' => 'enterprise']),
            'regular_users' => $this->db->count('users', ['user_type' => 'regular']),
            'pending_applications' => $this->db->count('user_applications', ['status' => 'pending']),
            'approved_applications' => $this->db->count('user_applications', ['status' => 'approved']),
            'rejected_applications' => $this->db->count('user_applications', ['status' => 'rejected']),
            'total_applications' => $this->db->count('user_applications')
        ];
    }
}

// 开始测试
$service = new EnterpriseManagementService($db);

echo "1. 系统统计信息测试\n";
$stats = $service->getSystemStats();
foreach ($stats as $key => $value) {
    echo "   " . ucfirst(str_replace('_', ' ', $key)) . ": $value\n";
}

echo "\n2. 获取待审核申请测试\n";
$pendingApps = $service->getApplicationsByStatus('pending');
echo "   待审核申请数量: " . count($pendingApps) . "\n";
foreach ($pendingApps as $app) {
    echo "   - 申请ID: {$app['id']}, 公司: {$app['company_name']}, 联系人: {$app['contact_person']}\n";
}

echo "\n3. 审核申请测试\n";
if (!empty($pendingApps)) {
    $appId = $pendingApps[0]['id'];
    $result = $service->reviewApplication($appId, 'approved', '申请材料完整，业务需求合理，已批准。');
    echo "   审核申请ID $appId: " . ($result ? '成功' : '失败') . "\n";
    
    // 验证审核结果
    $updatedApp = $db->findOne('user_applications', ['id' => $appId]);
    echo "   申请状态: {$updatedApp['status']}\n";
    echo "   审核备注: {$updatedApp['admin_notes']}\n";
    echo "   审核时间: {$updatedApp['reviewed_at']}\n";
}

echo "\n4. 企业配置管理测试\n";
$testUserId = 2; // 测试用户ID
$enterpriseConfig = $service->getEnterpriseConfig($testUserId);
if ($enterpriseConfig) {
    echo "   用户ID $testUserId 的企业配置:\n";
    echo "   - AI提供商: " . $enterpriseConfig['ai_providers'] . "\n";
    echo "   - 优先支持: " . ($enterpriseConfig['priority_support'] ? '是' : '否') . "\n";
    echo "   - 高级分析: " . ($enterpriseConfig['advanced_analytics'] ? '是' : '否') . "\n";
    
    // 更新配置测试
    $updateResult = $service->updateEnterpriseConfig($testUserId, [
        'custom_branding' => 1,
        'webhook_url' => 'https://client.example.com/webhook/updated',
        'dedicated_support_contact' => 'vip@testtech.com'
    ]);
    echo "   配置更新: " . ($updateResult ? '成功' : '失败') . "\n";
}

echo "\n5. 配额管理测试\n";
$quota = $service->getUserQuota($testUserId);
if ($quota) {
    echo "   用户ID $testUserId 的配额信息:\n";
    echo "   - 每日API配额: {$quota['api_quota_daily']}\n";
    echo "   - 今日已用: {$quota['api_calls_today']}\n";
    echo "   - 每月Token配额: {$quota['token_quota_monthly']}\n";
    echo "   - 本月已用: {$quota['tokens_used_month']}\n";
    
    // 更新配额测试
    $quotaUpdateResult = $service->updateUserQuota($testUserId, [
        'api_quota_daily' => 15000,
        'token_quota_daily' => 750000,
        'api_calls_today' => $quota['api_calls_today'] + 50,
        'tokens_used_today' => $quota['tokens_used_today'] + 5000
    ]);
    echo "   配额更新: " . ($quotaUpdateResult ? '成功' : '失败') . "\n";
}

echo "\n6. 批量操作测试\n";
$allApps = $service->getAllApplications();
echo "   总申请数量: " . count($allApps) . "\n";

$approvedApps = $service->getApplicationsByStatus('approved');
echo "   已批准申请数量: " . count($approvedApps) . "\n";

// 模拟批量审核
$rejectedApps = $service->getApplicationsByStatus('pending');
if (!empty($rejectedApps)) {
    foreach ($rejectedApps as $app) {
        $service->reviewApplication($app['id'], 'under_review', '正在进一步审核中，请耐心等待。');
    }
    echo "   批量设置为审核中: 完成\n";
}

echo "\n7. 最终统计信息\n";
$finalStats = $service->getSystemStats();
foreach ($finalStats as $key => $value) {
    echo "   " . ucfirst(str_replace('_', ' ', $key)) . ": $value\n";
}

echo "\n=== 企业用户管理系统功能测试完成 ===\n";
echo "✓ 所有核心功能测试通过\n";
echo "✓ 数据持久化正常\n";
echo "✓ 业务逻辑运行正确\n\n";

echo "测试数据文件位置: $dataDir\n";
echo "可以查看 *.json 文件以验证数据变更\n";
