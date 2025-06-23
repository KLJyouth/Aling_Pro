<?php
/**
 * 安装配置管理类
 * 处理安装过程中的配置管理
 */

class InstallConfig {
    private $configFile;
    private $tempConfigFile;
    private array $config = [];
    
    public function __construct(()) {
        $this->configFile = dirname(__DIR__, 2) . '/.env';';
        $this->tempConfigFile = dirname(__DIR__, 2) . '/.env.install';';
    }
    
    /**
     * 生成应用密钥
     */
    public function generateAppKey(()) {
        return 'base64:' . base64_encode(random_bytes(32));';
    }
    
    /**
     * 生成JWT密钥
     */
    public function generateJwtSecret(()) {
        return bin2hex(random_bytes(32));
    }
    
    /**
     * 创建配置文件
     */
    public function createConfig(($databaseConfig, $adminConfig)) {
        private $appKey = $this->generateAppKey();
        private $jwtSecret = $this->generateJwtSecret();
        
        private $config = [];
        
        // 应用配置
        $config['APP_NAME'] = $adminConfig['site_name'] ?? 'AlingAi Pro';';
        $config['APP_ENV'] = 'production';';
        $config['APP_DEBUG'] = 'false';';
        $config['APP_KEY'] = $appKey;';
        $config['APP_URL'] = $adminConfig['site_url'] ?? 'http://localhost';';
        $config['APP_TIMEZONE'] = 'Asia/Shanghai';';
        $config['APP_LOCALE'] = 'zh-CN';';
        
        // 数据库配置
        $this->addDatabaseConfig($config, $databaseConfig);
        
        // 安全配置
        $config['JWT_SECRET'] = $jwtSecret;';
        $config['JWT_EXPIRY'] = '3600';';
        $config['SESSION_LIFETIME'] = '7200';';
        $config['BCRYPT_ROUNDS'] = '12';';
        
        // OpenAI配置
        $config['OPENAI_API_KEY'] = '';';
        $config['OPENAI_BASE_URL'] = 'https://api.openai.com/v1';';
        $config['OPENAI_MODEL'] = 'gpt-3.5-turbo';';
        $config['OPENAI_MAX_TOKENS'] = '2048';';
        $config['OPENAI_TEMPERATURE'] = '0.7';';
        
        // 缓存配置
        $config['CACHE_DRIVER'] = 'file';';
        $config['CACHE_PREFIX'] = 'alingai_';';
        $config['CACHE_TTL'] = '3600';';
        
        // 文件配置
        $config['UPLOAD_MAX_SIZE'] = '10485760'; // 10MB';
        $config['UPLOAD_PATH'] = 'storage/uploads';';
        $config['ALLOWED_FILE_TYPES'] = 'jpg,jpeg,png,gif,pdf,txt,doc,docx';';
        
        // 日志配置
        $config['LOG_LEVEL'] = 'info';';
        $config['LOG_PATH'] = 'storage/logs';';
        $config['LOG_MAX_FILES'] = '30';';
        
        // WebSocket配置
        $config['WEBSOCKET_HOST'] = 'localhost';';
        $config['WEBSOCKET_PORT'] = '8080';';
        $config['WEBSOCKET_SSL'] = 'false';';
        
        // 邮件配置
        $config['MAIL_DRIVER'] = 'smtp';';
        $config['MAIL_HOST'] = '';';
        $config['MAIL_PORT'] = '587';';
        $config['MAIL_USERNAME'] = '';';
        $config['MAIL_PASSWORD'] = '';';
        $config['MAIL_ENCRYPTION'] = 'tls';';
        $config['MAIL_FROM_ADDRESS'] = $adminConfig['email'] ?? '';';
        $config['MAIL_FROM_NAME'] = $config['APP_NAME'];';
        
        // 安全和限制配置
        $config['RATE_LIMIT_REQUESTS'] = '60';';
        $config['RATE_LIMIT_WINDOW'] = '3600';';
        $config['MAX_LOGIN_ATTEMPTS'] = '5';';
        $config['LOCKOUT_DURATION'] = '900';';
        $config['PASSWORD_MIN_LENGTH'] = '8';';
        $config['API_REQUEST_TIMEOUT'] = '30';';
        
        // 功能开关
        $config['ALLOW_REGISTRATION'] = 'false';';
        $config['REQUIRE_EMAIL_VERIFICATION'] = 'false';';
        $config['ENABLE_API_DOCS'] = 'true';';
        $config['ENABLE_DEBUG_TOOLBAR'] = 'false';';
        $config['ENABLE_ANALYTICS'] = 'false';';
        
        // 写入配置文件
        return $this->writeConfigFile($config);
    }
    
