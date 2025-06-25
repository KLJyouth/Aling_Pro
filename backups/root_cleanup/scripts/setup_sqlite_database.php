<?php
/**
 * Simple SQLite Database Migration Runner
 * 
 * Creates essential tables for the AlingAi Pro system using SQLite
 */

try {
    echo "Starting SQLite database setup...\n";
    
    // Create database directory if it doesn't exist
    $dbDir = __DIR__ . '/../database';
    if (!is_dir($dbDir)) {
        mkdir($dbDir, 0755, true);
    }
    
    $dbPath = $dbDir . '/alingai_pro.sqlite';
    
    // Create SQLite connection
    $pdo = new PDO("sqlite:{$dbPath}");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Connected to SQLite database: {$dbPath}\n";
    
    // Create migrations table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS migrations (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            migration VARCHAR(255) NOT NULL UNIQUE,
            batch INTEGER NOT NULL,
            executed_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ");
    
    echo "✅ Migrations table created\n";
    
    // Define all tables to create
    $tables = [
        'users' => "
            CREATE TABLE IF NOT EXISTS users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                username VARCHAR(255) UNIQUE NOT NULL,
                email VARCHAR(255) UNIQUE NOT NULL,
                password_hash VARCHAR(255) NOT NULL,
                role VARCHAR(50) DEFAULT 'user',
                avatar_url VARCHAR(500),
                first_name VARCHAR(100),
                last_name VARCHAR(100),
                is_active BOOLEAN DEFAULT 1,
                is_verified BOOLEAN DEFAULT 0,
                email_verified_at DATETIME,
                phone VARCHAR(20),
                timezone VARCHAR(50) DEFAULT 'UTC',
                language VARCHAR(10) DEFAULT 'en',
                theme VARCHAR(20) DEFAULT 'light',
                notifications_enabled BOOLEAN DEFAULT 1,
                marketing_emails BOOLEAN DEFAULT 0,
                last_login_at DATETIME,
                login_count INTEGER DEFAULT 0,
                failed_login_attempts INTEGER DEFAULT 0,
                locked_until DATETIME,
                password_reset_token VARCHAR(255),
                password_reset_expires DATETIME,
                remember_token VARCHAR(255),
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                deleted_at DATETIME
            )
        ",
        
        'user_sessions' => "
            CREATE TABLE IF NOT EXISTS user_sessions (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NOT NULL,
                session_token VARCHAR(255) NOT NULL UNIQUE,
                ip_address VARCHAR(45),
                user_agent TEXT,
                last_activity DATETIME DEFAULT CURRENT_TIMESTAMP,
                expires_at DATETIME NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            )
        ",
        
        'api_tokens' => "
            CREATE TABLE IF NOT EXISTS api_tokens (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NOT NULL,
                name VARCHAR(255) NOT NULL,
                token_hash VARCHAR(255) NOT NULL UNIQUE,
                abilities TEXT,
                last_used_at DATETIME,
                expires_at DATETIME,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            )
        ",
        
        'chat_sessions' => "
            CREATE TABLE IF NOT EXISTS chat_sessions (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NOT NULL,
                title VARCHAR(255),
                model VARCHAR(100) DEFAULT 'gpt-3.5-turbo',
                system_prompt TEXT,
                temperature DECIMAL(3,2) DEFAULT 0.7,
                max_tokens INTEGER DEFAULT 2048,
                is_active BOOLEAN DEFAULT 1,
                metadata TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            )
        ",
        
        'chat_messages' => "
            CREATE TABLE IF NOT EXISTS chat_messages (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                session_id INTEGER NOT NULL,
                user_id INTEGER NOT NULL,
                role VARCHAR(20) NOT NULL,
                content TEXT NOT NULL,
                tokens_used INTEGER,
                model VARCHAR(100),
                metadata TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (session_id) REFERENCES chat_sessions(id) ON DELETE CASCADE,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            )
        ",
        
        'files' => "
            CREATE TABLE IF NOT EXISTS files (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NOT NULL,
                original_name VARCHAR(255) NOT NULL,
                stored_name VARCHAR(255) NOT NULL UNIQUE,
                mime_type VARCHAR(100),
                size INTEGER NOT NULL,
                extension VARCHAR(10),
                path VARCHAR(500),
                is_public BOOLEAN DEFAULT 0,
                download_count INTEGER DEFAULT 0,
                metadata TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                deleted_at DATETIME,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            )
        ",
        
        'file_shares' => "
            CREATE TABLE IF NOT EXISTS file_shares (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                file_id INTEGER NOT NULL,
                user_id INTEGER NOT NULL,
                share_token VARCHAR(255) NOT NULL UNIQUE,
                expires_at DATETIME,
                download_limit INTEGER,
                download_count INTEGER DEFAULT 0,
                require_auth BOOLEAN DEFAULT 0,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (file_id) REFERENCES files(id) ON DELETE CASCADE,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            )
        ",
        
        'system_settings' => "
            CREATE TABLE IF NOT EXISTS system_settings (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                key VARCHAR(255) NOT NULL UNIQUE,
                value TEXT,
                type VARCHAR(50) DEFAULT 'string',
                description TEXT,
                is_public BOOLEAN DEFAULT 0,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ",
        
        'activity_logs' => "
            CREATE TABLE IF NOT EXISTS activity_logs (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER,
                action VARCHAR(100) NOT NULL,
                description TEXT,
                ip_address VARCHAR(45),
                user_agent TEXT,
                metadata TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
            )
        ",
        
        'error_logs' => "
            CREATE TABLE IF NOT EXISTS error_logs (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                level VARCHAR(20) NOT NULL,
                message TEXT NOT NULL,
                context TEXT,
                file VARCHAR(500),
                line INTEGER,
                trace TEXT,
                user_id INTEGER,
                ip_address VARCHAR(45),
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
            )
        ",
        
        'performance_metrics' => "
            CREATE TABLE IF NOT EXISTS performance_metrics (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                metric_name VARCHAR(100) NOT NULL,
                value DECIMAL(10,4) NOT NULL,
                unit VARCHAR(20),
                metadata TEXT,
                recorded_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ",
        
        'rate_limits' => "
            CREATE TABLE IF NOT EXISTS rate_limits (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                identifier VARCHAR(255) NOT NULL,
                action VARCHAR(100) NOT NULL,
                attempts INTEGER DEFAULT 1,
                reset_time DATETIME NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                UNIQUE(identifier, action)
            )
        "
    ];
    
    // Execute table creation
    $createdTables = [];
    $batch = 1;
    
    foreach ($tables as $tableName => $sql) {
        try {
            $pdo->exec($sql);
            $createdTables[] = $tableName;
            
            // Record migration
            $migrationName = "create_{$tableName}_table";
            $pdo->prepare("INSERT OR IGNORE INTO migrations (migration, batch) VALUES (?, ?)")
                ->execute([$migrationName, $batch]);
            
            echo "✅ Created table: {$tableName}\n";
        } catch (PDOException $e) {
            echo "❌ Failed to create table {$tableName}: " . $e->getMessage() . "\n";
        }
    }
    
    // Create indexes for better performance
    $indexes = [
        "CREATE INDEX IF NOT EXISTS idx_users_email ON users(email)",
        "CREATE INDEX IF NOT EXISTS idx_users_username ON users(username)",
        "CREATE INDEX IF NOT EXISTS idx_users_role ON users(role)",
        "CREATE INDEX IF NOT EXISTS idx_user_sessions_user_id ON user_sessions(user_id)",
        "CREATE INDEX IF NOT EXISTS idx_user_sessions_token ON user_sessions(session_token)",
        "CREATE INDEX IF NOT EXISTS idx_chat_sessions_user_id ON chat_sessions(user_id)",
        "CREATE INDEX IF NOT EXISTS idx_chat_messages_session_id ON chat_messages(session_id)",
        "CREATE INDEX IF NOT EXISTS idx_chat_messages_user_id ON chat_messages(user_id)",
        "CREATE INDEX IF NOT EXISTS idx_files_user_id ON files(user_id)",
        "CREATE INDEX IF NOT EXISTS idx_activity_logs_user_id ON activity_logs(user_id)",
        "CREATE INDEX IF NOT EXISTS idx_activity_logs_action ON activity_logs(action)",
        "CREATE INDEX IF NOT EXISTS idx_error_logs_level ON error_logs(level)",
        "CREATE INDEX IF NOT EXISTS idx_performance_metrics_name ON performance_metrics(metric_name)",
        "CREATE INDEX IF NOT EXISTS idx_rate_limits_identifier ON rate_limits(identifier, action)"
    ];
    
    foreach ($indexes as $indexSql) {
        try {
            $pdo->exec($indexSql);
        } catch (PDOException $e) {
            echo "⚠️  Index creation warning: " . $e->getMessage() . "\n";
        }
    }
    
    echo "✅ Database indexes created\n";
    
    // Insert default system settings
    $defaultSettings = [
        ['app_name', 'AlingAi Pro', 'string', 'Application name', 1],
        ['app_version', '2.0.0', 'string', 'Application version', 1],
        ['maintenance_mode', '0', 'boolean', 'Maintenance mode status', 1],
        ['registration_enabled', '1', 'boolean', 'User registration enabled', 1],
        ['max_file_size', '10485760', 'integer', 'Maximum file upload size in bytes', 0],
        ['allowed_file_types', 'jpg,jpeg,png,gif,pdf,txt,doc,docx', 'string', 'Allowed file types for upload', 0],
        ['cache_enabled', '1', 'boolean', 'System cache enabled', 0],
        ['debug_mode', '0', 'boolean', 'Debug mode enabled', 0]
    ];
    
    $stmt = $pdo->prepare("INSERT OR IGNORE INTO system_settings (key, value, type, description, is_public) VALUES (?, ?, ?, ?, ?)");
    foreach ($defaultSettings as $setting) {
        $stmt->execute($setting);
    }
    
    echo "✅ Default system settings inserted\n";
    
    // Create default admin user if it doesn't exist
    $adminExists = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'admin'")->fetchColumn();
    
    if ($adminExists == 0) {
        $adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
        $pdo->prepare("
            INSERT INTO users (username, email, password_hash, role, is_active, is_verified, email_verified_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ")->execute([
            'admin',
            'admin@alingai.pro',
            $adminPassword,
            'admin',
            1,
            1,
            date('Y-m-d H:i:s')
        ]);
        
        echo "✅ Default admin user created (username: admin, password: admin123)\n";
    }
    
    // Show final statistics
    $tableCount = count($createdTables);
    $userCount = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $migrationCount = $pdo->query("SELECT COUNT(*) FROM migrations")->fetchColumn();
    
    echo "\n📊 Database Setup Summary:\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "✅ Tables created: {$tableCount}\n";
    echo "✅ Users: {$userCount}\n";
    echo "✅ Migrations recorded: {$migrationCount}\n";
    echo "✅ Database file: {$dbPath}\n";
    echo "✅ Database size: " . round(filesize($dbPath) / 1024, 2) . " KB\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "\n🎉 Database setup completed successfully!\n";
    echo "\n📝 Next steps:\n";
    echo "   1. Update your config to use SQLite database\n";
    echo "   2. Test the application with the new database\n";
    echo "   3. Login with admin credentials (admin/admin123)\n";
    
} catch (Exception $e) {
    echo "❌ Database setup failed: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
