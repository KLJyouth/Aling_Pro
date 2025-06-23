<?php
/**
 * 直接执行AI智能体数据表SQL
 */

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/src/Utils/EnvLoader.php';

use AlingAi\Utils\EnvLoader;

// 加载环境配置
EnvLoader::load(__DIR__ . '/.env');

echo "=== 直接执行AI智能体数据表SQL ===\n";

try {
    $dsn = sprintf(
        'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
        $_ENV['DB_HOST'],
        $_ENV['DB_PORT'],
        $_ENV['DB_DATABASE']
    );
    
    $pdo = new PDO($dsn, $_ENV['DB_USERNAME'], $_ENV['DB_PASSWORD'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
    ]);
    
    echo "✅ 数据库连接成功\n\n";
    
    // 直接定义SQL语句
    $sqlStatements = [
        // AI智能体基础表
        "CREATE TABLE IF NOT EXISTS `ai_agents` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `agent_id` varchar(100) NOT NULL UNIQUE COMMENT '智能体唯一标识',
            `name` varchar(200) NOT NULL COMMENT '智能体名称',
            `type` varchar(50) NOT NULL COMMENT '智能体类型',
            `capabilities` json NOT NULL COMMENT '智能体能力',
            `status` enum('active','inactive','maintenance','error') DEFAULT 'active' COMMENT '状态',
            `performance_score` decimal(3,2) DEFAULT 0.00 COMMENT '性能评分',
            `load_factor` decimal(3,2) DEFAULT 0.00 COMMENT '负载因子',
            `max_concurrent_tasks` int(11) DEFAULT 5 COMMENT '最大并发任务数',
            `current_tasks` int(11) DEFAULT 0 COMMENT '当前任务数',
            `total_tasks_completed` int(11) DEFAULT 0 COMMENT '总完成任务数',
            `success_rate` decimal(5,2) DEFAULT 0.00 COMMENT '成功率',
            `average_response_time` decimal(8,3) DEFAULT 0.000 COMMENT '平均响应时间(秒)',
            `last_active_at` timestamp NULL DEFAULT NULL COMMENT '最后活跃时间',
            `configuration` json DEFAULT NULL COMMENT '智能体配置',
            `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
            `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `idx_agent_id` (`agent_id`),
            KEY `idx_status` (`status`),
            KEY `idx_type` (`type`),
            KEY `idx_performance` (`performance_score`),
            KEY `idx_load` (`load_factor`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='AI智能体基础信息表'",
        
        // AI增强任务表
        "CREATE TABLE IF NOT EXISTS `ai_enhanced_tasks` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `task_id` varchar(100) NOT NULL UNIQUE COMMENT '任务唯一标识',
            `description` text NOT NULL COMMENT '任务描述',
            `type` varchar(50) NOT NULL DEFAULT 'general' COMMENT '任务类型',
            `priority` enum('low','normal','high','urgent') DEFAULT 'normal' COMMENT '优先级',
            `assigned_agent_id` varchar(100) DEFAULT NULL COMMENT '分配的智能体ID',
            `status` enum('pending','assigned','running','completed','failed','cancelled') DEFAULT 'pending' COMMENT '任务状态',
            `analysis_data` json DEFAULT NULL COMMENT '任务分析数据',
            `execution_data` json DEFAULT NULL COMMENT '执行数据',
            `result_data` json DEFAULT NULL COMMENT '结果数据',
            `performance_metrics` json DEFAULT NULL COMMENT '性能指标',
            `error_info` json DEFAULT NULL COMMENT '错误信息',
            `estimated_duration` int(11) DEFAULT NULL COMMENT '预估执行时间(秒)',
            `actual_duration` int(11) DEFAULT NULL COMMENT '实际执行时间(秒)',
            `retry_count` int(11) DEFAULT 0 COMMENT '重试次数',
            `max_retries` int(11) DEFAULT 3 COMMENT '最大重试次数',
            `started_at` timestamp NULL DEFAULT NULL COMMENT '开始时间',
            `completed_at` timestamp NULL DEFAULT NULL COMMENT '完成时间',
            `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
            `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `idx_task_id` (`task_id`),
            KEY `idx_status` (`status`),
            KEY `idx_priority` (`priority`),
            KEY `idx_assigned_agent` (`assigned_agent_id`),
            KEY `idx_type` (`type`),
            KEY `idx_created_at` (`created_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='AI增强任务表'",
        
        // 插入默认智能体数据
        "INSERT IGNORE INTO `ai_agents` (`agent_id`, `name`, `type`, `capabilities`, `status`, `performance_score`, `max_concurrent_tasks`) VALUES
        ('chat_assistant_001', '智能聊天助手', 'chat', '[\"general_conversation\", \"text_analysis\", \"content_generation\"]', 'active', 0.85, 10),
        ('data_analyst_001', '数据分析专家', 'analysis', '[\"data_analysis\", \"statistical_computing\", \"report_generation\"]', 'active', 0.90, 5),
        ('code_assistant_001', '代码助手', 'coding', '[\"code_generation\", \"code_review\", \"debugging\", \"optimization\"]', 'active', 0.88, 8),
        ('content_creator_001', '内容创作助手', 'content', '[\"content_writing\", \"creative_writing\", \"editing\", \"translation\"]', 'active', 0.82, 6),
        ('research_assistant_001', '研究助手', 'research', '[\"information_gathering\", \"document_analysis\", \"summarization\"]', 'active', 0.87, 4)"
    ];
    
    foreach ($sqlStatements as $index => $sql) {
        $statementNum = $index + 1;
        echo "执行语句 {$statementNum}...";
        
        try {
            $pdo->exec($sql);
            echo "✅ 成功\n";
        } catch (PDOException $e) {
            echo "❌ 失败: " . $e->getMessage() . "\n";
        }
    }
    
    // 验证表
    echo "\n=== 验证表结构 ===\n";
    $tables = ['ai_agents', 'ai_enhanced_tasks'];
    
    foreach ($tables as $table) {
        try {
            $stmt = $pdo->query("DESCRIBE `{$table}`");
            $columns = $stmt->fetchAll();
            echo "✅ {$table} - " . count($columns) . " 列\n";
        } catch (PDOException $e) {
            echo "❌ {$table} - 表不存在\n";
        }
    }
    
    // 检查数据
    echo "\n=== 检查智能体数据 ===\n";
    try {
        $stmt = $pdo->query("SELECT agent_id, name, type, status FROM ai_agents");
        $agents = $stmt->fetchAll();
        echo "✅ 智能体数量: " . count($agents) . "\n";
        
        foreach ($agents as $agent) {
            echo "  - {$agent['agent_id']}: {$agent['name']} ({$agent['type']}) - {$agent['status']}\n";
        }
    } catch (PDOException $e) {
        echo "❌ 无法检查智能体数据: " . $e->getMessage() . "\n";
    }
    
    echo "\n✅ AI智能体数据表创建完成！\n";
    
} catch (Exception $e) {
    echo "❌ 执行失败: " . $e->getMessage() . "\n";
}
