<?php

/**
 * 初始化干净的测试数据
 */

require_once __DIR__ . '/includes/FileDatabase.php';
require_once __DIR__ . '/includes/EnterpriseManagementService.php';

echo "=== 初始化企业管理系统测试数据 ===\n\n";

$dataDir = __DIR__ . '/storage/data';
$db = new FileDatabase($dataDir);

// 清理现有数据并创建新的测试数据
$tables = ['users', 'user_applications', 'user_quotas', 'enterprise_configs'];

foreach ($tables as $table) {
    $file = $dataDir . '/' . $table . '.json';
    if (file_exists($file)) {
        // 清空文件内容，重新开始
        file_put_contents($file, json_encode([], JSON_PRETTY_PRINT));
    }
}

echo "1. 创建测试用户...\n";

// 创建管理员用户
$adminId = $db->insert('users', [
    'username' => 'admin',
    'email' => 'admin@alingai.com',
    'password_hash' => password_hash('admin123', PASSWORD_DEFAULT),
    'full_name' => '系统管理员',
    'user_type' => 'admin',
    'status' => 'active'
]);

// 创建企业用户
$enterpriseUsers = [
    [
        'username' => 'tech_corp',
        'email' => 'contact@techcorp.com',
        'full_name' => '科技公司管理员',
        'user_type' => 'enterprise',
        'status' => 'active'
    ],
    [
        'username' => 'ai_startup',
        'email' => 'admin@aistartup.com',
        'full_name' => 'AI创业公司',
        'user_type' => 'enterprise',
        'status' => 'pending'
    ],
    [
        'username' => 'data_labs',
        'email' => 'info@datalabs.com',
        'full_name' => '数据实验室',
        'user_type' => 'enterprise',
        'status' => 'active'
    ]
];

$enterpriseUserIds = [];
foreach ($enterpriseUsers as $user) {
    $user['password_hash'] = password_hash('password123', PASSWORD_DEFAULT);
    $enterpriseUserIds[] = $db->insert('users', $user);
}

// 创建个人用户
$individualUsers = [
    [
        'username' => 'john_doe',
        'email' => 'john@example.com',
        'full_name' => 'John Doe',
        'user_type' => 'individual',
        'status' => 'active'
    ],
    [
        'username' => 'jane_smith',
        'email' => 'jane@example.com',
        'full_name' => 'Jane Smith',
        'user_type' => 'individual',
        'status' => 'active'
    ]
];

$individualUserIds = [];
foreach ($individualUsers as $user) {
    $user['password_hash'] = password_hash('password123', PASSWORD_DEFAULT);
    $individualUserIds[] = $db->insert('users', $user);
}

echo "   创建了 " . (1 + count($enterpriseUsers) + count($individualUsers)) . " 个用户\n\n";

echo "2. 创建企业申请...\n";

$applications = [
    [
        'user_id' => $enterpriseUserIds[0],
        'application_type' => 'enterprise',
        'company_name' => '创新科技有限公司',
        'business_license' => '91000000000000001X',
        'contact_person' => '李经理',
        'contact_phone' => '13800000001',
        'contact_email' => 'contact@techcorp.com',
        'business_description' => '专注于AI技术研发的科技公司',
        'use_case' => 'AI模型训练和推理服务',
        'expected_usage' => '月均100万次API调用',
        'status' => 'approved',
        'admin_notes' => '已通过审核，用户资质良好'
    ],
    [
        'user_id' => $enterpriseUserIds[1],
        'application_type' => 'enterprise',
        'company_name' => 'AI创新实验室',
        'business_license' => '91000000000000002X',
        'contact_person' => '张总监',
        'contact_phone' => '13800000002',
        'contact_email' => 'admin@aistartup.com',
        'business_description' => '人工智能创业公司',
        'use_case' => '智能客服系统开发',
        'expected_usage' => '月均50万次API调用',
        'status' => 'pending',
        'admin_notes' => ''
    ],
    [
        'user_id' => $enterpriseUserIds[2],
        'application_type' => 'enterprise',
        'company_name' => '大数据分析中心',
        'business_license' => '91000000000000003X',
        'contact_person' => '王研究员',
        'contact_phone' => '13800000003',
        'contact_email' => 'info@datalabs.com',
        'business_description' => '专业数据分析服务机构',
        'use_case' => '数据分析和报告生成',
        'expected_usage' => '月均80万次API调用',
        'status' => 'approved',
        'admin_notes' => '技术实力强，合作前景好'
    ]
];

