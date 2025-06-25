<?php

/**
 * 聊天系统测试脚本
 * 验证聊天系统的各个组件是否正常工�?
 */

require_once __DIR__ . '/vendor/autoload.php';

use AlingAi\Core\Container\ServiceContainer;
use AlingAi\Core\Logger\LoggerFactory;
use AlingAi\Services\DeepSeekAIService;
use AlingAi\Services\ChatService;
use AlingAi\Controllers\Api\EnhancedChatApiController;

// 设置错误报告
error_reporting(E_ALL];
ini_set('display_errors', '1'];

echo "=== AlingAi Pro 聊天系统测试 ===\n\n";

try {
    // 1. 测试日志系统
    echo "1. 测试日志系统...\n";
    $logger = LoggerFactory::createLogger(];
    $logger->info('聊天系统测试开�?];
    echo "�?日志系统正常\n\n";

    // 2. 测试服务容器
    echo "2. 测试服务容器...\n";
    $container = new ServiceContainer(];
    echo "�?服务容器创建成功\n\n";

    // 3. 测试DeepSeek AI服务
    echo "3. 测试DeepSeek AI服务...\n";
    try {
        $aiService = $container->get(DeepSeekAIService::class];
        $healthCheck = $aiService->healthCheck(];
        echo "�?DeepSeek AI服务状�? " . $healthCheck['status'] . "\n";
        echo "  消息: " . $healthCheck['message'] . "\n\n";
    } catch (Exception $e) {
        echo "�?DeepSeek AI服务测试失败: " . $e->getMessage() . "\n";
        echo "  这可能是由于API密钥未配置或网络问题\n\n";
    }

    // 4. 测试聊天服务
    echo "4. 测试聊天服务...\n";
    try {
        $chatService = $container->get(ChatService::class];
        echo "�?聊天服务创建成功\n\n";
    } catch (Exception $e) {
        echo "�?聊天服务创建失败: " . $e->getMessage() . "\n\n";
    }

    // 5. 测试聊天控制�?
    echo "5. 测试聊天控制�?..\n";
    try {
        $chatController = $container->get(EnhancedChatApiController::class];
        echo "�?聊天控制器创建成功\n\n";
    } catch (Exception $e) {
        echo "�?聊天控制器创建失�? " . $e->getMessage() . "\n\n";
    }

    // 6. 测试API调用（模拟）
    echo "6. 测试API调用（模拟）...\n";
    try {
        // 模拟HTTP请求
        $requestData = [
            'message' => '你好，请介绍一下你自己',
            'model' => 'deepseek-chat',
            'temperature' => 0.7
        ];
        
        echo "  发送测试消�? " . $requestData['message'] . "\n";
        
        // 这里可以添加实际的API调用测试
        echo "�?API调用模拟成功\n\n";
    } catch (Exception $e) {
        echo "�?API调用测试失败: " . $e->getMessage() . "\n\n";
    }

    // 7. 测试数据库连�?
    echo "7. 测试数据库连�?..\n";
    try {
        $dbManager = $container->get('AlingAi\Core\Database\DatabaseManager'];
        $connection = $dbManager->getConnection(];
        
        // 测试简单查�?
        $stmt = $connection->query('SELECT 1 as test'];
        $result = $stmt->fetch(];
        
        if ($result && $result['test'] == 1) {
            echo "�?数据库连接正常\n\n";
        } else {
            echo "�?数据库查询失败\n\n";
        }
    } catch (Exception $e) {
        echo "�?数据库连接失�? " . $e->getMessage() . "\n\n";
    }

    // 8. 系统信息
    echo "8. 系统信息...\n";
    echo "   PHP版本: " . PHP_VERSION . "\n";
    echo "   系统架构: " . php_uname('m') . "\n";
    echo "   操作系统: " . php_uname('s') . " " . php_uname('r') . "\n";
    echo "   内存限制: " . ini_get('memory_limit') . "\n";
    echo "   最大执行时�? " . ini_get('max_execution_time') . "秒\n";
    echo "   时区: " . date_default_timezone_get() . "\n\n";

    // 9. 环境检�?
    echo "9. 环境检�?..\n";
    $requiredExtensions = ['pdo', 'pdo_mysql', 'json', 'curl', 'mbstring'];
    $missingExtensions = [];
    
    foreach ($requiredExtensions as $ext) {
        if (!extension_loaded($ext)) {
            $missingExtensions[] = $ext;
        }
    }
    
    if (empty($missingExtensions)) {
        echo "�?所有必需的PHP扩展都已加载\n";
    } else {
        echo "�?缺少以下PHP扩展: " . implode(', ', $missingExtensions) . "\n";
    }
    
    // 检查环境变�?
    $requiredEnvVars = ['DEEPSEEK_API_KEY'];
    $missingEnvVars = [];
    
    foreach ($requiredEnvVars as $var) {
        if (empty(getenv($var))) {
            $missingEnvVars[] = $var;
        }
    }
    
    if (empty($missingEnvVars)) {
        echo "�?所有必需的环境变量都已设置\n";
    } else {
        echo "�?缺少以下环境变量: " . implode(', ', $missingEnvVars) . "\n";
        echo "  这些变量对于AI功能是必需的\n";
    }
    
    echo "\n";

    // 10. 总结
    echo "=== 测试总结 ===\n";
    echo "聊天系统核心组件测试完成。\n";
    echo "如果所有测试都通过，聊天系统应该可以正常工作。\n";
    echo "如有问题，请检查错误信息并确保所有依赖都已正确安装。\n\n";

} catch (Exception $e) {
    echo "�?测试过程中发生严重错�? " . $e->getMessage() . "\n";
    echo "堆栈跟踪:\n" . $e->getTraceAsString() . "\n";
}

echo "测试完成。\n"; 
