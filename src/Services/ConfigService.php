<?php
/**
 * AlingAi Pro - 配置服务
 * 负责应用程序配置的加载、管理和访问
 * 
 * @package AlingAi\Pro\Services
 * @version 2.0.0
 * @author AlingAi Team
 * @created 2024-12-19
 */

declare(strict_types=1);

namespace App\Services;

use Psr\Log\LoggerInterface;

class ConfigService
{
    private static $instance = null;
    private array $config = [];
    private array $cache = [];
    private ?LoggerInterface $logger;
    private string $configPath;

    public function __construct(LoggerInterface $logger = null, string $configPath = null)
    {
        $this->logger = $logger;
        $this->configPath = $configPath ?: __DIR__ . '/../../config';
        $this->loadConfiguration();
    }

    /**
     * 获取单例实例
     */
    public static function getInstance(LoggerInterface $logger = null)
    {
        if (self::$instance === null) {
            self::$instance = new self($logger);
        }
        return self::$instance;
    }

    /**
     * 加载所有配置
     */
    private function loadConfiguration(): void
    {
        // 加载环境变量
        $this->loadEnvironmentConfig();
        
        // 加载配置文件
        $configFiles = ['app', 'database', 'cache', 'logging', 'auth', 'mail', 'security'];
        
        foreach ($configFiles as $file) {
            $this->loadConfigFile($file);
        }
        
        // 加载数据库配置
        $this->loadDatabaseConfig();
        
        // 初始化数据库连接
        $this->initDatabase();
        
        // 处理环境变量覆盖
        $this->processEnvironmentOverrides();
    }

    /**
     * 加载环境变量配置
     */
    private function loadEnvironmentConfig()
    {
        // 如果 .env 文件存在，加载它
        $envFile = dirname($this->configPath) . '/.env';
        if (file_exists($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (strpos(trim($line), '#') === 0) {
                    continue;
                }
                
                if (strpos($line, '=') !== false) {
                    list($key, $value) = explode('=', $line, 2);
                    $key = trim($key);
                    $value = trim($value, '"\'');
                    $_ENV[$key] = $value;
                    putenv("$key=$value");
                }
            }
        }
        
        // 处理环境变量覆盖
        $this->processEnvironmentOverrides();
    }

    /**
     * 加载数据库配置
     */
    private function loadDatabaseConfig()
    {
        $this->config['database'] = [
            'driver' => $_ENV['DB_DRIVER'] ?? 'sqlite',
            'host' => $_ENV['DB_HOST'] ?? 'localhost',
            'port' => (int)($_ENV['DB_PORT'] ?? 3306),
            'database' => $_ENV['DB_DATABASE'] ?? __DIR__ . '/../../storage/database.sqlite',
            'username' => $_ENV['DB_USERNAME'] ?? '',
            'password' => $_ENV['DB_PASSWORD'] ?? '',
            'charset' => $_ENV['DB_CHARSET'] ?? 'utf8mb4',
            'collation' => $_ENV['DB_COLLATION'] ?? 'utf8mb4_unicode_ci',
            'prefix' => $_ENV['DB_PREFIX'] ?? '',
            'options' => [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                \PDO::ATTR_EMULATE_PREPARES => false,
            ]
        ];
    }

    /**
     * 初始化数据库连接
     */
    private function initDatabase()
    {
        try {
            $dbConfig = $this->config['database'];
            
            if ($dbConfig['driver'] === 'sqlite') {
                $dsn = "sqlite:" . $dbConfig['database'];
                $pdo = new \PDO($dsn);
            } else {
                $dsn = "{$dbConfig['driver']}:host={$dbConfig['host']};port={$dbConfig['port']};dbname={$dbConfig['database']};charset={$dbConfig['charset']}";
                $pdo = new \PDO($dsn, $dbConfig['username'], $dbConfig['password'], $dbConfig['options']);
            }
            
            $this->config['database']['connection'] = $pdo;
            
        } catch (\PDOException $e) {
            if ($this->logger) {
                $this->logger->error('Database connection failed', ['error' => $e->getMessage()]);
            }
        }
    }

    /**
     * 转换配置值类型
     */
    private function convertConfigValue($value, $type)
    {
        switch ($type) {
            case 'bool':
            case 'boolean':
                return filter_var($value, FILTER_VALIDATE_BOOLEAN);
            case 'int':
            case 'integer':
                return (int) $value;
            case 'float':
                return (float) $value;
            default:
                return $value;
        }
    }

    /**
     * 加载单个配置文件
     */
    private function loadConfigFile(string $name): void
    {
        $configFile = $this->configPath . "/{$name}.php";
        
        if (file_exists($configFile)) {
            $config = require $configFile;
            if (is_array($config)) {
                $this->config[$name] = $config;
            }
        } else {
            if ($this->logger) {
                $this->logger->warning("Configuration file not found: {$configFile}");
            }
        }
    }

