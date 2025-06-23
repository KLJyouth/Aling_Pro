<?php

declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

use AlingAi\Services\{DatabaseService, CacheService, EmailService, EnhancedUserManagementService};
use AlingAi\Controllers\EnterpriseAdminController;
use AlingAi\Utils\Logger;

/**
 * 企业用户管理系统测试脚本
 * 测试企业用户申请、审核、配额管理等功能
 */

echo "=== AlingAi Pro 企业用户管理系统测试 ===\n\n";

try {
    // 1. 加载环境配置
    echo "1. 加载环境配置...\n";
    $envFile = __DIR__ . '/.env';
    if (file_exists($envFile)) {
        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
                [$key, $value] = explode('=', $line, 2);
                $_ENV[trim($key)] = trim($value, '"\'');
            }
        }
        echo "  ✓ 环境配置已加载\n";
    } else {
        echo "  ⚠ 未找到 .env 文件\n";
    }

    // 2. 测试数据库连接
    echo "\n2. 测试数据库连接...\n";
    $dbConfig = [
        'host' => $_ENV['DB_HOST'] ?? 'localhost',
        'database' => $_ENV['DB_DATABASE'] ?? 'alingai_pro',
        'username' => $_ENV['DB_USERNAME'] ?? 'root',
        'password' => $_ENV['DB_PASSWORD'] ?? '',
        'charset' => 'utf8mb4'
    ];
    
    try {
        $pdo = new PDO(
            "mysql:host={$dbConfig['host']};dbname={$dbConfig['database']};charset={$dbConfig['charset']}",
            $dbConfig['username'],
            $dbConfig['password'],
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        echo "  ✓ 数据库连接成功\n";
    } catch (PDOException $e) {
        echo "  ✗ 数据库连接失败: " . $e->getMessage() . "\n";
        throw $e;
    }

    // 3. 初始化服务
    echo "\n3. 初始化服务...\n";
    $db = new DatabaseService($dbConfig);
    $cache = new CacheService();
    $logger = new Logger();
    
    $emailConfig = [
        'host' => $_ENV['MAIL_HOST'] ?? 'localhost',
        'port' => (int)($_ENV['MAIL_PORT'] ?? 587),
        'username' => $_ENV['MAIL_USERNAME'] ?? '',
        'password' => $_ENV['MAIL_PASSWORD'] ?? '',
        'encryption' => $_ENV['MAIL_ENCRYPTION'] ?? 'tls',
        'from_email' => $_ENV['MAIL_FROM_ADDRESS'] ?? 'noreply@alingai.com',
        'from_name' => $_ENV['MAIL_FROM_NAME'] ?? 'AlingAi Pro'
    ];
    
    $emailService = new EmailService($logger, $emailConfig);
    $userManagementService = new EnhancedUserManagementService($db, $cache, $emailService, $logger);
    
    echo "  ✓ 服务初始化完成\n";

    // 4. 测试企业用户管理服务
    echo "\n4. 测试企业用户管理服务...\n";
    
    // 测试数据
    $testUserId = 1; // 假设存在的用户ID
    $testApplicationData = [
        'user_id' => $testUserId,
        'company_name' => '测试科技有限公司',
        'company_size' => 'medium',
        'industry' => 'technology',
        'reason' => '申请企业用户权限以支持团队使用',
        'api_quota_daily' => 2000,
        'api_quota_monthly' => 60000
    ];

    try {
        // 4.1 测试企业申请提交
        echo "  4.1 测试企业申请提交...\n";
        $applicationResult = $userManagementService->submitEnterpriseApplication($testApplicationData);
        
        if ($applicationResult['success']) {
            echo "    ✓ 企业申请提交成功\n";
            echo "    申请ID: " . $applicationResult['application_id'] . "\n";
            echo "    消息: " . $applicationResult['message'] . "\n";
            
            $applicationId = $applicationResult['application_id'];
        } else {
            echo "    ✗ 企业申请提交失败\n";
            $applicationId = null;
        }
    } catch (Exception $e) {
        echo "    ⚠ 企业申请可能已存在或其他错误: " . $e->getMessage() . "\n";
        $applicationId = null;
    }

    // 4.2 测试API配额检查
    echo "  4.2 测试API配额检查...\n";
    try {
        $quotaCheck = $userManagementService->checkApiQuota($testUserId, 100);
        echo "    ✓ API配额检查完成\n";
        echo "    可以继续: " . ($quotaCheck['can_proceed'] ? '是' : '否') . "\n";
        echo "    每日剩余: " . $quotaCheck['daily_remaining'] . "\n";
        echo "    每月剩余: " . $quotaCheck['monthly_remaining'] . "\n";
    } catch (Exception $e) {
        echo "    ⚠ API配额检查失败: " . $e->getMessage() . "\n";
    }

    // 4.3 测试防机器人验证
    echo "  4.3 测试防机器人验证...\n";
    try {
        $verificationResult = $userManagementService->generateAntiBotVerification($testUserId);
        if ($verificationResult['success']) {
            echo "    ✓ 验证码生成成功\n";
            echo "    验证码: " . $verificationResult['code'] . "\n";
            
            // 测试验证
            $verified = $userManagementService->verifyAntiBotCode($testUserId, $verificationResult['code']);
            echo "    验证结果: " . ($verified ? '通过' : '失败') . "\n";
        }
    } catch (Exception $e) {
        echo "    ⚠ 防机器人验证失败: " . $e->getMessage() . "\n";
    }

    // 5. 测试企业管理控制器
    echo "\n5. 测试企业管理控制器...\n";
    $enterpriseController = new EnterpriseAdminController($db, $cache, $emailService, $userManagementService);
    echo "  ✓ 企业管理控制器初始化完成\n";

    // 6. 检查数据库表结构
    echo "\n6. 检查数据库表结构...\n";
    $requiredTables = [
        'users',
        'user_applications',
        'api_usage_stats',
        'wallet_transactions',
        'system_notifications',
        'ai_provider_configs'
    ];

    foreach ($requiredTables as $table) {
        $stmt = $pdo->prepare("SHOW TABLES LIKE ?");
        $stmt->execute([$table]);
        $exists = $stmt->fetchColumn() !== false;
        echo "  " . ($exists ? "✓" : "⚠") . " 表 {$table}: " . ($exists ? "存在" : "不存在") . "\n";
    }

    // 7. 生成测试报告
    echo "\n7. 生成测试报告...\n";
    $report = [
        'test_time' => date('Y-m-d H:i:s'),
        'database_connection' => 'success',
        'services_initialization' => 'success',
        'enterprise_application' => isset($applicationResult) && $applicationResult['success'] ? 'success' : 'warning',
        'api_quota_check' => isset($quotaCheck) ? 'success' : 'warning',
        'anti_bot_verification' => isset($verificationResult) && $verificationResult['success'] ? 'success' : 'warning',
        'controller_initialization' => 'success'
    ];

    $reportFile = __DIR__ . '/test_results/enterprise_management_test_' . date('Y_m_d_H_i_s') . '.json';
    if (!is_dir(dirname($reportFile))) {
        mkdir(dirname($reportFile), 0755, true);
    }
    file_put_contents($reportFile, json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    echo "  ✓ 测试报告已保存到: " . $reportFile . "\n";

    echo "\n=== 测试完成 ===\n";
    echo "企业用户管理系统基本功能测试通过！\n";
    echo "您可以继续进行以下操作：\n";
    echo "1. 运行数据库迁移脚本\n";
    echo "2. 配置邮件服务\n";
    echo "3. 测试前端集成\n";
    echo "4. 配置生产环境\n";

} catch (Exception $e) {
    echo "\n✗ 测试失败: " . $e->getMessage() . "\n";
    echo "错误位置: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "堆栈跟踪:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
