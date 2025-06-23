<?php

declare(strict_types=1);

/**
 * 企业用户管理系统核心服务类
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
        
        return $this->db->update('user_applications', ['id' => $applicationId], $data);
    }
    
    /**
     * 更新用户配额
     */
    public function updateUserQuota(int $userId, array $quotaData): bool {
        // 检查配额记录是否存在
        $existingQuota = $this->db->findOne('user_quotas', ['user_id' => $userId]);
        
        if ($existingQuota) {
            return $this->db->update('user_quotas', ['user_id' => $userId], $quotaData);
        } else {
            $quotaData['user_id'] = $userId;
            $this->db->insert('user_quotas', $quotaData);
            return true;
        }
    }
    
    /**
     * 获取用户配额
     */
    public function getUserQuota(int $userId): ?array {
        return $this->db->findOne('user_quotas', ['user_id' => $userId]);
    }
    
    /**
     * 获取企业配置
     */
    public function getEnterpriseConfig(int $userId): ?array {
        return $this->db->findOne('enterprise_configs', ['user_id' => $userId]);
    }
    
    /**
     * 更新企业配置
     */
    public function updateEnterpriseConfig(int $userId, array $configData): bool {
        $existingConfig = $this->db->findOne('enterprise_configs', ['user_id' => $userId]);
        
        if ($existingConfig) {
            return $this->db->update('enterprise_configs', ['user_id' => $userId], $configData);
        } else {
            $configData['user_id'] = $userId;
            $this->db->insert('enterprise_configs', $configData);
            return true;
        }
    }
    
    /**
     * 获取系统统计信息
     */
    public function getSystemStats(): array {
        $users = $this->db->find('users');
        $applications = $this->db->find('user_applications');
        $quotas = $this->db->find('user_quotas');
        
        $stats = [
            'total_users' => count($users),
            'total_applications' => count($applications),
            'pending_applications' => count($this->db->find('user_applications', ['status' => 'pending'])),
            'approved_applications' => count($this->db->find('user_applications', ['status' => 'approved'])),
            'rejected_applications' => count($this->db->find('user_applications', ['status' => 'rejected'])),
            'total_quotas' => count($quotas),
            'enterprise_users' => count(array_filter($users, function($user) {
                return isset($user['user_type']) && $user['user_type'] === 'enterprise';
            })),
            'individual_users' => count(array_filter($users, function($user) {
                return !isset($user['user_type']) || $user['user_type'] === 'individual';
            }))
        ];
        
        // 计算配额使用情况
        $totalApiCalls = 0;
        $totalTokens = 0;
        
        foreach ($quotas as $quota) {
            $totalApiCalls += $quota['api_calls_used'] ?? 0;
            $totalTokens += $quota['tokens_used'] ?? 0;
        }
        
        $stats['total_api_calls'] = $totalApiCalls;
        $stats['total_tokens'] = $totalTokens;
        
        return $stats;
    }
    
    /**
     * 获取所有用户
     */
    public function getAllUsers(): array {
        return $this->db->find('users');
    }
    
    /**
     * 获取企业用户
     */
    public function getEnterpriseUsers(): array {
        return $this->db->find('users', ['user_type' => 'enterprise']);
    }
    
    /**
     * 获取用户详细信息（包含配额和配置）
     */
    public function getUserDetails(int $userId): ?array {
        $user = $this->db->findOne('users', ['id' => $userId]);
        if (!$user) {
            return null;
        }
        
        $quota = $this->getUserQuota($userId);
        $config = $this->getEnterpriseConfig($userId);
        $application = $this->db->findOne('user_applications', ['user_id' => $userId]);
        
        return [
            'user' => $user,
            'quota' => $quota,
            'config' => $config,
            'application' => $application
        ];
    }
}
