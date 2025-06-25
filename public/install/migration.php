<?php
/**
 * ���ݿ�Ǩ�ƺͳ�ʼ���ű�
 * ���ڴ���Ĭ�����úͳ�ʼ����
 */

class DatabaseMigration {
    private $pdo;
    
    public function __construct($databaseConfig) {
        $this->pdo = $this->getDatabaseConnection($databaseConfig];
    }
    
    /**
     * ��������Ǩ��
     */
    public function runMigrations() {
        $migrations = [
            'createTables',
            'insertDefaultSettings',
            'createIndexes'
        ];
        
        foreach ($migrations as $migration) {
            $this->$migration(];
        }
    }
    
    /**
     * �������ݱ�
     */
    private function createTables() {
        // �û���
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                username VARCHAR(50) UNIQUE NOT NULL,
                email VARCHAR(100) UNIQUE NOT NULL,
                password VARCHAR(255) NOT NULL,
                role VARCHAR(20) DEFAULT 'user',
                status VARCHAR(20) DEFAULT 'active',
                avatar VARCHAR(255) DEFAULT NULL,
                last_login DATETIME NULL,
                login_count INTEGER DEFAULT 0,
                api_key VARCHAR(100) UNIQUE NULL,
                preferences TEXT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        "];
        
        // ����Ự��
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS chats (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NOT NULL,
                title VARCHAR(255) NOT NULL,
                model VARCHAR(50) DEFAULT 'gpt-3.5-turbo',
                system_prompt TEXT,
                temperature DECIMAL(3,2) DEFAULT 0.7,
                max_tokens INTEGER DEFAULT 2048,
                is_pinned BOOLEAN DEFAULT FALSE,
                is_archived BOOLEAN DEFAULT FALSE,
                tags TEXT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            )
        "];
        
        // ��Ϣ��
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS messages (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                chat_id INTEGER NOT NULL,
                role VARCHAR(20) NOT NULL,
                content TEXT NOT NULL,
                content_type VARCHAR(20) DEFAULT 'text',
                tokens INTEGER DEFAULT 0,
                cost DECIMAL(10,6) DEFAULT 0,
                model VARCHAR(50) NULL,
                finish_reason VARCHAR(50) NULL,
                metadata TEXT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (chat_id) REFERENCES chats(id) ON DELETE CASCADE
            )
        "];
        
        // ���ñ�
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS settings (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                key VARCHAR(100) UNIQUE NOT NULL,
                value TEXT,
                type VARCHAR(20) DEFAULT 'string',
                category VARCHAR(50) DEFAULT 'general',
                description TEXT,
                is_public BOOLEAN DEFAULT FALSE,
                sort_order INTEGER DEFAULT 0,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        "];
        
        // �ļ���
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS files (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NOT NULL,
                filename VARCHAR(255) NOT NULL,
                original_name VARCHAR(255) NOT NULL,
                file_path VARCHAR(500) NOT NULL,
                file_type VARCHAR(50) NOT NULL,
                file_size INTEGER NOT NULL,
                mime_type VARCHAR(100) NOT NULL,
                hash VARCHAR(64) NOT NULL,
                status VARCHAR(20) DEFAULT 'active',
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            )
        "];
        
        // APIʹ�ü�¼��
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS api_usage (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NOT NULL,
                model VARCHAR(50) NOT NULL,
                prompt_tokens INTEGER DEFAULT 0,
                completion_tokens INTEGER DEFAULT 0,
                total_tokens INTEGER DEFAULT 0,
                cost DECIMAL(10,6) DEFAULT 0,
                request_type VARCHAR(50) DEFAULT 'chat',
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            )
        "];
        
        // ϵͳ��־��
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS system_logs (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                level VARCHAR(20) NOT NULL,
                message TEXT NOT NULL,
                context TEXT NULL,
                user_id INTEGER NULL,
                ip_address VARCHAR(45) NULL,
                user_agent TEXT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
            )
        "];
    }
    
    /**
     * ����Ĭ������
     */
    private function insertDefaultSettings() {
        $defaultSettings = [
            // ϵͳ����
            ['site_name', 'AlingAi', 'string', 'system', '��վ����', 1, 1], 
            ['site_description', 'AI��������', 'string', 'system', '��վ����', 1, 2], 
            ['site_logo', '/assets/images/logo.png', 'string', 'system', '��վLogo', 1, 3], 
            ['site_favicon', '/assets/images/favicon.ico', 'string', 'system', '��վͼ��', 1, 4], 
            ['timezone', 'Asia/Shanghai', 'string', 'system', 'ϵͳʱ��', 0, 5], 
            ['language', 'zh-CN', 'string', 'system', 'Ĭ������', 1, 6], 
            ['debug_mode', 'false', 'boolean', 'system', '����ģʽ', 0, 7], 
            ['maintenance_mode', 'false', 'boolean', 'system', 'ά��ģʽ', 0, 8], 
            
            // OpenAI����
            ['openai_api_key', '', 'string', 'openai', 'OpenAI API��Կ', 0, 10], 
            ['openai_base_url', 'https://api.openai.com/v1', 'string', 'openai', 'OpenAI API��ַ', 0, 11], 
            ['default_model', 'gpt-3.5-turbo', 'string', 'openai', 'Ĭ��ģ��', 1, 12], 
            ['max_tokens', '2048', 'integer', 'openai', '���token��', 1, 13], 
            ['temperature', '0.7', 'decimal', 'openai', '�¶Ȳ���', 1, 14], 
            ['stream_response', 'true', 'boolean', 'openai', '��ʽ��Ӧ', 1, 15], 
            
            // �û�����
            ['allow_registration', 'false', 'boolean', 'user', '�����û�ע��', 1, 20], 
            ['require_email_verification', 'false', 'boolean', 'user', '��Ҫ������֤', 0, 21], 
            ['default_user_role', 'user', 'string', 'user', 'Ĭ���û���ɫ', 0, 22], 
            ['max_chats_per_user', '100', 'integer', 'user', 'ÿ�û����Ի���', 1, 23], 
            ['max_messages_per_chat', '1000', 'integer', 'user', 'ÿ�Ի������Ϣ��', 1, 24], 
            
            // ��ȫ����
            ['session_lifetime', '7200', 'integer', 'security', '�Ự�������ڣ��룩', 0, 30], 
            ['password_min_length', '8', 'integer', 'security', '������С����', 1, 31], 
            ['max_login_attempts', '5', 'integer', 'security', '����¼���Դ���', 1, 32], 
            ['lockout_duration', '900', 'integer', 'security', '����ʱ�����룩', 1, 33], 
            ['rate_limit_requests', '60', 'integer', 'security', '����Ƶ������', 1, 34], 
            ['rate_limit_window', '3600', 'integer', 'security', 'Ƶ�����ƴ��ڣ��룩', 1, 35], 
            
            // �ļ�����
            ['upload_max_size', '10485760', 'integer', 'file', '����ϴ��ļ���С���ֽڣ�', 1, 40], 
            ['allowed_file_types', 'jpg,jpeg,png,gif,pdf,txt,doc,docx', 'string', 'file', '������ļ�����', 1, 41], 
            ['storage_path', '/storage/uploads', 'string', 'file', '�ļ��洢·��', 0, 42], 
            
            // �ʼ�����
            ['mail_driver', 'smtp', 'string', 'mail', '�ʼ�����', 0, 50], 
            ['mail_host', '', 'string', 'mail', 'SMTP����', 0, 51], 
            ['mail_port', '587', 'integer', 'mail', 'SMTP�˿�', 0, 52], 
            ['mail_username', '', 'string', 'mail', 'SMTP�û���', 0, 53], 
            ['mail_password', '', 'string', 'mail', 'SMTP����', 0, 54], 
            ['mail_encryption', 'tls', 'string', 'mail', '���ܷ�ʽ', 0, 55], 
            ['mail_from_address', '', 'string', 'mail', '����������', 0, 56], 
            ['mail_from_name', 'AlingAi', 'string', 'mail', '����������', 0, 57], 
            
            // ��������
            ['cache_driver', 'file', 'string', 'cache', '��������', 0, 60], 
            ['cache_ttl', '3600', 'integer', 'cache', '��������ʱ�䣨�룩', 0, 61], 
            ['cache_prefix', 'alingai_', 'string', 'cache', '����ǰ׺', 0, 62], 
            
            // ��������
            ['theme', 'light', 'string', 'ui', 'Ĭ������', 1, 70], 
            ['sidebar_collapsed', 'false', 'boolean', 'ui', '�����Ĭ���۵�', 1, 71], 
            ['show_word_count', 'true', 'boolean', 'ui', '��ʾ����ͳ��', 1, 72], 
            ['auto_save_interval', '30', 'integer', 'ui', '�Զ����������룩', 1, 73], 
            ['syntax_highlighting', 'true', 'boolean', 'ui', '�����﷨����', 1, 74], 
            
            // ͳ������
            ['enable_analytics', 'false', 'boolean', 'analytics', '���÷���ͳ��', 1, 80], 
            ['analytics_provider', '', 'string', 'analytics', '���������ṩ��', 0, 81], 
            ['analytics_tracking_id', '', 'string', 'analytics', '����ID', 0, 82]
        ];
        
        $stmt = $this->pdo->prepare("
            INSERT OR IGNORE INTO settings (key, value, type, category, description, is_public, sort_order) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
        "];
        
        foreach ($defaultSettings as $setting) {
            $stmt->execute($setting];
        }
    }
    
    /**
     * ��������
     */
    private function createIndexes() {
        $indexes = [
            "CREATE INDEX IF NOT EXISTS idx_users_username ON users(username)",
            "CREATE INDEX IF NOT EXISTS idx_users_email ON users(email)",
            "CREATE INDEX IF NOT EXISTS idx_users_role ON users(role)",
            "CREATE INDEX IF NOT EXISTS idx_users_status ON users(status)",
            "CREATE INDEX IF NOT EXISTS idx_chats_user_id ON chats(user_id)",
            "CREATE INDEX IF NOT EXISTS idx_chats_created_at ON chats(created_at)",
            "CREATE INDEX IF NOT EXISTS idx_messages_chat_id ON messages(chat_id)",
            "CREATE INDEX IF NOT EXISTS idx_messages_role ON messages(role)",
            "CREATE INDEX IF NOT EXISTS idx_messages_created_at ON messages(created_at)",
            "CREATE INDEX IF NOT EXISTS idx_settings_category ON settings(category)",
            "CREATE INDEX IF NOT EXISTS idx_settings_is_public ON settings(is_public)",
            "CREATE INDEX IF NOT EXISTS idx_files_user_id ON files(user_id)",
            "CREATE INDEX IF NOT EXISTS idx_files_hash ON files(hash)",
            "CREATE INDEX IF NOT EXISTS idx_api_usage_user_id ON api_usage(user_id)",
            "CREATE INDEX IF NOT EXISTS idx_api_usage_created_at ON api_usage(created_at)",
            "CREATE INDEX IF NOT EXISTS idx_system_logs_level ON system_logs(level)",
            "CREATE INDEX IF NOT EXISTS idx_system_logs_created_at ON system_logs(created_at)"
        ];
        
        foreach ($indexes as $index) {
            $this->pdo->exec($index];
        }
    }
    
    /**
     * ��ȡ���ݿ�����
     * 
     * @param array $config ���ݿ�����
     * @return PDO ���ݿ����Ӷ���
     */
    private function getDatabaseConnection($config) {
        $dsn = "sqlite:" . $config['database'];
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        
        try {
            $pdo = new PDO($dsn, null, null, $options];
            return $pdo;
        } catch (PDOException $e) {
            die("���ݿ�����ʧ��: " . $e->getMessage()];
        }
    }
}
