<?php
/**
 * 高级配置管理服务
 * 支持环境变量、数据库配置、缓存、验证等功能
 */

namespace App\Services;

use Psr\Log\LoggerInterface;

class ConfigService
{
    private static $instance = null;
    private $logger;
    private array $config = [];
    private array $cache = [];
    private string $configPath;
    private $database = null;
    
    public function __construct(LoggerInterface $logger = null, string $configPath = null)
    {
        $this->logger = $logger;
        $this->configPath = $configPath ?: dirname(__DIR__, 2) . '/config';
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
     * 加载配置
     */
    private function loadConfiguration(): void
    {
        try {
            $this->loadEnvironmentConfig();
            $this->loadDatabaseConfig();
        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->error('配置加载失败: ' . $e->getMessage());
            } else {
                error_log('配置加载失败: ' . $e->getMessage());
            }
        }
    }
    
    /**
     * 加载环境配置
     */
    private function loadEnvironmentConfig()
    {
        $envFile = dirname(__DIR__, 2) . '/.env';
        
        if (!file_exists($envFile)) {
            $envFile = dirname(__DIR__, 2) . '/.env.production';
        }
        
        if (file_exists($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            
            foreach ($lines as $line) {
                if (strpos($line, '#') === 0) continue;
                if (strpos($line, '=') === false) continue;
                
                list($key, $value) = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value, '"\'');
                
                // 类型转换
                if (strtolower($value) === 'true') {
                    $value = true;
                } elseif (strtolower($value) === 'false') {
                    $value = false;
                } elseif (is_numeric($value)) {
                    $value = is_float($value) ? (float)$value : (int)$value;
                }
                
                $this->config[$key] = $value;
                
                // 设置环境变量
                if (!getenv($key)) {
                    putenv("$key=$value");
                }
            }
        }
    }
    
