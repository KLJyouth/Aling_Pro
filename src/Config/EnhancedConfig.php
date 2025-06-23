<?php

namespace AlingAi\Config;

/**
 * 增强配置管理器
 * 支持环境变量、配置验证、默认值和类型转换
 */
class EnhancedConfig
{
    private static $instance = null;
    private $config = [];
    private $loaded = false;

    private function __construct()
    {
        $this->loadEnvironmentConfig();
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * 加载环境配置
     */
    private function loadEnvironmentConfig(): void
    {
        if ($this->loaded) {
            return;
        }

        // 加载 .env 文件
        $envPath = dirname(__DIR__, 2) . '/.env';
        if (file_exists($envPath)) {
            $this->loadEnvFile($envPath);
        }

        // 基础配置
        $this->config = [
            // 应用配置
            'app' => [
                'name' => $this->env('APP_NAME', 'AlingAi Pro'),
                'env' => $this->env('APP_ENV', 'development'),
                'debug' => $this->env('APP_DEBUG', true, 'boolean'),
                'url' => $this->env('APP_URL', 'http://localhost:3000'),
                'timezone' => $this->env('APP_TIMEZONE', 'Asia/Shanghai'),
                'locale' => $this->env('APP_LOCALE', 'zh_CN'),
                'key' => $this->env('APP_KEY'),
            ],

            // Node.js 配置
            'node' => [
                'env' => $this->env('NODE_ENV', 'development'),
                'port' => $this->env('PORT', 3000, 'integer'),
            ],

            // 数据库配置
            'database' => [
                'mysql' => [
                    'host' => $this->env('MYSQL_HOST', '111.180.205.70'),
                    'port' => $this->env('MYSQL_PORT', 3306, 'integer'),
                    'database' => $this->env('MYSQL_DATABASE', 'alingai'),
                    'username' => $this->env('MYSQL_USER', 'AlingAi'),
                    'password' => $this->env('MYSQL_PASSWORD'),
                    'charset' => 'utf8mb4',
                    'collation' => 'utf8mb4_unicode_ci',
                    'options' => [
                        \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                        \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                        \PDO::ATTR_EMULATE_PREPARES => false,
                        \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
                    ]
                ],
                'mongodb' => [
                    'uri' => $this->env('MONGODB_URI', 'mongodb://Ai:168KLJyouth.@111.180.205.70:27017/Ai'),
                ],
                'connection' => $this->env('DB_CONNECTION', 'mysql'),
                'prefix' => $this->env('DB_PREFIX', ''),
            ],

            // Redis 配置
            'redis' => [
                'host' => $this->env('REDIS_HOST', '127.0.0.1'),
                'port' => $this->env('REDIS_PORT', 6379, 'integer'),
                'password' => $this->env('REDIS_PASSWORD'),
                'database' => $this->env('REDIS_DB', 0, 'integer'),
                'prefix' => $this->env('REDIS_PREFIX', 'alingai_pro:'),
            ],

            // JWT 配置
            'jwt' => [
                'secret' => $this->env('JWT_SECRET', '3f8d!@^kLz9$2xQw7pL0vB1nM4rT6yUe'),
                'expire' => $this->env('JWT_EXPIRE', '7d'),
                'ttl' => $this->env('JWT_TTL', 3600, 'integer'),
                'refresh_ttl' => $this->env('JWT_REFRESH_TTL', 604800, 'integer'),
                'leeway' => $this->env('JWT_LEEWAY', 60, 'integer'),
                'issuer' => $this->env('JWT_ISSUER', 'alingai-pro'),
                'audience' => $this->env('JWT_AUDIENCE', 'alingai-pro-users'),
            ],

            // 速率限制
            'rate_limit' => [
                'window' => $this->env('RATE_LIMIT_WINDOW', 15, 'integer'),
                'max' => $this->env('RATE_LIMIT_MAX', 100, 'integer'),
                'per_minute' => $this->env('API_RATE_LIMIT_PER_MINUTE', 100, 'integer'),
                'per_hour' => $this->env('API_RATE_LIMIT_PER_HOUR', 2000, 'integer'),
            ],

            // AI API 配置
            'ai' => [
                'deepseek' => [
                    'api_key' => $this->env('DEEPSEEK_API_KEY'),
                    'api_url' => $this->env('OPENAI_API_URL', 'https://api.deepseek.com/v1'),
                    'model' => $this->env('OPENAI_MODEL', 'deepseek-chat'),
                    'max_tokens' => $this->env('OPENAI_MAX_TOKENS', 2048, 'integer'),
                    'temperature' => $this->env('OPENAI_TEMPERATURE', 0.7, 'float'),
                ],
                'baidu' => [
                    'mcp_endpoint' => $this->env('MCP_ENDPOINT'),
                    'auth_token' => $this->env('AGENT_AUTH_TOKEN'),
                    'api_id' => $this->env('API_ID'),
                    'app_id' => $this->env('BAIDU_APP_ID'),
                    'secret_key' => $this->env('BAIDU_SECRET_KEY'),
                    'api_key' => $this->env('BAIDU_API_KEY'),
                ],
            ],

            // 邮件配置
            'mail' => [
                'driver' => $this->env('MAIL_DRIVER', 'smtp'),
                'smtp' => [
                    'host' => $this->env('SMTP_HOST', 'smtp.exmail.qq.com'),
                    'port' => $this->env('SMTP_PORT', 465, 'integer'),
                    'secure' => $this->env('SMTP_SECURE', 'SSL'),
                    'user' => $this->env('SMTP_USER', 'admin@gxggm.com'),
                    'password' => $this->env('SMTP_PASS'),
                ],
                'from' => [
                    'address' => $this->env('SMTP_FROM', 'admin@gxggm.com'),
                    'name' => $this->env('MAIL_FROM_NAME', 'AlingAi Pro'),
                ],
                'alert_email' => $this->env('ALERT_EMAIL', 'admin@gxggm.com'),
                'throttle_interval' => $this->env('EMAIL_THROTTLE_INTERVAL', 300000, 'integer'),
                'encryption' => $this->env('MAIL_ENCRYPTION', 'ssl'),
            ],

            // 日志配置
            'logging' => [
                'level' => $this->env('LOG_LEVEL', 'info'),
                'file_path' => $this->env('LOG_FILE_PATH', './logs/app.log'),
                'channel' => $this->env('LOG_CHANNEL', 'daily'),
            ],

            // 内存配置
            'memory' => [
                'db_path' => $this->env('MEMORY_DB_PATH', './agents/memory.db'),
                'clean_threshold' => $this->env('MEMORY_CLEAN_THRESHOLD', 1000, 'integer'),
            ],

            // 监控配置
            'monitoring' => [
                'health_check_frequency' => $this->env('HEALTH_CHECK_FREQUENCY', 300000, 'integer'),
                'resource_check_interval' => $this->env('RESOURCE_CHECK_INTERVAL', 60000, 'integer'),
                'metrics_retention_days' => $this->env('METRICS_RETENTION_DAYS', 30, 'integer'),
                'db_monitor_interval' => $this->env('DB_MONITOR_INTERVAL', 60000, 'integer'),
            ],

            // 告警阈值
            'alerts' => [
                'cpu_warning' => $this->env('CPU_WARNING_THRESHOLD', 70, 'integer'),
                'cpu_critical' => $this->env('CPU_CRITICAL_THRESHOLD', 90, 'integer'),
                'memory_warning' => $this->env('MEMORY_WARNING_THRESHOLD', 80, 'integer'),
                'memory_critical' => $this->env('MEMORY_CRITICAL_THRESHOLD', 90, 'integer'),
                'disk_warning' => $this->env('DISK_WARNING_THRESHOLD', 85, 'integer'),
                'disk_critical' => $this->env('DISK_CRITICAL_THRESHOLD', 95, 'integer'),
                'response_time_warning' => $this->env('RESPONSE_TIME_WARNING', 1000, 'integer'),
                'response_time_critical' => $this->env('RESPONSE_TIME_CRITICAL', 5000, 'integer'),
            ],

            // 功能开关
            'features' => [
                'registration' => $this->env('FEATURE_REGISTRATION', true, 'boolean'),
                'email_verification' => $this->env('FEATURE_EMAIL_VERIFICATION', true, 'boolean'),
                'password_reset' => $this->env('FEATURE_PASSWORD_RESET', true, 'boolean'),
                'admin_panel' => $this->env('FEATURE_ADMIN_PANEL', true, 'boolean'),
                'chat' => $this->env('FEATURE_CHAT', true, 'boolean'),
                'documents' => $this->env('FEATURE_DOCUMENTS', true, 'boolean'),
            ],

            // 安全配置
            'security' => [
                'force_https' => $this->env('FORCE_HTTPS', false, 'boolean'),
                'csrf_protection' => $this->env('CSRF_PROTECTION', true, 'boolean'),
            ],

            // 文件上传配置
            'upload' => [
                'max_size' => $this->env('UPLOAD_MAX_SIZE', 10485760, 'integer'),
                'allowed_types' => explode(',', $this->env('UPLOAD_ALLOWED_TYPES', 'jpg,jpeg,png,gif,webp,pdf,doc,docx,txt,md')),
            ],

            // 开发配置
            'development' => [
                'show_errors' => $this->env('DEV_SHOW_ERRORS', true, 'boolean'),
                'enable_profiler' => $this->env('DEV_ENABLE_PROFILER', true, 'boolean'),
                'enable_debug_bar' => $this->env('DEV_ENABLE_DEBUG_BAR', true, 'boolean'),
            ],
        ];

        $this->loaded = true;
    }

    /**
     * 加载环境变量文件
     */
    private function loadEnvFile(string $path): void
    {
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos(trim($line), '#') === 0) {
                continue;
            }

            if (strpos($line, '=') !== false) {
                list($name, $value) = explode('=', $line, 2);
                $name = trim($name);
                $value = trim($value);
                
                // 移除引号
                if (preg_match('/^"(.*)"$/', $value, $matches)) {
                    $value = $matches[1];
                }
                
                $_ENV[$name] = $value;
                putenv("$name=$value");
            }
        }
    }

    /**
     * 获取环境变量
     */
    private function env(string $key, $default = null, string $type = 'string')
    {
        $value = $_ENV[$key] ?? getenv($key) ?? $default;

        if ($value === null) {
            return null;
        }

        switch ($type) {
            case 'boolean':
                return filter_var($value, FILTER_VALIDATE_BOOLEAN);
            case 'integer':
                return (int) $value;
            case 'float':
                return (float) $value;
            case 'array':
                return explode(',', $value);
            default:
                return $value;
        }
    }

    /**
     * 获取配置值
     */
    public function get(string $key, $default = null)
    {
        $keys = explode('.', $key);
        $value = $this->config;

        foreach ($keys as $k) {
            if (!isset($value[$k])) {
                return $default;
            }
            $value = $value[$k];
        }

        return $value;
    }

    /**
     * 设置配置值
     */
    public function set(string $key, $value): void
    {
        $keys = explode('.', $key);
        $config = &$this->config;

        foreach ($keys as $k) {
            if (!isset($config[$k]) || !is_array($config[$k])) {
                $config[$k] = [];
            }
            $config = &$config[$k];
        }

        $config = $value;
    }

    /**
     * 验证必需的配置
     */
    public function validateRequired(): array
    {
        $errors = [];
        $required = [
            'database.mysql.password' => 'MySQL密码',
            'jwt.secret' => 'JWT密钥',
            'mail.smtp.password' => 'SMTP密码',
            'ai.deepseek.api_key' => 'DeepSeek API密钥',
        ];

        foreach ($required as $key => $name) {
            if (empty($this->get($key))) {
                $errors[] = "缺少必需的配置: {$name} ({$key})";
            }
        }

        return $errors;
    }

    /**
     * 获取数据库连接配置
     */
    public function getDatabaseConfig(string $connection = null): array
    {
        $connection = $connection ?: $this->get('database.connection');
        
        switch ($connection) {
            case 'mysql':
                return [
                    'driver' => 'mysql',
                    'host' => $this->get('database.mysql.host'),
                    'port' => $this->get('database.mysql.port'),
                    'database' => $this->get('database.mysql.database'),
                    'username' => $this->get('database.mysql.username'),
                    'password' => $this->get('database.mysql.password'),
                    'charset' => $this->get('database.mysql.charset'),
                    'collation' => $this->get('database.mysql.collation'),
                    'options' => $this->get('database.mysql.options'),
                ];
            default:
                throw new \InvalidArgumentException("Unsupported database connection: {$connection}");
        }
    }

    /**
     * 获取所有配置
     */
    public function all(): array
    {
        return $this->config;
    }

    /**
     * 检查是否在开发环境
     */
    public function isDevelopment(): bool
    {
        return $this->get('app.env') === 'development';
    }

    /**
     * 检查是否启用调试
     */
    public function isDebug(): bool
    {
        return $this->get('app.debug', false);
    }
}
