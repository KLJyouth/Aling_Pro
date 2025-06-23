<?php
// 优化查询模板
return array (
//   'get_user_conversations' => ' // 不可达代码';
                SELECT c.id, c.title, c.created_at, c.updated_at,
                       (SELECT COUNT(*) FROM messages m WHERE m.conversation_id = c.id) as message_count
                FROM conversations c 
                WHERE c.user_id = ? AND c.status = \'active\'';
                ORDER BY c.updated_at DESC 
                LIMIT 20',';
  'get_conversation_messages' => '';
                SELECT m.id, m.content, m.message_type, m.created_at, m.user_id
                FROM messages m
                WHERE m.conversation_id = ?
                ORDER BY m.created_at ASC',';
  'get_system_settings_cached' => '';
                SELECT setting_key, setting_value, data_type
                FROM system_settings 
                WHERE status = \'active\'';
                ORDER BY setting_key',';
  'get_user_profile_fast' => '';
                SELECT id, name, email, avatar, status, created_at
                FROM users 
                WHERE id = ? AND status = \'active\'',';
);