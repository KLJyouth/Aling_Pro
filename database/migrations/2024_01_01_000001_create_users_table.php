<?php
/**
 * 创建用户表迁移
 * 
 * @package AlingAi\Database\Migrations
 */

declare(strict_types=1);

use AlingAi\Services\DatabaseService;

class CreateUsersTable
{
    private DatabaseService $db;
    
    public function __construct(DatabaseService $db)
    {
        $this->db = $db;
    }
    
    public function up(): void
    {
        $sql = "
            CREATE TABLE IF NOT EXISTS `users` (
                `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `name` varchar(100) NOT NULL COMMENT '用户姓名',
                `email` varchar(255) NOT NULL COMMENT '邮箱地址',
                `email_verified_at` timestamp NULL DEFAULT NULL COMMENT '邮箱验证时间',
                `password` varchar(255) NOT NULL COMMENT '密码哈希',
                `role` enum('user','admin','moderator') NOT NULL DEFAULT 'user' COMMENT '用户角色',
                `avatar` varchar(500) NULL DEFAULT NULL COMMENT '头像URL',
                `status` enum('active','inactive','banned') NOT NULL DEFAULT 'active' COMMENT '用户状态',
                `is_active` tinyint(1) NOT NULL DEFAULT 1 COMMENT '是否激活',
                `last_login_at` timestamp NULL DEFAULT NULL COMMENT '最后登录时间',
                `login_count` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '登录次数',
                `settings` json NULL COMMENT '用户设置',
                `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
                `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
                PRIMARY KEY (`id`),
                UNIQUE KEY `users_email_unique` (`email`),
                KEY `users_role_index` (`role`),
                KEY `users_status_index` (`status`),
                KEY `users_created_at_index` (`created_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='用户表';
        ";
        
        $this->db->execute($sql);
        
        // 创建默认管理员用户
        $this->createDefaultAdmin();
    }
    
    public function down(): void
    {
        $sql = "DROP TABLE IF EXISTS `users`";
        $this->db->execute($sql);
    }
    
    private function createDefaultAdmin(): void
    {
        // 检查是否已存在管理员用户
        $existingAdmin = $this->db->query("SELECT id FROM users WHERE role = 'admin' LIMIT 1");
        if (!empty($existingAdmin)) {
            return;
        }
        
        $sql = "
            INSERT INTO `users` (
                `name`, 
                `email`, 
                `password`, 
                `role`, 
                `email_verified_at`, 
                `is_active`,
                `created_at`,
                `updated_at`
            ) VALUES (
                'Administrator',
                'admin@alingai.com',
                :password,
                'admin',
                NOW(),
                1,
                NOW(),
                NOW()
            )
        ";
        
        $this->db->execute($sql, [
            'password' => password_hash('admin123', PASSWORD_ARGON2ID)
        ]);
    }
}