    /**
     * 加载数据库配置
     */
    private function loadDatabaseConfig()
    {
        try {
            if ($this->database === null) {
                $this->initDatabase();
            }
            
            $stmt = $this->database->query("
                SELECT config_key, config_value, config_type 
                FROM system_configs 
                WHERE 1=1
            ");
            
            $dbConfigs = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            foreach ($dbConfigs as $config) {
                $value = $this->convertConfigValue($config['config_value'], $config['config_type']);
                $this->config[$config['config_key']] = $value;
            }
            
        } catch (\Exception $e) {
            // 数据库配置加载失败时使用环境配置
            if ($this->logger) {
                $this->logger->warning("数据库配置加载失败: " . $e->getMessage());
            } else {
                error_log("数据库配置加载失败: " . $e->getMessage());
            }
        }
    }
    
    /**
     * 初始化数据库连接
     */
    private function initDatabase()
    {
        $host = $this->config['DB_HOST'] ?? 'localhost';
        $port = $this->config['DB_PORT'] ?? 3306;
        $database = $this->config['DB_DATABASE'] ?? 'alingai';
        $username = $this->config['DB_USERNAME'] ?? 'root';
        $password = $this->config['DB_PASSWORD'] ?? '';
        
        $dsn = "mysql:host=$host;port=$port;dbname=$database;charset=utf8mb4";
        
        $this->database = new \PDO($dsn, $username, $password, [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC
        ]);
    }
    
    /**
     * 转换配置值类型
     */
    private function convertConfigValue($value, $type)
    {
        switch ($type) {
            case 'bool':
                return filter_var($value, FILTER_VALIDATE_BOOLEAN);
            case 'int':
                return (int)$value;
            case 'float':
                return (float)$value;
            case 'json':
                return json_decode($value, true);
            case 'array':
                return explode(',', $value);
            default:
                return $value;
        }
    }
    
    /**
     * 获取配置值
     */
    public function get($key, $default = null)
    {
        // 支持点分隔符访问嵌套配置
        if (strpos($key, '.') !== false) {
            $keys = explode('.', $key);
            $value = $this->config;
            
            foreach ($keys as $k) {
                if (is_array($value) && array_key_exists($k, $value)) {
                    $value = $value[$k];
                } else {
                    return $default;
                }
            }
            
            return $value;
        }
        
        return $this->config[$key] ?? $default;
    }
    
    /**
     * 设置配置值
     */
    public function set($key, $value, $persist = false)
    {
        $this->config[$key] = $value;
        
        if ($persist) {
            $this->saveToDatabase($key, $value);
        }
        
        return $this;
    }
    
    /**
     * 保存配置到数据库
     */
    private function saveToDatabase($key, $value)
    {
        try {
            if ($this->database === null) {
                $this->initDatabase();
            }
            
            $type = $this->getValueType($value);
            $valueStr = $this->serializeValue($value, $type);
            
            $stmt = $this->database->prepare("
                INSERT INTO system_configs (config_key, config_value, config_type) 
                VALUES (?, ?, ?)
                ON DUPLICATE KEY UPDATE 
                config_value = VALUES(config_value),
                config_type = VALUES(config_type),
                updated_at = CURRENT_TIMESTAMP
            ");
            
            $stmt->execute([$key, $valueStr, $type]);
            
        } catch (\Exception $e) {
            error_log("配置保存失败: " . $e->getMessage());
        }
    }
    
    /**
     * 获取值的类型
     */
    private function getValueType($value)
    {
        if (is_bool($value)) {
            return 'bool';
        } elseif (is_int($value)) {
            return 'int';
        } elseif (is_float($value)) {
            return 'float';
        } elseif (is_array($value)) {
            return 'json';
        } else {
            return 'string';
        }
    }
    
    /**
     * 序列化值
     */
    private function serializeValue($value, $type)
    {
        switch ($type) {
            case 'bool':
                return $value ? '1' : '0';
            case 'json':
                return json_encode($value);
            default:
                return (string)$value;
        }
    }
    
    /**
     * 检查配置是否存在
     */
    public function has($key)
    {
        return array_key_exists($key, $this->config);
    }
    
    /**
     * 获取所有配置
     */
    public function all()
    {
        return $this->config;
    }
    
    /**
     * 获取指定分组的配置
     */
    public function getGroup($group)
    {
        $configs = [];
        
        try {
            if ($this->database === null) {
                $this->initDatabase();
            }
            
            $stmt = $this->database->prepare("
                SELECT config_key, config_value, config_type 
                FROM system_configs 
                WHERE group_name = ?
                ORDER BY sort_order
            ");
            
            $stmt->execute([$group]);
            $dbConfigs = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            foreach ($dbConfigs as $config) {
                $value = $this->convertConfigValue($config['config_value'], $config['config_type']);
                $configs[$config['config_key']] = $value;
            }
            
        } catch (\Exception $e) {
            error_log("配置分组获取失败: " . $e->getMessage());
        }
        
        return $configs;
    }
    
    /**
     * 获取公开配置（前端可用）
     */
    public function getPublicConfigs()
    {
        $configs = [];
        
        try {
            if ($this->database === null) {
                $this->initDatabase();
            }
            
            $stmt = $this->database->query("
                SELECT config_key, config_value, config_type 
                FROM system_configs 
                WHERE is_public = 1
                ORDER BY sort_order
            ");
            
            $dbConfigs = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            foreach ($dbConfigs as $config) {
                $value = $this->convertConfigValue($config['config_value'], $config['config_type']);
                $configs[$config['config_key']] = $value;
            }
            
        } catch (\Exception $e) {
            error_log("公开配置获取失败: " . $e->getMessage());
        }
        
        return $configs;
    }
    
    /**
     * 获取数据库配置数组
     */
    public function getDatabaseConfig()
    {
        return [
            'host' => $this->get('DB_HOST', 'localhost'),
            'port' => $this->get('DB_PORT', 3306),
            'database' => $this->get('DB_DATABASE', 'alingai'),
            'username' => $this->get('DB_USERNAME', 'root'),
            'password' => $this->get('DB_PASSWORD', ''),
            'charset' => $this->get('DB_CHARSET', 'utf8mb4'),
            'collation' => $this->get('DB_COLLATION', 'utf8mb4_unicode_ci')
        ];
    }
    
    /**
     * 获取应用配置数组
     */
    public function getAppConfig()
    {
        return [
            'name' => $this->get('APP_NAME', 'AlingAi'),
            'version' => $this->get('APP_VERSION', '2.0.0'),
            'env' => $this->get('APP_ENV', 'production'),
            'debug' => $this->get('APP_DEBUG', false),
            'url' => $this->get('APP_URL', 'https://localhost'),
            'timezone' => $this->get('APP_TIMEZONE', 'UTC'),
            'language' => $this->get('APP_LANGUAGE', 'en')
        ];
    }
    
    /**
     * 重新加载配置
     */
    public function reload()
    {
        $this->config = [];
        $this->cache = [];
        $this->loadConfiguration();
        
        return $this;
    }
}
