<?php

namespace AlingAi\Utils;

use Psr\Log\LoggerInterface;

/**
 * 配置管理器
 * 
 * 提供强大的配置管理和验证功能
 * 优化性能：配置缓存、懒加载、增量更新
 * 增强功能：配置验证、热重载、环境管理
 */
class ConfigManager
{
    private LoggerInterface $logger;
    private array $config = [];
    private array $schema = [];
    private array $cache = [];
    private array $watchers = [];
    private string $configDir;
    
    public function __construct(LoggerInterface $logger, string $configDir = null)
    {
        $this->logger = $logger;
        $this->configDir = $configDir ?: dirname(__DIR__, 2) . '/config';
        
        $this->initializeDefaultSchema();
        $this->loadConfigurations();
    }
    
    /**
     * 初始化默认配置模式
     */
    private function initializeDefaultSchema(): void
    {
        $this->schema = [
            'app' => [
                'name' => ['type' => 'string', 'required' => true],
                'version' => ['type' => 'string', 'required' => true],
                'environment' => ['type' => 'string', 'required' => true, 'enum' => ['development', 'production', 'testing']],
                'debug' => ['type' => 'boolean', 'default' => false],
                'timezone' => ['type' => 'string', 'default' => 'UTC'],
                'locale' => ['type' => 'string', 'default' => 'zh_CN']
            ],
            'database' => [
                'driver' => ['type' => 'string', 'required' => true],
                'host' => ['type' => 'string', 'required' => true],
                'port' => ['type' => 'integer', 'default' => 3306],
                'database' => ['type' => 'string', 'required' => true],
                'username' => ['type' => 'string', 'required' => true],
                'password' => ['type' => 'string', 'required' => true],
                'charset' => ['type' => 'string', 'default' => 'utf8mb4'],
                'collation' => ['type' => 'string', 'default' => 'utf8mb4_unicode_ci'],
                'prefix' => ['type' => 'string', 'default' => ''],
                'strict' => ['type' => 'boolean', 'default' => true],
                'engine' => ['type' => 'string', 'default' => 'InnoDB']
            ],
            'cache' => [
                'driver' => ['type' => 'string', 'required' => true, 'enum' => ['file', 'redis', 'memcached']],
                'host' => ['type' => 'string', 'default' => 'localhost'],
                'port' => ['type' => 'integer', 'default' => 6379],
                'password' => ['type' => 'string', 'default' => null],
                'database' => ['type' => 'integer', 'default' => 0],
                'prefix' => ['type' => 'string', 'default' => 'alingai_'],
                'ttl' => ['type' => 'integer', 'default' => 3600]
            ],
            'session' => [
                'driver' => ['type' => 'string', 'default' => 'file'],
                'lifetime' => ['type' => 'integer', 'default' => 120],
                'expire_on_close' => ['type' => 'boolean', 'default' => false],
                'encrypt' => ['type' => 'boolean', 'default' => false],
                'files' => ['type' => 'string', 'default' => 'storage/sessions'],
                'connection' => ['type' => 'string', 'default' => null],
                'table' => ['type' => 'string', 'default' => 'sessions'],
                'store' => ['type' => 'string', 'default' => null],
                'lottery' => ['type' => 'array', 'default' => [2, 100]]
            ],
            'mail' => [
                'driver' => ['type' => 'string', 'default' => 'smtp'],
                'host' => ['type' => 'string', 'default' => 'localhost'],
                'port' => ['type' => 'integer', 'default' => 587],
                'username' => ['type' => 'string', 'default' => null],
                'password' => ['type' => 'string', 'default' => null],
                'encryption' => ['type' => 'string', 'default' => 'tls'],
                'from' => ['type' => 'array', 'required' => true],
                'markdown' => ['type' => 'array', 'default' => ['theme' => 'default', 'paths' => []]]
            ],
            'queue' => [
                'default' => ['type' => 'string', 'default' => 'sync'],
                'connections' => ['type' => 'array', 'default' => []]
            ],
            'logging' => [
                'default' => ['type' => 'string', 'default' => 'stack'],
                'channels' => ['type' => 'array', 'default' => []],
                'deprecations' => ['type' => 'array', 'default' => ['channel' => null, 'trace' => false]]
            ],
            'security' => [
                'jwt_secret' => ['type' => 'string', 'required' => true],
                'jwt_ttl' => ['type' => 'integer', 'default' => 3600],
                'bcrypt_rounds' => ['type' => 'integer', 'default' => 10],
                'rate_limiting' => ['type' => 'array', 'default' => []],
                'cors' => ['type' => 'array', 'default' => []]
            ],
            'ai' => [
                'providers' => ['type' => 'array', 'default' => []],
                'default_provider' => ['type' => 'string', 'default' => 'openai'],
                'models' => ['type' => 'array', 'default' => []],
                'api_keys' => ['type' => 'array', 'default' => []],
                'settings' => ['type' => 'array', 'default' => []]
            ]
        ];
    }
    
