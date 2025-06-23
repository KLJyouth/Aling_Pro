<?php
/**
 * 文件系统数据库测试脚本
 */

// 加载必要的文件
require_once __DIR__ . '/src/Database/FileSystemDB.php';

use AlingAi\Database\FileSystemDB;

echo "=== AlingAi Pro 文件系统数据库测试 ===\n\n";

try {    // 初始化文件系统数据库
    $db = new FileSystemDB(__DIR__ . '/storage/data');
    echo "✓ 文件系统数据库初始化成功\n";
    
    // 测试插入用户数据
    $userId = $db->insert('users', [
        'username' => 'test_user',
        'email' => 'test@example.com',
        'password' => password_hash('test123', PASSWORD_DEFAULT),
        'avatar' => '/images/default-avatar.png',
        'level' => 1
    ]);
    echo "✓ 用户数据插入成功，ID: {$userId}\n";
    
    // 测试查询用户数据
    $user = $db->selectOne('users', ['id' => $userId]);
    if ($user) {
        echo "✓ 用户数据查询成功: {$user['username']} ({$user['email']})\n";
    } else {
        echo "✗ 用户数据查询失败\n";
    }
    
    // 测试插入聊天会话
    $sessionId = $db->insert('chat_sessions', [
        'user_id' => $userId,
        'title' => '测试聊天会话',
        'model' => 'deepseek-chat'
    ]);
    echo "✓ 聊天会话创建成功，ID: {$sessionId}\n";
    
    // 测试插入聊天消息
    $messageId = $db->insert('chat_messages', [
        'session_id' => $sessionId,
        'user_id' => $userId,
        'role' => 'user',
        'content' => '你好，这是一条测试消息',
        'model' => 'deepseek-chat',
        'tokens_used' => 10
    ]);
    echo "✓ 聊天消息插入成功，ID: {$messageId}\n";
    
    // 测试插入AI回复
    $responseId = $db->insert('chat_messages', [
        'session_id' => $sessionId,
        'user_id' => $userId,
        'role' => 'assistant',
        'content' => '你好！我是AlingAi Pro，很高兴为您服务。',
        'model' => 'deepseek-chat',
        'tokens_used' => 15
    ]);
    echo "✓ AI回复插入成功，ID: {$responseId}\n";
    
    // 测试查询会话中的所有消息
    $messages = $db->select('chat_messages', ['session_id' => $sessionId]);
    echo "✓ 查询到 " . count($messages) . " 条聊天消息\n";
    
    // 测试系统设置
    $db->insert('system_settings', [
        'key' => 'ai_model_default',
        'value' => 'deepseek-chat',
        'type' => 'string'
    ]);
    echo "✓ 系统设置插入成功\n";
    
    // 测试系统监控指标
    $db->insert('system_metrics', [
        'metric_type' => 'cpu',
        'metric_name' => 'cpu_usage',
        'metric_value' => 25.5,
        'metric_unit' => '%',
        'status' => 'normal'
    ]);
    echo "✓ 系统监控指标插入成功\n";
    
    // 测试AI对话记录
    $db->insert('ai_conversations', [
        'user_id' => $userId,
        'model_name' => 'deepseek-chat',
        'prompt' => '请介绍一下人工智能',
        'response' => '人工智能是计算机科学的一个分支...',
        'tokens_used' => 50,
        'response_time' => 1.23
    ]);
    echo "✓ AI对话记录插入成功\n";
    
    // 测试邮件日志
    $db->insert('email_logs', [
        'to_email' => 'test@example.com',
        'subject' => '欢迎使用AlingAi Pro',
        'body' => '感谢您注册AlingAi Pro...',
        'status' => 'sent'
    ]);
    echo "✓ 邮件日志插入成功\n";
    
    // 测试更新操作
    $updated = $db->update('users', ['id' => $userId], ['level' => 2]);
    echo "✓ 用户级别更新成功，影响 {$updated} 条记录\n";
    
    // 获取所有表的统计信息
    echo "\n=== 数据库统计信息 ===\n";
    $stats = $db->getAllTablesStats();
    foreach ($stats as $table => $stat) {
        echo "表 {$table}: {$stat['record_count']} 条记录\n";
    }
    
    // 测试备份功能
    $backupFile = $db->backup();
    echo "\n✓ 数据备份成功: {$backupFile}\n";
    
    echo "\n=== 所有测试通过！文件系统数据库工作正常 ===\n";
    
} catch (Exception $e) {
    echo "✗ 测试失败: " . $e->getMessage() . "\n";
    echo "错误位置: " . $e->getFile() . ":" . $e->getLine() . "\n";
}