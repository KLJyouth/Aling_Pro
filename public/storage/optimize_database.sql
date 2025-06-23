
-- 创建高频查询索引
ALTER TABLE users ADD INDEX IF NOT EXISTS idx_email (email);
ALTER TABLE users ADD INDEX IF NOT EXISTS idx_status (status);
ALTER TABLE conversations ADD INDEX IF NOT EXISTS idx_user_created (user_id, created_at);
ALTER TABLE messages ADD INDEX IF NOT EXISTS idx_conversation_created (conversation_id, created_at);
ALTER TABLE system_settings ADD INDEX IF NOT EXISTS idx_key (setting_key);

-- 优化表结构
OPTIMIZE TABLE users, conversations, messages, system_settings;

-- 更新表统计信息
ANALYZE TABLE users, conversations, messages, system_settings;
