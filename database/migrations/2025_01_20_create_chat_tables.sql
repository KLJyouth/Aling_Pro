-- 聊天系统数据库表结构
-- 创建时间: 2025-01-20
-- 版本: 2.0.0

-- 会话表
CREATE TABLE IF NOT EXISTS conversations (
    id VARCHAR(36) PRIMARY KEY COMMENT '会话唯一标识符(UUID)',
    user_id INT NOT NULL COMMENT '用户ID',
    title VARCHAR(255) NOT NULL DEFAULT '新的对话' COMMENT '会话标题',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
    
    INDEX idx_user_id (user_id),
    INDEX idx_updated_at (updated_at),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='聊天会话表';

-- 消息表
CREATE TABLE IF NOT EXISTS messages (
    id INT AUTO_INCREMENT PRIMARY KEY COMMENT '消息ID',
    conversation_id VARCHAR(36) NOT NULL COMMENT '会话ID',
    role ENUM('user', 'assistant', 'system') NOT NULL COMMENT '消息角色',
    content TEXT NOT NULL COMMENT '消息内容',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    
    INDEX idx_conversation_id (conversation_id),
    INDEX idx_created_at (created_at),
    INDEX idx_role (role),
    FOREIGN KEY (conversation_id) REFERENCES conversations(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='聊天消息表';

-- 使用统计表
CREATE TABLE IF NOT EXISTS usage_stats (
    id INT AUTO_INCREMENT PRIMARY KEY COMMENT '统计ID',
    user_id INT NOT NULL COMMENT '用户ID',
    conversation_id VARCHAR(36) NOT NULL COMMENT '会话ID',
    tokens_used INT NOT NULL DEFAULT 0 COMMENT '使用的令牌数',
    model VARCHAR(100) NOT NULL COMMENT '使用的模型',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    
    INDEX idx_user_id (user_id),
    INDEX idx_conversation_id (conversation_id),
    INDEX idx_created_at (created_at),
    INDEX idx_model (model),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (conversation_id) REFERENCES conversations(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='AI使用统计表';

-- 插入示例数据（可选）
-- INSERT INTO conversations (id, user_id, title) VALUES 
-- ('550e8400-e29b-41d4-a716-446655440000', 1, '示例对话');

-- INSERT INTO messages (conversation_id, role, content) VALUES 
-- ('550e8400-e29b-41d4-a716-446655440000', 'user', '你好，请介绍一下你自己'),
-- ('550e8400-e29b-41d4-a716-446655440000', 'assistant', '你好！我是AlingAi Pro的智能助手，很高兴为您服务。我可以帮助您解答问题、提供建议、协助编程等。有什么我可以帮助您的吗？'); 