    /**
     * 处理环境变量覆盖
     */
    private function processEnvironmentOverrides(): void
    {
        $envMappings = [
            'APP_NAME' => 'app.name',
            'APP_ENV' => 'app.environment',
            'APP_DEBUG' => 'app.debug',
            'APP_URL' => 'app.url',
            'APP_TIMEZONE' => 'app.timezone',
            
            'DB_HOST' => 'database.host',
            'DB_PORT' => 'database.port',
            'DB_DATABASE' => 'database.database',
            'DB_USERNAME' => 'database.username',
            'DB_PASSWORD' => 'database.password',
            
            'REDIS_HOST' => 'cache.redis.host',
            'REDIS_PORT' => 'cache.redis.port',
            'REDIS_PASSWORD' => 'cache.redis.password',
            'REDIS_DATABASE' => 'cache.redis.database',
            
            'MAIL_DRIVER' => 'mail.driver',
            'MAIL_HOST' => 'mail.host',
            'MAIL_PORT' => 'mail.port',
            'MAIL_USERNAME' => 'mail.username',
            'MAIL_PASSWORD' => 'mail.password',
            'MAIL_ENCRYPTION' => 'mail.encryption',
            'MAIL_FROM_ADDRESS' => 'mail.from.address',
            'MAIL_FROM_NAME' => 'mail.from.name',
            
            'JWT_SECRET' => 'auth.jwt.secret',
            'JWT_TTL' => 'auth.jwt.ttl',
            'JWT_REFRESH_TTL' => 'auth.jwt.refresh_ttl',
            
            'LOG_LEVEL' => 'logging.level',
            'LOG_CHANNEL' => 'logging.default'
        ];
        
        foreach ($envMappings as $envVar => $configPath) {
            $value = $_ENV[$envVar] ?? null;
            if ($value !== null) {
                $this->setByPath($configPath, $this->castEnvironmentValue($value));
            }
        }
    }

    /**
     * 转换环境变量值类型
     */
    private function castEnvironmentValue($value)
    {
        if ($value === 'true' || $value === '(true)') {
            return true;
        }
        
        if ($value === 'false' || $value === '(false)') {
            return false;
        }
        
        if ($value === 'null' || $value === '(null)') {
            return null;
        }
        
        if (is_numeric($value)) {
            return strpos($value, '.') !== false ? (float) $value : (int) $value;
        }
        
        return $value;
    }

    /**
     * 获取配置值
     */
    public function get(string $key, $default = null)
    {
        // 检查缓存
        if (isset($this->cache[$key])) {
            return $this->cache[$key];
        }
        
        $value = $this->getByPath($key, $default);
        
        // 缓存结果
        $this->cache[$key] = $value;
        
        return $value;
    }

    /**
     * 设置配置值
     */
    public function set(string $key, $value): void
    {
        $this->setByPath($key, $value);
        
        // 更新缓存
        $this->cache[$key] = $value;
    }

    /**
     * 检查配置键是否存在
     */
    public function has(string $key): bool
    {
        return $this->getByPath($key, '__NOT_FOUND__') !== '__NOT_FOUND__';
    }

    /**
     * 获取所有配置
     */
    public function all(): array
    {
        return $this->config;
    }

    /**
     * 获取配置分组
     */
    public function getGroup(string $group): array
    {
        return $this->config[$group] ?? [];
    }

    /**
     * 根据路径获取值
     */
    private function getByPath(string $path, $default = null)
    {
        $keys = explode('.', $path);
        $value = $this->config;
        
        foreach ($keys as $key) {
            if (!is_array($value) || !array_key_exists($key, $value)) {
                return $default;
            }
            $value = $value[$key];
        }
        
        return $value;
    }

    /**
     * 根据路径设置值
     */
    private function setByPath(string $path, $value): void
    {
        $keys = explode('.', $path);
        $current = &$this->config;
        
        foreach ($keys as $i => $key) {
            if ($i === count($keys) - 1) {
                $current[$key] = $value;
            } else {
                if (!isset($current[$key]) || !is_array($current[$key])) {
                    $current[$key] = [];
                }
                $current = &$current[$key];
            }
        }
    }

    /**
     * 获取数据库配置
     */
    public function getDatabaseConfig(): array
    {
        return $this->getGroup('database');
    }

    /**
     * 获取缓存配置
     */
    public function getCacheConfig(): array
    {
        return $this->getGroup('cache');
    }

    /**
     * 获取邮件配置
     */
    public function getMailConfig(): array
    {
        return $this->getGroup('mail');
    }

    /**
     * 获取认证配置
     */
    public function getAuthConfig(): array
    {
        return $this->getGroup('auth');
    }

    /**
     * 获取日志配置
     */
    public function getLoggingConfig(): array
    {
        return $this->getGroup('logging');
    }

    /**
     * 获取安全配置
     */
    public function getSecurityConfig(): array
    {
        return $this->getGroup('security');
    }

    /**
     * 是否为调试模式
     */
    public function isDebug(): bool
    {
        return $this->get('app.debug', false);
    }

    /**
     * 获取环境
     */
    public function getEnvironment(): string
    {
        return $this->get('app.environment', 'production');
    }

    /**
     * 是否为生产环境
     */
    public function isProduction(): bool
    {
        return $this->getEnvironment() === 'production';
    }

    /**
     * 是否为开发环境
     */
    public function isDevelopment(): bool
    {
        return $this->getEnvironment() === 'development';
    }

    /**
     * 获取应用URL
     */
    public function getAppUrl(): string
    {
        return rtrim($this->get('app.url', 'http://localhost'), '/');
    }

    /**
     * 获取时区
     */
    public function getTimezone(): string
    {
        return $this->get('app.timezone', 'UTC');
    }

    /**
     * 刷新配置缓存
     */
    public function refresh(): void
    {
        $this->cache = [];
        $this->loadConfiguration();
    }

    /**
     * 验证必需的配置项
     */
    public function validateRequired(array $requiredKeys = []): array
    {
        $defaultRequired = [
            'app.name',
            'app.environment',
            'database.host',
            'database.database',
            'auth.jwt.secret'
        ];
        
        $required = array_merge($defaultRequired, $requiredKeys);
        $missing = [];
        
        foreach ($required as $key) {
            if (!$this->has($key) || $this->get($key) === '') {
                $missing[] = $key;
            }
        }
        
        return $missing;
    }
}