    /**
     * 加载配置
     */
    private function loadConfigurations(): void
    {
        try {
            // 加载环境配置
            $this->loadEnvironmentConfig();
            
            // 加载配置文件
            $this->loadConfigFiles();
            
            // 验证配置
            $this->validateConfigurations();
            
            $this->logger->info('配置加载完成', [
                'config_sections' => array_keys($this->config)
            ]);
            
        } catch (\Exception $e) {
            $this->logger->error('配置加载失败', [
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }
    
    /**
     * 加载环境配置
     */
    private function loadEnvironmentConfig(): void
    {
        $envFile = dirname($this->configDir, 1) . '/.env';
        
        if (file_exists($envFile)) {
            $envVars = parse_ini_file($envFile, false, INI_SCANNER_RAW);
            
            if ($envVars) {
                foreach ($envVars as $key => $value) {
                    $_ENV[$key] = $value;
                    $_SERVER[$key] = $value;
                }
            }
        }
    }
    
    /**
     * 加载配置文件
     */
    private function loadConfigFiles(): void
    {
        $configFiles = [
            'app.php',
            'database.php',
            'cache.php',
            'session.php',
            'mail.php',
            'queue.php',
            'logging.php',
            'security.php',
            'ai.php'
        ];
        
        foreach ($configFiles as $file) {
            $filePath = $this->configDir . '/' . $file;
            
            if (file_exists($filePath)) {
                $config = include $filePath;
                $section = basename($file, '.php');
                $this->config[$section] = $config;
            }
        }
    }
    
    /**
     * 验证配置
     */
    private function validateConfigurations(): void
    {
        foreach ($this->schema as $section => $sectionSchema) {
            if (isset($this->config[$section])) {
                $this->validateSection($section, $this->config[$section], $sectionSchema);
            } else {
                // 检查必需的部分
                $this->validateRequiredSection($section, $sectionSchema);
            }
        }
    }
    
    /**
     * 验证配置部分
     */
    private function validateSection(string $section, array $config, array $schema): void
    {
        foreach ($schema as $key => $rule) {
            $value = $config[$key] ?? null;
            
            // 检查必需字段
            if (isset($rule['required']) && $rule['required'] && $value === null) {
                throw new \InvalidArgumentException("配置项 {$section}.{$key} 是必需的");
            }
            
            // 如果值为null且有默认值，使用默认值
            if ($value === null && isset($rule['default'])) {
                $this->config[$section][$key] = $rule['default'];
                continue;
            }
            
            // 验证类型
            if ($value !== null) {
                $this->validateValue($section, $key, $value, $rule);
            }
        }
    }
    
    /**
     * 验证必需的部分
     */
    private function validateRequiredSection(string $section, array $schema): void
    {
        $requiredKeys = array_filter($schema, function($rule) {
            return isset($rule['required']) && $rule['required'];
        });
        
        if (!empty($requiredKeys)) {
            throw new \InvalidArgumentException("配置部分 {$section} 是必需的");
        }
        
        // 创建默认配置
        $this->config[$section] = [];
        foreach ($schema as $key => $rule) {
            if (isset($rule['default'])) {
                $this->config[$section][$key] = $rule['default'];
            }
        }
    }
    
    /**
     * 验证值
     */
    private function validateValue(string $section, string $key, $value, array $rule): void
    {
        $expectedType = $rule['type'];
        
        switch ($expectedType) {
            case 'string':
                if (!is_string($value)) {
                    throw new \InvalidArgumentException("配置项 {$section}.{$key} 必须是字符串");
                }
                break;
                
            case 'integer':
                if (!is_numeric($value)) {
                    throw new \InvalidArgumentException("配置项 {$section}.{$key} 必须是整数");
                }
                $this->config[$section][$key] = (int)$value;
                break;
                
            case 'boolean':
                if (!is_bool($value) && !in_array($value, [0, 1, '0', '1', 'true', 'false'], true)) {
                    throw new \InvalidArgumentException("配置项 {$section}.{$key} 必须是布尔值");
                }
                $this->config[$section][$key] = filter_var($value, FILTER_VALIDATE_BOOLEAN);
                break;
                
            case 'array':
                if (!is_array($value)) {
                    throw new \InvalidArgumentException("配置项 {$section}.{$key} 必须是数组");
                }
                break;
                
            default:
                // 未知类型，跳过验证
                break;
        }
        
        // 验证枚举值
        if (isset($rule['enum']) && !in_array($value, $rule['enum'])) {
            throw new \InvalidArgumentException("配置项 {$section}.{$key} 必须是以下值之一: " . implode(', ', $rule['enum']));
        }
        
        // 验证范围
        if (isset($rule['min']) && $value < $rule['min']) {
            throw new \InvalidArgumentException("配置项 {$section}.{$key} 不能小于 {$rule['min']}");
        }
        
        if (isset($rule['max']) && $value > $rule['max']) {
            throw new \InvalidArgumentException("配置项 {$section}.{$key} 不能大于 {$rule['max']}");
        }
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
        
        $keys = explode('.', $key);
        $value = $this->config;
        
        foreach ($keys as $k) {
            if (!isset($value[$k])) {
                return $default;
            }
            $value = $value[$k];
        }
        
        // 缓存结果
        $this->cache[$key] = $value;
        
        return $value;
    }
    
    /**
     * 设置配置值
     */
    public function set(string $key, $value): void
    {
        $keys = explode('.', $key);
        $config = &$this->config;
        
        foreach ($keys as $i => $k) {
            if ($i === count($keys) - 1) {
                $config[$k] = $value;
            } else {
                if (!isset($config[$k]) || !is_array($config[$k])) {
                    $config[$k] = [];
                }
                $config = &$config[$k];
            }
        }
        
        // 清除相关缓存
        $this->clearCache($key);
        
        // 通知观察者
        $this->notifyWatchers($key, $value);
    }
    
    /**
     * 检查配置是否存在
     */
    public function has(string $key): bool
    {
        $keys = explode('.', $key);
        $config = $this->config;
        
        foreach ($keys as $k) {
            if (!isset($config[$k])) {
                return false;
            }
            $config = $config[$k];
        }
        
        return true;
    }
    
    /**
     * 获取所有配置
     */
    public function all(): array
    {
        return $this->config;
    }
    
    /**
     * 获取配置部分
     */
    public function getSection(string $section): array
    {
        return $this->config[$section] ?? [];
    }
    
    /**
     * 设置配置部分
     */
    public function setSection(string $section, array $config): void
    {
        $this->config[$section] = $config;
        
        // 验证新配置
        if (isset($this->schema[$section])) {
            $this->validateSection($section, $config, $this->schema[$section]);
        }
        
        // 清除相关缓存
        $this->clearCache($section);
        
        // 通知观察者
        $this->notifyWatchers($section, $config);
    }
    
    /**
     * 添加配置模式
     */
    public function addSchema(string $section, array $schema): void
    {
        $this->schema[$section] = $schema;
    }
    
    /**
     * 获取配置模式
     */
    public function getSchema(string $section = null): array
    {
        if ($section) {
            return $this->schema[$section] ?? [];
        }
        
        return $this->schema;
    }
    
    /**
     * 重新加载配置
     */
    public function reload(): void
    {
        $this->cache = [];
        $this->loadConfigurations();
        
        $this->logger->info('配置重新加载完成');
    }
    
    /**
     * 热重载配置
     */
    public function hotReload(): void
    {
        $this->reload();
        
        // 通知所有观察者配置已重新加载
        foreach ($this->watchers as $watcher) {
            call_user_func($watcher, 'reload', null);
        }
    }
    
    /**
     * 添加配置观察者
     */
    public function watch(string $key, callable $callback): void
    {
        $this->watchers[$key] = $callback;
    }
    
    /**
     * 移除配置观察者
     */
    public function unwatch(string $key): void
    {
        unset($this->watchers[$key]);
    }
    
    /**
     * 通知观察者
     */
    private function notifyWatchers(string $key, $value): void
    {
        foreach ($this->watchers as $watchedKey => $callback) {
            if ($key === $watchedKey || strpos($key, $watchedKey . '.') === 0) {
                call_user_func($callback, $key, $value);
            }
        }
    }
    
    /**
     * 清除缓存
     */
    private function clearCache(string $key): void
    {
        // 清除精确匹配的缓存
        unset($this->cache[$key]);
        
        // 清除相关缓存
        foreach (array_keys($this->cache) as $cachedKey) {
            if (strpos($cachedKey, $key . '.') === 0) {
                unset($this->cache[$cachedKey]);
            }
        }
    }
    
    /**
     * 导出配置
     */
    public function export(string $format = 'php'): string
    {
        switch ($format) {
            case 'php':
                return $this->exportToPhp();
                
            case 'json':
                return json_encode($this->config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                
            case 'yaml':
                return $this->exportToYaml();
                
            default:
                throw new \InvalidArgumentException("不支持的导出格式: {$format}");
        }
    }
    
    /**
     * 导出为PHP
     */
    private function exportToPhp(): string
    {
        $output = "<?php\n\nreturn [\n";
        
        foreach ($this->config as $section => $config) {
            $output .= "    '{$section}' => " . $this->arrayToPhp($config, 1) . ",\n";
        }
        
        $output .= "];\n";
        
        return $output;
    }
    
    /**
     * 数组转PHP代码
     */
    private function arrayToPhp(array $array, int $indent = 0): string
    {
        if (empty($array)) {
            return '[]';
        }
        
        $indentStr = str_repeat('    ', $indent);
        $output = "[\n";
        
        foreach ($array as $key => $value) {
            $output .= $indentStr . "    '{$key}' => ";
            
            if (is_array($value)) {
                $output .= $this->arrayToPhp($value, $indent + 1);
            } elseif (is_string($value)) {
                $output .= "'" . addslashes($value) . "'";
            } elseif (is_bool($value)) {
                $output .= $value ? 'true' : 'false';
            } elseif (is_null($value)) {
                $output .= 'null';
            } else {
                $output .= $value;
            }
            
            $output .= ",\n";
        }
        
        $output .= $indentStr . "]";
        
        return $output;
    }
    
    /**
     * 导出为YAML
     */
    private function exportToYaml(): string
    {
        return $this->arrayToYaml($this->config);
    }
    
    /**
     * 数组转YAML
     */
    private function arrayToYaml(array $array, int $indent = 0): string
    {
        $yaml = '';
        $indentStr = str_repeat('  ', $indent);
        
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                if (empty($value)) {
                    $yaml .= "{$indentStr}{$key}: []\n";
                } else {
                    $yaml .= "{$indentStr}{$key}:\n";
                    $yaml .= $this->arrayToYaml($value, $indent + 1);
                }
            } else {
                $yaml .= "{$indentStr}{$key}: " . json_encode($value, JSON_UNESCAPED_UNICODE) . "\n";
            }
        }
        
        return $yaml;
    }
    
    /**
     * 获取环境变量
     */
    public function getEnv(string $key, $default = null)
    {
        return $_ENV[$key] ?? $_SERVER[$key] ?? $default;
    }
    
    /**
     * 设置环境变量
     */
    public function setEnv(string $key, $value): void
    {
        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
    }
    
    /**
     * 获取当前环境
     */
    public function getEnvironment(): string
    {
        return $this->get('app.environment', 'production');
    }
    
    /**
     * 检查是否为开发环境
     */
    public function isDevelopment(): bool
    {
        return $this->getEnvironment() === 'development';
    }
    
    /**
     * 检查是否为生产环境
     */
    public function isProduction(): bool
    {
        return $this->getEnvironment() === 'production';
    }
    
    /**
     * 检查是否为测试环境
     */
    public function isTesting(): bool
    {
        return $this->getEnvironment() === 'testing';
    }
    
    /**
     * 获取配置统计信息
     */
    public function getStats(): array
    {
        return [
            'sections' => count($this->config),
            'cache_entries' => count($this->cache),
            'watchers' => count($this->watchers),
            'schema_sections' => count($this->schema)
        ];
    }
} 