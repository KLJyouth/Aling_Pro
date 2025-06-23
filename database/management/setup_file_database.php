<?php

declare(strict_types=1);

/**
 * 简单的文件数据库类
 * 用于在没有数据库连接时进行测试
 */
class FileDatabase {
    private string $dataDir;
    
    public function __construct(string $dataDir) {
        $this->dataDir = $dataDir;
        if (!is_dir($this->dataDir)) {
            mkdir($this->dataDir, 0755, true);
        }
    }
    
    public function insert(string $table, array $data): int {
        $data['id'] = $this->getNextId($table);
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        $records = $this->getRecords($table);
        $records[] = $data;
        $this->saveRecords($table, $records);
        
        return $data['id'];
    }
    
    public function find(string $table, array $conditions = []): array {
        $records = $this->getRecords($table);
        
        if (empty($conditions)) {
            return $records;
        }
        
        return array_filter($records, function($record) use ($conditions) {
            foreach ($conditions as $key => $value) {
                if (!isset($record[$key]) || $record[$key] != $value) {
                    return false;
                }
            }
            return true;
        });
    }
    
    public function findOne(string $table, array $conditions): ?array {
        $results = $this->find($table, $conditions);
        return !empty($results) ? array_values($results)[0] : null;
    }
    
    public function update(string $table, array $conditions, array $data): bool {
        $records = $this->getRecords($table);
        $updated = false;
        
        foreach ($records as &$record) {
            $matches = true;
            foreach ($conditions as $key => $value) {
                if (!isset($record[$key]) || $record[$key] != $value) {
                    $matches = false;
                    break;
                }
            }
            
            if ($matches) {
                $record = array_merge($record, $data);
                $record['updated_at'] = date('Y-m-d H:i:s');
                $updated = true;
            }
        }
        
        if ($updated) {
            $this->saveRecords($table, $records);
        }
        
        return $updated;
    }
    
    public function count(string $table, array $conditions = []): int {
        return count($this->find($table, $conditions));
    }
    
    private function getRecords(string $table): array {
        $file = $this->dataDir . "/{$table}.json";
        if (!file_exists($file)) {
            return [];
        }
        
        $content = file_get_contents($file);
        return json_decode($content, true) ?: [];
    }
    
    private function saveRecords(string $table, array $records): void {
        $file = $this->dataDir . "/{$table}.json";
        file_put_contents($file, json_encode($records, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
      private function getNextId(string $table): int {
        $records = $this->getRecords($table);
        if (empty($records)) {
            return 1;
        }
        
        $ids = array_column($records, 'id');
        $maxId = empty($ids) ? 0 : max($ids);
        return (int)$maxId + 1;
    }
}

echo "=== 初始化文件数据库 ===\n\n";

$dataDir = __DIR__ . '/storage/data';
$db = new FileDatabase($dataDir);

echo "✓ 文件数据库初始化完成\n";
echo "  数据存储目录: $dataDir\n\n";

// 创建测试数据
echo "创建测试数据...\n";

// 插入管理员用户
$adminId = $db->insert('users', [
    'username' => 'admin',
    'email' => 'admin@alingai.com',
    'password_hash' => password_hash('admin123', PASSWORD_DEFAULT),
    'user_type' => 'admin',
    'status' => 'active'
]);
echo "  ✓ 管理员用户创建完成 (ID: $adminId)\n";

// 插入测试普通用户
$userId = $db->insert('users', [
    'username' => 'testuser',
    'email' => 'test@example.com',
    'password_hash' => password_hash('test123', PASSWORD_DEFAULT),
    'user_type' => 'regular',
    'status' => 'active'
]);
echo "  ✓ 测试用户创建完成 (ID: $userId)\n";

// 插入企业用户申请
$appId = $db->insert('user_applications', [
    'user_id' => $userId,
    'application_type' => 'enterprise',
    'company_name' => '测试科技有限公司',
    'business_license' => '91000000000000000X',
    'contact_person' => '张三',
    'contact_phone' => '13800138000',
    'business_description' => '专注于AI技术开发和应用的科技公司',
    'application_data' => json_encode([
        'expected_api_calls' => 100000,
        'business_scale' => 'medium',
        'use_cases' => ['文档生成', '智能客服', '数据分析']
    ]),
    'status' => 'pending'
]);
echo "  ✓ 企业申请创建完成 (ID: $appId)\n";

// 插入另一个已审核的申请
$app2Id = $db->insert('user_applications', [
    'user_id' => $userId,
    'application_type' => 'enterprise',
    'company_name' => '创新科技股份有限公司',
    'business_license' => '91000000000000001Y',
    'contact_person' => '李四',
    'contact_phone' => '13900139000',
    'business_description' => '企业级AI解决方案提供商',
    'application_data' => json_encode([
        'expected_api_calls' => 500000,
        'business_scale' => 'large',
        'use_cases' => ['智能分析', '自动化办公', '客户服务']
    ]),
    'status' => 'approved',
    'admin_notes' => '申请材料齐全，业务需求合理，已批准。',
    'reviewed_at' => date('Y-m-d H:i:s')
]);
echo "  ✓ 已审核企业申请创建完成 (ID: $app2Id)\n";

// 插入用户配额记录
$quotaId = $db->insert('user_quota', [
    'user_id' => $userId,
    'api_quota_daily' => 1000,
    'api_quota_monthly' => 30000,
    'api_calls_today' => 150,
    'api_calls_month' => 4500,
    'token_quota_daily' => 50000,
    'token_quota_monthly' => 1500000,
    'tokens_used_today' => 7500,
    'tokens_used_month' => 225000,
    'last_reset_date' => date('Y-m-d')
]);
echo "  ✓ 用户配额记录创建完成 (ID: $quotaId)\n";

// 插入企业配置记录
$configId = $db->insert('user_enterprise_config', [
    'user_id' => $userId,
    'ai_providers' => json_encode(['openai', 'anthropic', 'google']),
    'custom_models' => json_encode([
        ['name' => 'custom-gpt-4', 'endpoint' => 'https://api.custom.com/v1'],
        ['name' => 'fine-tuned-model', 'endpoint' => 'https://api.internal.com/v1']
    ]),
    'webhook_url' => 'https://client.example.com/webhook',
    'priority_support' => 1,
    'custom_branding' => 1,
    'advanced_analytics' => 1,
    'dedicated_support_contact' => 'support@testtech.com',
    'enterprise_features' => json_encode(['sso', 'audit_logs', 'custom_limits'])
]);
echo "  ✓ 企业配置记录创建完成 (ID: $configId)\n";

// 验证数据
echo "\n验证数据...\n";
echo "  用户数量: " . $db->count('users') . "\n";
echo "  申请数量: " . $db->count('user_applications') . "\n";
echo "  配额记录数量: " . $db->count('user_quota') . "\n";
echo "  企业配置数量: " . $db->count('user_enterprise_config') . "\n";

// 显示示例查询
echo "\n示例查询结果:\n";

$pendingApps = $db->find('user_applications', ['status' => 'pending']);
echo "  待审核申请数量: " . count($pendingApps) . "\n";

$adminUser = $db->findOne('users', ['user_type' => 'admin']);
echo "  管理员用户: " . ($adminUser ? $adminUser['username'] : '未找到') . "\n";

echo "\n✓ 文件数据库初始化和测试完成!\n";
echo "数据文件存储位置: $dataDir\n";
