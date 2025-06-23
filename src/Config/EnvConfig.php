<?php

namespace AlingAi\Config;

/**
 * 环境配置管理器
 */
class EnvConfig
{
    private static $config = null;
    
    public static function load($envFile = null)
    {
        if (self::$config !== null) {
            return self::$config;
        }
        
        $envFile = $envFile ?: __DIR__ . '/../../.env';
        $config = [];
        
        // 加载.env文件
        if (file_exists($envFile)) {
            $envLines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($envLines as $line) {
                if (strpos($line, '#') === 0 || strpos($line, '//') === 0) continue;
                if (strpos($line, '=') !== false) {
                    [$key, $value] = explode('=', $line, 2);
                    $key = trim($key);
                    $value = trim($value);
                    if (!empty($key)) {
                        $config[$key] = $value;
                    }
                }
            }
        }
        
        // 构建应用配置
        self::$config = [
            'app' => [
                'name' => $config['APP_NAME'] ?? 'AlingAi Pro',
                'env' => $config['APP_ENV'] ?? 'development',
                'debug' => ($config['APP_DEBUG'] ?? 'true') === 'true',
                'url' => $config['APP_URL'] ?? 'http://localhost:3000',
                'timezone' => $config['APP_TIMEZONE'] ?? 'Asia/Shanghai',
                'locale' => $config['APP_LOCALE'] ?? 'zh_CN'
            ],
            'database' => [
                'connection' => $config['DB_CONNECTION'] ?? 'mysql',
                'host' => $config['DB_HOST'] ?? '111.180.205.70',
                'port' => $config['DB_PORT'] ?? '3306',
                'name' => $config['DB_DATABASE'] ?? 'alingai',
                'user' => $config['DB_USERNAME'] ?? 'AlingAi',
                'password' => $config['DB_PASSWORD'] ?? '',
                'charset' => 'utf8mb4'
            ],
            'jwt' => [
                'secret' => $config['JWT_SECRET'] ?? 'your-jwt-secret-key-change-this',
                'issuer' => $config['JWT_ISSUER'] ?? 'alingai-pro',
                'audience' => $config['JWT_AUDIENCE'] ?? 'alingai-pro-app',
                'expire_time' => intval($config['JWT_EXPIRE_TIME'] ?? 3600),
                'refresh_expire_time' => intval($config['JWT_REFRESH_EXPIRE_TIME'] ?? 86400)
            ],
            'deepseek' => [
                'api_key' => $config['DEEPSEEK_API_KEY'] ?? '',
                'api_url' => $config['OPENAI_API_URL'] ?? 'https://api.deepseek.com',
                'model' => $config['DEEPSEEK_MODEL'] ?? 'deepseek-chat'
            ],
            'cache' => [
                'driver' => $config['CACHE_DRIVER'] ?? 'file',
                'redis_host' => $config['REDIS_HOST'] ?? '127.0.0.1',
                'redis_port' => $config['REDIS_PORT'] ?? '6379'
            ]
        ];
        
        return self::$config;
    }
    
    public static function get($key = null, $default = null)
    {
        $config = self::load();
        
        if ($key === null) {
            return $config;
        }
        
        $keys = explode('.', $key);
        $value = $config;
        
        foreach ($keys as $k) {
            if (!isset($value[$k])) {
                return $default;
            }
            $value = $value[$k];
        }
        
        return $value;
    }
}