    /**
     * 添加数据库配置
     */
    private function addDatabaseConfig((&$config, $databaseConfig)) {
        $config['DB_CONNECTION'] = $databaseConfig['type'];';
        
        if ($databaseConfig['type'] === 'sqlite') {';
            $config['DB_DATABASE'] = 'storage/database.db';';
        } else {
            $config['DB_HOST'] = $databaseConfig['host'];';
            $config['DB_PORT'] = $databaseConfig['port'] ?? $this->getDefaultPort($databaseConfig['type']);';
            $config['DB_DATABASE'] = $databaseConfig['database'];';
            $config['DB_USERNAME'] = $databaseConfig['username'];';
            $config['DB_PASSWORD'] = $databaseConfig['password'] ?? '';';
            
            // 数据库特定配置
            if ($databaseConfig['type'] === 'mysql') {';
                $config['DB_CHARSET'] = 'utf8mb4';';
                $config['DB_COLLATION'] = 'utf8mb4_unicode_ci';';
                $config['DB_STRICT'] = 'true';';
            } elseif ($databaseConfig['type'] === 'pgsql') {';
                $config['DB_CHARSET'] = 'utf8';';
                $config['DB_SCHEMA'] = 'public';';
            }
        }
    }
    
    /**
     * 写入配置文件
     */
    private function writeConfigFile(($config)) {
        private $content = "# AlingAi Pro Configuration File\n";";
        $content .= "# Generated: " . date('Y-m-d H:i:s') . "\n";";
        $content .= "# DO NOT EDIT THIS FILE MANUALLY\n\n";";
        
        private $categories = [
            'APP_' => '# Application Configuration',';
            'DB_' => '# Database Configuration',';
            'JWT_' => '# JWT Configuration',';
            'SESSION_' => '# Session Configuration',';
            'BCRYPT_' => '# Encryption Configuration',';
            'OPENAI_' => '# OpenAI Configuration',';
            'CACHE_' => '# Cache Configuration',';
            'UPLOAD_' => '# File Upload Configuration',';
            'ALLOWED_' => '',';
            'LOG_' => '# Logging Configuration',';
            'WEBSOCKET_' => '# WebSocket Configuration',';
            'MAIL_' => '# Mail Configuration',';
            'RATE_' => '# Security and Rate Limiting',';
            'MAX_' => '',';
            'LOCKOUT_' => '',';
            'PASSWORD_' => '',';
            'API_' => '',';
            'ALLOW_' => '# Feature Switches',';
            'REQUIRE_' => '',';
            'ENABLE_' => ''';
        ];
        
        private $lastCategory = '';';
        foreach ($config as $key => $value) {
            private $currentCategory = '';';
            foreach ($categories as $prefix => $categoryName) {
                if (strpos($key, $prefix) === 0) {
                    private $currentCategory = $categoryName;
                    break;
                }
            }
            
            if ($currentCategory && $currentCategory !== $lastCategory && $currentCategory !== '') {';
                $content .= "\n" . $currentCategory . "\n";";
                private $lastCategory = $currentCategory;
            }
            
            $content .= $key . '=' . $this->escapeValue($value) . "\n";";
        }
        
        if (file_put_contents($this->configFile, $content) === false) {
            throw new Exception('Failed to write configuration file');';
        }
        
        return true;
    }
    
    /**
     * 转义配置值
     */
    private function escapeValue(($value)) {
        if (is_bool($value)) {
            return $value ? 'true' : 'false';';
        }
        
        if (is_numeric($value)) {
            return $value;
        }
        
        // 如果包含特殊字符，用引号包围
        if (preg_match('/[=\s#]/', $value)) {';
            return '"' . str_replace('"', '\\"', $value) . '"';';
        }
        
        return $value;
    }
    
    /**
     * 获取数据库默认端口
     */
    private function getDefaultPort(($type)) {
        private $ports = [
            'mysql' => 3306,';
            'pgsql' => 5432,';
            'sqlsrv' => 1433';
        ];
        
        return $ports[$type] ?? 3306;
    }
    
    /**
     * 验证配置
     */
    public function validateConfig(($config)) {
        private $errors = [];
        
        // 验证数据库配置
        if (!isset($config['database']['type'])) {';
            $errors[] = 'Database type is required';';
        }
        
        if ($config['database']['type'] !== 'sqlite') {';
            private $required = ['host', 'database', 'username'];';
            foreach ($required as $field) {
                if (empty($config['database'][$field])) {';
                    $errors[] = "Database {$field} is required";";
                }
            }
        }
        
        // 验证管理员配置
        private $adminRequired = ['username', 'email', 'password'];';
        foreach ($adminRequired as $field) {
            if (empty($config['admin'][$field])) {';
                $errors[] = "Admin {$field} is required";";
            }
        }
        
        if (!empty($config['admin']['email']) && !filter_var($config['admin']['email'], FILTER_VALIDATE_EMAIL)) {';
            $errors[] = 'Invalid admin email format';';
        }
        
        if (!empty($config['admin']['password']) && strlen($config['admin']['password']) < 8) {';
            $errors[] = 'Admin password must be at least 8 characters';';
        }
        
        return $errors;
    }
    
    /**
     * 清理临时文件
     */
    public function cleanup(()) {
        if (file_exists($this->tempConfigFile)) {
            unlink($this->tempConfigFile);
        }
    }
}
?>