foreach ($applications as $app) {
    $db->insert('user_applications', $app);
}

echo "   创建了 " . count($applications) . " 个企业申请\n\n";

echo "3. 创建用户配额...\n";

$quotas = [
    [
        'user_id' => $enterpriseUserIds[0],
        'api_calls_limit' => 1000000,
        'api_calls_used' => 250000,
        'tokens_limit' => 50000000,
        'tokens_used' => 12500000,
        'daily_limit' => 50000,
        'monthly_limit' => 1000000,
        'rate_limit' => 1000,
        'status' => 'active'
    ],
    [
        'user_id' => $enterpriseUserIds[2],
        'api_calls_limit' => 800000,
        'api_calls_used' => 156000,
        'tokens_limit' => 40000000,
        'tokens_used' => 7800000,
        'daily_limit' => 40000,
        'monthly_limit' => 800000,
        'rate_limit' => 800,
        'status' => 'active'
    ],
    [
        'user_id' => $individualUserIds[0],
        'api_calls_limit' => 10000,
        'api_calls_used' => 2500,
        'tokens_limit' => 500000,
        'tokens_used' => 125000,
        'daily_limit' => 500,
        'monthly_limit' => 10000,
        'rate_limit' => 100,
        'status' => 'active'
    ]
];

foreach ($quotas as $quota) {
    $db->insert('user_quotas', $quota);
}

echo "   创建了 " . count($quotas) . " 个用户配额记录\n\n";

echo "4. 创建企业配置...\n";

$configs = [
    [
        'user_id' => $enterpriseUserIds[0],
        'ai_providers' => json_encode([
            'openai' => ['enabled' => true, 'api_key' => 'sk-***'],
            'anthropic' => ['enabled' => true, 'api_key' => 'sk-***'],
            'google' => ['enabled' => false, 'api_key' => '']
        ]),
        'custom_models' => json_encode([
            'gpt-4-turbo', 'claude-3-sonnet', 'gemini-pro'
        ]),
        'webhook_url' => 'https://techcorp.com/webhook',
        'callback_url' => 'https://techcorp.com/callback',
        'allowed_domains' => json_encode(['techcorp.com', '*.techcorp.com']),
        'ip_whitelist' => json_encode(['192.168.1.0/24', '10.0.0.0/8']),
        'features' => json_encode([
            'advanced_analytics' => true,
            'custom_fine_tuning' => true,
            'priority_support' => true,
            'sla_guarantee' => true
        ]),
        'billing_type' => 'enterprise',
        'contract_start' => date('Y-m-d', strtotime('-30 days')),
        'contract_end' => date('Y-m-d', strtotime('+330 days')),
        'status' => 'active'
    ],
    [
        'user_id' => $enterpriseUserIds[2],
        'ai_providers' => json_encode([
            'openai' => ['enabled' => true, 'api_key' => 'sk-***'],
            'anthropic' => ['enabled' => false, 'api_key' => ''],
            'google' => ['enabled' => true, 'api_key' => 'sk-***']
        ]),
        'custom_models' => json_encode([
            'gpt-4', 'gemini-pro'
        ]),
        'webhook_url' => 'https://datalabs.com/webhook',
        'callback_url' => 'https://datalabs.com/callback',
        'allowed_domains' => json_encode(['datalabs.com']),
        'ip_whitelist' => json_encode(['203.0.113.0/24']),
        'features' => json_encode([
            'advanced_analytics' => true,
            'custom_fine_tuning' => false,
            'priority_support' => true,
            'sla_guarantee' => false
        ]),
        'billing_type' => 'pay_per_use',
        'contract_start' => date('Y-m-d', strtotime('-60 days')),
        'contract_end' => date('Y-m-d', strtotime('+300 days')),
        'status' => 'active'
    ]
];

foreach ($configs as $config) {
    $db->insert('enterprise_configs', $config);
}

echo "   创建了 " . count($configs) . " 个企业配置记录\n\n";

echo "=== 数据初始化完成 ===\n";
echo "系统统计:\n";
echo "- 用户总数: " . count($db->find('users')) . "\n";
echo "- 企业申请: " . count($db->find('user_applications')) . "\n";
echo "- 配额记录: " . count($db->find('user_quotas')) . "\n";
echo "- 企业配置: " . count($db->find('enterprise_configs')) . "\n";

// 创建管理服务并获取统计信息
$service = new EnterpriseManagementService($db);
$stats = $service->getSystemStats();

echo "\n系统详细统计:\n";
foreach ($stats as $key => $value) {
    echo "- $key: $value\n";
}
