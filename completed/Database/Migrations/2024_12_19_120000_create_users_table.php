<?php

declare(strict_types=1);

use PDO;

/**
 * Migration: Migration_2024_12_19_120000_CreateUsersTable
 * 
 * 创建用户表
 */
/**
 * Migration_2024_12_19_120000_CreateUsersTable 类
 *
 * @package 
 */
class Migration_2024_12_19_120000_CreateUsersTable
{
    private PDO $db;

    /**


     * __construct 方法


     *


     * @param PDO $db


     * @return void


     */


    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Run the migration
     */
    /**

     * up 方法

     *

     * @return void

     */

    public function up(): void
    {
        $sql = "
            CREATE TABLE users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(50) NOT NULL UNIQUE,
                email VARCHAR(255) NOT NULL UNIQUE,
                password VARCHAR(255) NOT NULL,
                role ENUM('user', 'admin', 'moderator') DEFAULT 'user',
                status ENUM('active', 'inactive', 'pending', 'suspended') DEFAULT 'pending',
                avatar VARCHAR(255) NULL,
                bio TEXT NULL,
                preferences JSON NULL,
                permissions JSON NULL,
                verification_token VARCHAR(255) NULL,
                reset_token VARCHAR(255) NULL,
                reset_token_expires_at TIMESTAMP NULL,
                email_verified_at TIMESTAMP NULL,
                last_login_at TIMESTAMP NULL,
                login_count INT DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                deleted_at TIMESTAMP NULL,
                INDEX idx_email (email),
                INDEX idx_username (username),
                INDEX idx_status (status),
                INDEX idx_role (role),
                INDEX idx_verification_token (verification_token),
                INDEX idx_reset_token (reset_token),
                INDEX idx_created_at (created_at),
                INDEX idx_deleted_at (deleted_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ";
        
        $this->db->exec($sql);
    }

    /**
     * Reverse the migration
     */
    /**

     * down 方法

     *

     * @return void

     */

    public function down(): void
    {
        $this->db->exec("DROP TABLE IF EXISTS users");
    }
}
